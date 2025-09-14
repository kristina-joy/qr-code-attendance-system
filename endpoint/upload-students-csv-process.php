<?php
include("../conn/conn.php");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_FILES['csvfile']) && $_FILES['csvfile']['error'] === 0) {

        $fileTmp = $_FILES['csvfile']['tmp_name'];
        $file = fopen($fileTmp, 'r');

        $headerSkipped = false;
        $addedCount = 0;

        while (($row = fgetcsv($file, 1000, ",")) !== FALSE) {
            if (!$headerSkipped) { $headerSkipped = true; continue; }

            $studentName = $row[0] ?? '';
            $course = $row[1] ?? '';
            $year = $row[2] ?? '';
            $generatedCode = $row[3] ?? uniqid('QR_', true); // use provided QR or generate

            if ($studentName && $course) {
                $stmt = $conn->prepare(
                    "INSERT INTO tbl_student (student_name, course, year, generated_code) 
                     VALUES (:student_name, :course, :year, :generated_code)"
                );
                $stmt->bindParam(":student_name", $studentName, PDO::PARAM_STR);
                $stmt->bindParam(":course", $course, PDO::PARAM_STR);
                $stmt->bindParam(":year", $year, PDO::PARAM_STR);
                $stmt->bindParam(":generated_code", $generatedCode, PDO::PARAM_STR);
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

