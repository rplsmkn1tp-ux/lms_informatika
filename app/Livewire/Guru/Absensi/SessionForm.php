<?php

namespace App\Livewire\Guru\Absensi;

use Livewire\Component;
use App\Models\Classroom;
use App\Models\AttendanceSession;
use App\Models\AttendanceRecord;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Attributes\Layout;
use Carbon\Carbon;
use App\Services\AttendanceService;

class SessionForm extends Component
{
    use AuthorizesRequests;

    public Classroom $classroom;
    public ?AttendanceSession $session = null;
    
    public $meeting_date;
    public $meeting_no;
    public $topic;
    public $edit_reason = '';

    // Array of student attendance status [student_id => status]
    public $attendance = [];
    public $notes = [];

    // Mode: create, edit_open, edit_closed
    public $mode = 'create';
    
    public $students = [];

    #[Layout('components.layouts.guru')]
    public function mount(Classroom $classroom, $sessionId)
    {
        $this->classroom = $classroom;

        if (!Auth::user()->teachingAssignments()->where('classroom_id', $classroom->id)->exists()) {
            abort(403, 'Anda tidak berwenang.');
        }

        $this->students = $classroom->students()->orderBy('name')->get();

        if ($sessionId === 'new') {
            $this->mode = 'create';
            $this->meeting_date = Carbon::today()->format('Y-m-d');
            $lastSession = AttendanceSession::where('classroom_id', $classroom->id)->orderBy('meeting_no', 'desc')->first();
            $this->meeting_no = $lastSession ? $lastSession->meeting_no + 1 : 1;
            
            if (request()->has('topic') && request()->query('topic') !== 'Belum ada materi') {
                $this->topic = request()->query('topic');
            }
            
            foreach ($this->students as $student) {
                $this->attendance[$student->id] = 'hadir';
                $this->notes[$student->id] = '';
            }
        } else {
            $this->session = AttendanceSession::with('records')->findOrFail($sessionId);
            $this->meeting_date = $this->session->meeting_date->format('Y-m-d');
            $this->meeting_no = $this->session->meeting_no;
            $this->topic = $this->session->topic;
            
            $this->mode = $this->session->status === 'open' ? 'edit_open' : 'edit_closed';

            foreach ($this->students as $student) {
                $record = $this->session->records->where('student_id', $student->id)->first();
                $this->attendance[$student->id] = $record ? $record->status : 'hadir';
                $this->notes[$student->id] = $record ? $record->note : '';
            }
        }
    }

    public function markAllPresent()
    {
        foreach ($this->students as $student) {
            $this->attendance[$student->id] = 'hadir';
        }
    }

    public function save()
    {
        $this->validate([
            'meeting_date' => 'required|date',
            'meeting_no' => 'required|integer|min:1',
            'topic' => 'nullable|string|max:255',
            'edit_reason' => $this->mode === 'edit_closed' ? 'required|string|min:5' : 'nullable'
        ]);

        $formattedDate = Carbon::parse($this->meeting_date)->format('Y-m-d');

        if ($this->mode === 'create') {
            $this->session = AttendanceSession::create([
                'classroom_id' => $this->classroom->id,
                'teacher_id' => Auth::id(),
                'academic_year_id' => $this->classroom->academic_year_id,
                'meeting_date' => $formattedDate,
                'meeting_no' => $this->meeting_no,
                'topic' => $this->topic,
                'status' => 'open',
                'opened_at' => now(),
            ]);
        } else {
            $this->session->update([
                'meeting_date' => $formattedDate,
                'meeting_no' => $this->meeting_no,
                'topic' => $this->topic,
                'edit_reason' => $this->mode === 'edit_closed' ? $this->edit_reason : $this->session->edit_reason,
            ]);
        }

        $students = $this->classroom->students()->get();
        foreach ($students as $student) {
            $studentId = is_object($student) ? $student->id : $student['id'];
            $status = strtolower($this->attendance[$studentId] ?? 'hadir');
            if (!in_array($status, ['hadir', 'izin', 'sakit', 'alpa'])) {
                $status = 'hadir';
            }

            AttendanceRecord::updateOrCreate(
                [
                    'session_id' => $this->session->id,
                    'student_id' => $studentId,
                ],
                [
                    'status' => $status,
                    'note' => $this->notes[$studentId] ?? null,
                    'marked_by' => Auth::id(),
                    'marked_at' => now(),
                ]
            );
        }

        session()->flash('status', 'Absensi berhasil disimpan.');
        
        return redirect()->route('guru.absensi.session', $this->classroom->id);
    }

    public function closeSession()
    {
        $this->save(); // Save first
        
        $this->session->update([
            'status' => 'closed',
            'closed_at' => now(),
        ]);
        
        session()->flash('status', 'Absensi berhasil ditutup.');
        return redirect()->route('guru.absensi.session', $this->classroom->id);
    }

    public function render()
    {
        $service = new AttendanceService();
        $percentages = [];
        foreach ($this->students as $student) {
            $percentages[$student->id] = $service->getStudentAttendancePercentage($student->id, $this->classroom->id, $this->classroom->academic_year_id);
        }

        return view('livewire.guru.absensi.session-form', [
            'percentages' => $percentages
        ]);
    }
}
