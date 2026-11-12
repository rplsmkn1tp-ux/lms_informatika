<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Lesson;
use App\Models\LearningObjective;
use Livewire\Livewire;
use App\Livewire\Guru\LessonManagement;

class GuruAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_guru_hanya_bisa_mengakses_halaman_guru()
    {
        $guru = User::factory()->create(['role' => 'guru']);

        $response = $this->actingAs($guru)->get('/guru');
        $response->assertStatus(200);

        $response = $this->actingAs($guru)->get('/admin');
        $response->assertStatus(403);
    }

    public function test_guru_a_tidak_bisa_mengubah_data_materi_bukan_miliknya()
    {
        $guruA = User::factory()->create(['role' => 'guru']);
        $guruB = User::factory()->create(['role' => 'guru']);

        $element = \App\Models\Element::create([
            'code' => 'ELM',
            'name' => 'Elemen Test',
            'description' => 'Elemen Test'
        ]);

        $tpA = LearningObjective::create([
            'element_id' => $element->id,
            'created_by' => $guruA->id,
            'code' => 'TPA',
            'title' => 'TP A',
            'description' => 'TP A'
        ]);

        $lessonA = Lesson::create([
            'created_by' => $guruA->id,
            'learning_objective_id' => $tpA->id,
            'code' => 'LESSON-A',
            'title' => 'Lesson A',
            'content' => 'Content A',
            'status' => 'draft',
            'sort_order' => 1
        ]);

        // Guru B mencoba mengedit materi milik Guru A
        Livewire::actingAs($guruB)
            ->test(LessonManagement::class)
            ->call('edit', $lessonA->id)
            ->assertForbidden();

        // Guru B mencoba menghapus materi milik Guru A
        Livewire::actingAs($guruB)
            ->test(LessonManagement::class)
            ->call('delete', $lessonA->id)
            ->assertForbidden();
            
        // Guru A bisa mengedit miliknya sendiri
        Livewire::actingAs($guruA)
            ->test(LessonManagement::class)
            ->call('edit', $lessonA->id)
            ->assertOk();
    }
}
