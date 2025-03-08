<?php

namespace Database\Seeders;

use App\Models\Role\Permission;
use App\Models\Role\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;


class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */

    public function run()
    {
        DB::beginTransaction();
        try {
            // Create Master Role
            $masterRole = Role::firstOrCreate(['name' => 'Master', "displaying"=>"Master","description" => "Master in the system"]);

            // Define permissions
            $permissions = ['master.create', 'master.assign', 'mas
            ter.remove'];

            foreach ($permissions as $permissionName) {
                // Create each permission
                $permission = Permission::firstOrCreate(['name' => $permissionName, "displaying" => $permissionName,
                    "guard_name" => "sanctum", "group" => "master", "is_admin" => true]);

                // Assign permission to Master role
                $masterRole->givePermissionTo($permission);
            }

            // Create Master User
            $masterUser = User::firstOrCreate(
                ['email' => "masteracount@gmail.com",
                    'password' => Hash::make('master123'),
                    'first_name' => "master",
                    'last_name' => "master",
                    'phone' => "0992257835",

                    'gender' => "male",
                    'uuid' => \Str::uuid(),
                    // Update password as needed
                ]
            );

            // Assign Master role to the Master user
            $masterUser->assignRole($masterRole);

            DB::commit();

            echo "Master role, account, and permissions created successfully.\n";
        } catch (\Exception $e) {
            DB::rollBack();
            echo "Error: " . $e->getMessage() . "\n";
        }
    }
}
