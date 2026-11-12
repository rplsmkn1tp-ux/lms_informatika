<?php

namespace App\Livewire\Guru;

use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('components.layouts.guru')]
class LessonPublish extends Component
{
    public function render()
    {
        return view('livewire.guru.lesson-publish');
    }
}



