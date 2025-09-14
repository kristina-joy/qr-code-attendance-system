<?php
include("../conn/conn.php");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['student_name'], $_POST['course'], $_POST['year'])) {
        $studentName = trim($_POST['student_name']);
        $studentCourse = trim($_POST['course']);
        $studentYear = trim($_POST['year']);
        $generatedCode = trim($_POST['generated_code']);

        // Auto-generate QR code if empty
        if (empty($generatedCode)) {
            $generatedCode = uniqid("QR_", true); // unique code
        }

        try {
            $stmt = $conn->prepare("
                INSERT INTO tbl_student (student_name, course, year, generated_code) 
                VALUES (:student_name, :course, :year, :generated_code)
            ");
            $stmt->bindParam(":student_name", $studentName, PDO::PARAM_STR); 
            $stmt->bindParam(":course", $studentCourse, PDO::PARAM_STR);
            $stmt->bindParam(":year", $studentYear, PDO::PARAM_STR);
            $stmt->bindParam(":generated_code", $generatedCode, PDO::PARAM_STR);

            $stmt->execute();

            header("Location: ../masterlist.php?success=Student added successfully");
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