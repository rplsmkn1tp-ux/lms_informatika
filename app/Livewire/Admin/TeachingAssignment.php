<?php

namespace App\Livewire\Admin;

use App\Models\User;
use App\Models\Classroom;
use App\Models\TeachingAssignment as AssignmentModel;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;

#[Layout('components.admin.layout')]
class TeachingAssignment extends Component
{
    public $assignmentId = null;
    public $teacher_id;
    public $classroom_id;
    public $subject;

    public $showModal = false;

    protected $rules = [
        'teacher_id' => 'required|exists:users,id',
        'classroom_id' => 'required|exists:classrooms,id',
        'subject' => 'required|string|max:50',
    ];

    #[On('createAssignment')]
    public function create()
    {
        $this->resetInput();
        $this->showModal = true;
    }

    public function edit($id)
    {
        $assignment = AssignmentModel::findOrFail($id);
        $this->assignmentId = $assignment->id;
        $this->teacher_id = $assignment->teacher_id;
        $this->classroom_id = $assignment->classroom_id;
        $this->subject = $assignment->subject;
        
        $this->resetValidation();
        $this->showModal = true;
    }

    public function save()
    {
        $this->validate();

        $exists = AssignmentModel::where('teacher_id', $this->teacher_id)
            ->where('classroom_id', $this->classroom_id)
            ->where('subject', $this->subject);

        if ($this->assignmentId) {
            $exists->where('id', '!=', $this->assignmentId);
        }

        if ($exists->exists()) {
            $this->addError('subject', 'Plot mengajar ini sudah ada.');
            return;
        }

        if ($this->assignmentId) {
            $assignment = AssignmentModel::findOrFail($this->assignmentId);
            $assignment->update([
                'teacher_id' => $this->teacher_id,
                'classroom_id' => $this->classroom_id,
                'subject' => $this->subject,
            ]);
            session()->flash('success', 'Plot mengajar berhasil diubah.');
        } else {
            AssignmentModel::create([
                'teacher_id' => $this->teacher_id,
                'classroom_id' => $this->classroom_id,
                'subject' => $this->subject,
            ]);
            session()->flash('success', 'Plot mengajar berhasil ditambahkan.');
        }

        $this->showModal = false;
        $this->resetInput();
    }

    public function deleteAssignment($id)
    {
        AssignmentModel::findOrFail($id)->delete();
        session()->flash('success', 'Plot mengajar berhasil dihapus.');
    }

    public function resetInput()
    {
        $this->assignmentId = null;
        $this->teacher_id = null;
        $this->classroom_id = null;
        $this->subject = '';
        $this->resetValidation();
    }

    public function render()
    {
        // Teachers active
        $teachers = User::where('role', 'guru')->where('status', 'active')->get();
        
        // Classes in active academic year
        $classrooms = Classroom::whereHas('academicYear', function ($query) {
            $query->where('is_active', true);
        })->get();

        $assignments = AssignmentModel::with(['teacher', 'classroom.academicYear'])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('livewire.admin.teaching-assignment', compact('teachers', 'classrooms', 'assignments'));
    }
}
