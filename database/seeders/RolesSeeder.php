<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RolesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            ['name' => 'Board'],
            ['name' => 'Director'],
            ['name' => 'FieldWorker'],
            ['name' => 'Teacher'],
            ['name' => 'Facilitator'],
            ['name' => 'Mentor'],
            ['name' => 'Student'],
        ];

        foreach ($roles as $role) {
            Role::updateOrCreate(['name' => $role['name']], $role);
        }
    }
}
