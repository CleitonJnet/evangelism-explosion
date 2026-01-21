<?php

namespace Database\Seeders;

use App\Models\Training;
use Illuminate\Database\Seeder;

class TrainingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Training::create([ 'id' => 1, 'course_id' => 1, 'teacher_id' => 2, 'church_id' => 1, 'coordinator' => 'Pr Fransisco Batista', 'url' => null, 'phone' => '21972765535', 'email' => 'teste@teste.com', 'street' => 'Rua Dr. Paulo Alves', 'number' => '125', 'complement' => null, 'district' => 'Ingá', 'city' => 'Niterói', 'state' => 'RJ', 'postal_code' => '24365170', 'price' => '180', 'price_church' => '0', 'discount' => null, 'totStudents' => 0, 'totChurches' => 0, 'totNewChurches' => 0, 'totPastors' => 0, 'totKitsReceived' => 0, 'totKitsUsed' => 0, 'totApproaches' => 0, 'totDecisions' => 0, 'totListeners' => 0, 'notes' => null, 'status' => 1 /** 0-planning|1-scheduled|2-canceled|3-completed */ ]);
        // Training::create([ 'id' => 2, 'course_id' => 7, 'teacher_id' => 1, 'church_id' => 3, 'coordinator' => 'Pr Fransisco Batista', 'url' => null, 'phone' => '21972765535', 'email' => 'teste@teste.com', 'street' => 'Av. Guarapuava', 'number' => '1087', 'complement' => null, 'district' => 'Jardim Iguassu', 'city' => 'Rondonópolis', 'state' => 'MT', 'postal_code' => '78730398', 'price' => '150', 'price_church' => '0', 'discount' => null, 'totStudents' => 0, 'totChurches' => 0, 'totNewChurches' => 0, 'totPastors' => 0, 'totKitsReceived' => 0, 'totKitsUsed' => 0, 'totApproaches' => 0, 'totDecisions' => 0, 'totListeners' => 0, 'notes' => null, 'status' => 1 /** 0-planning|1-scheduled|2-canceled|3-completed */ ]);

        // EventDate::create(['training_id' => 1, 'date' => '2026-02-06', 'start_time' => '19:00:00', 'end_time' => '21:30:00' ]);
        // EventDate::create(['training_id' => 1, 'date' => '2026-02-07', 'start_time' => '08:00:00', 'end_time' => '22:00:00' ]);
        // EventDate::create(['training_id' => 1, 'date' => '2026-02-08', 'start_time' => '08:00:00', 'end_time' => '18:00:00' ]);

        // EventDate::create(['training_id' => 2, 'date' => '2026-03-21', 'start_time' => '08:00:00', 'end_time' => '17:30:00' ]);
        // EventDate::create(['training_id' => 2, 'date' => '2026-03-22', 'start_time' => '08:00:00', 'end_time' => '17:30:00' ]);

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
