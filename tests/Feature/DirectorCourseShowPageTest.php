<?php

use App\Models\Course;
use App\Models\Material;
use App\Models\MaterialComponent;
use App\Models\Ministry;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

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
    $kit = Material::query()->create([
        'name' => 'Kit do aluno',
        'type' => 'composite',
        'minimum_stock' => 2,
    ]);
    $manual = Material::query()->create([
        'name' => 'Manual do aluno',
        'type' => 'simple',
    ]);
    $course->studyMaterials()->attach([$kit->id, $manual->id]);
    MaterialComponent::query()->create([
        'parent_material_id' => $kit->id,
        'component_material_id' => $manual->id,
        'quantity' => 1,
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
        ->assertSee('Conteúdo pedagógico')
        ->assertSee('Material de estudos do aluno')
        ->assertSee('Kit do aluno')
        ->assertSee('Manual do aluno')
        ->assertSee('Implementação local')
        ->assertSee('Treinamento intensivo')
        ->assertSee('Líderes e facilitadores locais')
        ->assertSee('https://example.com/curso', escape: false)
        ->assertSee('Aplicação prática em campo.')
        ->assertSee('Sessões mínimas STP')
        ->assertDontSee('Certificado');
});

it('updates the study materials list for a course', function (): void {
    $course = Course::factory()->create();
    $director = createDirectorForCoursePage();
    $kit = Material::query()->create([
        'name' => 'Kit principal',
        'type' => 'composite',
    ]);
    $manual = Material::query()->create([
        'name' => 'Manual complementar',
        'type' => 'simple',
    ]);

    Livewire::actingAs($director)
        ->test(\App\Livewire\Pages\App\Director\Course\View::class, ['course' => $course])
        ->call('openStudyMaterialsModal')
        ->set('selectedStudyMaterialIds', [$kit->id, $manual->id])
        ->call('saveStudyMaterials')
        ->assertSet('showStudyMaterialsModal', false);

    expect($course->fresh()->studyMaterials()->pluck('materials.id')->all())
        ->toContain($kit->id, $manual->id);
});

it('shows all teachers on the director course details page without pagination', function (): void {
    $ministry = Ministry::query()->create([
        'name' => 'Evangelismo Eficaz',
        'initials' => 'EE',
    ]);

    $course = Course::factory()->create([
        'ministry_id' => $ministry->id,
        'execution' => 0,
        'name' => 'Clínica de Líderes',
    ]);

    $teacherRole = Role::query()->firstOrCreate(['name' => 'Teacher']);

    foreach (range(1, 6) as $index) {
        $teacher = User::factory()->create([
            'name' => sprintf('Professor %02d', $index),
            'email' => sprintf('professor%02d@example.com', $index),
        ]);
        $teacher->roles()->syncWithoutDetaching([$teacherRole->id]);
        $course->teachers()->attach($teacher->id, ['status' => 1]);
    }

    $director = createDirectorForCoursePage();

    $this->actingAs($director)
        ->get(route('app.director.ministry.course.show', ['ministry' => $ministry, 'course' => $course]))
        ->assertOk()
        ->assertSee('Professores do curso (6)')
        ->assertSee('Professor 01')
        ->assertSee('Professor 02')
        ->assertSee('Professor 03')
        ->assertSee('Professor 04')
        ->assertSee('Professor 05')
        ->assertSee('Professor 06');
});
