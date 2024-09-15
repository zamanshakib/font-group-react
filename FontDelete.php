<?php

require_once 'include/DatabaseConnection.php';
interface FontDeleteInterface
{
    public function delete(): int;
}
class FontDelete
{
    private $db;

    public function __construct(DatabaseConnection $databaseConnection)
    {
        $this->db = $databaseConnection->getConnection();
    }

    public function delete($fontID)
    {
        try {
            // Start a transaction to ensure both deletions are processed together
            $this->db->beginTransaction();
            // Fetch the font details
            $stmt = $this->db->prepare("SELECT * FROM fonts WHERE id = :id");
            $stmt->execute([':id' => $fontID]);
            $font = $stmt->fetch();

            if (!$font) {
                throw new Exception('Font not found in the database.');
            }

            $fontPath = $font['path'];

            // Delete the font file from the file system
            if (file_exists($fontPath)) {
                if (!unlink($fontPath)) {
                    throw new Exception('Error deleting font file from the file system.');
                }
            } else {
                throw new Exception('Font file not found on the server.');
            }

            // Delete the font record from the database
            $stmt = $this->db->prepare("DELETE FROM fonts WHERE id = :id");
            $stmt->execute([':id' => $fontID]);
            $fontDeleted = $stmt->rowCount();

            // Delete related font form group mappings
            $gstmt = $this->db->prepare("DELETE FROM font_group_mapping WHERE font_id = :font_id");
            $gstmt->execute([':font_id' => $fontID]);
            $mappingDeleted = $gstmt->rowCount(); // Check if mappings were deleted

            if ($fontDeleted > 0 || $mappingDeleted > 0) {
                $this->db->commit();
                return ['status' => 'success', 'message' => 'Font deleted successfully'];
            } else {
                throw new Exception('Error deleting font from the database.');
            }
        } catch (Exception $e) {
            // Rollback on any error
            $this->db->rollBack();
            throw new Exception('Error deleting font group: ' . $e->getMessage());
        }
    }
}

$databaseConnection = new DatabaseConnection('localhost', 'font_manager', 'root', '');
$fontDelete = new FontDelete($databaseConnection);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the input data
    $input = json_decode(file_get_contents('php://input'), true);
    $fontId = $input['id'];

    if ($fontId) {
        try {
            // Call the delete method
            $response = $fontDelete->delete($fontId);
            echo json_encode(['status' => 'success', 'message' => 'Font deleted successfully', 'response' => $response]);
        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Invalid font ID']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
}
