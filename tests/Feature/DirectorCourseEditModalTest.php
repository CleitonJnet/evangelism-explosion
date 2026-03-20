<?php

use App\Helpers\MoneyHelper;
use App\Livewire\Pages\App\Director\Course\EditModal;
use App\Models\Course;
use App\Models\Ministry;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

function createDirectorForCourseEditModal(): User
{
    $director = User::factory()->create();
    $directorRole = Role::query()->firstOrCreate(['name' => 'Director']);
    $director->roles()->syncWithoutDetaching([$directorRole->id]);

    return $director;
}

it('updates an existing course from the course details modal', function (): void {
    $ministry = Ministry::query()->create([
        'name' => 'Evangelismo Eficaz',
        'initials' => 'EE',
        'color' => '#0f172a',
    ]);

    $course = Course::factory()->create([
        'ministry_id' => $ministry->id,
        'execution' => 0,
        'min_stp_sessions' => 1,
        'is_accreditable' => false,
        'type' => 'Clínica',
        'name' => 'Clínica de Líderes',
        'initials' => 'CL',
        'slogan' => 'Formação estratégica',
        'learnMoreLink' => 'https://example.com/curso-antigo',
        'color' => '#1d4ed8',
        'price' => '120,00',
        'targetAudience' => 'Líderes em formação',
        'knowhow' => 'Conteúdo introdutório',
        'description' => 'Curso voltado para o preparo inicial.',
        'logo' => 'https://example.com/logo-antigo.png',
        'banner' => 'https://example.com/banner-antigo.png',
    ]);

    $director = createDirectorForCourseEditModal();

    Livewire::actingAs($director)
        ->test(EditModal::class, ['courseId' => $course->id])
        ->call('openModal')
        ->set('execution', 1)
        ->set('min_stp_sessions', 3)
        ->set('is_accreditable', true)
        ->set('type', 'Implementação')
        ->set('name', 'Clínica Atualizada')
        ->set('initials', 'CA')
        ->set('learnMoreLink', 'https://example.com/curso-atualizado')
        ->set('color', '#0f172a')
        ->set('slogan', 'Nova apresentação')
        ->set('price', '180,00')
        ->set('targetAudience', 'Facilitadores locais')
        ->set('knowhow', 'Aplicação prática')
        ->set('description', 'Curso atualizado para acompanhamento local.')
        ->call('save')
        ->assertDispatched('director-course-updated')
        ->assertDispatched('director-ministry-updated')
        ->assertSet('showModal', false);

    $course->refresh();

    expect((int) $course->execution)->toBe(1)
        ->and((int) $course->min_stp_sessions)->toBe(3)
        ->and($course->is_accreditable)->toBeTrue()
        ->and($course->type)->toBe('Implementação')
        ->and($course->name)->toBe('Clínica Atualizada')
        ->and($course->initials)->toBe('CA')
        ->and($course->learnMoreLink)->toBe('https://example.com/curso-atualizado')
        ->and($course->color)->toBe('#0f172a')
        ->and($course->slogan)->toBe('Nova apresentação')
        ->and($course->price)->toBe('180,00')
        ->and(str_contains((string) $course->getRawOriginal('price'), ','))->toBeFalse()
        ->and(MoneyHelper::toDatabase($course->getRawOriginal('price')))->toBe('180.00')
        ->and($course->targetAudience)->toBe('Facilitadores locais')
        ->and($course->knowhow)->toBe('Aplicação prática')
        ->and($course->description)->toBe('Curso atualizado para acompanhamento local.');
});

it('sanitizes non numeric characters in the course price on edit', function (): void {
    $ministry = Ministry::query()->create([
        'name' => 'Evangelismo Eficaz',
        'initials' => 'EE',
        'color' => '#0f172a',
    ]);

    $course = Course::factory()->create([
        'ministry_id' => $ministry->id,
        'price' => '120,00',
    ]);

    $director = createDirectorForCourseEditModal();

    Livewire::actingAs($director)
        ->test(EditModal::class, ['courseId' => $course->id])
        ->call('openModal')
        ->set('price', '99abc')
        ->assertSet('price', '99');
});
