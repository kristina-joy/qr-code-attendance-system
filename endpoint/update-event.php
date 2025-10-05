<?php
session_start();
include('../conn/conn.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['event_id'];
    $name = $_POST['event_name'];
    $date = $_POST['event_date'];
    $desc = $_POST['event_desc'];
    $time_in = $_POST['time_in'];
    $time_out = $_POST['time_out'];
    $updated_by = $_SESSION['user']; // logged-in user

    // Validate time
    if ($time_out <= $time_in) {
        exit('Invalid time range. Time Out must be later than Time In.');
    }

    $stmt = $conn->prepare("
        UPDATE tbl_events
        SET 
            event_name = :name,
            event_date = :date,
            event_desc = :desc,
            time_in = :time_in,
            time_out = :time_out,
            updated_at = NOW(),
            updated_by = :updated_by
        WHERE event_id = :id
    ");

    $stmt->execute([
        ':name' => $name,
        ':date' => $date,
        ':desc' => $desc,
        ':time_in' => $time_in,
        ':time_out' => $time_out,
        ':updated_by' => $updated_by,
        ':id' => $id
    ]);

    header('Location: ../events.php');
    exit;
}
?>

