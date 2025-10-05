<?php
session_start();
include('./conn/conn.php');

// Optional: require login if desired
// if (!isset($_SESSION['user'])) { header('Location: index.php'); exit; }

$event_id = isset($_GET['event_id']) ? intval($_GET['event_id']) : 0;

// Fetch event details (optional, to show event name)
$event = null;
if ($event_id > 0) {
    $stmt = $conn->prepare("SELECT * FROM tbl_events WHERE event_id = :id LIMIT 1");
    $stmt->bindParam(':id', $event_id);
    $stmt->execute();
    $event = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8"/>
<meta name="viewport" content="width=device-width,initial-scale=1"/>
<title>Attendance - QR Code Attendance System</title>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
<style>
@import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap');
body { font-family: 'Poppins', sans-serif; background: #eef2f5; }
.main { padding: 20px; }
.attendance-wrapper { max-width:1200px; margin: 24px auto; }
.qr-panel { background: #fff; padding:18px; border-radius:10px; box-shadow: 0 4px 14px rgba(0,0,0,0.06); }
.att-list-panel { background: #fff; padding:18px; border-radius:10px; box-shadow: 0 4px 14px rgba(0,0,0,0.06); }
.viewport { width:100%; border-radius:8px; border:1px solid #ddd; }
.btn-group-toggle .btn input[type="radio"] { position:absolute; clip:rect(0,0,0,0); pointer-events:none; }
</style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
  <a class="navbar-brand ml-3" href="#">QR Code Attendance System</a>
  <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navCollapse2">
    <span class="navbar-toggler-icon"></span>
  </button>

  <div class="collapse navbar-collapse" id="navCollapse2">
    <ul class="navbar-nav mr-auto">
      <li class="nav-item"><a class="nav-link" href="./events.php">Home</a></li>
      <li class="nav-item"><a class="nav-link" href="./masterlist.php">List of Students</a></li>
      <li class="nav-item"><a class="nav-link" href="./reports.php">Reports</a></li>
    </ul>
    <ul class="navbar-nav ml-auto">
      <li class="nav-item mr-3"><a class="nav-link" href="./logout.php">Logout</a></li>
    </ul>
  </div>
</nav>

<div class="container attendance-wrapper">
    <div class="row">
        <div class="col-md-4">
            <div class="qr-panel mb-4">
                <h5 class="mb-2">Scan QR Code</h5>
                <?php if ($event): ?>
                    <div class="mb-2"><strong>Event:</strong> <?= htmlspecialchars($event['event_name']) ?> <small class="text-muted">(<?= htmlspecialchars($event['event_date']) ?>)</small></div>
                <?php endif; ?>

                <div class="scanner-con mb-3">
                    <p class="small text-muted">Position your QR code in front of the camera.</p>
                    <video id="interactive" class="viewport"></video>
                </div>

                <div class="qr-detected-container" style="display:none;">
                    <form action="./endpoint/add-attendance.php" method="POST" class="mb-0">
                        <h6 class="text-center">QR Detected</h6>
                        <input type="hidden" id="detected-qr-code" name="qr_code">
                        <input type="hidden" name="event_id" value="<?= $event_id ?>">
                        <div class="btn-group btn-group-toggle d-flex" data-toggle="buttons">
                            <label class="btn btn-success flex-fill">
                                <input type="radio" name="action_type" value="time_in" required> Time In
                            </label>
                            <label class="btn btn-primary flex-fill">
                                <input type="radio" name="action_type" value="time_out" required> Time Out
                            </label>
                        </div>
                        <button type="submit" class="btn btn-dark btn-block mt-2">Submit Attendance</button>
                        <button type="button" id="scan-again" class="btn btn-link btn-block mt-1">Scan Again</button>
                    </form>
                </div>
            </div>

            <div class="qr-help small text-muted">
                Tip: If the camera fails, try allowing camera permissions in your browser, or use a different device.
            </div>
        </div>

        <div class="col-md-8">
            <div class="att-list-panel">
                <h5>List of Present Students</h5>
                <div class="table-responsive">
                    <table class="table table-sm table-bordered text-center" id="attendanceTable">
                        <thead class="thead-dark">
                            <tr>
                                <th>#</th>
                                <th>Name</th>
                                <th>Course - Year</th>
                                <th>Time In</th>
                                <th>Time Out</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php
                        // Fetch attendance for this event (if event_id is 0, fetch recent)
                        $sql = "SELECT a.*, s.student_name, s.course, s.year
                                FROM tbl_attendance a
                                LEFT JOIN tbl_student s ON s.tbl_student_id = a.tbl_student_id";
                        if ($event_id > 0) {
                            $sql .= " WHERE a.event_id = :event_id";
                        }
                        $sql .= " ORDER BY a.tbl_attendance_id DESC LIMIT 200";

                        $stmt = $conn->prepare($sql);
                        if ($event_id > 0) $stmt->bindParam(':event_id', $event_id, PDO::PARAM_INT);
                        $stmt->execute();
                        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
                        $i = 1;
                        foreach ($rows as $row):
                            $attendanceID = intval($row['tbl_attendance_id']);
                            $studentName = $row['student_name'] ?? 'Unknown';
                            $studentCourse = $row['course'] ?? '';
                            $studentYear = $row['year'] ?? '';
                            $timeIn = $row['time_in'] ?? '';
                            $timeOut = $row['time_out'] ?? '';
                        ?>
                            <tr>
                                <td><?= $i++ ?></td>
                                <td><?= htmlspecialchars($studentName) ?></td>
                                <td><?= htmlspecialchars($studentCourse . ' - ' . $studentYear) ?></td>
                                <td><?= htmlspecialchars($timeIn) ?></td>
                                <td><?= htmlspecialchars($timeOut) ?></td>
                                <td>
                                    <button class="btn btn-danger btn-sm" onclick="deleteAttendance(<?= $attendanceID ?>)">Delete</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
</div>

<!-- scripts -->
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://rawgit.com/schmich/instascan-builds/master/instascan.min.js"></script>

<script>
let scanner;
function startScanner() {
    try {
        scanner = new Instascan.Scanner({ video: document.getElementById('interactive'), mirror: false });
        scanner.addListener('scan', function (content) {
            // Put the scanned QR content in the form input and show the confirmation area
            document.getElementById('detected-qr-code').value = content;
            $('.scanner-con').hide();
            $('.qr-detected-container').show();
            if (scanner) {
                try { scanner.stop(); } catch(e) { /* ignore */ }
            }
            console.log('QR scanned:', content);
        });

        Instascan.Camera.getCameras().then(function (cameras) {
            if (cameras.length > 0) {
                // Prefer back camera if available
                let cam = cameras[0];
                for (let i = 0; i < cameras.length; i++) {
                    if (cameras[i].name && /back|rear|environment/gi.test(cameras[i].name)) {
                        cam = cameras[i];
                        break;
                    }
                }
                scanner.start(cam);
            } else {
                alert('No cameras found. Please allow camera access or try another device.');
            }
        }).catch(function (e) {
            console.error('Camera error', e);
            alert('Camera access error: ' + e);
        });
    } catch (err) {
        console.error(err);
        alert('Scanner initialization failed: ' + err);
    }
}

document.addEventListener('DOMContentLoaded', function () {
    startScanner();

    // Scan again button
    document.getElementById('scan-again').addEventListener('click', function () {
        $('.qr-detected-container').hide();
        $('.scanner-con').show();
        if (scanner) {
            Instascan.Camera.getCameras().then(function (cameras) {
                if (cameras.length > 0) {
                    scanner.start(cameras[0]);
                }
            }).catch(function (e) {
                console.error('Camera error', e);
            });
        }
    });
});

function deleteAttendance(id) {
    if (confirm('Delete this attendance record?')) {
        window.location = './endpoint/delete-attendance.php?attendance=' + id;
    }
}
</script>
</body>
</html>
