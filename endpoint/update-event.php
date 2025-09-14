<?php
include('../conn/conn.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['event_id'];
    $name = $_POST['event_name'];
    $date = $_POST['event_date'];
    $desc = $_POST['event_desc'];

    $stmt = $conn->prepare("UPDATE tbl_events 
                            SET event_name = :name, event_date = :date, event_desc = :desc, updated_at = NOW() 
                            WHERE event_id = :id");
    $stmt->execute([
        ':name' => $name,
        ':date' => $date,
        ':desc' => $desc,
        ':id' => $id
    ]);

    header('Location: ../events.php');
    exit;
}
?>
