<?php

use App\Helpers\AddressHelper;

it('formats address parts with commas', function () {
    $address = AddressHelper::format_address(
        street: 'Rua A',
        number: '123',
        complement: 'Sala 4',
        district: 'Centro',
        city: 'Niterói',
        state: 'RJ',
        postal_code: '24.000-000',
    );

    expect($address)->toBe('Rua A, 123, Sala 4, Centro, Niterói, RJ, 24.000-000');
});

it('ignores null or blank values when formatting', function () {
    $address = AddressHelper::format_address(
        street: ' Rua B ',
        number: '',
        complement: '   ',
        district: null,
        city: 'Rio de Janeiro',
        state: 'RJ',
        postal_code: null,
    );

    expect($address)->toBe('Rua B, Rio de Janeiro, RJ');
});

it('returns an empty string when no address value is provided', function () {
    $address = AddressHelper::format_address();

    expect($address)->toBe('');
});
