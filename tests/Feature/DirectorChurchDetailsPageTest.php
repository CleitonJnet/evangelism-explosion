<?php

use App\Livewire\Pages\App\Director\Church\View as ChurchDetailsView;
use App\Models\Church;
use App\Models\Course;
use App\Models\Role;
use App\Models\Training;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

function createDirectorForChurchDetails(): User
{
    $director = User::factory()->create();
    $directorRole = Role::query()->firstOrCreate(['name' => 'Director']);
    $director->roles()->syncWithoutDetaching([$directorRole->id]);

    return $director;
}

it('refreshes director church details when church is updated via modal event', function (): void {
    $church = Church::factory()->create([
        'name' => 'Igreja Diretor Original',
        'pastor' => 'Pr. Diretor Original',
    ]);

    $director = createDirectorForChurchDetails();

    Livewire::actingAs($director)
        ->test(ChurchDetailsView::class, ['church' => $church])
        ->assertSee('Igreja Diretor Original')
        ->assertSee('Pr. Diretor Original');

    $church->update([
        'name' => 'Igreja Diretor Atualizada',
        'pastor' => 'Pr. Diretor Atualizado',
    ]);

    Livewire::actingAs($director)
        ->test(ChurchDetailsView::class, ['church' => $church])
        ->dispatch('director-church-updated', churchId: $church->id)
        ->assertSee('Igreja Diretor Atualizada')
        ->assertSee('Pr. Diretor Atualizado');
});

it('shows the add participant entry point on the director church details page', function (): void {
    $church = Church::factory()->create([
        'name' => 'Igreja Diretor Perfil',
        'pastor' => 'Pr. Diretor Perfil',
    ]);

    $director = createDirectorForChurchDetails();

    $response = $this->actingAs($director)->get(route('app.director.church.show', $church));

    $response->assertOk();
    $response->assertSeeText('Novo participante');
    $response->assertSeeText('Novo participante da igreja');
});

it('shows clickable members with course badges on the director church details page', function (): void {
    $church = Church::factory()->create();
    $director = createDirectorForChurchDetails();
    $member = User::factory()->create([
        'church_id' => $church->id,
        'name' => 'Membro Diretor',
        'email' => 'membro.diretor@example.org',
        'phone' => '11999990000',
    ]);
    $course = Course::factory()->create([
        'name' => 'Curso Diretor',
        'initials' => 'CD',
        'color' => '#0f766e',
    ]);
    $training = Training::factory()->create([
        'church_id' => $church->id,
        'course_id' => $course->id,
    ]);
    $training->students()->attach($member->id, [
        'accredited' => 0,
        'kit' => 0,
        'payment' => 0,
    ]);

    $response = $this->actingAs($director)->get(route('app.director.church.show', $church));

    $response->assertOk();
    $response->assertSeeText('Membro Diretor');
    $response->assertSeeText('membro.diretor@example.org');
    $response->assertSeeText($member->phone ?? '');
    $response->assertSeeText('CD');
    $response->assertSee(route('app.director.church.profiles.show', $member), false);
    $response->assertSee('hover:bg-amber-50/70', false);
});

it('sorts linked members by courses on director church details', function (): void {
    $church = Church::factory()->create();
    $director = createDirectorForChurchDetails();

    $memberOne = User::factory()->create([
        'church_id' => $church->id,
        'name' => 'Alpha Diretor',
        'email' => 'alpha.diretor@example.org',
    ]);
    $memberTwo = User::factory()->create([
        'church_id' => $church->id,
        'name' => 'Beta Diretor',
        'email' => 'beta.diretor@example.org',
    ]);

    $courseOne = Course::factory()->create(['name' => 'Curso A', 'initials' => 'CA']);
    $courseTwo = Course::factory()->create(['name' => 'Curso B', 'initials' => 'CB']);

    $trainingOne = Training::factory()->create(['church_id' => $church->id, 'course_id' => $courseOne->id]);
    $trainingTwo = Training::factory()->create(['church_id' => $church->id, 'course_id' => $courseTwo->id]);

    $trainingOne->students()->attach($memberTwo->id, ['accredited' => 0, 'kit' => 0, 'payment' => 0]);
    $trainingTwo->students()->attach($memberTwo->id, ['accredited' => 0, 'kit' => 0, 'payment' => 0]);
    $trainingOne->students()->attach($memberOne->id, ['accredited' => 0, 'kit' => 0, 'payment' => 0]);

    Livewire::actingAs($director)
        ->test(ChurchDetailsView::class, ['church' => $church])
        ->call('sortMembersBy', 'courses')
        ->assertSeeInOrder(['Beta Diretor', 'Alpha Diretor']);
});
