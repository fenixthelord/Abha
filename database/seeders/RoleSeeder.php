<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
         \App\Models\Role\Role::create([
             'name' => 'Master',
             'description' => 'master of the system',
        'guard_name' => 'sanctum']);
        // ]);
    }
}
