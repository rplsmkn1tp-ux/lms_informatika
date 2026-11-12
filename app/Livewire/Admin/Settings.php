<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\WithFileUploads;
use App\Models\Setting;
use Illuminate\Support\Facades\Storage;
use Flux\Flux;

#[Layout('components.admin.layout')]
class Settings extends Component
{
    use WithFileUploads;

    public $app_name = 'Ruang Informatika';
    public $app_subtitle = 'SMK Negeri 1 Tanjungpandan';
    public $registration_open = true;
    public $teacher_approval_required = true;
    public $default_kktp = 75;
    
    public $dashboard_guru_title = 'Halo, Guru {nama}. Siap mengajar hari ini?';
    public $dashboard_guru_subtitle = 'Semester Ganjil 2026/2027';
    public $dashboard_siswa_title = 'Halo, {nama}. Siap lanjut belajar?';
    public $dashboard_siswa_subtitle = '';
    
    public $logo;
    public $currentLogoUrl = null;

    public function mount()
    {
        // Pioritaskan app_name dari yang terbaru atau jika sama, baca dari 'app_name'
        $this->app_name = Setting::getValue('app_name', 'Ruang Informatika');
        $this->app_subtitle = Setting::getValue('app_subtitle', 'SMK Negeri 1 Tanjungpandan');
        $this->registration_open = Setting::getValue('registration_open', true);
        $this->teacher_approval_required = Setting::getValue('teacher_approval_required', true);
        $this->default_kktp = Setting::getValue('default_kktp', 75);
        $this->dashboard_guru_title = Setting::getValue('dashboard_guru_title', 'Halo, Guru {nama}. Siap mengajar hari ini?');
        $this->dashboard_guru_subtitle = Setting::getValue('dashboard_guru_subtitle', 'Semester Ganjil 2026/2027');
        $this->dashboard_siswa_title = Setting::getValue('dashboard_siswa_title', 'Halo, {nama}. Siap lanjut belajar?');
        $this->dashboard_siswa_subtitle = Setting::getValue('dashboard_siswa_subtitle', '');
        $this->currentLogoUrl = Setting::getValue('app_logo') ?? Setting::getValue('logo');
    }

    public function save()
    {
        if (auth()->user()->role !== 'admin') {
            abort(403);
        }

        $this->validate([
            'app_name' => 'required|string|max:255',
            'app_subtitle' => 'nullable|string|max:255',
            'registration_open' => 'boolean',
            'teacher_approval_required' => 'boolean',
            'default_kktp' => 'required|integer|min:0|max:100',
            'dashboard_guru_title' => 'required|string|max:255',
            'dashboard_guru_subtitle' => 'nullable|string|max:255',
            'dashboard_siswa_title' => 'required|string|max:255',
            'dashboard_siswa_subtitle' => 'nullable|string|max:255',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,svg,webp|max:2048',
        ]);

        Setting::setValue('app_name', $this->app_name);
        Setting::setValue('app_subtitle', $this->app_subtitle);
        Setting::setValue('registration_open', $this->registration_open);
        Setting::setValue('teacher_approval_required', $this->teacher_approval_required);
        Setting::setValue('default_kktp', $this->default_kktp);
        Setting::setValue('dashboard_guru_title', $this->dashboard_guru_title);
        Setting::setValue('dashboard_guru_subtitle', $this->dashboard_guru_subtitle ?? '');
        Setting::setValue('dashboard_siswa_title', $this->dashboard_siswa_title);
        Setting::setValue('dashboard_siswa_subtitle', $this->dashboard_siswa_subtitle ?? '');

        if ($this->logo) {
            if ($this->currentLogoUrl) {
                Storage::disk('public')->delete($this->currentLogoUrl);
            }
            $path = $this->logo->store('logos', 'public');
            Setting::setValue('app_logo', $path);
            $this->currentLogoUrl = $path;
        }

        Flux::toast('Pengaturan berhasil disimpan', variant: 'success');
        $this->logo = null;
    }

    public function removeLogo()
    {
        if (auth()->user()->role !== 'admin') {
            abort(403);
        }

        if ($this->currentLogoUrl) {
            Storage::disk('public')->delete($this->currentLogoUrl);
            Setting::setValue('app_logo', null);
            $this->currentLogoUrl = null;
        }

        Flux::toast('Logo dikembalikan ke default', variant: 'success');
    }

    public function render()
    {
        return view('livewire.admin.settings');
    }
}
