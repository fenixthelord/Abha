<?php
namespace App\Services\Excel;

use App\Models\Department;

class DepartmentTransformer
{
    public function transform(Department $department): array
    {
        return [
            'Name (en)'       => $department->getTranslation('name', 'en'),
            'Name (ar)'       => $department->getTranslation('name', 'ar'),
        ];
    }
}
