<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use App\Models\AcademicYear;
use Illuminate\Support\Facades\DB;
use Flux\Flux;

use Livewire\Attributes\Layout;

#[Layout('components.admin.layout')]
class AcademicYearManagement extends Component
{
    public $name = '';
    public $semester = 'ganjil';
    
    public $editingId = null;

    protected function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'semester' => 'required|in:ganjil,genap',
        ];
    }

    public function save()
    {
        $this->validate();

        // Check combination name + semester uniqueness
        $exists = AcademicYear::where('name', $this->name)
            ->where('semester', $this->semester)
            ->where('id', '!=', $this->editingId)
            ->exists();

        if ($exists) {
            $this->addError('name', 'Tahun ajaran dengan semester tersebut sudah ada.');
            return;
        }

        if ($this->editingId) {
            $ay = AcademicYear::findOrFail($this->editingId);
            $ay->update([
                'name' => $this->name,
                'semester' => $this->semester,
            ]);
            Flux::toast('Tahun ajaran berhasil diubah.', variant: 'success');
        } else {
            AcademicYear::create([
                'name' => $this->name,
                'semester' => $this->semester,
                'is_active' => false,
            ]);
            Flux::toast('Tahun ajaran berhasil ditambahkan.', variant: 'success');
        }

        $this->reset(['name', 'semester', 'editingId']);
    }

    public function edit($id)
    {
        $ay = AcademicYear::findOrFail($id);
        $this->editingId = $ay->id;
        $this->name = $ay->name;
        $this->semester = $ay->semester;
    }

    public function cancelEdit()
    {
        $this->reset(['name', 'semester', 'editingId']);
    }

    public function delete($id)
    {
        $ay = AcademicYear::findOrFail($id);
        
        if ($ay->is_active) {
            Flux::toast('Tidak dapat menghapus tahun ajaran yang sedang aktif.', variant: 'danger');
            return;
        }

        if ($ay->classrooms()->exists()) {
            Flux::toast('Tidak dapat menghapus tahun ajaran karena sudah memiliki kelas terkait.', variant: 'danger');
            return;
        }

        $ay->delete();
        Flux::toast('Tahun ajaran berhasil dihapus.', variant: 'success');
    }

    public function setActive($id)
    {
        DB::transaction(function () use ($id) {
            AcademicYear::where('is_active', true)->update(['is_active' => false]);
            AcademicYear::where('id', $id)->update(['is_active' => true]);
        });
        
        Flux::toast('Tahun ajaran aktif berhasil diubah.', variant: 'success');
    }

    public function render()
    {
        return view('livewire.admin.academic-year-management', [
            'academicYears' => AcademicYear::withCount('classrooms')->orderByDesc('id')->get()
        ]);
    }
}
