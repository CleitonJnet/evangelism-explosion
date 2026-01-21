<?php

use App\Livewire\Pages\App\Director\Ministry\Create;
use App\Models\Ministry;
use Livewire\Livewire;

test('ministry creation validates required fields', function () {
    Livewire::test(Create::class)
        ->call('submit')
        ->assertHasErrors([
            'initials' => 'required',
            'name' => 'required',
        ]);
});

test('ministry can be created', function () {
    Livewire::test(Create::class)
        ->set('initials', 'EE')
        ->set('name', 'Everyday Evangelism')
        ->set('logo', 'logos/ee.png')
        ->set('color', '#4F4F4F')
        ->set('description', 'Descricao do ministerio')
        ->call('submit')
        ->assertHasNoErrors();

    expect(Ministry::query()->count())->toBe(1);

    $this->assertDatabaseHas('ministries', [
        'initials' => 'EE',
        'name' => 'Everyday Evangelism',
        'logo' => 'logos/ee.png',
        'color' => '#4F4F4F',
        'description' => 'Descricao do ministerio',
    ]);
});
test('example', function () {
    $response = $this->get('/');

    $response->assertStatus(200);
});
