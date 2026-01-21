<?php

uses(Tests\TestCase::class);

use App\Models\Training;

it('formats training prices in BRL', function () {
    config(['money.currency' => 'BRL']);

    $training = new Training([
        'price' => '1234.56',
        'price_church' => '1.234,56',
    ]);

    expect($training->price)->toBe('R$ 1.234,56')
        ->and($training->price_church)->toBe('R$ 1.234,56');
});

it('formats training prices in USD', function () {
    config(['money.currency' => 'USD']);

    $training = new Training([
        'price' => 1234.56,
    ]);

    expect($training->price)->toBe('$ 1,234.56');
});

it('returns null for missing prices', function () {
    config(['money.currency' => 'BRL']);

    $training = new Training([
        'price' => null,
        'price_church' => '',
    ]);

    expect($training->price)->toBeNull()
        ->and($training->price_church)->toBeNull();
});

it('sums training prices into payment', function () {
    config(['money.currency' => 'BRL']);

    $training = new Training([
        'price' => '10,00',
        'price_church' => '5,50',
    ]);

    expect($training->payment)->toBe('R$ 15,50');
});

it('subtracts discount from payment', function () {
    config(['money.currency' => 'BRL']);

    $training = new Training([
        'price' => '100,00',
        'price_church' => '20,00',
        'discount' => '15,00',
    ]);

    expect($training->payment)->toBe('R$ 105,00');
});

it('returns null payment when prices are missing', function () {
    config(['money.currency' => 'BRL']);

    $training = new Training([
        'price' => null,
        'price_church' => '',
    ]);

    expect($training->payment)->toBeNull();
});
