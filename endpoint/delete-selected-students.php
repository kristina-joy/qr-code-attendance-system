<?php
session_start();
include __DIR__ . "/../conn/conn.php"; // Database connection

// Enable error reporting for debugging (remove in production)
ini_set('display_errors', 1);
error_reporting(E_ALL);

$message = '';

try {
    // BULK DELETE: 'students' parameter should be a comma-separated list of IDs
    if(isset($_GET['students'])) {
        $ids = explode(",", $_GET['students']);
        $ids = array_map('intval', $ids); // ensure integers

        if(count($ids) > 0){
            $placeholders = implode(',', array_fill(0, count($ids), '?'));
            $stmt = $conn->prepare("DELETE FROM tbl_student WHERE tbl_student_id IN ($placeholders)");

            if($stmt->execute($ids)){
                $message = count($ids) . " student(s) deleted successfully!";
            } else {
                $errorInfo = $stmt->errorInfo();
                $message = "Error deleting students: " . $errorInfo[2];
            }
        } else {
            $message = "No students selected.";
        }
    } else {
        $message = "No students selected.";
    }
} catch (PDOException $e){
    $message = "Error deleting: " . $e->getMessage();
}

// Redirect back to masterlist.php with a message
header("Location: ../masterlist.php?success=" . urlencode($message));
exit;
?>
