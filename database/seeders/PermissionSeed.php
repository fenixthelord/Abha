<?php

namespace Database\Seeders;

use App\Models\Role\Permission;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use App\Models\Role\Role;
use Illuminate\Support\Facades\Hash;

class PermissionSeed extends Seeder
{
    // Define the actions you want to create permissions for
    protected $actions = ['create', 'update', 'show', 'delete', 'restore'];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all model classes from the app/Models directory (including subfolders)
        $models = $this->getModels();

        // Create permissions for each model and action
        foreach ($models as $model) {
            $this->createPermissionsForModel($model);
        }

        $this->command->info('Permissions seeded successfully!');
        $this->MasterRole();
        $this->OwnerRole();
    }

    /**
     * Get all models from the app/Models directory (including subfolders).
     *
     * @return array
     */
    protected function getModels()
    {
        $models = [];
        $modelFiles = File::allFiles(app_path('Models'));

        foreach ($modelFiles as $file) {
            // Get the relative path of the model file (including subfolders)
            $relativePath = $file->getRelativePathName();
            // Remove the file extension and replace slashes with namespace separators
            $modelClass = app()->getNamespace() . 'Models\\' . str_replace(
                ['/', '.php'],
                ['\\', ''],
                $relativePath
            );

            // Check if the class exists
            if (class_exists($modelClass)) {
                $models[] = $modelClass;
            }
        }

        return $models;
    }

    /**
     * Create permissions for a specific model.
     *
     * @param string $model
     * @return void
     */
    protected function createPermissionsForModel($model)
    {

        $modelName = class_basename($model);

        foreach ($this->actions as $action) {
            $permissionName = strtolower("{$modelName}.{$action}");
            $permission = Permission::where('name', $permissionName)
                ->where('guard_name', 'sanctum')
                ->first();

            if (!$permission) {
                $permission = Permission::create([
                    'name' => $permissionName,
                    'displaying' => $permissionName,
                    'guard_name' => 'sanctum',
                    'group' => strtolower($modelName),
                    'is_admin' => 0,
                ]);
            }
        }
    }
    public function MasterRole()
    { {
            DB::beginTransaction();
            try {
                // Create Master Role
                $masterRole = Role::where('name', 'Master')->where('guard_name', 'sanctum')->first();
                if (!$masterRole) {
                    $masterRole = Role::Create(['name' => 'Master', "displaying" => "Master", "description" => "Master in the system"]);
                }

                // Define permissions
                $permissions = ['master.create', 'master.assign', 'mas
            ter.remove'];

                foreach ($permissions as $permissionName) {

                    // Create each permission
                    $permission = Permission::where('name', $permissionName)
                        ->where('guard_name', 'sanctum')
                        ->first();
                    if (!$permission) {
                        $permission = Permission::Create([
                            'name' => $permissionName,
                            "displaying" => $permissionName,
                            "guard_name" => "sanctum",
                            "group" => "master",
                            "is_admin" => true
                        ]);
                    }

                    // Assign permission to Master role
                    $masterRole->givePermissionTo($permission);
                }
                $masterUser = User::where('email', 'masteracount@gmail.com')->first();
                if (!$masterUser) {
                    // Create Master User
                    $masterUser = User::Create(
                        [
                            'email' => "masteracount@gmail.com",
                            'password' => Hash::make('master123'),
                            'first_name' => "master",
                            'last_name' => "master",
                            'phone' => "0592257835",

                            'gender' => "male",
                            // Update password as needed
                        ]
                    );
                }
                $permissions = Permission::all();
                if (!$permissions->isEmpty()) {

                    $masterRole->givePermissionTo($permissions);
                }

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

    protected function OwnerRole()
    { {
            DB::beginTransaction();
            try {
                // Create Master Role
                $owner = Role::where('name', 'Master_Owner')->first();
                if (!$owner) {
                    $owner = Role::Create(['name' => 'Master_Owner', "displaying" => "owner", "description" => "owner of the system"]);
                }

                // Define permissions


                $OwnerUser = User::where('email', 'owneracount@gmail.com')->first();
                if (!$OwnerUser) {

                    // Create Master User
                    $OwnerUser = User::Create(
                        [
                            'email' => "owneracount@gmail.com",
                            'password' => Hash::make('owner123'),
                            'first_name' => "owner",
                            'last_name' => "Owner",
                            'phone' => "0592257836",

                            'gender' => "male",
                            // Update password as needed
                        ]
                    );
                }
                $permissions = Permission::where('is_admin', false)->get();
                if (!$permissions->isEmpty()) {

                    $owner->givePermissionTo($permissions);
                }

                // Assign Master role to the Master user
                $OwnerUser->assignRole($owner);

                DB::commit();

                echo "Owner role, account, and permissions created successfully.\n";
            } catch (\Exception $e) {
                DB::rollBack();
                echo "Error: " . $e->getMessage() . "\n";
            }
        }
    }
}
