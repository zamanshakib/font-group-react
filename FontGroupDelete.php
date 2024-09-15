<?php

require_once 'include/DatabaseConnection.php';
interface FontGroupDeleteInterface
{
    public function delete(): int;
}

class FontGroupDelete
{
    private $db;

    public function __construct(DatabaseConnection $databaseConnection)
    {
        $this->db = $databaseConnection->getConnection();
    }

    public function delete($groupId)
    {
        try {
            // Start a transaction to ensure both deletions are processed together
            $this->db->beginTransaction();

            // Delete the font group
            $stmt = $this->db->prepare("DELETE FROM font_groups WHERE id = :id");
            $stmt->execute([':id' => $groupId]);
            $groupDeleted = $stmt->rowCount(); // Check if the group was deleted

            // Delete related font group mappings
            $gstmt = $this->db->prepare("DELETE FROM font_group_mapping WHERE group_id = :group_id");
            $gstmt->execute([':group_id' => $groupId]);
            $mappingDeleted = $gstmt->rowCount(); // Check if mappings were deleted

            // If either the group or mappings were deleted, commit the transaction
            if ($groupDeleted > 0 || $mappingDeleted > 0) {
                $this->db->commit();
                return [
                    'status' => 'success',
                    'message' => 'Font group deleted successfully',
                ];
            } else {
                // Rollback if nothing was deleted
                $this->db->rollBack();
                return [
                    'status' => 'error',
                    'message' => 'Font group not found or no fonts to delete',
                ];
            }
        } catch (Exception $e) {
            // Rollback on any error
            $this->db->rollBack();
            throw new Exception('Error deleting font group: ' . $e->getMessage());
        }
    }
}
// Instantiate the class with DB credentials
$databaseConnection = new DatabaseConnection('localhost', 'font_manager', 'root', '');

$fontGroupDelete = new FontGroupDelete($databaseConnection);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the input data
    $input = json_decode(file_get_contents('php://input'), true);
    $groupId = $input['id'];

    if ($groupId) {
        try {
            // Call the delete method
            $response = $fontGroupDelete->delete($groupId);
            echo json_encode(['status' => 'success', 'message' => 'Font group deleted successfully', 'response' => $response]);
        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Invalid font group ID']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
}
