<?php

use App\Models\User;
use Database\Seeders\UsersSeeder;

test('users seeder creates 5000 users', function () {
    $this->seed(UsersSeeder::class);

    expect(User::query()->count())->toBe(5000);
});
