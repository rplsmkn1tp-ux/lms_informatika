<?php

namespace App\Livewire\Guru;

use App\Models\Element;
use App\Models\LearningObjective;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('components.layouts.guru')]
class TpManagement extends Component
{
    public $isModalOpen = false;
    
    public $tpId, $element_id, $code, $title, $description, $sort_order, $kktp;
    
    // Default KKTP from settings or fixed
    public $defaultKktp = 75;

    // Filter Elemen (dropdown: '' = semua, id = element_id)
    public $selectedElement = '';

    // Search query
    public $search = '';

    protected function rules()
    {
        return [
            'element_id' => 'required|exists:elements,id',
            'code' => [
                'required',
                'string',
                'max:50',
                \Illuminate\Validation\Rule::unique('learning_objectives', 'code')
                    ->where('element_id', $this->element_id)
                    ->ignore($this->tpId),
            ],
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'sort_order' => 'required|integer',
            'kktp' => 'nullable|integer|min:0|max:100',
        ];
    }

    protected $messages = [
        'code.unique' => 'Kode ATP sudah ada pada elemen ini. Silakan gunakan kode unik.',
    ];

    public function mount()
    {
        $this->defaultKktp = \App\Models\Setting::where('key', 'default_kktp')->value('value') ?? 75;
    }

    public function updatedElementId($val)
    {
        if ($val && !$this->tpId && empty($this->code)) {
            $element = Element::find($val);
            if ($element) {
                $maxSortOrder = LearningObjective::where('element_id', $val)
                    ->where(function ($q) {
                        $q->where('created_by', Auth::id())
                          ->orWhere('is_template', true);
                    })
                    ->max('sort_order') ?? 0;
                $this->sort_order = $maxSortOrder + 1;
                $this->code = $element->code . '-' . ($maxSortOrder + 1);
            }
        }
    }

    public function render()
    {
        $allElements = Element::orderBy('id')->get();

        $elementsQuery = Element::orderBy('id');
        if ($this->selectedElement) {
            $elementsQuery->where('id', $this->selectedElement);
        }
        $elements = $elementsQuery->get();

        $tpsQuery = LearningObjective::with(['element', 'lessons'])
            ->where(function ($q) {
                $q->where('created_by', Auth::id())
                  ->orWhere('is_template', true);
            });

        if ($this->selectedElement) {
            $tpsQuery->where('element_id', $this->selectedElement);
        }

        if (trim($this->search)) {
            $term = '%' . trim($this->search) . '%';
            $tpsQuery->where(function ($q) use ($term) {
                $q->where('code', 'like', $term)
                  ->orWhere('title', 'like', $term)
                  ->orWhere('description', 'like', $term);
            });
        }

        $myTps = $tpsQuery->orderBy('element_id')->orderBy('sort_order')->get();
        $templates = LearningObjective::where('is_template', true)->orderBy('element_id')->orderBy('sort_order')->get();

        // Count per element for badges and counters
        $countsByElement = LearningObjective::where(function ($q) {
                $q->where('created_by', Auth::id())
                  ->orWhere('is_template', true);
            })
            ->selectRaw('element_id, count(*) as count')
            ->groupBy('element_id')
            ->pluck('count', 'element_id')
            ->toArray();

        return view('livewire.guru.tp-management', [
            'elements' => $elements,
            'allElements' => $allElements,
            'myTps' => $myTps,
            'templates' => $templates,
            'countsByElement' => $countsByElement,
        ]);
    }

    public function create($elementId = null)
    {
        $this->resetInputFields();
        $this->kktp = $this->defaultKktp;

        if ($elementId) {
            $this->element_id = $elementId;
            $element = Element::find($elementId);
            if ($element) {
                $maxSortOrder = LearningObjective::where('element_id', $elementId)
                    ->where(function ($q) {
                        $q->where('created_by', Auth::id())
                          ->orWhere('is_template', true);
                    })
                    ->max('sort_order') ?? 0;
                $this->sort_order = $maxSortOrder + 1;
                $this->code = $element->code . '-' . ($maxSortOrder + 1);
            }
        }

        $this->isModalOpen = true;
    }

    public function edit($id)
    {
        $tp = LearningObjective::findOrFail($id);
        if ($tp->created_by !== Auth::id() && !$tp->is_template) abort(403);

        $this->tpId = $tp->id;
        $this->element_id = $tp->element_id;
        $this->code = $tp->code;
        $this->title = $tp->title;
        $this->description = $tp->description;
        $this->sort_order = $tp->sort_order;
        $this->kktp = $tp->kktp;
        $this->isModalOpen = true;
    }

    public function save()
    {
        $this->validate();

        if ($this->tpId) {
            $tp = LearningObjective::findOrFail($this->tpId);
            if ($tp->created_by !== Auth::id() && !$tp->is_template) abort(403);
            
            $tp->update([
                'element_id' => $this->element_id,
                'code' => $this->code,
                'title' => $this->title,
                'description' => $this->description,
                'sort_order' => $this->sort_order,
                'kktp' => $this->kktp,
                'created_by' => Auth::id(),
                'is_template' => false,
            ]);
        } else {
            LearningObjective::create([
                'element_id' => $this->element_id,
                'code' => $this->code,
                'title' => $this->title,
                'description' => $this->description,
                'sort_order' => $this->sort_order,
                'kktp' => $this->kktp,
                'is_template' => false,
                'created_by' => Auth::id(),
            ]);
        }

        $this->closeModal();
        session()->flash('success', 'Tujuan Pembelajaran berhasil disimpan.');
    }

    public function delete($id)
    {
        $tp = LearningObjective::withCount(['lessons', 'assessments'])->findOrFail($id);
        if ($tp->created_by !== Auth::id() && !$tp->is_template) abort(403);
        
        $isUsedInAssessmentsPivot = \Illuminate\Support\Facades\DB::table('assessment_learning_objective')
            ->where('learning_objective_id', $tp->id)
            ->exists();

        if ($tp->lessons_count > 0 || $tp->assessments_count > 0 || $isUsedInAssessmentsPivot) {
            session()->flash('error', 'ATP ' . $tp->code . ' tidak dapat dihapus karena sudah digunakan oleh materi atau tugas.');
            return;
        }

        $tp->delete();
        session()->flash('success', 'Tujuan Pembelajaran berhasil dihapus.');
    }

    public function updateOrder($orderedIds)
    {
        foreach ($orderedIds as $index => $id) {
            LearningObjective::where('id', $id)
                ->where(function ($q) {
                    $q->where('created_by', Auth::id())
                      ->orWhere('is_template', true);
                })
                ->update(['sort_order' => $index + 1]);
        }
    }

    public function copyTemplate($id = null)
    {
        $query = LearningObjective::where('is_template', true);
        if ($id) {
            $query->where('id', $id);
        }
        $templates = $query->get();
        $count = 0;
        
        foreach ($templates as $template) {
            if ($template->created_by !== Auth::id()) {
                $template->update([
                    'created_by' => Auth::id(),
                    'is_template' => false,
                ]);
                $count++;
            }
        }

        if ($count > 0) {
            session()->flash('success', "Berhasil menyalin {$count} ATP dari template.");
        } else {
            session()->flash('success', "Semua template ATP sudah aktif di akun Anda.");
        }
    }

    public function closeModal()
    {
        $this->isModalOpen = false;
        $this->resetInputFields();
    }

    private function resetInputFields()
    {
        $this->tpId = null;
        $this->element_id = null;
        $this->code = '';
        $this->title = '';
        $this->description = '';
        $this->sort_order = 1;
        $this->kktp = $this->defaultKktp;
    }
}



