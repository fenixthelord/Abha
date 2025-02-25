<?php

namespace App\Services\Excel;

use App\Models\Position;

class PositionTransformer
{
    public function transform(Position $position): array
    {
        return [
            'Name (en)'       => $position->getTranslation('name', 'en') ?? 'N/A',
            'Name (ar)'       => $position->getTranslation('name', 'ar') ?? 'N/A',
            'Parent Position' => $position->parent ? $position->parent->getTranslation('name', 'en') : 'N/A',
            'Created At'      => $position->created_at->format('Y-m-d H:i:s'),

        ];
    }
}
