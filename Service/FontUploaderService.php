<?php

class FontUploaderService
{
    private $fileValidator;
    private $fileUploader;
    private $fontRepository;

    public function __construct(FileValidator $fileValidator, FileUploader $fileUploader, FontRepository $fontRepository)
    {
        $this->fileValidator = $fileValidator;
        $this->fileUploader = $fileUploader;
        $this->fontRepository = $fontRepository;
    }

    public function uploadFont($file)
    {
        // Validate file
        $this->fileValidator->validate($file);

        $fileName = basename($file['name']);
        $targetPath = $this->fileUploader->upload($file);

        // Check if the font already exists
        if ($this->fontRepository->isFontExists($fileName, $targetPath)) {
            return [
                'status' => 'error',
                'message' => 'Font already uploaded'
            ];
        }

        // Save font to the database
        $fontId = $this->fontRepository->saveFont($fileName, $targetPath);

        return [
            'status' => 'success',
            'message' => 'Font uploaded successfully',
            'filePath' => $targetPath,
            'fontId' => $fontId
        ];
    }
}
