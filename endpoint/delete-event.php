<?php
include('../conn/conn.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['event_id'];

    $stmt = $conn->prepare("DELETE FROM tbl_events WHERE event_id = :id");
    $stmt->execute([':id' => $id]);

    header('Location: ../events.php');
    exit;
}
?>
