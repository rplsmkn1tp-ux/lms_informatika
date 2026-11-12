<?php

namespace App\Actions\Fortify;

use App\Concerns\PasswordValidationRules;
use App\Concerns\ProfileValidationRules;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules, ProfileValidationRules;

    /**
     * Validate and create a newly registered user.
     *
     * @param  array<string, string>  $input
     */
    public function create(array $input): User
    {
        $role = $input['role'] ?? 'siswa';

        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => $this->passwordRules(),
            'role' => ['required', 'in:siswa,guru'],
        ];

        if ($role === 'siswa') {
            $rules['nisn'] = ['nullable', 'string', 'max:50', 'unique:users,nisn'];
            $rules['classroom_id'] = ['required', 'exists:classrooms,id'];
        }

        Validator::make($input, $rules)->validate();

        $status = $role === 'siswa' ? 'active' : 'pending';

        $user = User::create([
            'name' => $input['name'],
            'email' => $input['email'],
            'password' => $input['password'],
            'role' => $role,
            'nisn' => $role === 'siswa' ? $input['nisn'] : null,
            'status' => $status,
        ]);

        if ($role === 'siswa') {
            $classroom = \App\Models\Classroom::find($input['classroom_id']);
            if ($classroom) {
                $user->classrooms()->attach($classroom->id, ['academic_year_id' => $classroom->academic_year_id]);
            }
        }

        return $user;
    }
}
