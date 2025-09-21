<?php
include("../conn/conn.php");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (!empty($_POST['qr_code']) && !empty($_POST['event_id']) && !empty($_POST['action_type'])) {

        $qrCode = trim($_POST['qr_code']);
        $event_id = intval($_POST['event_id']); 
        $action_type = $_POST['action_type']; 
        date_default_timezone_set('Asia/Manila'); 
        $now = date("Y-m-d H:i:s");


        try {
            $selectStmt = $conn->prepare("SELECT tbl_student_id, student_name FROM tbl_student WHERE generated_code = :generated_code");
            $selectStmt->bindParam(":generated_code", $qrCode, PDO::PARAM_STR);
            $selectStmt->execute();
            $student = $selectStmt->fetch(PDO::FETCH_ASSOC);

            if (!$student) {
                echo "<script>alert('No student found for this QR code'); window.location.href='../index.php?event_id=$event_id';</script>";
                exit();
            }

            $studentID = $student['tbl_student_id'];

            $checkStmt = $conn->prepare("SELECT * FROM tbl_attendance WHERE tbl_student_id = :student_id AND event_id = :event_id");
            $checkStmt->bindParam(":student_id", $studentID, PDO::PARAM_INT);
            $checkStmt->bindParam(":event_id", $event_id, PDO::PARAM_INT);
            $checkStmt->execute();
            $attendance = $checkStmt->fetch(PDO::FETCH_ASSOC);

            if ($attendance) {

                if ($action_type === 'time_in') {
                    $updateStmt = $conn->prepare("UPDATE tbl_attendance SET time_in = :time_in WHERE tbl_attendance_id = :id");
                    $updateStmt->bindParam(":time_in", $now);
                } else {
                    $updateStmt = $conn->prepare("UPDATE tbl_attendance SET time_out = :time_out WHERE tbl_attendance_id = :id");
                    $updateStmt->bindParam(":time_out", $now);
                }
                $updateStmt->bindParam(":id", $attendance['tbl_attendance_id']);
                $updateStmt->execute();
            } else {

                if ($action_type === 'time_in') {
                    $insertStmt = $conn->prepare("INSERT INTO tbl_attendance (tbl_student_id, event_id, time_in) VALUES (:student_id, :event_id, :time_in)");
                    $insertStmt->bindParam(":time_in", $now);
                } else {
                    $insertStmt = $conn->prepare("INSERT INTO tbl_attendance (tbl_student_id, event_id, time_out) VALUES (:student_id, :event_id, :time_out)");
                    $insertStmt->bindParam(":time_out", $now);
                }
                $insertStmt->bindParam(":student_id", $studentID, PDO::PARAM_INT);
                $insertStmt->bindParam(":event_id", $event_id, PDO::PARAM_INT);
                $insertStmt->execute();
            }

            $studentNameSafe = htmlspecialchars($student['student_name'], ENT_QUOTES, 'UTF-8');
            echo "<script>alert('Attendance recorded successfully for $studentNameSafe!'); window.location.href='../index.php?event_id=$event_id';</script>";
            exit();

        } catch (PDOException $e) {
            echo "Database error: " . htmlspecialchars($e->getMessage());
            exit();
        }

    } else {
        echo "<script>alert('QR code, Event ID, or Action missing!'); window.location.href='../events.php';</script>";
        exit();
    }

} else {
    die("Invalid request method.");
}

