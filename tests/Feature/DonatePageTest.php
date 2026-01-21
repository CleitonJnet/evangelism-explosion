<?php

test('donate page loads', function () {
    $compiledPath = sys_get_temp_dir() . '/ee-views';

    if (! is_dir($compiledPath)) {
        mkdir($compiledPath, 0775, true);
    }

    config()->set('view.compiled', $compiledPath);

    $this->get(route('web.donate'))
        ->assertSuccessful()
        ->assertSee('Oferta Missionária');
});
