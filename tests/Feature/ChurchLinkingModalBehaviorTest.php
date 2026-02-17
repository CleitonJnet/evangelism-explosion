<?php

use App\Models\Church;
use App\Models\User;

it('flags the modal to open when user has neither church nor temp church', function (): void {
    $user = User::factory()->create([
        'church_id' => null,
        'church_temp_id' => null,
    ]);

    $response = $this->actingAs($user)->get(route('web.home'));

    $response->assertOk();
    $response->assertSessionHas('church_modal_open', true);
});

it('does not re-open modal in the same session after prompt flag is set', function (): void {
    $user = User::factory()->create([
        'church_id' => null,
        'church_temp_id' => null,
    ]);

    $response = $this
        ->withSession(['church_modal_prompted' => true])
        ->actingAs($user)
        ->get(route('web.home'));

    $response->assertOk();
    $response->assertSessionMissing('church_modal_open');
});

it('clears modal flags when user is already linked to an official church', function (): void {
    $church = Church::factory()->create();
    $user = User::factory()->create([
        'church_id' => $church->id,
        'church_temp_id' => null,
    ]);

    $response = $this
        ->withSession([
            'church_modal_open' => true,
            'church_modal_prompted' => true,
        ])
        ->actingAs($user)
        ->get(route('web.home'));

    $response->assertOk();
    $response->assertSessionMissing('church_modal_open');
    $response->assertSessionMissing('church_modal_prompted');
});
