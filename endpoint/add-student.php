<?php
include("../conn/conn.php");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if(!empty($_POST['student_name']) && !empty($_POST['course']) && !empty($_POST['year'])){
        $studentName = trim($_POST['student_name']);
        $studentCourse = trim($_POST['course']);
        $studentYear = trim($_POST['year']);
        $generatedCode = !empty($_POST['generated_code']) ? trim($_POST['generated_code']) : bin2hex(random_bytes(5));

        try {
            // Check for duplicate
            $check = $conn->prepare("SELECT COUNT(*) FROM tbl_student WHERE student_name = :name AND course = :course AND year = :year");
            $check->bindParam(':name', $studentName, PDO::PARAM_STR);
            $check->bindParam(':course', $studentCourse, PDO::PARAM_STR);
            $check->bindParam(':year', $studentYear, PDO::PARAM_INT);
            $check->execute();
            $count = $check->fetchColumn();

            if ($count > 0) {
                echo "<script>
                        alert('Student already exists in the list!');
                        window.location.href='../masterlist.php';
                      </script>";
                exit();
            }

            // Insert new student
            $stmt = $conn->prepare("
                INSERT INTO tbl_student (student_name, course, year, generated_code) 
                VALUES (:student_name, :course, :year, :generated_code)
            ");

            $stmt->bindParam(":student_name", $studentName, PDO::PARAM_STR); 
            $stmt->bindParam(":course", $studentCourse, PDO::PARAM_STR);
            $stmt->bindParam(":year", $studentYear, PDO::PARAM_INT);
            $stmt->bindParam(":generated_code", $generatedCode, PDO::PARAM_STR);

            $stmt->execute();
            header("Location: ../masterlist.php");
            exit();
        } catch(PDOException $e) {
            echo "Database error: " . htmlspecialchars($e->getMessage());
        }
    } else {
        echo "<script>alert('Please fill in all fields'); window.location.href='../masterlist.php';</script>";
    }
} else {
    die("Invalid request method.");
}
?>
