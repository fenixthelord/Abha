<?php

namespace App\Services\Excel;

use App\Models\Type;

class TypeTransformer
{
    public function transform(Type $type): array
    {
        return [
            'Name (en)'       => $type->getTranslation('name', 'en'),
            'Name (ar)'       => $type->getTranslation('name', 'ar'),
            'Service (en)'    => $type->service ? $type->service->getTranslation('name', 'en') : null,
            'Service (ar)'    => $type->service ? $type->service->getTranslation('name', 'ar') : null,
            'Form (en)'       => $type->form ? $type->form->getTranslation('name', 'en') : null,
            'Form (ar)'       => $type->form ? $type->form->getTranslation('name', 'ar') : null,
            'Created At'      => $type->created_at->format('Y-m-d H:i:s'),
        ];
    }
}
