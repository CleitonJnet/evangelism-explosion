<?php

use App\Models\Church;
use Database\Seeders\ChurchesSeeder;

test('churches seeder creates 100 churches', function () {
    $this->seed(ChurchesSeeder::class);

    expect(Church::query()->count())->toBe(100);
});
