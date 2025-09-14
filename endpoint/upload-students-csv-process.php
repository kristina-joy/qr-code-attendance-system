<?php
include("../conn/conn.php");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_FILES['csvfile']) && $_FILES['csvfile']['error'] === 0) {
        $fileTmp = $_FILES['csvfile']['tmp_name'];
        $file = fopen($fileTmp, 'r');

        if (!$file) {
            header("Location: ../masterlist.php?error=Failed to open CSV file");
            exit();
        }

        $headerSkipped = false;
        $addedCount = 0;

        while (($row = fgetcsv($file, 1000, ",")) !== FALSE) {
            // Skip header row
            if (!$headerSkipped) { 
                $headerSkipped = true; 
                continue; 
            }

            $name = trim($row[0] ?? '');
            $course = trim($row[1] ?? '');
            $qrCode = trim($row[2] ?? '');

            // Auto-generate QR code if empty
            if (empty($qrCode)) {
                $qrCode = uniqid('QR_', true);
            }

            if ($name && $course) {
                $stmt = $conn->prepare("INSERT INTO tbl_student (student_name, course_section, generated_code) VALUES (:name, :course, :qr)");
                $stmt->bindParam(':name', $name, PDO::PARAM_STR);
                $stmt->bindParam(':course', $course, PDO::PARAM_STR);
                $stmt->bindParam(':qr', $qrCode, PDO::PARAM_STR);
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
