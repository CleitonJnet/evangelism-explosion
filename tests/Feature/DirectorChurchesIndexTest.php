<?php

use App\Models\Church;
use App\Models\Course;
use App\Models\Role;
use App\Models\Training;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

it('lists users without church linkage in a dedicated table for directors', function (): void {
    $director = User::factory()->create();
    $directorRole = Role::query()->firstOrCreate(['name' => 'Director']);
    $director->roles()->syncWithoutDetaching([$directorRole->id]);

    $unlinkedUser = User::factory()->create([
        'name' => 'Pessoa Sem Igreja Diretor',
        'email' => 'sem.igreja.diretor@example.com',
        'church_id' => null,
        'church_temp_id' => null,
    ]);

    $linkedUser = User::factory()->create([
        'name' => 'Pessoa Com Igreja Diretor',
        'email' => 'com.igreja.diretor@example.com',
        'church_id' => \App\Models\Church::factory()->create()->id,
    ]);

    Livewire::actingAs($director)
        ->test(\App\Livewire\Pages\App\Director\Church\Index::class)
        ->assertViewHas('unlinkedUsers', function ($paginator) use ($unlinkedUser, $linkedUser) {
            $listedUserIds = collect($paginator->items())->pluck('id')->all();

            expect($listedUserIds)->toContain($unlinkedUser->id);
            expect($listedUserIds)->not->toContain($linkedUser->id);

            return true;
        });
});

it('allows director to remove unlinked users from the dedicated table', function (): void {
    $director = User::factory()->create();
    $directorRole = Role::query()->firstOrCreate(['name' => 'Director']);
    $director->roles()->syncWithoutDetaching([$directorRole->id]);

    $unlinkedUser = User::factory()->create([
        'church_id' => null,
        'church_temp_id' => null,
    ]);

    Livewire::actingAs($director)
        ->test(\App\Livewire\Pages\App\Director\Church\Index::class)
        ->call('removeUnlinkedUser', $unlinkedUser->id);

    $this->assertDatabaseMissing('users', ['id' => $unlinkedUser->id]);
});

it('allows director to associate church and lists user trainings in modal', function (): void {
    $director = User::factory()->create();
    $directorRole = Role::query()->firstOrCreate(['name' => 'Director']);
    $director->roles()->syncWithoutDetaching([$directorRole->id]);

    $church = Church::factory()->create(['name' => 'Igreja Para Vinculo Diretor']);
    $unlinkedUser = User::factory()->create([
        'church_id' => null,
        'church_temp_id' => null,
    ]);

    $training = Training::factory()->create([
        'church_id' => $church->id,
    ]);
    $training->students()->attach($unlinkedUser->id);

    Livewire::actingAs($director)
        ->test(\App\Livewire\Pages\App\Director\Church\Index::class)
        ->call('openUnlinkedUserModal', $unlinkedUser->id)
        ->assertSet('selectedUnlinkedUserId', $unlinkedUser->id)
        ->assertViewHas('selectedUserTrainings', function ($trainings) use ($training) {
            $trainingIds = collect($trainings->all())->pluck('id')->all();

            expect($trainingIds)->toContain($training->id);

            return true;
        })
        ->set('linkChurchId', $church->id)
        ->call('associateChurchToSelectedUser')
        ->assertSet('showUnlinkedUserModal', false);

    $unlinkedUser->refresh();

    expect($unlinkedUser->church_id)->toBe($church->id);
    expect($unlinkedUser->church_temp_id)->toBeNull();
});

