<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Flux\Flux;

#[Title('Profil Saya')]
class Profil extends Component
{
    public string $name = '';
    public string $email = '';
    
    // Password update fields
    public string $current_password = '';
    public string $password = '';
    public string $password_confirmation = '';

    public function mount()
    {
        $this->name = Auth::user()->name;
        $this->email = Auth::user()->email;
    }

    public function updateProfile()
    {
        $user = Auth::user();

        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique(User::class)->ignore($user->id),
            ],
        ]);

        $user->fill($validated);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        Flux::toast(variant: 'success', text: 'Profil berhasil diperbarui.');
    }

    public function updatePassword()
    {
        $user = Auth::user();

        if (RateLimiter::tooManyAttempts('update-password:'.$user->id, 5)) {
            $seconds = RateLimiter::availableIn('update-password:'.$user->id);
            throw ValidationException::withMessages([
                'current_password' => trans('auth.throttle', ['seconds' => $seconds, 'minutes' => ceil($seconds / 60)]),
            ]);
        }

        RateLimiter::hit('update-password:'.$user->id);

        $validated = $this->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        RateLimiter::clear('update-password:'.$user->id);

        $user->update([
            'password' => Hash::make($validated['password']),
        ]);

        $this->reset(['current_password', 'password', 'password_confirmation']);

        Flux::toast(variant: 'success', text: 'Kata sandi berhasil diperbarui.');
    }

    public function render()
    {
        $user = Auth::user();
        
        if ($user->role === 'admin') {
            return view('livewire.profil')->layout('components.admin.layout');
        } elseif ($user->role === 'guru') {
            return view('livewire.profil')->layout('components.layouts.guru');
        } else {
            return view('livewire.profil')->layout('components.layouts.siswa');
        }
    }
}
