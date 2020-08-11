<?php

namespace App\Service;

use Aws\S3\S3Client;
use Aws\S3\Exception\S3Exception;

class AwsS3
{
    private string $original_url = "https://lebaneseupload.s3.eu-west-3.amazonaws.com/";
    private string $bucketName = 'lebaneseupload';
    private string $IAM_KEY = 'AKIAUEBXYF3EEF67RGU2';
    private string $IAM_SECRET = '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';

    private S3Client $s3;

    function __construct()
    {
        try {
            //code...
            $this->s3 = new S3Client([
                'credentials' => [
                    'key' => $this->IAM_KEY,
                    'secret' => $this->IAM_SECRET
                ],
                'version' => 'latest',
                'region'  => 'eu-west-3'
            ]);
        } catch (S3Exception $th) {
            dd("on connection" . $th);
        }
    }

    function getResources($page = 0)
    {
        $NUMBER_ITEM_PER_PAGE = 6;
        //page is used for pagination
        $firstIndex = ($page * 5);
        $lastIndex = $firstIndex + $NUMBER_ITEM_PER_PAGE;

        try {
            $result = $this->s3->getIterator('ListObjects', [
                'Bucket' => $this->bucketName,
                'Prefix'    => "uploads/"
            ]);
            $result_files = [];
            foreach ($result as $item) {
                array_push(
                    $result_files,
                    $this->original_url . $item["Key"]
                );
            }

            $result_files_filtered = [];
            for ($i = $firstIndex; $i < $lastIndex; $i++){
                if(isset($result_files[$i])){
                    array_push($result_files_filtered, $result_files[$i]);
                }
            }
            $numberOfPages = count($result_files) / $NUMBER_ITEM_PER_PAGE;
            return [
                "itemInPage"=>$result_files_filtered,
                "totalNumberOfPages" => $numberOfPages
            ];
        } catch (S3Exception $th) {
            dd($th);
        }
    }

    function addOneResource($file)
    {
        $fileName = uniqid() . '-' . $file->getClientOriginalName();
        $key = 'uploads/' . $fileName;
        $file_parts = pathinfo($key);
        $extensions = $file_parts["extension"];

        if (!$this->checkFileType($extensions)) return null;

        try {
            $this->s3->putObject([
                'Bucket' => $this->bucketName,
                'Key' => $key,
                'SourceFile' => $file,
                'StorageClasss' => 'REDUCED_REDUNDANCY'
            ]);

            return $fileName;
        } catch (S3Exception $th) {
            dd("error");
        }
    }

    /**
     * @param string $ext
     * @return bool 
     */
    private function checkFileType($ext)
    {
        $ext = strtolower($ext);
        $file_type = null;
        if (
            $ext == "jpg" ||
            $ext == "png" ||
            $ext == "png" ||
            $ext == "bmp" ||
            $ext == "gif" ||
            $ext == "ico" ||
            $ext == "jpeg" ||
            $ext == "svg" ||
            $ext == "tiff" ||
            $ext == "webp"
        ) {
            $file_type = true;
        } else {
            $file_type = false;
        }

        return $file_type;
    }
}
