<?php
namespace App\Services\Excel;

use App\Models\Service;

class ServiceTransformer
{
    public function transform(Service $service): array
    {
        return [
            'Name (en)'       => $service->getTranslation('name', 'en'),
            'Name (ar)'       => $service->getTranslation('name', 'ar'),
            'Details (en)'    => $service->getTranslation('details', 'en'),
            'Details (ar)'    => $service->getTranslation('details', 'ar'),
            'Department (en)' => $service->department ? $service->department->getTranslation('name', 'en') : null,
            'Department (ar)' => $service->department ? $service->department->getTranslation('name', 'ar') : null,
        ];
    }
}
