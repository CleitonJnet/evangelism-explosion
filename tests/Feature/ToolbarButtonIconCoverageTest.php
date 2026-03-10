<?php

use Illuminate\Support\Facades\Blade;

it('renders the inventory toolbar buttons with the supported icon set', function (): void {
    $html = Blade::render(
        <<<'BLADE'
            <div>
                <x-src.toolbar.button label="Novo item simples" icon="archive-box" />
                <x-src.toolbar.button label="Novo composto" icon="squares-2x2" />
                <x-src.toolbar.button label="Saída" icon="minus" />
                <x-src.toolbar.button label="Ajuste" icon="arrow-path" />
                <x-src.toolbar.button label="Perda" icon="exclamation-triangle" />
            </div>
        BLADE
    );

    expect($html)->toContain('Novo composto');
    expect($html)->toContain('Novo item simples');
    expect(substr_count($html, '<svg'))->toBeGreaterThanOrEqual(5);
});
