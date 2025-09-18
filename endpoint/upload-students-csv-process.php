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

            // CSV format: student_name, course, year
            $studentName = trim($row[0] ?? '');
            $course      = trim($row[1] ?? '');
            $year        = trim($row[2] ?? '');

            if ($studentName && $course && $year) {

                // Check for existing student (avoid duplicates)
                $stmtCheck = $conn->prepare("
                    SELECT tbl_student_id 
                    FROM tbl_student 
                    WHERE student_name = :student_name 
                      AND course = :course 
                      AND year = :year
                ");
                $stmtCheck->bindParam(":student_name", $studentName, PDO::PARAM_STR);
                $stmtCheck->bindParam(":course", $course, PDO::PARAM_STR);
                $stmtCheck->bindParam(":year", $year, PDO::PARAM_INT);
                $stmtCheck->execute();

                if ($stmtCheck->rowCount() == 0) {
                    // Insert new student with auto-generated QR code
                    $generatedCode = bin2hex(random_bytes(5)); // 10-character code
                    $stmtInsert = $conn->prepare("
                        INSERT INTO tbl_student (student_name, course, year, generated_code)
                        VALUES (:student_name, :course, :year, :generated_code)
                    ");
                    $stmtInsert->bindParam(":student_name", $studentName, PDO::PARAM_STR);
                    $stmtInsert->bindParam(":course", $course, PDO::PARAM_STR);
                    $stmtInsert->bindParam(":year", $year, PDO::PARAM_INT);
                    $stmtInsert->bindParam(":generated_code", $generatedCode, PDO::PARAM_STR);
                    $stmtInsert->execute();
                    $addedCount++;
                }
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


