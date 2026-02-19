<?php

use App\Livewire\Pages\App\Teacher\Training\Statistics;
use App\Models\Church;
use App\Models\Role;
use App\Models\Training;
use App\Models\User;
use Livewire\Livewire;

function createTeacherForStatisticsPage(): User
{
    $teacher = User::factory()->create(['church_id' => null]);
    $teacherRole = Role::query()->firstOrCreate(['name' => 'Teacher']);
    $teacher->roles()->syncWithoutDetaching([$teacherRole->id]);

    return $teacher;
}

it('renders the teacher training statistics page with the livewire component', function () {
    $teacher = createTeacherForStatisticsPage();
    $church = Church::factory()->create();
    $training = Training::factory()->create([
        'teacher_id' => $teacher->id,
        'church_id' => $church->id,
    ]);

    $response = $this
        ->actingAs($teacher)
        ->get(route('app.teacher.trainings.statistics', $training));

    $response->assertOk();
    $response->assertSeeLivewire(Statistics::class);
    $response->assertSeeText('Integrantes das Equipes');
});

it('calculates statistics totals inside the livewire component', function () {
    $teacher = createTeacherForStatisticsPage();
    $church = Church::factory()->create();
    $training = Training::factory()->create([
        'teacher_id' => $teacher->id,
        'church_id' => $church->id,
    ]);

    Livewire::actingAs($teacher)
        ->test(Statistics::class, ['training' => $training])
        ->assertSet('columnTotals.0', 1)
        ->assertSet('columnTotals.1', 13)
        ->assertSet('columnTotals.2', 4)
        ->assertSet('columnTotals.3', 2)
        ->assertSet('columnTotals.4', 20)
        ->assertSet('columnTotals.5', 50)
        ->assertSet('columnTotals.6', 25)
        ->assertSet('columnTotals.7', 15)
        ->assertSet('columnTotals.8', 5)
        ->assertSet('columnTotals.9', 5)
        ->assertSet('columnTotals.10', 5)
        ->assertSet('columnTotals.11', 45);
});

it('moves students between teams and keeps alphabetical order in each team', function () {
    $teacher = createTeacherForStatisticsPage();
    $church = Church::factory()->create();
    $training = Training::factory()->create([
        'teacher_id' => $teacher->id,
        'church_id' => $church->id,
    ]);

    $component = Livewire::actingAs($teacher)
        ->test(Statistics::class, ['training' => $training])
        ->call('moveStudent', 2004, 2, 1, null);

    $teamOne = collect($component->get('approaches'))->firstWhere('id', 1);
    $teamTwo = collect($component->get('approaches'))->firstWhere('id', 2);

    expect(collect($teamOne['students'])->pluck('name')->values()->all())->toBe([
        'Carlos Eduardo Lima',
        'Maria Jose',
        'Pb. Gabriel Ferreira',
    ]);
    expect(collect($teamTwo['students'])->pluck('name')->values()->all())->toBe([
        'Ana Paula Souza',
    ]);
});

it('swaps mentors between teams without mixing with students', function () {
    $teacher = createTeacherForStatisticsPage();
    $church = Church::factory()->create();
    $training = Training::factory()->create([
        'teacher_id' => $teacher->id,
        'church_id' => $church->id,
    ]);

    $component = Livewire::actingAs($teacher)
        ->test(Statistics::class, ['training' => $training])
        ->call('swapMentor', 1001, 1, 3);

    $teamOne = collect($component->get('approaches'))->firstWhere('id', 1);
    $teamThree = collect($component->get('approaches'))->firstWhere('id', 3);

    expect($teamOne['mentor']['name'])->toBe('Mariana Chagas');
    expect($teamThree['mentor']['name'])->toBe('Dc. Antonio Maia');
    expect(count($teamOne['students']))->toBe(2);
    expect(count($teamThree['students']))->toBe(2);
});
