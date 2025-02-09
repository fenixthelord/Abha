<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Traits\FileUploader;
use App\Http\Traits\ResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class UploadFileController extends Controller
{
    use FileUploader, ResponseTrait;

    public function upload(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'document' => 'required|file',
        ]);

        if ($validator->fails()) {
            return $this->returnValidationError($validator);
        }

        $file = $request->file('document');

        try {
            $filePath = $this->uploadFile($file, 'documents');

            return $this->returnData($filePath,  'File uploaded successfully');
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }
}
