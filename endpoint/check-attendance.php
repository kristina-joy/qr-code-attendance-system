<?php
include('../conn/conn.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $eventId = $_POST['event_id'] ?? null;
    $qrCode  = $_POST['qr_code'] ?? null;

    if (!$eventId || !$qrCode) {
        echo json_encode(['status' => 'error', 'message' => 'Missing event or QR code']);
        exit;
    }

    try {
        // Look up student using QR Code
        $stmt = $conn->prepare("SELECT tbl_student_id FROM tbl_student WHERE generated_code = :qrCode");
        $stmt->bindParam(':qrCode', $qrCode, PDO::PARAM_STR);
        $stmt->execute();
        $student = $stmt->fetch();

        if (!$student) {
            echo json_encode(['status' => 'notfound']);
            exit;
        }

        $studentId = $student['tbl_student_id'];

        // Check if already attended
        $check = $conn->prepare("SELECT * FROM tbl_attendance WHERE event_id = :eventId AND tbl_student_id = :studentId");
        $check->bindParam(':eventId', $eventId, PDO::PARAM_INT);
        $check->bindParam(':studentId', $studentId, PDO::PARAM_INT);
        $check->execute();
        $exists = $check->fetch();

        if ($exists) {
            echo json_encode(['status' => 'exists']);
        } else {
            echo json_encode(['status' => 'ok']);
        }

    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }

} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
}

