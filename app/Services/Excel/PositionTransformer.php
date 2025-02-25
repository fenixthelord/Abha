<?php

namespace App\Services\Excel;

use App\Models\Position;

class PositionTransformer
{
    public function transform(Position $position): array
    {
        return [
            'Name (en)'       => $position->getTranslation('name', 'en'),
            'Name (ar)'       => $position->getTranslation('name', 'ar'),

            'Created At'      => $position->created_at->format('Y-m-d H:i:s'),
        ];
    }
}
