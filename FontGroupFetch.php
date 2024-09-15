<?php

require_once 'include/DatabaseConnection.php';
interface FontGroupFetchInterface
{
    public function fetchGroupFonts(): array;
}


class FontGroupFetch
{
    private $db;

    public function __construct(DatabaseConnection $databaseConnection)
    {
        // Database connection
        $this->db = $databaseConnection->getConnection();
    }

    public function fetchGroupFonts()
    {
        try {
            // SQL query to fetch font groups with font count, names, and ids
            $stmt = $this->db->query("
                SELECT 
                    font_groups.id, 
                    font_groups.name, 
                    (SELECT COUNT(fgm.font_id) 
                     FROM font_group_mapping fgm
                     WHERE fgm.group_id = font_groups.id
                    ) AS font_count,
                    (SELECT GROUP_CONCAT(f.name SEPARATOR ', ') 
                     FROM font_group_mapping fgm
                     JOIN fonts f ON fgm.font_id = f.id
                     WHERE fgm.group_id = font_groups.id
                    ) AS font_names,
                    (SELECT GROUP_CONCAT(f.id SEPARATOR ', ') 
                     FROM font_group_mapping fgm
                     JOIN fonts f ON fgm.font_id = f.id
                     WHERE fgm.group_id = font_groups.id
                    ) AS font_ids
                FROM 
                    font_groups
            ");

            // Fetch all results as an associative array
            $fonts = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Process font_ids to convert them from a comma-separated string to an array
            foreach ($fonts as &$fontGroup) {
                // Check if font_ids exist, then explode the string into an array
                if (isset($fontGroup['font_ids']) && !empty($fontGroup['font_ids'])) {
                    $fontGroup['font_ids'] = array_map('intval', explode(', ', $fontGroup['font_ids']));
                } else {
                    $fontGroup['font_ids'] = []; // Set as an empty array if no font_ids exist
                }
            }

            // Return the formatted response
            return [
                'status' => 'success',
                'message' => 'Group Fonts fetched successfully',
                'fonts' => $fonts
            ];
        } catch (PDOException $e) {
            // Handle any potential database errors
            return [
                'status' => 'error',
                'message' => 'Failed to fetch Group Fonts: ' . $e->getMessage(),
                'fonts' => []
            ];
        }
    }
}

$databaseConnection = new DatabaseConnection('localhost', 'font_manager', 'root', '');
$fontGroupFetch = new FontGroupFetch($databaseConnection);


if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    header('Content-Type: application/json');
    echo json_encode($fontGroupFetch->fetchGroupFonts());
}
