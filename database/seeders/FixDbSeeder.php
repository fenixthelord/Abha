<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class FixDbSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $models = $this->getAvailableModels();

        foreach ($models as $model) {
            $use = new $model;
            $table_name = $use->getTable();


            // تجاهل الجداول غير المرغوب فيها
            if (in_array($table_name, ['users', 'audits', 'LinkedSocialAccounts', 'DeviceTokens', 'NotificationDetails'])) {
                continue;
            }

            $products = \DB::table($table_name)->get();
            foreach ($products as $product) {
                $productInstance = $use::find($product->id);
                if (!$productInstance) {
                    continue; // Skip if the product instance is not found
                }

                // Ensure the model has the getTransAble method
                if (method_exists($productInstance, 'getTransAble')) {
                    $columns = $productInstance->getTransAble();

                    foreach ($columns as $column) {
                        if ($column && isset($product->$column)) {
                            $value = $product->$column;

                            // إذا كانت القيمة JSON، تجاهل التحويل
                            if ($this->isJson($value)) {
                                continue;
                            }

                            // إذا لم يكن JSON، خزّنه كنص عادي
                            $productInstance->setTranslation($column, 'en', (string) $value);
                            $productInstance->save();
                        }
                    }
                }
            }
        }
    }

    /**
     * Get all available models from the Models directory.
     */
    public static function getAvailableModels()
    {
        // Directly return the models using the helper function
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



    }

    /**
     * Helper function to get all model classes from the Models directory.
     */


    /**
     * Helper function to check if a string is a valid JSON.
     */
    private function isJson($string)
    {
        json_decode($string);
        return json_last_error() === JSON_ERROR_NONE;
    }
}
