<?php

namespace App\Livewire\Guru\Jadwal;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\TeachingAssignment;
use App\Models\ScheduleEntry;
use App\Models\Classroom;
use Illuminate\Support\Facades\DB;

class Index extends Component
{
    use WithFileUploads;

    public $file;
    public $activeTab = 'jadwal'; // jadwal, import, tambah

    public $formClassId = '';
    public $formDayOfWeek = '';
    public $formStartPeriod = '';
    public $formEndPeriod = '';
    public $formRoom = '';

    public $isEditMode = false;
    public $editEntryId = null;

    public function tambahManual()
    {
        $this->validate([
            'formClassId' => 'required',
            'formDayOfWeek' => 'required|integer|between:1,7',
            'formStartPeriod' => 'required|integer|min:1',
            'formEndPeriod' => 'required|integer|gte:formStartPeriod|max:12',
            'formRoom' => 'nullable|string',
        ]);

        $assignment = TeachingAssignment::where('teacher_id', auth()->id())
            ->where('classroom_id', $this->formClassId)
            ->first();

        if ($assignment) {
            if ($this->isEditMode && $this->editEntryId) {
                $entry = ScheduleEntry::find($this->editEntryId);
                if ($entry) {
                    $entry->update([
                        'teaching_assignment_id' => $assignment->id,
                        'day_of_week' => $this->formDayOfWeek,
                        'start_period_no' => $this->formStartPeriod,
                        'end_period_no' => $this->formEndPeriod,
                        'room' => $this->formRoom ?? '-',
                    ]);
                    session()->flash('message', 'Jadwal berhasil diperbarui!');
                    $this->cancelEdit();
                }
            } else {
                $exists = ScheduleEntry::where('teaching_assignment_id', $assignment->id)
                    ->where('day_of_week', $this->formDayOfWeek)
                    ->where('start_period_no', $this->formStartPeriod)
                    ->exists();

                if (!$exists) {
                    ScheduleEntry::create([
                        'teaching_assignment_id' => $assignment->id,
                        'day_of_week' => $this->formDayOfWeek,
                        'start_period_no' => $this->formStartPeriod,
                        'end_period_no' => $this->formEndPeriod,
                        'room' => $this->formRoom ?? '-',
                    ]);
                    session()->flash('message', 'Jadwal berhasil ditambahkan!');
                    $this->cancelEdit();
                } else {
                    session()->flash('error', 'Jadwal sudah ada untuk waktu dan hari tersebut.');
                }
            }
        } else {
            session()->flash('error', 'Anda tidak ditugaskan di kelas tersebut.');
        }
    }

    public function cancelEdit()
    {
        $this->reset(['formClassId', 'formDayOfWeek', 'formStartPeriod', 'formEndPeriod', 'formRoom', 'isEditMode', 'editEntryId']);
        $this->activeTab = 'jadwal';
    }

    public function editEntry($id)
    {
        $entry = ScheduleEntry::find($id);
        if ($entry) {
            $assignment = TeachingAssignment::find($entry->teaching_assignment_id);
            if ($assignment && $assignment->teacher_id === auth()->id()) {
                $this->isEditMode = true;
                $this->editEntryId = $entry->id;
                $this->formClassId = $assignment->classroom_id;
                $this->formDayOfWeek = $entry->day_of_week;
                $this->formStartPeriod = $entry->start_period_no;
                $this->formEndPeriod = $entry->end_period_no;
                $this->formRoom = $entry->room;
                $this->activeTab = 'tambah';
            }
        }
    }

    public function render()
    {
        $assignments = TeachingAssignment::with(['classroom', 'scheduleEntries'])
            ->where('teacher_id', auth()->id())
            ->get();
            
        $schedule = [
            1 => [], // Senin
            2 => [], // Selasa
            3 => [], // Rabu
            4 => [], // Kamis
            5 => [], // Jumat
        ];
        
        foreach ($assignments as $assignment) {
            foreach ($assignment->scheduleEntries as $entry) {
                if (isset($schedule[$entry->day_of_week])) {
                    $schedule[$entry->day_of_week][] = [
                        'class' => $assignment->classroom->name,
                        'start' => $entry->start_period_no,
                        'end' => $entry->end_period_no,
                        'room' => $entry->room,
                        'note' => $entry->note,
                        'id' => $entry->id,
                    ];
                }
            }
        }
        
        // Sort each day by start period
        foreach ($schedule as $day => &$entries) {
            usort($entries, function($a, $b) {
                return $a['start'] <=> $b['start'];
            });
        }
        
        // Build matrix for grid layout
        $matrix = [];
        for ($d = 1; $d <= 7; $d++) {
            for ($s = 1; $s <= 12; $s++) {
                $matrix[$d][$s] = null;
            }
        }
        $teacherCode = auth()->user()->code ?? auth()->id(); // fallback to id if code doesn't exist
        foreach ($assignments as $assignment) {
            $subject = $assignment->subject ?? 'Informatika';
            foreach ($assignment->scheduleEntries as $entry) {
                for ($s = $entry->start_period_no; $s <= $entry->end_period_no; $s++) {
                    if ($entry->day_of_week >= 1 && $entry->day_of_week <= 7 && $s >= 1 && $s <= 12) {
                        $matrix[$entry->day_of_week][$s] = [
                            'class' => $assignment->classroom->name,
                            'subject' => $subject,
                            'code' => $teacherCode,
                            'id' => $entry->id,
                        ];
                    }
                }
            }
        }

        return view('livewire.guru.jadwal.index', [
            'schedule' => $schedule,
            'matrix' => $matrix,
            'assignments' => $assignments
        ])->layout('components.layouts.guru');
    }

