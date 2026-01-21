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
        $director = Role::firstOrCreate(['name' => 'Director']);
        $teacher = Role::firstOrCreate(['name' => 'Teacher']);
        $facilitator = Role::firstOrCreate(['name' => 'Facilitator']);
        $mentor = Role::firstOrCreate(['name' => 'Mentor']);
        $student = Role::firstOrCreate(['name' => 'Student']);

        $userOne = User::create([
            'id' => 1,
            'name' => 'Cleiton dos Santos',
            'email' => 'csilva@eeworks.org',
            'password' => bcrypt('Master@01'),
            'church_id' => 1,
        ]);
        $userTwo = User::create([
            'id' => 2,
            'name' => 'Jailton Barreto Rangel',
            'email' => 'jailtonbarreto@eeworks.org',
            'password' => bcrypt('Master@01'),
            'church_id' => 2,
        ]);
        $userThree = User::create([
            'id' => 3,
            'name' => 'Cleverson Rodrigues',
            'email' => 'cleverson@eeworks.org',
            'password' => bcrypt('Master@01'),
            'church_id' => 3,
        ]);

        $factoryCount = max(0, 5000 - 3);

        User::factory()
            ->count($factoryCount)
            ->create();

        $userOne->roles()->sync([$director->id]);
        $userTwo->roles()->sync([$teacher->id, $facilitator->id]);
        $userThree->roles()->sync([$mentor->id, $student->id]);

        $roleIds = [$director->id, $teacher->id, $facilitator->id, $mentor->id, $student->id];

        User::query()
            ->whereDoesntHave('roles')
            ->select(['id'])
            ->chunkById(200, function ($users) use ($roleIds): void {
                foreach ($users as $user) {
                    $user->roles()->attach(Arr::random($roleIds));
                }
            });
    }
}
