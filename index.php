<?php
include('./conn/conn.php');

// Get event_id from URL
$eventId = $_GET['event_id'] ?? null;
if (!$eventId) {
    die("No event selected. Please go back and choose an event.");
}

// Fetch attendance records for this event only
$stmt = $conn->prepare("
    SELECT a.tbl_attendance_id, a.time_in, s.student_name, s.course, s.year
    FROM tbl_attendance a
    LEFT JOIN tbl_student s ON s.tbl_student_id = a.tbl_student_id
    WHERE a.event_id = :event_id
    ORDER BY a.time_in DESC
");
$stmt->bindParam(':event_id', $eventId);
$stmt->execute();
$result = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QR Code Attendance System</title>

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@500&display=swap');

        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(to bottom, rgba(255,255,255,0.15) 0%, rgba(0,0,0,0.15) 100%),
                        radial-gradient(at top center, rgba(255,255,255,0.40) 0%, rgba(0,0,0,0.40) 120%) #989898;
            background-blend-mode: multiply, multiply;
            background-attachment: fixed;
            background-size: cover;
        }

        .main {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 91.5vh;
        }

        .attendance-container {
            height: 90%;
            width: 90%;
            border-radius: 20px;
            padding: 40px;
            background-color: rgba(255, 255, 255, 0.8);
        }

        .attendance-container > div {
            box-shadow: rgba(0, 0, 0, 0.24) 0px 3px 8px;
            border-radius: 10px;
            padding: 30px;
        }

        .attendance-container > div:last-child {
            width: 64%;
            margin-left: auto;
        }

        .back-btn {
            margin-bottom: 15px;
        }
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
                <li class="nav-item"><a class="nav-link" href="./list_of_students.php">List of Students</a></li>
                <li class="nav-item"><a class="nav-link" href="./reports.php">Reports</a></li>
            </ul>
            <ul class="navbar-nav ml-auto">
                <li class="nav-item mr-3"><a class="nav-link" href="#">Logout</a></li>
            </ul>
        </div>
    </nav>

    <div class="main">
        <div class="attendance-container row">
            <!-- Scanner -->
            <div class="qr-container col-4">
                <div class="scanner-con">
                    <h5 class="text-center">Scan your QR Code here for your attendance</h5>
                    <video id="interactive" class="viewport" width="100%"></video>
                </div>

                <div class="qr-detected-container" style="display: none;">
                    <form action="./endpoint/add-attendance.php" method="POST">
                        <h4 class="text-center">Student QR Detected!</h4>
                        <input type="hidden" id="detected-qr-code" name="generated_code">
                        <input type="hidden" name="event_id" value="<?= htmlspecialchars($eventId) ?>">
                        <button type="submit" class="btn btn-dark form-control">Submit Attendance</button>
                    </form>
                </div>
            </div>

            <!-- Attendance List -->
            <div class="attendance-list">
                <!-- Back Button -->
                <a href="events.php" class="btn btn-secondary back-btn">&larr; Back to Events</a>

                <h4>List of Present Students</h4>
                <div class="table-container table-responsive">
                    <table class="table text-center table-sm" id="attendanceTable">
                        <thead class="thead-dark">
                            <tr>
                                <th scope="col">#</th>
                                <th scope="col">Name</th>
                                <th scope="col">Course</th>
                                <th scope="col">Year</th>
                                <th scope="col">Time In</th>
                                <th scope="col">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $counter = 1; ?>
                            <?php foreach ($result as $row): ?>
                                <tr>
                                    <td><?= $counter++ ?></td> 
                                    <td><?= htmlspecialchars($row["student_name"]) ?></td>
                                    <td><?= htmlspecialchars($row["course"]) ?></td>
                                    <td><?= htmlspecialchars($row["year"]) ?></td>
                                    <td><?= $row["time_in"] ?></td>
                                    <td>
                                        <button class="btn btn-danger btn-sm" onclick="deleteAttendance(<?= $row['tbl_attendance_id'] ?>, <?= $eventId ?>)">X</button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        
        </div>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.5.1/dist/jquery.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.min.js"></script>

    <!-- Instascan JS -->
    <script src="https://rawgit.com/schmich/instascan-builds/master/instascan.min.js"></script>

    <script>
        let scanner;

        function startScanner() {
            scanner = new Instascan.Scanner({ video: document.getElementById('interactive') });

            scanner.addListener('scan', function (content) {
                document.getElementById("detected-qr-code").value = content;
                console.log("QR Detected:", content);

                scanner.stop();
                document.querySelector(".qr-detected-container").style.display = '';
                document.querySelector(".scanner-con").style.display = 'none';
            });

            Instascan.Camera.getCameras()
                .then(function (cameras) {
                    if (cameras.length > 0) {
                        scanner.start(cameras[0]);
                    } else {
                        alert('No cameras found.');
                    }
                })
                .catch(function (err) {
                    alert('Camera access error: ' + err);
                });
        }

        document.addEventListener('DOMContentLoaded', startScanner);

        function deleteAttendance(id, eventId) {
            if (confirm("Do you want to remove this attendance?")) {
                window.location = `./endpoint/delete-attendance.php?attendance=${id}&event_id=${eventId}`;
            }
        }
    </script>
</body>
</html>


