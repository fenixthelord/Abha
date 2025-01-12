<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Traits\ResponseTrait;

class LanguageController extends Controller
{
    use ResponseTrait;
    public function swap($locale)
    {
        if (!in_array($locale, SupportedLanguages())) {
            $locale = 'en';
        }
        app()->setLocale($locale);

        return $this->returnSuccessMessage('Language changed successfully');
    }
}
