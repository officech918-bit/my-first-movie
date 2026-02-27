<?php
require_once __DIR__ . '/inc/requires.php';

try {
    $database = new MySQLDB();
    $db = $database->db;

    // IDs of the rows to delete
    $ids_to_delete = [31, 32];

    if (!empty($ids_to_delete)) {
        $placeholders = implode(',', array_fill(0, count($ids_to_delete), '?'));
        $stmt = $db->prepare("DELETE FROM behind_the_scenes WHERE id IN ($placeholders)");
        
        if ($stmt) {
            $stmt->execute($ids_to_delete);
            $affected_rows = $stmt->rowCount();
            echo "Successfully deleted $affected_rows row(s) from the 'behind_the_scenes' table.<br>";
        } else {
            echo "Error preparing the delete statement.<br>";
        }
    } else {
        echo "No IDs provided for deletion.<br>";
    }

    echo "Database cleanup complete.";

} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
} catch (Exception $e) {
    die("An error occurred: " . $e->getMessage());
}
?>