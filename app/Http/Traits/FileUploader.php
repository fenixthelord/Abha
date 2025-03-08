<?php

namespace App\Http\Traits;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Http\UploadedFile;

trait FileUploader
{
    /**
     * For Upload Images.
     * @param mixed $request
     * @param mixed $data
     * @param mixed $name
     * @param mixed|null $inputName
     * @return bool|string
     */
    public function uploadImagePublic($request, $name, $inputName = 'image', $extra = null)
    {

        if (!is_null($extra)) {

            $requestFile = $extra;
        } else {
            $requestFile = $request->file($inputName);
        }
        try {

            $dir = 'public/images/' . $name;
            $randomNumber = Str::random(5);
            $fixName = $randomNumber . '.' . $requestFile->extension();
            if ($requestFile) {
                Storage::putFileAs($dir, $requestFile, $fixName);
                $url = Storage::url($dir . '/' . $fixName);
                $request->image = $fixName;
            }

            return $url;
        } catch (\Throwable $th) {
            report($th);

            return $th->getMessage();
        }
    }

    public function uploadImagePrivate($request, $data, $name, $inputName = 'image')
    {
        $requestFile = $request->file($inputName);

        try {
            $dir = 'images/' . $name;
            $randomNumber = Str::random(5);
            $fixName = $data->id . '-' . $randomNumber . '.' . $requestFile->extension();
            if ($requestFile) {
                $url =  Storage::putFileAs($dir, $requestFile, $fixName);
                $request->image = $fixName;
            }
            return $url;
        } catch (\Throwable $th) {
            report($th);
            return $th->getMessage();
        }
    }


    public function uploadMultiImage($request, $data, $name, $inputName = 'images')
    {

        $requestFiles = $request->file($inputName);

        if (!is_array($requestFiles)) {
            return ['status' => 'Error', 'message' => 'The input must be an array of files for: ' . $inputName];
        }

        $uploadedImages = [];

        foreach ($requestFiles as $file) {
            $dir = 'public/images/' . $name;
            $fixName = uniqid() . '-' . $name . '.'  . $file->getClientOriginalExtension();

            if ($file) {
                Storage::putFileAs($dir, $file, $fixName);
                $uploadedImages[] = [
                    'url' => $dir . '/' . $fixName,
                ];
            }
        }
        return $uploadedImages;
    }


    /**
     * Upload a file safely.
     *
     * @param  \Illuminate\Http\UploadedFile  $file
     * @param  string  $folder  Folder path to store the file (default: 'files')
     * @param  int  $maxSize  Maximum allowed file size in kilobytes (default: 2048 KB)
     * @return string  The file path where the file was stored.
     *
     * @throws \Exception If the file type, MIME type, or size is not allowed.
     */
    public function uploadFile(UploadedFile $file, string $folder = 'files', int $maxSize = 2048): string
    {
        // Define allowed file extensions.
        $allowedExtensions = ['doc', 'docx', 'xls', 'xlsx', 'pdf'];

        // Define allowed MIME types.
        $allowedMimeTypes = [
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'application/pdf',
        ];

        // Validate file size.
        if (($file->getSize() / 1024) > $maxSize) {
            throw new \Exception("The file exceeds the maximum allowed size of {$maxSize} KB.");
        }

        // Validate file extension.
        $extension = strtolower($file->getClientOriginalExtension());
        if (!in_array($extension, $allowedExtensions)) {
            throw new \Exception("Files of type '{$extension}' are not allowed.");
        }

        // Validate MIME type.
        $mimeType = $file->getMimeType();
        if (!in_array($mimeType, $allowedMimeTypes)) {
            throw new \Exception("Files with MIME type '{$mimeType}' are not allowed.");
        }

        // Generate a unique file name.
        $safeName = Str::random(40) . '.' . $extension;

        // Store the file using Laravel's Storage facade (using the 'public' disk).
        $path = "storage/" . $file->storeAs($folder, $safeName, 'public');

        if (!$path) {
            throw new \Exception("Failed to upload file.");
        }

        return $path;
    }
}
