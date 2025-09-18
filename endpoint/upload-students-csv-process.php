<?php
include("../conn/conn.php");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_FILES['csvfile']) && $_FILES['csvfile']['error'] === 0) {

        $fileTmp = $_FILES['csvfile']['tmp_name'];
        $file = fopen($fileTmp, 'r');

        $headerSkipped = false;
        $addedCount = 0;

        while (($row = fgetcsv($file, 1000, ",")) !== FALSE) {
            if (!$headerSkipped) { 
                $headerSkipped = true; 
                continue; 
            }

            // CSV format: student_id,student_name,course,year
            $studentId   = trim($row[0] ?? '');
            $studentName = trim($row[1] ?? '');
            $course      = trim($row[2] ?? '');
            $year        = trim($row[3] ?? '');

            if ($studentId && $studentName && $course && $year) {
                $stmt = $conn->prepare("
                    INSERT INTO tbl_student (tbl_student_id, student_name, course, year)
                    VALUES (:student_id, :student_name, :course, :year)
                    ON DUPLICATE KEY UPDATE 
                        student_name = VALUES(student_name),
                        course = VALUES(course),
                        year = VALUES(year)
                ");
                $stmt->bindParam(":student_id", $studentId, PDO::PARAM_STR); // <- student ID as string
                $stmt->bindParam(":student_name", $studentName, PDO::PARAM_STR);
                $stmt->bindParam(":course", $course, PDO::PARAM_STR);
                $stmt->bindParam(":year", $year, PDO::PARAM_STR);
                $stmt->execute();
                $addedCount++;
            }
        }

        fclose($file);
        header("Location: ../masterlist.php?success=Added $addedCount students from CSV");
        exit();
    } else {
        header("Location: ../masterlist.php?error=No file uploaded or file error");
        exit();
    }
}
?>

