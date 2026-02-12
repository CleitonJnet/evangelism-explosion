<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;

class UsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $board = Role::firstOrCreate(['name' => 'Board']);
        $director = Role::firstOrCreate(['name' => 'Director']);
        $teacher = Role::firstOrCreate(['name' => 'Teacher']);
        $facilitator = Role::firstOrCreate(['name' => 'Facilitator']);
        $mentor = Role::firstOrCreate(['name' => 'Mentor']);
        $student = Role::firstOrCreate(['name' => 'Student']);

        $userOne = User::create([
            'id' => 1,
            'name' => 'Cleiton dos Santos',
            'email' => 'csilva@eeworks.org',
            'phone'=> '21972765535',
            'password' => bcrypt('Master@01'),
            'church_id' => 1,
        ]);
        $userTwo = User::create([
            'id' => 2,
            'name' => 'Jailton Barreto Rangel',
            'email' => 'jailtonbarreto@eeworks.org',
            'phone'=> '21991046211',
            'password' => bcrypt('Master@01'),
            'church_id' => 4,
        ]);
        $userThree = User::create([
            'id' => 3,
            'name' => 'Cleverson Rodrigues',
            'phone'=> '6692603673',
            'email' => 'cleverson@eebrasil.org.br',
            'password' => bcrypt('Master@01'),
            'church_id' => 5,
        ]);

        $userFour = User::create([
            'id' => 4,
            'name' => 'Davdsion Freitas',
            'phone'=> '21992192082',
            'email' => 'davidsonfreitas@eebrasil.org.br',
            'password' => bcrypt('Master@01'),
            'church_id' => 3,
        ]);

        $userFive = User::create([
            'id' => 5,
            'name' => 'Robert D. Foster',
            'phone'=> '6787364150',
            'email' => 'rfoster@eeworks.org',
            'password' => bcrypt('Master@01'),
            'church_id' => 6,
        ]);

        $factoryCount = max(0, 100);

        User::factory()
            ->count($factoryCount)
            ->create();

        $userOne->roles()->sync([1,2,3,4,5,6,7]);
        $userTwo->roles()->sync([1,2,3,4,5,6]);
        $userThree->roles()->sync([3,4,5,6]);
        $userFour->roles()->sync([1,3,4,5,6]);
        $userFive->roles()->sync([1,2,3,4,5,6,7]);

        $roleIds = [$board->id, $director->id, $teacher->id, $facilitator->id, $mentor->id, $student->id];

        User::query()
            ->whereDoesntHave('roles')
            ->select(['id'])
            ->chunkById(20, function ($users) use ($roleIds): void {
                foreach ($users as $user) {
                    $user->roles()->attach(Arr::random($roleIds));
                }
            });
    }
}
