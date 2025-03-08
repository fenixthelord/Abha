<?php

namespace App\Services\Excel;

use App\Models\Position;

class PositionTransformer
{
    public function transform(Position $position): array
    {
        return [
            'name_ar' => $position->getTranslation('name', 'ar'),
            'name_en' => $position->getTranslation('name', 'en'),
            'parent_id' => $position->parent_id,
            'Created At'      => $position->created_at->format('Y-m-d H:i:s'),
        ];
    }
}