    public function deleteEntry($id)
    {
        $entry = ScheduleEntry::find($id);
        if ($entry) {
            // Ensure this entry belongs to a TeachingAssignment owned by auth user
            $assignment = TeachingAssignment::find($entry->teaching_assignment_id);
            if ($assignment && $assignment->teacher_id === auth()->id()) {
                $entry->delete();
                session()->flash('message', 'Jadwal berhasil dihapus!');
            }
        }
    }

    public function downloadTemplate()
    {
        $csv = "Classroom_ID,Hari(1=Senin..5=Jumat),Jam_Mulai,Jam_Selesai,Ruangan\n";
        $csv .= "1,1,1,2,Lab Komputer 1\n";
        $csv .= "2,2,3,4,Lab Komputer 2\n";
        
        return response()->streamDownload(function() use ($csv) {
            echo $csv;
        }, 'template_jadwal.csv', [
            'Content-Type' => 'text/csv',
        ]);
    }
    
    public function exportJadwal()
    {
        $assignments = TeachingAssignment::with(['classroom', 'scheduleEntries'])
            ->where('teacher_id', auth()->id())
            ->get();
            
        $csv = "Kelas,Hari,Jam_Mulai,Jam_Selesai,Ruangan\n";
        $days = [1=>'Senin', 2=>'Selasa', 3=>'Rabu', 4=>'Kamis', 5=>'Jumat', 6=>'Sabtu', 7=>'Minggu'];
        
        foreach ($assignments as $assignment) {
            foreach ($assignment->scheduleEntries as $entry) {
                $className = $assignment->classroom->name;
                $dayName = $days[$entry->day_of_week] ?? $entry->day_of_week;
                $csv .= "\"{$className}\",\"{$dayName}\",{$entry->start_period_no},{$entry->end_period_no},\"{$entry->room}\"\n";
            }
        }
        
        return response()->streamDownload(function() use ($csv) {
            echo $csv;
        }, 'jadwal_mengajar.csv', [
            'Content-Type' => 'text/csv',
        ]);
    }

    public function import()
    {
        $this->validate([
            'file' => 'required|file|mimes:csv,txt|max:2048',
        ]);
        
        try {
            $path = $this->file->getRealPath();
            $data = array_map('str_getcsv', file($path));
            $header = array_shift($data); // remove header
            
            DB::beginTransaction();
            foreach ($data as $row) {
                if (count($row) < 5) continue;
                
                // Parse classroom id or name
                $classId = trim($row[0], '"\' ');
                if (!is_numeric($classId)) {
                    $classroom = Classroom::where('name', $classId)->first();
                    if ($classroom) {
                        $classId = $classroom->id;
                    } else {
                        continue; // Skip if classroom not found
                    }
                }
                
                // Parse day of week numeric or string
                $dayOfWeek = trim($row[1], '"\' ');
                if (!is_numeric($dayOfWeek)) {
                    $daysMap = ['senin'=>1, 'selasa'=>2, 'rabu'=>3, 'kamis'=>4, 'jumat'=>5, 'sabtu'=>6, 'minggu'=>7];
                    $dayLower = strtolower($dayOfWeek);
                    if (isset($daysMap[$dayLower])) {
                        $dayOfWeek = $daysMap[$dayLower];
                    } else {
                        continue; // Skip if day not valid
                    }
                }

                $assignment = TeachingAssignment::where('teacher_id', auth()->id())
                    ->where('classroom_id', $classId)
                    ->first();
                    
                if ($assignment) {
                    // Check if entry already exists
                    $exists = ScheduleEntry::where('teaching_assignment_id', $assignment->id)
                        ->where('day_of_week', $dayOfWeek)
                        ->where('start_period_no', $row[2])
                        ->exists();
                        
                    if (!$exists) {
                        ScheduleEntry::create([
                            'teaching_assignment_id' => $assignment->id,
                            'day_of_week' => $dayOfWeek,
                            'start_period_no' => trim($row[2], '"\' '),
                            'end_period_no' => trim($row[3], '"\' '),
                            'room' => trim($row[4], '"\' '),
                        ]);
                    }
                }
            }
            DB::commit();
            session()->flash('message', 'Jadwal berhasil diimport!');
            $this->activeTab = 'jadwal';
            $this->file = null;
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Gagal import: format tidak valid.');
        }
    }
}
