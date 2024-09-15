<?php

require_once 'include/DatabaseConnection.php';
interface FontRepositoryInterface
{
    public function fetchFonts(): array;
}
class FontFetch implements FontRepositoryInterface
{
    private $db;

    public function __construct(DatabaseConnection $databaseConnection)
    {
        $this->db = $databaseConnection->getConnection();
    }

    public function fetchFonts(): array
    {
        $stmt = $this->db->query("SELECT * FROM fonts");
        $fonts = $stmt->fetchAll();
        return [
            'status' => 'success',
            'message' => 'Fonts fetched successfully',
            'fonts' => $fonts
        ];
    }
}

$databaseConnection = new DatabaseConnection('localhost', 'font_manager', 'root', '');
$fontFetch = new FontFetch($databaseConnection);

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    header('Content-Type: application/json');
    echo json_encode($fontFetch->fetchFonts());
}
