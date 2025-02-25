<?php
namespace App\Services\Excel;

use App\Models\Category;

class CategoryTransformer
{
    public function transform(Category $category): array
    {
        return [
            'Name (en)'       => $category->getTranslation('name', 'en'),
            'Name (ar)'       => $category->getTranslation('name', 'ar'),
            'Department Name (en)' => $category->department->getTranslation('name', 'en') ?? 'no Department',
            'Department Name (ar)' => $category->department->getTranslation('name', 'ar') ?? 'بدون قسم',
            'Type (en)'       => $this->getTypeEn($category),
            'Type (ar)'       => $this->getTypeAr($category),
        ];
    }
    public function getTypeEn($category)
    {
        if ($category->parent_id == null) {
            return 'Category';
        }
        elseif ($category->parent_id !== null) {
            return 'Sub Category';
        }
    }
    public function getTypeAr($category)
    {
        if ($category->parent_id == null) {
            return 'فئة';
        }
        elseif ($category->parent_id !== null) {
            return 'فئة فرعية';
        }
    }
}
