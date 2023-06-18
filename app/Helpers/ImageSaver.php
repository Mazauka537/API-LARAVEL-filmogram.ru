<?php

namespace App\Helpers;

use Illuminate\Database\Eloquent\InvalidCastException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ImageSaver
{
    public static function saveImage($image, $path, $id, $oldImageName, $isImageDeleted) {
        if ($image) {
            $separatedBase64 = explode(',', $image);
            $encodedImage = base64_decode($separatedBase64[1]);
            $extension = explode('/', mime_content_type($image))[1];
            $fileName = $id . '.' . $extension;

            try {
                Storage::disk('local')->delete($path . $oldImageName);
                Storage::disk('local')->put($path . $fileName, $encodedImage);
            } catch (InvalidCastException $exception) {
                Storage::delete($path . $fileName);
                return response('', 500);
            }
        } else {
            if ($isImageDeleted) {
                $fileName = '';
            } else {
                $fileName = $oldImageName;
            }
        }

        return $fileName;
    }
}
