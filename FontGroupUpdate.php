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

// Handle the POST request for font group update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $input = json_decode(file_get_contents('php://input'), true);
        $groupId = $input['groupId'];
        $groupName = $input['groupName'];
        $fonts = $input['groupFonts'];

        $updateResponse = $fontGroupService->updateFontGroup($groupId, $groupName, $fonts);

        echo json_encode($updateResponse);
    } catch (Exception $e) {
        echo json_encode([
            'status' => 'error',
            'message' => $e->getMessage(),
        ]);
    }
}
