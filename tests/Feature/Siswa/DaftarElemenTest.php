<?php

namespace Tests\Feature\Siswa;

use App\Livewire\Siswa\Belajar\DaftarElemen;
use App\Models\Element;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class DaftarElemenTest extends TestCase
{
    use RefreshDatabase;

    public function test_siswa_can_view_daftar_elemen()
    {
        $siswa = User::factory()->create(['role' => 'siswa']);
        
        $response = $this->actingAs($siswa)->get(route('siswa.belajar'));
        $response->assertStatus(200);
        $response->assertSeeLivewire(DaftarElemen::class);
    }

    public function test_elemen_are_displayed()
    {
        $siswa = User::factory()->create(['role' => 'siswa']);
        $element1 = Element::create(['code' => 'BK', 'name' => 'Berpikir Komputasional', 'description' => 'Desc']);
        $element2 = Element::create(['code' => 'TIK', 'name' => 'Teknologi Informasi dan Komunikasi', 'description' => 'Desc']);

        Livewire::actingAs($siswa)
            ->test(DaftarElemen::class)
            ->assertSee('BK')
            ->assertSee('Berpikir Komputasional')
            ->assertSee('TIK')
            ->assertSee('Teknologi Informasi dan Komunikasi');
    }
}
