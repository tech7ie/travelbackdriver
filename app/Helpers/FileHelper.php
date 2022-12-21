<?php

namespace App\Helpers;
use Illuminate\Filesystem\Filesystem;
use Image;
use File;

class FileHelper
{
    public static function upload($file, $path, $disk)
    {
        if(!File::exists($path)) {
            File::makeDirectory($path);
        }

        $file->storeAs(
            $path, $file->getClientOriginalName(), $disk
        );

        return [
            'status' => true,
            'filename' => $file->getClientOriginalName()
        ];
    }

    public static function remove($path)
    {
        if(File::exists($path)) {
            File::deleteDirectory($path);
        }
    }

}
