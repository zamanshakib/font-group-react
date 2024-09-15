<?php
// Require necessary files
require_once 'include/DatabaseConnection.php';
require_once 'include/FileValidator.php';
require_once 'include/FileUploader.php';
require_once 'Repository/FontRepository.php';
require_once 'Service/FontUploaderService.php';


// Initialize required classes
$databaseConnection = new DatabaseConnection('localhost', 'font_manager', 'root', '');
$db = $databaseConnection->getConnection();



$fileValidator = new FileValidator();
$fileUploader = new FileUploader('uploads/');
$fontRepository = new FontRepository($db);
$fontUploaderService = new FontUploaderService($fileValidator, $fileUploader, $fontRepository);


// Handle file upload request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['font'])) {
    try {
        if (!isset($_FILES['font']['name'])) {
            throw new Exception('File was not uploaded correctly.');
        }

        $response = $fontUploaderService->uploadFont($_FILES['font']);
        echo json_encode($response);
    } catch (Exception $e) {
        echo json_encode([
            'status' => 'error',
            'message' => $e->getMessage()
        ]);
    }
} else {
    echo json_encode([
        'status' => 'error',
        'message' => 'No file uploaded'
    ]);
}
