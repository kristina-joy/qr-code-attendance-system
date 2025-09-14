<?php
include('./conn/conn.php');

// Handle new event submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['new_event'])) {
    $name = $_POST['event_name'];
    $date = $_POST['event_date'];
    $desc = $_POST['event_desc'];
    $created_by = 'Admin'; // placeholder for user, update later

    $stmt = $conn->prepare("
        INSERT INTO tbl_events (event_name, event_date, event_desc, created_by) 
        VALUES (:name, :date, :desc, :created_by)
    ");
    $stmt->bindParam(':name', $name);
    $stmt->bindParam(':date', $date);
    $stmt->bindParam(':desc', $desc);
    $stmt->bindParam(':created_by', $created_by);
    $stmt->execute();
}

// Fetch events
$stmt = $conn->prepare("SELECT * FROM tbl_events ORDER BY event_date DESC");
$stmt->execute();
$events = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Events - QR Attendance System</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
<style>
@import url('https://fonts.googleapis.com/css2?family=Poppins:wght@500&display=swap');
body { font-family: 'Poppins', sans-serif; background: #f1f1f1; }
.main { padding: 20px; }
.events-container { display: flex; flex-wrap: wrap; gap: 20px; }
.event-card { 
    background: #fff; 
    padding: 20px; 
    border-radius: 10px; 
    box-shadow: 0px 2px 8px rgba(0,0,0,0.2); 
    text-align:center; 
    transition: transform .2s;
    cursor: pointer;
}
.event-card:hover { transform: scale(1.05); }
.add-card {
    border: 2px dashed #999;
    color: #555;
    background: #fafafa;
}
.action-buttons { margin-top: 10px; }
</style>
</head>
<body>

<!-- NAVBAR -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <a class="navbar-brand ml-4" href="#">QR Code Attendance System</a>
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent">
        <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navbarSupportedContent">
        <ul class="navbar-nav mr-auto">
            <li class="nav-item active"><a class="nav-link" href="./events.php">Home</a></li>
            <li class="nav-item"><a class="nav-link" href="./masterlist.php">List of Students<span class="sr-only">(current)</span></a></li>
            <li class="nav-item"><a class="nav-link" href="./reports.php">Reports</a></li>
        </ul>
        <ul class="navbar-nav ml-auto">
            <li class="nav-item mr-3"><a class="nav-link" href="#">Logout</a></li>
        </ul>
    </div>
</nav>