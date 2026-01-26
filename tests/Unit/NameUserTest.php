<?php

use App\Helpers\NameUser;

it('returns initials from first and last name', function (string $name, string $expected) {
    expect(NameUser::initials($name))->toBe($expected);
})->with([
    'full name' => ['João da Silva', 'JS'],
    'extra spaces' => ['  Maria   Clara  ', 'MC'],
    'single name' => ['Plato', 'P'],
    'empty string' => ['   ', ''],
]);
