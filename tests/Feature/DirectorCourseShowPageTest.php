<?php

use App\Models\Course;
use App\Models\Ministry;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function createDirectorForCoursePage(): User
{
    $director = User::factory()->create();
    $directorRole = Role::query()->firstOrCreate(['name' => 'Director']);
    $director->roles()->syncWithoutDetaching([$directorRole->id]);

    return $director;
}

it('renders the director course details page within the selected ministry', function (): void {
    $ministry = Ministry::query()->create([
        'name' => 'Evangelismo Eficaz',
        'initials' => 'EE',
    ]);

    $course = Course::factory()->create([
        'ministry_id' => $ministry->id,
        'order' => 3,
        'execution' => 1,
        'type' => 'Implementação',
        'initials' => 'CL',
        'name' => 'Clínica de Líderes',
        'slogan' => 'Treinamento intensivo',
        'targetAudience' => 'Líderes e facilitadores locais',
        'learnMoreLink' => 'https://example.com/curso',
        'color' => '#0f172a',
        'description' => 'Uma trilha completa para multiplicação.',
        'knowhow' => 'Aplicação prática em campo.',
        'logo' => 'https://example.com/logo.png',
        'banner' => 'https://example.com/banner.png',
        'price' => 120.50,
        'min_stp_sessions' => 4,
        'certificate' => 'Sim',
    ]);

    $director = createDirectorForCoursePage();

    $this->actingAs($director)
        ->get(route('app.director.ministry.course.show', ['ministry' => $ministry, 'course' => $course]))
        ->assertOk()
        ->assertSee('Detalhes do curso')
        ->assertSee('Clínica de Líderes')
        ->assertSee('open-director-course-edit-modal')
        ->assertSee('Unidades')
        ->assertSee('Informações do curso')
        ->assertSee('Mídia e identidade')
        ->assertSee('Conteúdo pedagógico')
        ->assertSee('Implementação local')
        ->assertSee('Treinamento intensivo')
        ->assertSee('Líderes e facilitadores locais')
        ->assertSee('https://example.com/curso', escape: false)
        ->assertSee('Aplicação prática em campo.')
        ->assertSee('Sessões mínimas STP')
        ->assertDontSee('Certificado');
});
