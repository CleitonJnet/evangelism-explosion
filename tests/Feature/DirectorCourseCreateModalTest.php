<?php

use App\Helpers\MoneyHelper;
use App\Livewire\Pages\App\Director\Course\CreateModal;
use App\Models\Course;
use App\Models\Ministry;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

function createDirectorForCourseCreateModal(): User
{
    $director = User::factory()->create();
    $directorRole = Role::query()->firstOrCreate(['name' => 'Director']);
    $director->roles()->syncWithoutDetaching([$directorRole->id]);

    return $director;
}

it('creates a new course from the ministry details modal', function (): void {
    $ministry = Ministry::query()->create([
        'name' => 'Evangelismo Eficaz',
        'initials' => 'EE',
        'color' => '#0f172a',
    ]);

    $director = createDirectorForCourseCreateModal();

    Livewire::actingAs($director)
        ->test(CreateModal::class, ['ministry' => $ministry])
        ->call('openModal')
        ->set('execution', 0)
        ->set('min_stp_sessions', 2)
        ->set('type', 'Clínica')
        ->set('name', 'Clínica de Líderes')
        ->set('initials', 'CL')
        ->set('learnMoreLink', 'https://example.com/curso')
        ->set('color', '#1d4ed8')
        ->set('slogan', 'Formação estratégica')
        ->set('price', '120,00')
        ->set('targetAudience', 'Líderes em formação')
        ->set('knowhow', 'Conteúdo introdutório')
        ->set('description', 'Curso voltado para o preparo inicial.')
        ->call('save')
        ->assertDispatched('director-course-created')
        ->assertDispatched('director-ministry-updated')
        ->assertSet('showModal', false);

    $course = Course::query()->where('name', 'Clínica de Líderes')->first();

    expect($course)->not->toBeNull();
    expect($course?->ministry_id)->toBe($ministry->id);
    expect((int) $course?->execution)->toBe(0);
    expect((int) $course?->order)->toBe(1);
    expect($course?->certificate)->toBeNull();
    expect($course?->price)->toBe('120,00');
    expect(str_contains((string) $course?->getRawOriginal('price'), ','))->toBeFalse();
    expect(MoneyHelper::toDatabase($course?->getRawOriginal('price')))->toBe('120.00');
});

it('sanitizes non numeric characters in the course price on create', function (): void {
    $ministry = Ministry::query()->create([
        'name' => 'Evangelismo Eficaz',
        'initials' => 'EE',
        'color' => '#0f172a',
    ]);

    $director = createDirectorForCourseCreateModal();

    Livewire::actingAs($director)
        ->test(CreateModal::class, ['ministry' => $ministry])
        ->call('openModal')
        ->set('price', '120abc')
        ->assertSet('price', '120');
});
