<?php
include("../conn/conn.php");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['student_name'], $_POST['course_section'])) {
        $studentName = $_POST['student_name'];
        $studentCourse = $_POST['course_section'];

        // Automatically generate QR code for manual add
        $generatedCode = uniqid('QR_', true);

        try {
            $stmt = $conn->prepare("
                INSERT INTO tbl_student (student_name, course_section, generated_code)
                VALUES (:student_name, :course_section, :generated_code)
            ");
            $stmt->bindParam(":student_name", $studentName);
            $stmt->bindParam(":course_section", $studentCourse);
            $stmt->bindParam(":generated_code", $generatedCode);
            $stmt->execute();

            header("Location: ../masterlist.php");
            exit();
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
        }

    } else {
        echo "<script>
                alert('Please fill in all fields!');
                window.location.href = '../masterlist.php';
              </script>";
    }
}
?>


