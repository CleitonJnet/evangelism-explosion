<?php

use App\Helpers\PhoneHelper;

it('formats brazilian phones', function (string $input, string $expected) {
    expect(PhoneHelper::format_phone($input))->toBe($expected);
})->with([
    'landline digits' => ['2133224455', '(21) 3322-4455'],
    'landline masked' => ['(21) 3322-4455', '(21) 3322-4455'],
    'mobile digits' => ['21972765535', '(21) 97276-5535'],
    'mobile masked' => ['(21) 97276-5535', '(21) 97276-5535'],
    'with ddi' => ['5521972765535', '(21) 97276-5535'],
]);

it('returns original value for invalid phones', function (string $input) {
    expect(PhoneHelper::format_phone($input))->toBe($input);
})->with([
    'too short' => ['123'],
    'unexpected length' => ['1234567890123'],
]);

it('returns null when phone is null', function () {
    expect(PhoneHelper::format_phone(null))->toBeNull();
});
