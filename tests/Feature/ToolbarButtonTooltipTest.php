<?php

use Illuminate\Support\Facades\Blade;

it('renders tooltip classes for toolbar button', function () {
    $html = Blade::render('<x-src.toolbar.button label="Agenda" tooltip="Tooltip agenda" icon="calendar" />');

    expect($html)
        ->toContain('title="Tooltip agenda"')
        ->toContain('aria-label="Tooltip agenda"')
        ->toContain('Tooltip agenda')
        ->toContain('toolbar-button')
        ->toContain('toolbar-tooltip')
        ->toContain('role="tooltip"');
});

it('keeps toolbar nav container with horizontal auto overflow', function () {
    $html = Blade::render('<x-src.toolbar.nav>Conteudo</x-src.toolbar.nav>');

    expect($html)
        ->toContain('overflow-x-auto')
        ->toContain('overflow-y-hidden')
        ->toContain('overflow-hidden')
        ->toContain('toolbar-scroll')
        ->toContain('toolbar-scroll-track')
        ->toContain('min-w-max');
});
