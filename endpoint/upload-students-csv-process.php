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

            $name = $row[0] ?? '';
            $course = $row[1] ?? '';
            $qrCode = $row[2] ?? uniqid('QR_', true); // use provided QR or generate

            if ($name && $course) {
                $stmt = $conn->prepare("INSERT INTO tbl_student (student_name, course_section, generated_code) VALUES (:name, :course, :qr)");
                $stmt->bindParam(":name", $name);
                $stmt->bindParam(":course", $course);
                $stmt->bindParam(":qr", $qrCode);
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

