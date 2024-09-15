<?php

class FileUploader
{
    private $uploadDirectory;

    public function __construct($uploadDirectory = 'uploads/')
    {
        $this->uploadDirectory = $uploadDirectory;
    }

    public function upload($file)
    {
        $storageFileName = str_replace(' ', '_', $file['name']);
        $targetPath = $this->uploadDirectory . $storageFileName;

        // Check if uploads folder exists, if not create it
        if (!is_dir($this->uploadDirectory)) {
            mkdir($this->uploadDirectory, 0777, true);
        }

        if (move_uploaded_file($file['tmp_name'], $targetPath)) {
            return $targetPath;
        } else {
            throw new Exception("Error uploading file.");
        }
    }
}
