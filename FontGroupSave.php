<?php

// Require necessary files
require_once 'include/DatabaseConnection.php';
require_once 'Repository/FontGroupRepository.php';
require_once 'Service/FontGroupService.php';

// Initialize necessary classes
$databaseConnection = new DatabaseConnection('localhost', 'font_manager', 'root', '');
$db = $databaseConnection->getConnection();

$fontGroupRepository = new FontGroupRepository($db);
$fontGroupService = new FontGroupService($fontGroupRepository);

// Handle the POST request for font group creation
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $input = json_decode(file_get_contents('php://input'), true);
        $groupName = $input['groupName'];
        $fonts = $input['groupFonts'];

        $storeResponse = $fontGroupService->saveFontGroup($groupName, $fonts);

        // Prepare a response array
        $response = [
            'status' => 'success',
            'storeResponse' => $storeResponse,
            'fonts' => $fonts,
        ];

        echo json_encode($response);
    } catch (Exception $e) {
        echo json_encode([
            'status' => 'error',
            'message' => $e->getMessage(),
        ]);
    }
}
