<?php

use App\Livewire\Pages\App\Director\Ministry\View as MinistryView;
use App\Models\Course;
use App\Models\Ministry;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

function createDirectorForMinistryCourseReorder(): User
{
    $director = User::factory()->create();
    $directorRole = Role::query()->firstOrCreate(['name' => 'Director']);
    $director->roles()->syncWithoutDetaching([$directorRole->id]);

    return $director;
}

it('reorders ministry courses across leadership and implementation lists', function (): void {
    $ministry = Ministry::query()->create([
        'name' => 'Evangelismo Eficaz',
        'initials' => 'EE',
    ]);

    $leadershipCourse = Course::factory()->create([
        'ministry_id' => $ministry->id,
        'execution' => 0,
        'order' => 1,
        'name' => 'Curso de Liderança',
    ]);

    $implementationFirstCourse = Course::factory()->create([
        'ministry_id' => $ministry->id,
        'execution' => 1,
        'order' => 1,
        'name' => 'Implementação A',
    ]);

    $implementationSecondCourse = Course::factory()->create([
        'ministry_id' => $ministry->id,
        'execution' => 1,
        'order' => 2,
        'name' => 'Implementação B',
    ]);

    $director = createDirectorForMinistryCourseReorder();

    Livewire::actingAs($director)
        ->test(MinistryView::class, ['ministry' => $ministry])
        ->call('moveCourseAfter', $leadershipCourse->id, 1, $implementationFirstCourse->id);

    expect($leadershipCourse->fresh())
        ->execution->toBe(1)
        ->order->toBe(2);

    expect($implementationFirstCourse->fresh()->order)->toBe(1);
    expect($implementationSecondCourse->fresh()->order)->toBe(3);
});
