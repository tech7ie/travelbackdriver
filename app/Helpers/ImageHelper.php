<?php

namespace App\Helpers;
use Illuminate\Filesystem\Filesystem;
use Image;
use File;

class FileHelper
{
    public static function upload($image, $path, $disk): array
    {
//        $width = ($type == 'images') ? env('IMAGE_SIZE_WIDTH') : env('ICON_SIZE_WIDTH');
//        $height = ($type == 'images') ? env('IMAGE_SIZE_WIDTH') : env('ICON_SIZE_WIDTH');
        //$imgName = pathinfo($image->getClientOriginalName(), PATHINFO_FILENAME) . '.' . $image->getClientOriginalExtension();

        if(!File::exists($path)) {
            File::makeDirectory($path);
        }

//        $files = new Filesystem;
//        $files->cleanDirectory($path);
//
//        $uploadImage = Image::make($image)->encode(env('IMAGE_EXTENSION'))->resize($width, $height, function ($constraint) {
//            $constraint->aspectRatio();
//            $constraint->upsize();
//        });

        $passportFile = $image->store(
            $path, $disk
        );
        //$uploadImage->save($path . '/' . $imgName);

        return [
            'status' => true,
            'filename' => $passportFile
        ];
    }

}
