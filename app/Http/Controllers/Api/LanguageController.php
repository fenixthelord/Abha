<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Traits\ResponseTrait;

class LanguageController extends Controller
{
    use ResponseTrait;
    public function swap($locale)
    {
        try {

            if (!in_array($locale, SupportedLanguages())) {
                $locale = 'en';
            }
            app()->setLocale($locale);
            
            return $this->returnSuccessMessage('Language changed successfully');
        } catch (\Exception $e) {
            abort(400 , $e->getMessage()); 
        }
    }
}
