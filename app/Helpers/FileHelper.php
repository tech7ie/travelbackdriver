<?php

namespace App\Helpers;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Storage;
use Image;
use File;

class FileHelper
{
    public static function upload($file, $path, $disk, $width = null, $height = null, $extension = null, $filename = null): array
    {
        $extension = $extension ?? $file->getClientOriginalExtension();
        $filename = empty($filename) ? pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME) : $filename;
        $file = Image::make($file);
        $width = $width ?? $file->width();
        $height = $height ?? $file->height();

        $file = $file->fit($width, $height)->encode($extension);
        Storage::disk($disk)->put($path . '/' . $filename . '.' . $extension, $file);

        return [
            'status' => true,
            'filename' => $filename . '.' . $extension
        ];
    }

    public static function remove($path)
    {
        if(File::exists($path)) {
            File::deleteDirectory($path);
        }
    }

//    public static function resize($file, $x, $y, $extension): array
//    {
//        $originName = $file->getClientOriginalName();
//        $file = Image::make($file)->crop($x, $y)->encode($extension);
//
//        return [
//            'file' => $file,
//            'filename' => pathinfo($originName, PATHINFO_FILENAME) . '.' . $extension
//        ];
//    }
}
