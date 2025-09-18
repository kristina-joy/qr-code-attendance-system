<?php
// endpoint/qr-debug.php
ini_set('display_errors', 1);
error_reporting(E_ALL);

$eventId = $_POST['event_id'] ?? null;
$qrData  = $_POST['qr_data'] ?? '(none)';

echo "<h2>Debug QR Scan</h2>";
echo "<p><b>Event ID:</b> " . htmlspecialchars($eventId) . "</p>";
echo "<p><b>Raw QR Data:</b> <pre>" . htmlspecialchars($qrData) . "</pre></p>";

echo "<hr><h3>Now checking DB...</h3>";

include('../conn/conn.php');

// Exact match on tbl_student_id if numeric
if (ctype_digit($qrData)) {
    $stmt = $conn->prepare("SELECT * FROM tbl_student WHERE tbl_student_id = :id");
    $stmt->bindValue(':id', (int)$qrData, PDO::PARAM_INT);
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "<h4>Lookup by tbl_student_id = {$qrData}</h4><pre>" . print_r($row, true) . "</pre>";
}

// Exact match on generated_code
$stmt2 = $conn->prepare("SELECT * FROM tbl_student WHERE generated_code = :gc");
$stmt2->bindValue(':gc', $qrData, PDO::PARAM_STR);
$stmt2->execute();
$row2 = $stmt2->fetch(PDO::FETCH_ASSOC);
echo "<h4>Lookup by generated_code = '{$qrData}'</h4><pre>" . print_r($row2, true) . "</pre>";
