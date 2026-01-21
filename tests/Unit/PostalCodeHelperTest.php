<?php

use App\Helpers\PostalCodeHelper;

it('formats brazilian postal codes', function (string $input, string $expected) {
    expect(PostalCodeHelper::format_postalcode($input))->toBe($expected);
})->with([
    'raw digits' => ['97276553', '97276-553'],
    'masked input' => ['97.276-553', '97276-553'],
]);

it('returns the original string when postal code is invalid', function (string $input) {
    expect(PostalCodeHelper::format_postalcode($input))->toBe($input);
})->with([
    'too short' => ['1234'],
    'too long' => ['12345-6789'],
]);

it('returns null when postal code is null', function () {
    expect(PostalCodeHelper::format_postalcode(null))->toBeNull();
});
