<?php

class FileValidator
{
    private $allowedExtensions = ['ttf'];
    private $maxFileSize = 5000000; // 5MB limit

    public function validate($file)
    {
        $fileExtension = pathinfo($file['name'], PATHINFO_EXTENSION);

        if (!in_array($fileExtension, $this->allowedExtensions)) {
            throw new Exception("Invalid file type. Only .ttf files are allowed.");
        }

        if ($file['size'] > $this->maxFileSize) {
            throw new Exception("File size exceeds limit.");
        }

        return true;
    }
}