it('lists all system users in the directory modal and filters by church city state and email for directors', function (): void {
    $director = User::factory()->create();
    $directorRole = Role::query()->firstOrCreate(['name' => 'Director']);
    $director->roles()->syncWithoutDetaching([$directorRole->id]);

    $church = Church::factory()->create([
        'name' => 'Igreja Diretório Diretor',
    ]);

    $listedRole = Role::query()->firstOrCreate(['name' => 'Teacher']);

    $listedUser = User::factory()->create([
        'name' => 'Helena Diretório',
        'email' => 'helena.diretorio@example.com',
        'city' => 'Curitiba',
        'state' => 'PR',
        'church_id' => $church->id,
    ]);
    $listedUser->roles()->syncWithoutDetaching([$listedRole->id]);

    $course = Course::factory()->create([
        'initials' => 'TD',
        'name' => 'Treinamento Diretor',
    ]);
    $training = Training::factory()->create(['course_id' => $course->id]);
    $training->students()->attach($listedUser->id);

    $hiddenUser = User::factory()->create([
        'name' => 'Pessoa Fora do Filtro',
        'email' => 'fora.filtro@example.com',
        'city' => 'Goiania',
        'state' => 'GO',
        'church_id' => null,
        'church_temp_id' => null,
    ]);

    $component = Livewire::actingAs($director)
        ->test(\App\Livewire\Pages\App\Director\Church\Index::class)
        ->call('openAllUsersModal')
        ->assertSet('showAllUsersModal', true)
        ->assertViewHas('allUsers', function ($paginator) use ($listedUser, $hiddenUser) {
            $listedUserIds = collect($paginator->items())->pluck('id')->all();

            expect($listedUserIds)->toContain($listedUser->id);
            expect($listedUserIds)->toContain($hiddenUser->id);

            return true;
        });

    $filteredByChurch = $component
        ->set('userDirectorySearch', 'Igreja Diretório Diretor')
        ->viewData('allUsers');

    expect(collect($filteredByChurch->items())->pluck('id')->all())
        ->toContain($listedUser->id)
        ->not->toContain($hiddenUser->id);

    $component
        ->set('userDirectorySearch', 'Curitiba')
        ->assertSeeText('Helena Diretório')
        ->set('userDirectorySearch', 'PR')
        ->assertSeeText('Helena Diretório')
        ->set('userDirectorySearch', 'helena.diretorio@example.com')
        ->assertSeeText('Helena Diretório')
        ->assertSeeText('TD');
});

it('renders the church-context profile route for users listed in the directory modal for directors', function (): void {
    $director = User::factory()->create();
    $directorRole = Role::query()->firstOrCreate(['name' => 'Director']);
    $director->roles()->syncWithoutDetaching([$directorRole->id]);

    $listedUser = User::factory()->create([
        'name' => 'Perfil Igrejas Diretor',
        'church_id' => null,
        'church_temp_id' => null,
    ]);

    Livewire::actingAs($director)
        ->test(\App\Livewire\Pages\App\Director\Church\Index::class)
        ->call('openAllUsersModal')
        ->assertSee(route('app.director.church.profiles.show', $listedUser));

    $response = $this->actingAs($director)->get(route('app.director.church.profiles.show', $listedUser));

    $response->assertOk();
    $response->assertSeeText('Perfil Igrejas Diretor');
    $response->assertSeeText('Voltar para igrejas');
    $response->assertDontSeeText('Excluir conta');
});

it('searches church and user dropdown by pastor city state and email for directors', function (): void {
    $director = User::factory()->create();
    $directorRole = Role::query()->firstOrCreate(['name' => 'Director']);
    $director->roles()->syncWithoutDetaching([$directorRole->id]);

    $church = Church::factory()->create([
        'name' => 'Igreja Filtro Diretor',
        'pastor' => 'Pr. Samuel Diretor',
        'city' => 'Campinas',
        'state' => 'SP',
        'contact_email' => 'filtro.diretor@example.com',
    ]);

    $user = User::factory()->create([
        'name' => 'Membro Filtro Diretor',
        'email' => 'membro.filtro.diretor@example.com',
        'city' => 'Campinas',
        'state' => 'SP',
        'church_id' => $church->id,
    ]);

    Livewire::actingAs($director)
        ->test(\App\Livewire\Pages\App\Director\Church\Index::class)
        ->set('churchSearch', 'Samuel Diretor')
        ->assertSeeText($church->name)
        ->set('churchSearch', 'Campinas')
        ->assertSeeText($church->name)
        ->assertSeeText($user->name)
        ->assertSee(route('app.director.church.profiles.show', $user))
        ->set('churchSearch', 'SP')
        ->assertSeeText($church->name)
        ->assertSeeText($user->name)
        ->set('churchSearch', 'filtro.diretor@example.com')
        ->assertSeeText($church->name)
        ->set('churchSearch', 'membro.filtro.diretor@example.com')
        ->assertSeeText($user->name);
});
