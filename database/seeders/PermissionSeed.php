<?php

namespace Database\Seeders;

use App\Models\Role\Permission;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

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
            Permission::firstOrCreate([
                'name' => $permissionName,
                'displaying' => $permissionName,
                'group' => strtolower($modelName),
                'is_admin' => 0,
            ]);
        }
    }
}
