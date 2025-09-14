<?php
include('../conn/conn.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $eventId = $_POST['event_id'] ?? null;
    $qrCode  = $_POST['generated_code'] ?? null;

    if (!$eventId || !$qrCode) {
        die("Missing event_id or qr_code.");
    }

    try {
        // Look up student using QR Code
        $stmt = $conn->prepare("
            SELECT tbl_student_id 
            FROM tbl_student 
            WHERE generated_code = :qrCode
        ");
        $stmt->bindParam(':qrCode', $qrCode, PDO::PARAM_STR);
        $stmt->execute();
        $student = $stmt->fetch();

        if (!$student) {
            die("Student not found for this QR Code.");
        }

        $studentId = $student['tbl_student_id'];

        // Check if already attended
        $check = $conn->prepare("
            SELECT * 
            FROM tbl_attendance 
            WHERE event_id = :eventId AND tbl_student_id = :studentId
        ");
        $check->bindParam(':eventId', $eventId, PDO::PARAM_INT);
        $check->bindParam(':studentId', $studentId, PDO::PARAM_INT);
        $check->execute();
        $exists = $check->fetch();

        if ($exists) {
                        echo "<script>
                alert('Duplicate! Attendance already added!');
                window.location.href = 'http://localhost/qr-code-attendance-system/index.php?event_id={$eventId}';
            </script>";
    
            exit;
        }

        // Insert attendance
        $insert = $conn->prepare("
            INSERT INTO tbl_attendance (event_id, tbl_student_id, time_in) 
            VALUES (:eventId, :studentId, NOW())
        ");
        $insert->bindParam(':eventId', $eventId, PDO::PARAM_INT);
        $insert->bindParam(':studentId', $studentId, PDO::PARAM_INT);
        $insert->execute();

        header("Location: ../index.php?event_id=" . urlencode($eventId));
        exit;

    } catch (PDOException $e) {
        die("Database error: " . $e->getMessage());
    }
} else {
    die("Invalid request method.");
}


