<?php
include("../conn/conn.php");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['tbl_student_id'], $_POST['student_name'], $_POST['course'], $_POST['year'])) {
        $studentId = trim($_POST['tbl_student_id']);
        $studentName = trim($_POST['student_name']);
        $studentCourse = trim($_POST['course']);
        $studentYear = trim($_POST['year']);
        $$studentIdDVC = trim($_POST['student_id']);

        try {
            $stmt = $conn->prepare("
                UPDATE tbl_student 
                SET student_name = :student_name, course = :course, year = :year 
                WHERE tbl_student_id = :tbl_student_id
            ");
            $stmt->bindParam(":student_name", $studentName, PDO::PARAM_STR);
            $stmt->bindParam(":course", $studentCourse, PDO::PARAM_STR);
            $stmt->bindParam(":year", $studentYear, PDO::PARAM_STR);
            $stmt->bindParam(":tbl_student_id", $studentId, PDO::PARAM_INT);
            $stmt->bindParam(":student_id", $studentIdDVC, PDO::PARAM_INT);

            $stmt->execute();

            header("Location: ../masterlist.php?success=Student updated successfully");
            exit();
        } catch (PDOException $e) {
            echo "<script>alert('Database Error: " . addslashes($e->getMessage()) . "'); window.location.href='../masterlist.php';</script>";
            exit();
        }

    } else {
        echo "<script>alert('Please fill in all required fields!'); window.location.href='../masterlist.php';</script>";
        exit();
    }
} else {
    echo "<script>alert('Invalid request method!'); window.location.href='../masterlist.php';</script>";
    exit();
}
?>
