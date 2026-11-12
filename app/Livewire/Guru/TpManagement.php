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

    protected $rules = [
        'element_id' => 'required|exists:elements,id',
        'code' => 'required|string|max:50',
        'title' => 'required|string|max:255',
        'description' => 'nullable|string',
        'sort_order' => 'required|integer',
        'kktp' => 'required|integer|min:0|max:100',
    ];

    public function mount()
    {
        $this->defaultKktp = \App\Models\Setting::where('key', 'default_kktp')->value('value') ?? 75;
    }

    public function render()
    {
        $elements = Element::all();
        $myTps = LearningObjective::where('created_by', Auth::id())->orderBy('element_id')->orderBy('sort_order')->get();
        $templates = LearningObjective::where('is_template', true)->orderBy('element_id')->orderBy('sort_order')->get();

        return view('livewire.guru.tp-management', compact('elements', 'myTps', 'templates'));
    }

    public function create()
    {
        $this->resetInputFields();
        $this->kktp = $this->defaultKktp;
        $this->isModalOpen = true;
    }

    public function edit($id)
    {
        $tp = LearningObjective::findOrFail($id);
        if ($tp->created_by !== Auth::id()) abort(403);

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
            if ($tp->created_by !== Auth::id()) abort(403);
            
            $tp->update([
                'element_id' => $this->element_id,
                'code' => $this->code,
                'title' => $this->title,
                'description' => $this->description,
                'sort_order' => $this->sort_order,
                'kktp' => $this->kktp,
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
        $tp = LearningObjective::findOrFail($id);
        if ($tp->created_by !== Auth::id()) abort(403);
        
        $tp->delete();
        session()->flash('success', 'Tujuan Pembelajaran berhasil dihapus.');
    }

    public function updateOrder($orderedIds)
    {
        foreach ($orderedIds as $index => $id) {
            LearningObjective::where('id', $id)
                ->where('created_by', Auth::id())
                ->update(['sort_order' => $index + 1]);
        }
    }

    public function copyTemplate()
    {
        $templates = LearningObjective::where('is_template', true)->get();
        $count = 0;
        
        foreach ($templates as $template) {
            $exists = LearningObjective::where('created_by', Auth::id())
                ->where('element_id', $template->element_id)
                ->where('code', $template->code)
                ->exists();
                
            if (!$exists) {
                LearningObjective::create([
                    'element_id' => $template->element_id,
                    'code' => $template->code,
                    'title' => $template->title,
                    'description' => $template->description,
                    'sort_order' => $template->sort_order,
                    'kktp' => $template->kktp ?? $this->defaultKktp,
                    'is_template' => false,
                    'created_by' => Auth::id(),
                ]);
                $count++;
            }
        }
        
        session()->flash('success', $count . ' TP berhasil disalin dari master.');
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



