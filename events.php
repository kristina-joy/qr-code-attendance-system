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
            <li class="nav-item"><a class="nav-link" href="./list_of_students.php">List of Students</a></li>
            <li class="nav-item"><a class="nav-link" href="./reports.php">Reports</a></li>
        </ul>
        <ul class="navbar-nav ml-auto">
            <li class="nav-item mr-3"><a class="nav-link" href="#">Logout</a></li>
        </ul>
    </div>
</nav>

<div class="main">
    <h3 class="mb-4">Events</h3>
    <div class="events-container row">

        <!-- Add Event Card -->
        <div class="col-3 event-card add-card" data-toggle="modal" data-target="#createEventModal">
            <i class="bi bi-plus-circle" style="font-size:50px;"></i>
            <h6 class="mt-2">Create Event</h6>
        </div>

        <!-- Event Cards -->
        <?php foreach ($events as $event) { ?>
            <div class="col-3">
                <!-- Clickable Event Card -->
                <div class="event-card" onclick="window.location.href='index.php?event_id=<?= $event['event_id'] ?>'">
                    <i class="bi bi-folder-fill" style="font-size:50px;"></i>
                    <h6 class="mt-2"><?= htmlspecialchars($event['event_name']) ?></h6>
                    <small><?= $event['event_date'] ?> | <?= htmlspecialchars($event['created_by']) ?></small>
                    <p class="mt-2 text-muted" style="font-size:14px;"><?= htmlspecialchars($event['event_desc']) ?></p>
                </div>

                <!-- Action buttons under card -->
                <div class="action-buttons text-center">
                    <!-- Edit -->
                    <button class="btn btn-sm btn-warning" data-toggle="modal" data-target="#editEventModal<?= $event['event_id'] ?>">Edit</button>
                    <!-- Delete -->
                    <form action="endpoint/delete-event.php" method="POST" style="display:inline;">
                        <input type="hidden" name="event_id" value="<?= $event['event_id'] ?>">
                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Delete this event?')">Delete</button>
                    </form>
                </div>
            </div>

            <!-- Edit Modal -->
            <div class="modal fade" id="editEventModal<?= $event['event_id'] ?>" tabindex="-1" role="dialog">
              <div class="modal-dialog" role="document">
                <form method="POST" action="endpoint/update-event.php">
                    <input type="hidden" name="event_id" value="<?= $event['event_id'] ?>">
                    <div class="modal-content">
                      <div class="modal-header">
                        <h5 class="modal-title">Edit Event</h5>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                      </div>
                      <div class="modal-body">
                        <div class="form-group">
                            <label>Event Name</label>
                            <input type="text" name="event_name" class="form-control" value="<?= htmlspecialchars($event['event_name']) ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Date</label>
                            <input type="date" name="event_date" class="form-control" value="<?= $event['event_date'] ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Description</label>
                            <textarea name="event_desc" class="form-control"><?= htmlspecialchars($event['event_desc']) ?></textarea>
                        </div>
                      </div>
                      <div class="modal-footer">
                        <button class="btn btn-primary">Save Changes</button>
                      </div>
                    </div>
                </form>
              </div>
            </div>
        <?php } ?>

    </div>
</div>

<!-- Modal for Creating Event -->
<div class="modal fade" id="createEventModal" tabindex="-1" role="dialog">
  <div class="modal-dialog" role="document">
    <form method="POST">
        <input type="hidden" name="new_event" value="1">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Create New Event</h5>
            <button type="button" class="close" data-dismiss="modal">&times;</button>
          </div>
          <div class="modal-body">
            <div class="form-group">
                <label>Event Name</label>
                <input type="text" name="event_name" class="form-control" required>
            </div>
            <div class="form-group">
                <label>Date</label>
                <input type="date" name="event_date" class="form-control" required>
            </div>
            <div class="form-group">
                <label>Description</label>
                <textarea name="event_desc" class="form-control"></textarea>
            </div>
          </div>
          <div class="modal-footer">
            <button class="btn btn-primary">Create Event</button>
          </div>
        </div>
    </form>
  </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.min.js"></script>
</body>
</html>
