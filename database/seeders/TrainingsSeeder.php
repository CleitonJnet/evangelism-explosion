<?php

namespace Database\Seeders;

use App\Models\Training;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TrainingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Training::create(
            [
                'banner' => null,
                'coordinator' => 'Secretaria',
                'phone' => '21972765535',
                'email' => 'secretaria@pibinga.org.br',
                'street' => 'Rua Dr. Paulo Alves',
                'number' => '125',
                'complement' => null,
                'district' => 'Ingá',
                'city' => 'Niterói',
                'state' => 'RJ',
                'postal_code' => '24365170',
                'url' => null,
                'price' => '180.00',
                'price_church' => '20.00',
                'discount' => '0.00',
                'kits' => null,
                'totStudents' => 0,
                'totChurches' => 0,
                'totNewChurches' => 0,
                'totPastors' => 0,
                'totKitsUsed' => 0,
                'totListeners' => 0,
                'totKitsReceived' => 0,
                'totApproaches' => 0,
                'totDecisions' => 0,
                'notes' => null,
                'status' => 1,
                'welcome_duration_minutes' => 30,
                'course_id' => 1,
                'teacher_id' => 1,
                'church_id' => 1,
            ]
        );

        DB::table('event_dates')->insert(['training_id' => 1, 'date' => '2026-02-06', 'start_time' => '18:30:00', 'end_time' => '21:30:00']);
        DB::table('event_dates')->insert(['training_id' => 1, 'date' => '2026-02-07', 'start_time' => '08:30:00', 'end_time' => '21:30:00']);
        DB::table('event_dates')->insert(['training_id' => 1, 'date' => '2026-02-08', 'start_time' => '08:30:00', 'end_time' => '18:00:00']);

        Training::create(
            [
                'banner' => null,
                'coordinator' => 'Secretaria',
                'phone' => '21972765535',
                'email' => 'secretaria@pibinga.org.br',
                'street' => 'Rua Dr. Paulo Alves',
                'number' => '125',
                'complement' => null,
                'district' => 'Ingá',
                'city' => 'Niterói',
                'state' => 'RJ',
                'postal_code' => '24365170',
                'url' => null,
                'price' => '180.00',
                'price_church' => '20.00',
                'discount' => '0.00',
                'kits' => null,
                'totStudents' => 0,
                'totChurches' => 0,
                'totNewChurches' => 0,
                'totPastors' => 0,
                'totKitsUsed' => 0,
                'totListeners' => 0,
                'totKitsReceived' => 0,
                'totApproaches' => 0,
                'totDecisions' => 0,
                'notes' => null,
                'status' => 1,
                'welcome_duration_minutes' => 30,
                'course_id' => 1,
                'teacher_id' => 1,
                'church_id' => 1,
            ]
        );

        DB::table('event_dates')->insert(['training_id' => 2, 'date' => '2026-02-02', 'start_time' => '19:00:00', 'end_time' => '22:30:00']);
        DB::table('event_dates')->insert(['training_id' => 2, 'date' => '2026-02-03', 'start_time' => '19:00:00', 'end_time' => '22:30:00']);
        DB::table('event_dates')->insert(['training_id' => 2, 'date' => '2026-02-04', 'start_time' => '19:00:00', 'end_time' => '22:30:00']);
        DB::table('event_dates')->insert(['training_id' => 2, 'date' => '2026-02-05', 'start_time' => '19:00:00', 'end_time' => '22:30:00']);
        DB::table('event_dates')->insert(['training_id' => 2, 'date' => '2026-02-06', 'start_time' => '19:00:00', 'end_time' => '22:30:00']);
        DB::table('event_dates')->insert(['training_id' => 2, 'date' => '2026-02-07', 'start_time' => '09:00:00', 'end_time' => '17:00:00']);

        Training::factory()
            ->courseOne()
            ->count(18)
            ->create();

        Training::factory()
            ->courseTwo()
            ->count(18)
            ->create();

        Training::factory()
            ->courseThree()
            ->count(18)
            ->create();

        Training::factory()
            ->count(255)
            ->create();
    }
}
