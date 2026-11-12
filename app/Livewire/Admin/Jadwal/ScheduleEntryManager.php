<?php

namespace App\Livewire\Admin\Jadwal;

use App\Models\TeachingAssignment;
use App\Models\ScheduleEntry;
use App\Models\BellPeriod;
use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('components.admin.layout')]
class ScheduleEntryManager extends Component
{
    public TeachingAssignment $assignment;

    public $entryId = null;
    public $day_of_week = 1;
    public $start_period_no = 1;
    public $end_period_no = 1;
    public $room = '';
    public $note = '';

    public $showModal = false;

    protected $rules = [
        'day_of_week' => 'required|integer|min:1|max:7',
        'start_period_no' => 'required|integer|min:1',
        'end_period_no' => 'required|integer|min:1|gte:start_period_no',
        'room' => 'nullable|string|max:50',
        'note' => 'nullable|string|max:255',
    ];

    public function mount(TeachingAssignment $assignment)
    {
        $this->assignment = $assignment;
    }

    public function create()
    {
        $this->resetInput();
        $this->showModal = true;
    }

    public function edit($id)
    {
        $entry = ScheduleEntry::findOrFail($id);
        $this->entryId = $entry->id;
        $this->day_of_week = $entry->day_of_week;
        $this->start_period_no = $entry->start_period_no;
        $this->end_period_no = $entry->end_period_no;
        $this->room = $entry->room;
        $this->note = $entry->note;

        $this->resetValidation();
        $this->showModal = true;
    }

    public function save()
    {
        $this->validate();

        // Validasi tidak boleh melintasi jam istirahat
        $academicYearId = $this->assignment->classroom->academic_year_id;
        $periods = BellPeriod::where('academic_year_id', $academicYearId)
            ->where('day_of_week', $this->day_of_week)
            ->whereBetween('period_no', [$this->start_period_no, $this->end_period_no])
            ->get();

        foreach ($periods as $period) {
            if ($period->type === 'istirahat' || $period->type === 'kegiatan') {
                $this->addError('end_period_no', 'Slot jadwal mengajar tidak boleh melintasi jam istirahat/kegiatan.');
                return;
            }
        }

        $data = [
            'teaching_assignment_id' => $this->assignment->id,
            'day_of_week' => $this->day_of_week,
            'start_period_no' => $this->start_period_no,
            'end_period_no' => $this->end_period_no,
            'room' => $this->room,
            'note' => $this->note,
        ];

        if ($this->entryId) {
            ScheduleEntry::findOrFail($this->entryId)->update($data);
            session()->flash('success', 'Jadwal berhasil diubah.');
        } else {
            ScheduleEntry::create($data);
            session()->flash('success', 'Jadwal berhasil ditambahkan.');
        }

        $this->showModal = false;
        $this->resetInput();
    }

    public function deleteEntry($id)
    {
        ScheduleEntry::findOrFail($id)->delete();
        session()->flash('success', 'Jadwal berhasil dihapus.');
    }

    public function resetInput()
    {
        $this->entryId = null;
        $this->day_of_week = 1;
        $this->start_period_no = 1;
        $this->end_period_no = 1;
        $this->room = '';
        $this->note = '';
        $this->resetValidation();
    }

    public function render()
    {
        $entries = ScheduleEntry::where('teaching_assignment_id', $this->assignment->id)
            ->orderBy('day_of_week')
            ->orderBy('start_period_no')
            ->get();

        return view('livewire.admin.jadwal.schedule-entry-manager', compact('entries'));
    }
}
