<?php

use App\Livewire\Pages\App\Director\Church\Hosts;
use App\Models\Church;
use App\Models\HostChurch;
use Livewire\Livewire;

test('host churches list is ordered by church name', function () {
    $alpha = Church::create(['name' => 'Alpha Igreja']);
    $beta = Church::create(['name' => 'Beta Igreja']);

    HostChurch::create(['church_id' => $beta->id]);
    HostChurch::create(['church_id' => $alpha->id]);

    Livewire::test(Hosts::class)
        ->assertSeeInOrder(['Alpha Igreja', 'Beta Igreja'])
        ->assertSee(route('app.director.church.view_host', ['church' => $alpha->id]))
        ->assertSee(route('app.director.church.view_host', ['church' => $beta->id]));
});
test('example', function () {
    $response = $this->get('/');

    $response->assertStatus(200);
});
