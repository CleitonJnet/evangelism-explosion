<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Blade;

it('renders toolbar button icons', function (string $icon): void {
    $html = Blade::render('<x-src.toolbar.button icon="'.$icon.'" label="Test" />');

    expect($html)->toContain('<svg');
})->with([
    'list',
    'calendar',
    'calendar-check',
    'home',
    'arrow-left',
    'users',
    'document-text',
    'chart-bar',
    'pencil',
    'trash',
    'briefcase',
    'eye',
    'user-group',
]);
