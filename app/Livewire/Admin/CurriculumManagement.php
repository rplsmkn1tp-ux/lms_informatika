<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\Element;
use App\Models\LearningObjective;
use App\Models\Setting;
use Illuminate\Validation\Rule;
use Flux\Flux;

#[Layout('components.admin.layout')]
class CurriculumManagement extends Component
{
    // Elements (only edit description)
    public $editingElementId = null;
    public $elementDescription = '';

    // Learning Objectives (TP)
    public $selectedElementId = null;
    public $tpEditingId = null;
    public $tpCode = '';
    public $tpTitle = '';
    public $tpDescription = '';
    public $tpSortOrder = 0;
    public $tpKktp = '';

    public function mount()
    {
        $this->selectedElementId = Element::first()?->id;
    }

    public function selectElement($id)
    {
        $this->selectedElementId = $id;
        $this->cancelEditTp();
    }

    // --- Element Management ---
    public function editElement($id)
    {
        $element = Element::findOrFail($id);
        $this->editingElementId = $element->id;
        $this->elementDescription = $element->description;
    }

    public function saveElement()
    {
        $this->validate([
            'elementDescription' => 'nullable|string',
        ]);

        $element = Element::findOrFail($this->editingElementId);
        $element->update(['description' => $this->elementDescription]);
        
        $this->editingElementId = null;
        $this->elementDescription = '';
        Flux::toast('Deskripsi elemen berhasil disimpan.', variant: 'success');
    }

    public function cancelEditElement()
    {
        $this->editingElementId = null;
        $this->elementDescription = '';
    }

    // --- TP Management ---
    public function editTp($id)
    {
        $tp = LearningObjective::findOrFail($id);
        $this->tpEditingId = $tp->id;
        $this->tpCode = $tp->code;
        $this->tpTitle = $tp->title;
        $this->tpDescription = $tp->description;
        $this->tpSortOrder = $tp->sort_order;
        $this->tpKktp = $tp->kktp;
    }

    public function saveTp()
    {
        $this->validate([
            'tpCode' => [
                'required',
                'string',
                'max:255',
                Rule::unique('learning_objectives', 'code')->where(function ($query) {
                    return $query->where('element_id', $this->selectedElementId);
                })->ignore($this->tpEditingId)
            ],
            'tpTitle' => 'required|string|max:255',
            'tpDescription' => 'nullable|string',
            'tpSortOrder' => 'required|integer',
            'tpKktp' => 'nullable|integer|min:0|max:100',
        ]);

        $data = [
            'element_id' => $this->selectedElementId,
            'code' => $this->tpCode,
            'title' => $this->tpTitle,
            'description' => $this->tpDescription,
            'sort_order' => $this->tpSortOrder,
            'kktp' => $this->tpKktp ?: null, // null means use default_kktp
            'is_template' => true,
        ];

        if ($this->tpEditingId) {
            $tp = LearningObjective::findOrFail($this->tpEditingId);
            $tp->update($data);
            Flux::toast('Tujuan Pembelajaran berhasil diubah.', variant: 'success');
        } else {
            $data['created_by'] = auth()->id();
            LearningObjective::create($data);
            Flux::toast('Tujuan Pembelajaran berhasil ditambahkan.', variant: 'success');
        }

        $this->cancelEditTp();
    }

    public function cancelEditTp()
    {
        $this->tpEditingId = null;
        $this->tpCode = '';
        $this->tpTitle = '';
        $this->tpDescription = '';
        $this->tpSortOrder = 0;
        $this->tpKktp = '';
    }

    public function deleteTp($id)
    {
        $tp = LearningObjective::findOrFail($id);
        $tp->delete();
        Flux::toast('Tujuan Pembelajaran berhasil dihapus.', variant: 'success');
    }

    public function render()
    {
        $elements = Element::all();
        $tps = [];
        if ($this->selectedElementId) {
            $tps = LearningObjective::where('element_id', $this->selectedElementId)
                                    ->orderBy('sort_order')
                                    ->get();
        }

        return view('livewire.admin.curriculum-management', [
            'elements' => $elements,
            'tps' => $tps,
            'default_kktp' => Setting::getValue('default_kktp', 75)
        ]);
    }
}
