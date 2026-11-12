<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Admin
        User::create([
            'name' => env('ADMIN_NAME', 'Admin'),
            'email' => env('ADMIN_EMAIL', 'admin@example.com'),
            'password' => Hash::make(env('ADMIN_PASSWORD', 'password')),
            'role' => 'admin',
            'status' => 'active',
        ]);

        // Academic Year
        $ay = \App\Models\AcademicYear::create([
            'name' => '2026/2027',
            'semester' => 'ganjil',
            'is_active' => true,
        ]);

        // Classrooms
        $classA = \App\Models\Classroom::create([
            'name' => 'X RPL 1',
            'join_code' => 'XRPL1-A2B3',
            'academic_year_id' => $ay->id,
            'weekly_lesson_target' => 5
        ]);

        if (app()->environment('local', 'testing')) {
            // Guru
            $guru = User::create([
                'name' => 'Guru Budi',
                'email' => 'guru@example.com',
                'password' => Hash::make('password'),
                'role' => 'guru',
                'status' => 'active',
            ]);
            
            \App\Models\TeachingAssignment::create([
                'teacher_id' => $guru->id,
                'classroom_id' => $classA->id,
                'subject' => 'Informatika'
            ]);

            // Siswa
            $siswa = User::create([
                'name' => 'Siswa Andi',
                'email' => 'siswa@example.com',
                'nisn' => '1234567890',
                'password' => Hash::make('password'),
                'role' => 'siswa',
                'status' => 'active',
            ]);
            
            $siswa->classrooms()->attach($classA->id, ['academic_year_id' => $classA->academic_year_id]);
        }

        $this->call(ContentSeeder::class);
    }
}
