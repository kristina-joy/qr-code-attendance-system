<?php
include('./conn/conn.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['student_name']);
    $course = trim($_POST['course']);
    $year = trim($_POST['year']);
    $studentID = trim($_POST['student_id']); // ✅ Added to capture Student ID

    // Generate random QR code value
    $qrCode = bin2hex(random_bytes(5)); 

    // ✅ Added student_id column in query
    $stmt = $conn->prepare("INSERT INTO tbl_student (student_id, student_name, course, year, generated_code) 
                            VALUES (:student_id, :name, :course, :year, :code)");
    
    // ✅ Bound the Student ID parameter properly
    $stmt->bindParam(':student_id', $studentID);
    $stmt->bindParam(':name', $name);
    $stmt->bindParam(':course', $course);
    $stmt->bindParam(':year', $year);
    $stmt->bindParam(':code', $qrCode);
    
    $stmt->execute();

    echo "<h3>Registration successful!</h3>";
    echo "<p>Your QR code is:</p>";
    echo "<img src='https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=$qrCode'>";
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Student Registration</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
</head>
<body class="bg-light">
<div class="container mt-5">
  <div class="card p-4 shadow">
    <h3 class="mb-4 text-center">Student Registration</h3>
    <form method="POST">
      <div class="form-group">
        <label>Name</label>
        <input type="text" name="student_name" class="form-control" required>
      </div>
      <div class="form-group">
        <label>Course</label>
        <input type="text" name="course" class="form-control" required>
      </div>
      <div class="form-group">
        <label>Year</label>
        <input type="text" name="year" class="form-control" required>
      </div>
      <div class="form-group">
        <label>Student ID</label>
        <input type="text" name="student_id" class="form-control" placeholder="e.g. 2025-001" required>
      </div>

      <button type="submit" class="btn btn-dark btn-block">Register</button>
    </form>
  </div>
</div>
</body>
</html>

