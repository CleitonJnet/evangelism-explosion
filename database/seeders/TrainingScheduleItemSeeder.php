<?php

namespace Database\Seeders;

use App\Models\Training;
use App\Services\Schedule\TrainingScheduleGenerator;
use Illuminate\Database\Seeder;

class TrainingScheduleItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $generator = app(TrainingScheduleGenerator::class);

        Training::query()
            ->with(['eventDates', 'course.sections'])
            ->take(10)
            ->get()
            ->each(function (Training $training) use ($generator): void {
                $generator->generate($training, 'FULL');
            });
    }
}
