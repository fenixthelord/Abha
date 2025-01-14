<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class PermissionSeeder extends Seeder
{
    private $permissions = [
        [
            'name' => "create users",
            'displaying'=>"create users",
            'guard_name' => 'web',
            'group'=>"users",
            'is_admin'=>true
        ],
        [
            'name' => "update users",
            'displaying'=>"update users",
            'guard_name' => 'web',
            'group'=>"users",
            'is_admin'=>true
        ],

    ];
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        foreach ($this->permissions as $permission) {
            Permission::create($permission);
        }

    }
}
