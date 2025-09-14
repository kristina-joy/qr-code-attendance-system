<?php
include('../conn/conn.php');

if (isset($_GET['attendance'])) {
    $attendance = (int)$_GET['attendance']; // cast to int for safety
    $eventId = (int)($_GET['event_id'] ?? 0); // optional: pass event_id for redirect

    try {
        $stmt = $conn->prepare("DELETE FROM tbl_attendance WHERE tbl_attendance_id = :id");
        $stmt->bindParam(':id', $attendance, PDO::PARAM_INT);
        $success = $stmt->execute();

        if ($success) {
            echo "<script>
                alert('Attendance deleted successfully!');
                window.location.href = 'http://localhost/qr-code-attendance-system/index.php?event_id={$eventId}';
            </script>";
        } else {
            echo "<script>
                alert('Failed to delete attendance!');
                window.location.href = 'http://localhost/qr-code-attendance-system/index.php?event_id={$eventId}';
            </script>";
        }

    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
}
?>
