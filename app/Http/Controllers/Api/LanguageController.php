<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Traits\ResponseTrait;

class LanguageController extends Controller
{
    use ResponseTrait;
    public function swap($locale)
    {
        if (!in_array($locale, ['en', 'fr', 'ar', 'de'])) {
            // return $this->returnError('Language not supported');
            return response()->json([
                'status' => false,
                'code' => 400,
                'msg' => "Language not supported",
            ], 400);
        }

        app()->setLocale($locale);

        return $this->returnSuccessMessage('Language changed successfully');
    }
}
