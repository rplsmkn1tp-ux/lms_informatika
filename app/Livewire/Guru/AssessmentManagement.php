<?php

namespace App\Livewire\Guru;

use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('components.layouts.guru')]
class AssessmentManagement extends Component
{
    public $isModalOpen = false;
    public $assessmentId;
    public $learning_objective_id, $title, $type, $instructions;

    protected $rules = [
        'learning_objective_id' => 'required|exists:learning_objectives,id',
        'title' => 'required|string|max:255',
        'type' => 'required|in:tugas,quiz',
        'instructions' => 'nullable|string',
    ];

    public function render()
    {
        $assessments = \App\Models\Assessment::where('created_by', \Illuminate\Support\Facades\Auth::id())->get();
        $tps = \App\Models\LearningObjective::where('created_by', \Illuminate\Support\Facades\Auth::id())->get();
        return view('livewire.guru.assessment-management', compact('assessments', 'tps'));
    }

    public function create()
    {
        $this->reset(['assessmentId', 'learning_objective_id', 'title', 'type', 'instructions']);
        $this->isModalOpen = true;
    }

    public function edit($id)
    {
        $assessment = \App\Models\Assessment::findOrFail($id);
        if ($assessment->created_by !== \Illuminate\Support\Facades\Auth::id()) abort(403);
        $this->assessmentId = $assessment->id;
        $this->learning_objective_id = $assessment->learning_objective_id;
        $this->title = $assessment->title;
        $this->type = $assessment->type;
        $this->instructions = $assessment->instructions;
        $this->isModalOpen = true;
    }

    public function save()
    {
        $this->validate();
        
        $tp = \App\Models\LearningObjective::findOrFail($this->learning_objective_id);
        if ($tp->created_by !== \Illuminate\Support\Facades\Auth::id()) abort(403);

        if ($this->assessmentId) {
            $assessment = \App\Models\Assessment::findOrFail($this->assessmentId);
            if ($assessment->created_by !== \Illuminate\Support\Facades\Auth::id()) abort(403);
            $assessment->update([
                'learning_objective_id' => $this->learning_objective_id,
                'title' => $this->title,
                'type' => $this->type,
                'instructions' => $this->instructions,
            ]);
            session()->flash('message', 'Asesmen diperbarui.');
        } else {
            \App\Models\Assessment::create([
                'learning_objective_id' => $this->learning_objective_id,
                'title' => $this->title,
                'type' => $this->type,
                'instructions' => $this->instructions,
                'status' => 'draft',
                'created_by' => \Illuminate\Support\Facades\Auth::id(),
            ]);
            session()->flash('message', 'Asesmen ditambahkan.');
        }
        $this->isModalOpen = false;
    }

    public function delete($id)
    {
        $assessment = \App\Models\Assessment::findOrFail($id);
        if ($assessment->created_by !== \Illuminate\Support\Facades\Auth::id()) abort(403);
        $assessment->delete();
        session()->flash('message', 'Asesmen dihapus.');
    }
}



