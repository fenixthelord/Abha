<?php

namespace App\Http\Traits;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

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
    public function uploadImagePublic($request, $name, $inputName = 'image',$extra = null)
    {

        if(!is_null($extra)){

            $requestFile=$extra;


        }else{
            $requestFile = $request->file($inputName);
        }
        try {

            $dir = 'public/images/'.$name;
            $randomNumber = Str::random(5);
            $fixName =$randomNumber.'.'.$requestFile->extension();
            if ($requestFile) {
                 Storage::putFileAs($dir, $requestFile, $fixName);
                 $url = Storage::url($dir.'/'.$fixName);
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
            $dir = 'images/'.$name;
            $randomNumber = Str::random(5);
            $fixName = $data->id.'-'.$randomNumber.'.'.$requestFile->extension();
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
}
