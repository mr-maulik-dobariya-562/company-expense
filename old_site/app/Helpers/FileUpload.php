<?php

namespace App\Helpers;

use Aws\Exception\AwsException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Laravel\Facades\Image;

class FileUpload
{

    /**
     * Handles the uploading of files.
     * @param UploadedFile $file The file to upload.
     * @param string $folder The folder to upload the file into, defaults to 'uploads'.
     * @return string|null The path to the uploaded file or null if the upload failed.
     */
    public static function upload(UploadedFile $file, $subfolder, $disk = "public")
    {
        try {
            if (!$file->isValid()) {
                return null;
            }

            $folder    = $subfolder;
            $extension = $file->getClientOriginalExtension();
            $oneTime   = time();
            $UUID      = uniqid("IMG-");

            $originalFilename = $UUID . $oneTime . '.' . $extension;
            // $filename_200     = $UUID . $oneTime . '_200' . '.' . $extension;
            // $filename_400     = $UUID . $oneTime . '_400' . '.' . $extension;
            // $filename_800     = $UUID . $oneTime . '_800' . '.' . $extension;


            $originalFilePath = $folder . '/' . $originalFilename;
            // $Filepath_200     = $folder . '/' . $filename_200;
            // $Filepath_400     = $folder . '/' . $filename_400;
            // $Filepath_800     = $folder . '/' . $filename_800;

            $ImageInstance = Image::read($file);

            // echo "<pre>";
            // print_r($ImageInstance);

            // $small  = $ImageInstance->resize(200, 200)->encodeByExtension($extension);
            // $medium = $ImageInstance->resize(400, 400)->encodeByExtension($extension);
            // $large  = $ImageInstance->resize(800, 800)->encodeByExtension($extension);

            $storage = Storage::disk(app("storage") ?? $disk);

            // $storage->put($Filepath_200, (string) $small, 'public');
            // $storage->put($Filepath_400, (string) $medium, 'public');
            // $storage->put($Filepath_800, (string) $large, 'public');


            // Resize images and save them
            // self::resizeAndSave($file, $Filepath_200, 200, $extension, $disk);
            // self::resizeAndSave($file, $Filepath_400, 400, $extension, $disk);
            // self::resizeAndSave($file, $Filepath_800, 800, $extension, $disk);

            if ($storage->put($originalFilePath, file_get_contents($file), 'public')) {
                return $originalFilename;
            }
            return null;
        } catch (\Exception | AwsException $e) {
            Log::error(" Error uploading file: FileUpload::upload() \n" . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Generates the URL for a file in a specified folder.
     *
     * @param string $fileName The name of the file.
     * @param string $folderName The name of the folder.
     * @param string $disk The storage disk to use. Defaults to "public".
     * @return string|null The URL of the file, or null if the file does not exist.
     */
    public static function url($fileName, $folderName, $size = null)
    {
        $filePath = $folderName . '/' . static::getImageByDimension($fileName, $size);

        $defaultDisk =   app("storage");


        /* Comment this if the your 'aws or digitalocean' is not publicly accessible */

        if ($defaultDisk == "s3") {

            return env("AWS_URL") . $filePath;
        } elseif ($defaultDisk == "digitalocean") {

            return env("DO_URL") . $filePath;
        }

        $storage = Storage::disk($defaultDisk);

        if ($storage->exists($filePath)) {

            return $storage->url($filePath);
        } else {

            return null;
        }
    }

    public static function delete($fileName, $folderName, $disk = "public")
    {
        $filePath = $folderName . '/' . $fileName;
        $storage  = Storage::disk($disk);
        if ($storage->exists($filePath))
            return $storage->delete($filePath);

        return false;
    }

    public static function getImageByDimension($fileName, $size = null): string
    {
        $fileParts = explode('.', $fileName);
        return ($fileParts[0]   . "." . end($fileParts));
    }

    private static function resizeAndSave($file, $savePath, $newWidth, $extension, $disk)
    {
        list($originalWidth, $originalHeight) = getimagesize($file->getRealPath());
        $aspectRatio                            = $originalWidth / $originalHeight;

        if ($newWidth / $newWidth > $aspectRatio) {
            $newHeight = $newWidth / $aspectRatio;
        } else {
            $newHeight = $newWidth;
            $newWidth  = $newHeight * $aspectRatio;
        }

        $newImage = imagecreatetruecolor($newWidth, $newHeight);

        switch (strtolower($extension)) {
            case 'jpg':
            case 'jpeg':
                $sourceImage = imagecreatefromjpeg($file->getRealPath());
                break;
            case 'png':
                $sourceImage = imagecreatefrompng($file->getRealPath());
                break;
            case 'gif':
                $sourceImage = imagecreatefromgif($file->getRealPath());
                break;
            default:
                throw new \Exception("Unsupported image type: $extension");
        }

        imagecopyresampled($newImage, $sourceImage, 0, 0, 0, 0, $newWidth, $newHeight, $originalWidth, $originalHeight);

        ob_start();
        switch (strtolower($extension)) {
            case 'jpg':
            case 'jpeg':
                imagejpeg($newImage);
                break;
            case 'png':
                imagepng($newImage);
                break;
            case 'gif':
                imagegif($newImage);
                break;
        }
        $imageData = ob_get_clean();

        Log::info($imageData);

        Storage::disk($disk)->put($savePath, $imageData, 'public');

        imagedestroy($sourceImage);
        imagedestroy($newImage);
    }


    public static function uploadBase64Image($imageBase64, $folderName = null)
    {
        if ($imageBase64) {
            // Check if the base64 string is in correct format
            if (preg_match('/^data:image\/(\w+);base64,/', $imageBase64, $type)) {

                $imageBase64 = substr($imageBase64, strpos($imageBase64, ',') + 1);
                $fileType = strtolower($type[1]); // jpg, png, gif

                // Validate file type
                if (!in_array($fileType, ['jpeg', 'jpg', 'png', 'gif'])) {
                    return null;
                }

                // Decode the base64 string
                $imageBinary = base64_decode($imageBase64);

                // Create a unique filename
                $filename = uniqid() . '.' . $fileType;

                // Define folder path
                $folderPath = $folderName;

                // Save the image
                Storage::put($folderPath . '/' . $filename, $imageBinary);

                return $filename;
            }
        }
        return null;
    }
}
