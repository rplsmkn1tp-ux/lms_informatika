<?php

namespace App\Livewire\Admin;

use App\Models\Classroom;
use App\Models\AcademicYear;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Illuminate\Support\Str;

#[Layout('components.admin.layout')]
class ClassroomManagement extends Component
{
    public $academicYears;
    
    public $showModal = false;
    public $isEdit = false;
    public $classroomId = null;

    public $academic_year_id;
    public $name;
    public $grade;
    public $major;
    public $weekly_lesson_target = 1;

    public $selectedAcademicYear = null;

    public $showMembersModal = false;
    public $selectedClassroom = null;

    protected $rules = [
        'academic_year_id' => 'required|exists:academic_years,id',
        'name' => 'required|string|max:50',
        'grade' => 'required|integer',
        'major' => 'nullable|string|max:50',
        'weekly_lesson_target' => 'required|integer|min:1',
    ];

    public function mount()
    {
        $this->academicYears = AcademicYear::orderBy('id', 'desc')->get();
        if ($this->academicYears->isNotEmpty()) {
            $this->selectedAcademicYear = $this->academicYears->first()->id;
        }
    }

    #[On('updateAcademicYear')]
    public function updateAcademicYear($id)
    {
        $this->selectedAcademicYear = $id;
    }

    #[On('createClassroom')]
    public function create()
    {
        $this->resetInput();
        $this->isEdit = false;
        $this->showModal = true;
    }

    public function edit($id)
    {
        $this->resetInput();
        $this->isEdit = true;
        $this->classroomId = $id;

        $classroom = Classroom::findOrFail($id);
        $this->academic_year_id = $classroom->academic_year_id;
        $this->name = $classroom->name;
        $this->grade = $classroom->grade;
        $this->major = $classroom->major;
        $this->weekly_lesson_target = $classroom->weekly_lesson_target;

        $this->showModal = true;
    }

    public function generateJoinCode($name)
    {
        $prefix = preg_replace('/[^A-Za-z0-9]/', '', $name);
        $prefix = strtoupper(substr($prefix, 0, 5));
        $prefix = str_replace(['O', '0', 'I', '1'], ['X', '9', 'Y', '8'], $prefix);
        
        // Generate random 4 chars without O, 0, I, 1
        $chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
        $random = substr(str_shuffle(str_repeat($chars, 4)), 0, 4);

        return $prefix . '-' . $random;
    }

    public function save()
    {
        $this->validate();

        if ($this->isEdit) {
            $classroom = Classroom::findOrFail($this->classroomId);
            $classroom->update([
                'academic_year_id' => $this->academic_year_id,
                'name' => $this->name,
                'grade' => $this->grade,
                'major' => $this->major,
                'weekly_lesson_target' => $this->weekly_lesson_target,
            ]);
            session()->flash('success', 'Kelas berhasil diperbarui.');
        } else {
            Classroom::create([
                'academic_year_id' => $this->academic_year_id,
                'name' => $this->name,
                'grade' => $this->grade,
                'major' => $this->major,
                'weekly_lesson_target' => $this->weekly_lesson_target,
                'join_code' => $this->generateJoinCode($this->name),
                'join_code_active' => true,
            ]);
            session()->flash('success', 'Kelas berhasil ditambahkan.');
        }

        $this->showModal = false;
    }

    public function deleteClass($id)
    {
        $classroom = Classroom::withCount('students')->findOrFail($id);
        
        if ($classroom->students_count > 0) {
            session()->flash('error', 'Tidak dapat menghapus kelas yang sudah memiliki siswa.');
            return;
        }

        $classroom->delete();
        session()->flash('success', 'Kelas berhasil dihapus.');
    }

    public function regenerateJoinCode($id)
    {
        $classroom = Classroom::findOrFail($id);
        $classroom->update([
            'join_code' => $this->generateJoinCode($classroom->name)
        ]);
        session()->flash('success', 'Kode kelas berhasil di-regenerate.');
    }

    public function toggleJoinCodeActive($id)
    {
        $classroom = Classroom::findOrFail($id);
        $classroom->update([
            'join_code_active' => !$classroom->join_code_active
        ]);
        session()->flash('success', 'Status pendaftaran kelas diperbarui.');
    }

    public function viewMembers($id)
    {
        $this->selectedClassroom = Classroom::with('students')->findOrFail($id);
        $this->showMembersModal = true;
    }

    public function resetInput()
    {
        $this->academic_year_id = null;
        $this->name = '';
        $this->grade = '';
        $this->major = '';
        $this->weekly_lesson_target = 1;
        $this->classroomId = null;
    }

    public function render()
    {
        $classrooms = Classroom::with('academicYear')
            ->withCount('students')
            ->when($this->selectedAcademicYear, function($q) {
                $q->where('academic_year_id', $this->selectedAcademicYear);
            })
            ->get();
        return view('livewire.admin.classroom-management', compact('classrooms'));
    }
}
