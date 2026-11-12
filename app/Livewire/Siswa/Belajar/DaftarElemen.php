<?php

namespace App\Livewire\Siswa\Belajar;

use App\Models\Element;
use Livewire\Component;

class DaftarElemen extends Component
{
    public function render()
    {
        $elements = Element::orderBy('code')->get();

        return view('livewire.siswa.belajar.daftar-elemen', [
            'elements' => $elements
        ])->layout('components.layouts.siswa');
    }
}
