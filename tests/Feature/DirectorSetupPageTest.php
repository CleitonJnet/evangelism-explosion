<?php

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('shows translated role labels on the director setup page', function (): void {
    $director = User::factory()->create();

    $directorRole = Role::query()->firstOrCreate(['name' => 'Director']);
    $boardRole = Role::query()->firstOrCreate(['name' => 'Board']);
    $fieldWorkerRole = Role::query()->firstOrCreate(['name' => 'FieldWorker']);
    $teacherRole = Role::query()->firstOrCreate(['name' => 'Teacher']);
    $facilitatorRole = Role::query()->firstOrCreate(['name' => 'Facilitator']);
    $mentorRole = Role::query()->firstOrCreate(['name' => 'Mentor']);
    $studentRole = Role::query()->firstOrCreate(['name' => 'Student']);

    $director->roles()->syncWithoutDetaching([$directorRole->id]);

    $response = $this->actingAs($director)->get(route('app.director.setup'));

    $response->assertOk();
    $response->assertSeeText('Conselho Nacional');
    $response->assertSeeText('Diretor Nacional');
    $response->assertSeeText('Missionário de Campo');
    $response->assertSeeText('Professor');
    $response->assertSeeText('Facilitador');
    $response->assertSeeText('Mentor');
    $response->assertSeeText('Aluno');
});
