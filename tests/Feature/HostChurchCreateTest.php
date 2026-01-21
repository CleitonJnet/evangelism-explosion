<?php

use App\Livewire\Pages\App\Director\Church\MakeHost;
use App\Models\Church;
use App\Models\HostChurch;
use Livewire\Livewire;

test('host church creation validates required fields', function () {
    Livewire::test(MakeHost::class)
        ->call('submit')
        ->assertHasErrors([
            'church_id' => 'required',
        ]);
});

test('host church can be created', function () {
    $church = Church::create(['name' => 'Igreja Central']);
    $sinceDate = now()->toDateString();

    Livewire::test(MakeHost::class)
        ->set('church_id', $church->id)
        ->set('since_date', $sinceDate)
        ->set('notes', 'Observacoes')
        ->call('submit')
        ->assertHasNoErrors();

    expect(HostChurch::query()->count())->toBe(1);

    $hostChurch = HostChurch::query()->first();
    expect($hostChurch)->not->toBeNull();
    expect($hostChurch->since_date?->toDateString())->toBe($sinceDate);

    $this->assertDatabaseHas('host_churches', [
        'church_id' => $church->id,
        'notes' => 'Observacoes',
    ]);
});

test('host church selection is disabled when already registered', function () {
    $church = Church::create(['name' => 'Igreja Base']);
    HostChurch::create(['church_id' => $church->id]);

    Livewire::test(MakeHost::class)
        ->assertSee('Igreja Base')
        ->assertSeeHtml('value="'.$church->id.'" disabled');
});
test('example', function () {
    $response = $this->get('/');

    $response->assertStatus(200);
});
