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

    private const FILE_SIZE = 15 * 1024; // 15 MB

    public function upload(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'document' => 'required|file|size:' . SELF::FILE_SIZE,
        ], [
            'document.size' =>  "The document field must be " . SELF::FILE_SIZE / 1024 . " MB or less."
        ]);

        if ($validator->fails()) {
            return $this->returnValidationError($validator);
        }

        $file = $request->file('document');

        try {
            $filePath = $this->uploadFile($file, 'documents', SELF::FILE_SIZE);

            return $this->returnData($filePath,  __('validation.custom.upload_file.upload_file_success'));
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }
}
