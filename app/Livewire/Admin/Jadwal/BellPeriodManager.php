<?php

namespace App\Livewire\Admin\Jadwal;

use App\Models\AcademicYear;
use App\Models\BellPeriod;
use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('components.admin.layout')]
class BellPeriodManager extends Component
{
    public $academic_year_id;
    public $day_of_week;
    public $period_no;
    public $starts_at;
    public $ends_at;
    public $type = 'pelajaran'; // pelajaran/istirahat/kegiatan
    public $label;

    public $periodId = null;
    public $showModal = false;

    protected $rules = [
        'academic_year_id' => 'required|exists:academic_years,id',
        'day_of_week' => 'required|integer|min:1|max:7',
        'period_no' => 'required|integer|min:1',
        'starts_at' => 'required',
        'ends_at' => 'required',
        'type' => 'required|in:pelajaran,istirahat,kegiatan',
        'label' => 'nullable|string|max:50',
    ];

    public function mount()
    {
        $activeYear = AcademicYear::where('is_active', true)->first();
        if ($activeYear) {
            $this->academic_year_id = $activeYear->id;
        }
    }

    public function create()
    {
        $this->resetInput();
        $this->showModal = true;
    }

    public function edit($id)
    {
        $period = BellPeriod::findOrFail($id);
        $this->periodId = $period->id;
        $this->academic_year_id = $period->academic_year_id;
        $this->day_of_week = $period->day_of_week;
        $this->period_no = $period->period_no;
        $this->starts_at = $period->starts_at;
        $this->ends_at = $period->ends_at;
        $this->type = $period->type;
        $this->label = $period->label;

        $this->resetValidation();
        $this->showModal = true;
    }

    public function save()
    {
        $this->validate();

        $data = [
            'academic_year_id' => $this->academic_year_id,
            'day_of_week' => $this->day_of_week,
            'period_no' => $this->period_no,
            'starts_at' => $this->starts_at,
            'ends_at' => $this->ends_at,
            'type' => $this->type,
            'label' => $this->label,
        ];

        if ($this->periodId) {
            BellPeriod::findOrFail($this->periodId)->update($data);
            session()->flash('success', 'Waktu pelajaran berhasil diubah.');
        } else {
            BellPeriod::create($data);
            session()->flash('success', 'Waktu pelajaran berhasil ditambahkan.');
        }

        $this->showModal = false;
        $this->resetInput();
    }

    public function deletePeriod($id)
    {
        BellPeriod::findOrFail($id)->delete();
        session()->flash('success', 'Waktu pelajaran berhasil dihapus.');
    }

    public function resetInput()
    {
        $this->periodId = null;
        $this->day_of_week = 1;
        $this->period_no = 1;
        $this->starts_at = '07:00';
        $this->ends_at = '07:45';
        $this->type = 'pelajaran';
        $this->label = '';
        $this->resetValidation();
    }

    public function render()
    {
        $academicYears = AcademicYear::orderBy('name', 'desc')->get();
        $periods = BellPeriod::with('academicYear')
            ->where('academic_year_id', $this->academic_year_id)
            ->orderBy('day_of_week')
            ->orderBy('period_no')
            ->get();

        return view('livewire.admin.jadwal.bell-period-manager', compact('academicYears', 'periods'));
    }
}
