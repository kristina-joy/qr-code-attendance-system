<?php
session_start();

// Fix include path depending on your folder structure
include('../conn/conn.php'); // Make sure this path is correct

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (!empty($_POST['qr_code']) && !empty($_POST['event_id'])) {

        $qr_code = trim($_POST['qr_code']);
        $event_id = intval($_POST['event_id']); // Make sure this is integer

        try {
            // Check if student exists by generated_code
            $stmt = $conn->prepare("SELECT tbl_student_id, student_name FROM tbl_student WHERE generated_code = :qr_code");
            $stmt->bindParam(':qr_code', $qr_code, PDO::PARAM_STR);
            $stmt->execute();
            $student = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$student) {
                echo "<script>alert('Student not found!'); window.location.href='../index.php?event_id=$event_id';</script>";
                exit();
            }

            $student_id = $student['tbl_student_id'];

            // Check if attendance already exists for this student in this event
            $stmt = $conn->prepare("SELECT COUNT(*) FROM tbl_attendance WHERE tbl_student_id = :student_id AND event_id = :event_id");
            $stmt->bindParam(':student_id', $student_id, PDO::PARAM_INT);
            $stmt->bindParam(':event_id', $event_id, PDO::PARAM_INT);
            $stmt->execute();
            $attendanceExists = $stmt->fetchColumn();

            if ($attendanceExists > 0) {
                echo "<script>alert('Attendance already recorded for this student.'); window.location.href='../index.php?event_id=$event_id';</script>";
                exit();
            }

            // Insert attendance
            $stmt = $conn->prepare("INSERT INTO tbl_attendance (tbl_student_id, event_id, time_in) VALUES (:student_id, :event_id, NOW())");
            $stmt->bindParam(':student_id', $student_id, PDO::PARAM_INT);
            $stmt->bindParam(':event_id', $event_id, PDO::PARAM_INT);
            $stmt->execute();

            $student_name_safe = htmlspecialchars($student['student_name'], ENT_QUOTES, 'UTF-8');
            echo "<script>alert('Attendance recorded successfully for $student_name_safe!'); window.location.href='../index.php?event_id=$event_id';</script>";
            exit();

        } catch (PDOException $e) {
            echo "Database error: " . htmlspecialchars($e->getMessage());
        }

    } else {
        echo "<script>alert('QR code or Event ID missing!'); window.location.href='../events.php';</script>";
        exit();
    }

} else {
    die("Invalid request method.");
}
?>

