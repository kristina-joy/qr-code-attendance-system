<?php
session_start();
include('./conn/conn.php');

// Redirect if not logged in
if (!isset($_SESSION['user'])) {
    header('Location: index.php');
    exit;
}

// Handle date range filter
$startDate = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-01');
$endDate = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-t');

// Fetch events in range
$eventsStmt = $conn->prepare("
    SELECT * FROM tbl_events 
    WHERE event_date BETWEEN :start_date AND :end_date
    ORDER BY event_date DESC
");
$eventsStmt->bindParam(':start_date', $startDate);
$eventsStmt->bindParam(':end_date', $endDate);
$eventsStmt->execute();
$events = $eventsStmt->fetchAll(PDO::FETCH_ASSOC);

// If event_id is provided, fetch detailed attendance
$attendance = [];
$selectedEvent = null;
if (isset($_GET['event_id'])) {
    $event_id = $_GET['event_id'];

    // Event info
    $eventStmt = $conn->prepare("SELECT * FROM tbl_events WHERE event_id = :event_id LIMIT 1");
    $eventStmt->bindParam(':event_id', $event_id);
    $eventStmt->execute();
    $selectedEvent = $eventStmt->fetch(PDO::FETCH_ASSOC);

    // Attendance info
    $attStmt = $conn->prepare("
        SELECT s.tbl_student_id, s.student_name, a.time_in, a.time_out,
               CASE
                   WHEN a.time_in IS NULL THEN 'Absent'
                   WHEN TIME(a.time_in) > '08:15:00' THEN 'Late'
                   ELSE 'Present'
               END AS status
        FROM tbl_student s
        LEFT JOIN tbl_attendance a 
            ON s.tbl_student_id = a.tbl_student_id AND a.event_id = :event_id
        ORDER BY s.student_name
    ");
    $attStmt->bindParam(':event_id', $event_id);
    $attStmt->execute();
    $attendance = $attStmt->fetchAll(PDO::FETCH_ASSOC);

    // Summary
    $total = count($attendance);
    $present = count(array_filter($attendance, fn($row) => $row['status'] === 'Present' || $row['status'] === 'Late'));
    $absent = $total - $present;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Reports - QR Code Attendance System</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
<style>
body { font-family: 'Poppins', sans-serif; background:#f1f1f1; padding:20px; }
.present { color: green; font-weight: bold; }
.late { color: orange; font-weight: bold; }
.absent { color: red; font-weight: bold; }
</style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <a class="navbar-brand ml-4" href="#">QR Code Attendance System</a>
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent">
        <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarSupportedContent">
        <ul class="navbar-nav mr-auto">
            <li class="nav-item"><a class="nav-link" href="./events.php">Home</a></li>
            <li class="nav-item"><a class="nav-link" href="./masterlist.php">List of Students</a></li>
            <li class="nav-item active"><a class="nav-link" href="./reports.php">Reports</a></li>
        </ul>
        <ul class="navbar-nav ml-auto">
            <li class="nav-item mr-3"><a class="nav-link" href="#">Logout</a></li>
        </ul>
    </div>
</nav>

<div class="container mt-4">
    <h2>Attendance Reports</h2>

    <!-- Date Range Filter -->
    <form method="get" class="form-inline mb-3">
        <label class="mr-2">Start Date:</label>
        <input type="date" name="start_date" class="form-control mr-2" value="<?= htmlspecialchars($startDate) ?>">
        <label class="mr-2">End Date:</label>
        <input type="date" name="end_date" class="form-control mr-2" value="<?= htmlspecialchars($endDate) ?>">
        <button type="submit" class="btn btn-primary">View Events</button>
    </form>

    <!-- Event List -->
    <div class="row">
        <?php if($events): ?>
            <?php foreach($events as $event): ?>
                <div class="col-md-4 mb-3">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title"><?= htmlspecialchars($event['event_name']) ?></h5>
                            <h6 class="card-subtitle mb-2 text-muted"><?= $event['event_date'] ?></h6>
                            <p class="card-text"><?= htmlspecialchars($event['event_desc']) ?></p>
                            <a href="?start_date=<?= $startDate ?>&end_date=<?= $endDate ?>&event_id=<?= $event['event_id'] ?>" class="btn btn-info btn-sm">View Report</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>No events found in this range.</p>
        <?php endif; ?>
    </div>

    <?php if($selectedEvent): ?>
        <hr>
        <h3>Event Report: <?= htmlspecialchars($selectedEvent['event_name']) ?> (<?= $selectedEvent['event_date'] ?>)</h3>
        <p><?= htmlspecialchars($selectedEvent['event_desc']) ?></p>

        <!-- Summary -->
        <div class="row mb-3">
            <div class="col-md-4"><div class="alert alert-info">Total Students: <?= $total ?></div></div>
            <div class="col-md-4"><div class="alert alert-success">Present: <?= $present ?></div></div>
            <div class="col-md-4"><div class="alert alert-danger">Absent: <?= $absent ?></div></div>
        </div>

        <!-- Attendance Table -->
        <table class="table table-bordered table-striped">
            <thead class="thead-dark">
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Time In</th>
                    <th>Time Out</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($attendance as $row): ?>
                    <tr>
                        <td><?= $row['tbl_student_id'] ?></td>
                        <td><?= htmlspecialchars($row['student_name']) ?></td>
                        <td><?= $row['time_in'] ? date("g:i A", strtotime($row['time_in'])) : '-' ?></td>
                        <td><?= $row['time_out'] ? date("g:i A", strtotime($row['time_out'])) : '-' ?></td>
                        <td class="<?= strtolower($row['status']) ?>"><?= $row['status'] ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <!-- Print Button -->
        <button class="btn btn-secondary" onclick="window.print()">Print / Save PDF</button>
    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/jquery@3.5.1/dist/jquery.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

