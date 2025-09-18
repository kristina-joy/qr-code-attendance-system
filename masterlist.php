<?php
session_start();
include ('./conn/conn.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>QR Code Attendance System</title>

<!-- Bootstrap CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">

<!-- DataTables CSS -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.css" />
<link rel="stylesheet" href="https://cdn.datatables.net/fixedheader/3.4.0/css/fixedHeader.dataTables.min.css">

<style>
@import url('https://fonts.googleapis.com/css2?family=Poppins:wght@500&display=swap');
* { margin: 0; padding: 0; font-family: 'Poppins', sans-serif; }

body {
    background: linear-gradient(to bottom, rgba(255,255,255,0.15) 0%, rgba(0,0,0,0.15) 100%), 
                radial-gradient(at top center, rgba(255,255,255,0.40) 0%, rgba(0,0,0,0.40) 120%) #989898;
    background-blend-mode: multiply,multiply;
    background-attachment: fixed;
    background-repeat: no-repeat;
    background-size: cover;
}

.main {
    display: flex;
    justify-content: center;
    align-items: center;
    height: 91.5vh;
}

.student-container {
    display: flex;
    flex-direction: column;
    height: 90%;
    width: 90%;
    border-radius: 20px;
    padding: 40px;
    background-color: rgba(255, 255, 255, 0.8);
}

.title {
    flex: 0 0 auto;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.table-container {
    flex: 1 1 auto;
    min-height: 0;
    overflow: hidden;
    position: relative;
}

/* Table setup */
#studentTable {
    width: 100% !important;
    table-layout: fixed;
    border-collapse: collapse;
}

#studentTable th, #studentTable td {
    text-align: center;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.bold-btn {
    font-weight: bold;
}

#studentTable thead th {
    background-color: #343a40;
    color: #fff;
}

.dataTables_wrapper .dataTables_filter {
    position: sticky;
    top: 0;
    background-color: linear-gradient(to bottom, rgba(255,255,255,0.15) 0%, rgba(0,0,0,0.15) 100%), 
                      radial-gradient(at top center, rgba(255,255,255,0.40) 0%, rgba(0,0,0,0.40) 120%) #989898;
    color: #1e1b1bff;
    z-index: 15;
    padding: 10px 0;
}

.dataTables_wrapper .dataTables_filter input {
    background-color: #fff;
    color: #000;
}
</style>

</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <a class="navbar-brand ml-4" href="#">QR Code Attendance System</a>
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent">
        <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarSupportedContent">
        <ul class="navbar-nav mr-auto">
            <li class="nav-item"><a class="nav-link" href="./events.php">Home</a></li>
            <li class="nav-item active"><a class="nav-link" href="./masterlist.php">List of Students</a></li>
            <li class="nav-item"><a class="nav-link" href="./reports.php">Reports</a></li>
        </ul>
        <ul class="navbar-nav ml-auto">
            <li class="nav-item mr-3"><a class="nav-link" href="#">Logout</a></li>
        </ul>
    </div>
</nav>

<div class="main">
    <div class="student-container">
        <div class="title">
            <h4>List of Students</h4>
            <div class="d-flex" style="gap:8px;">
                <button class="btn btn-dark" data-toggle="modal" data-target="#uploadModal">Upload File</button>
                <button class="btn btn-dark" data-toggle="modal" data-target="#addStudentModal">Add Student</button>
                <button class="btn btn-warning bold-btn" id="deleteSelectedBtn">Delete Selected</button>
            </div>
        </div>
        <hr>
        <div class="table-container">
            <table class="table table-bordered text-center table-sm" id="studentTable">
                <thead class="thead-dark">
                    <tr>
                        <th style="width:30px"><input type="checkbox" id="selectAll"></th>
                        <th style="width:50px">#</th>
                        <th style="width:200px">Name</th>
                        <th style="width:120px">Course</th>
                        <th style="width:120px">Year</th>
                        <th style="width:150px">Action</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                $stmt = $conn->prepare("SELECT * FROM tbl_student ORDER BY tbl_student_id ASC");
                $stmt->execute();
                $result = $stmt->fetchAll();
                $counter = 1; 
                foreach ($result as $row):
                    $studentID = $row["tbl_student_id"];
                    $studentName = $row["student_name"];
                    $studentCourse = $row["course"];
                    $studentYear = $row["year"];
                    $qrCode = $row["generated_code"];

                    // Generate QR code if empty
                    if(empty($qrCode)){
                        $qrCode = bin2hex(random_bytes(5)); // 10-character random code
                        $updateStmt = $conn->prepare("UPDATE tbl_student SET generated_code=:code WHERE tbl_student_id=:id");
                        $updateStmt->bindParam(':code', $qrCode);
                        $updateStmt->bindParam(':id', $studentID);
                        $updateStmt->execute();
                    }
                ?>
                    <tr>
                        <td><input type="checkbox" class="studentCheckbox" value="<?= $studentID ?>"></td>
                        <th><?= $counter++ ?></th>
                        <td><?= htmlspecialchars($studentName) ?></td>
                        <td><?= htmlspecialchars($studentCourse) ?></td>
                        <td><?= htmlspecialchars($studentYear) ?></td>
                        <td>
                            <button class="btn btn-success btn-sm" data-toggle="modal" data-target="#qrCodeModal<?= $studentID ?>">
                                <img src="https://cdn-icons-png.flaticon.com/512/1341/1341632.png" width="16">
                            </button>

                            <!-- QR Modal -->
                            <div class="modal fade" id="qrCodeModal<?= $studentID ?>" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title"><?= htmlspecialchars($studentName) ?>'s QR Code</h5>
                                            <button type="button" class="close" data-dismiss="modal">&times;</button>
                                        </div>
                                        <div class="modal-body text-center">
                                            <img src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=<?= $qrCode ?>" width="300">
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <button class="btn btn-secondary btn-sm" onclick="openUpdateModal(<?= $studentID ?>)">&#128393;</button>
                            <button class="btn btn-danger btn-sm" onclick="deleteStudent(<?= $studentID ?>)">&#10006;</button>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Upload Modal -->
<div class="modal fade" id="uploadModal" data-backdrop="static" data-keyboard="false">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Upload CSV File</h5>
        <button type="button" class="close" data-dismiss="modal">&times;</button>
      </div>
      <div class="modal-body">
        <form action="./endpoint/upload-students-csv-process.php" method="post" enctype="multipart/form-data">
            <div class="form-group">
                <label for="csvFile">Choose CSV File</label>
                <input type="file" class="form-control-file" id="csvFile" name="csvfile" accept=".csv" required>
            </div>
            <div class="form-group">
                <?php
                if(isset($_GET['success'])) {
                    echo '<div class="alert alert-success">'.htmlspecialchars($_GET['success']).'</div>';
                } elseif(isset($_GET['error'])) {
                    echo '<div class="alert alert-danger">'.htmlspecialchars($_GET['error']).'</div>';
                }
                ?>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="submit" class="btn btn-dark">Upload</button>
            </div>
        </form>
      </div>
    </div>
  </div>
</div>

<!-- Add Student Modal -->
<div class="modal fade" id="addStudentModal" data-backdrop="static" data-keyboard="false">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Add Student</h5>
        <button type="button" class="close" data-dismiss="modal">&times;</button>
      </div>
      <div class="modal-body">
        <form action="./endpoint/add-student.php" method="post">
            <div class="form-group">
                <label for="studentName">Name</label>
                <input type="text" class="form-control" id="studentName" name="student_name" required>
            </div>
            <div class="form-group">
                <label for="course">Course</label>
                <input type="text" class="form-control" id="course" name="course" required>
            </div>
            <div class="form-group">
                <label for="year">Year</label>
                <input type="text" class="form-control" id="year" name="year" required>
            </div>
            <div class="form-group">
                <label for="generatedCode">QR Code (optional)</label>
                <input type="text" class="form-control" id="generatedCode" name="generated_code" placeholder="Leave empty to auto-generate">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="submit" class="btn btn-dark">Add Student</button>
            </div>
        </form>
      </div>
    </div>
  </div>
</div>

<!-- Update Student Modal -->
<div class="modal fade" id="updateStudentModal" data-backdrop="static" data-keyboard="false">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Update Student</h5>
        <button type="button" class="close" data-dismiss="modal">&times;</button>
      </div>
      <div class="modal-body">
        <form action="./endpoint/update-student.php" method="post">
            <input type="hidden" id="updateStudentId" name="tbl_student_id">
            <div class="form-group">
                <label for="updateStudentName">Name</label>
                <input type="text" class="form-control" id="updateStudentName" name="student_name" required>
            </div>
            <div class="form-group">
                <label for="updateStudentCourse">Course</label>
                <input type="text" class="form-control" id="updateStudentCourse" name="course" required>
            </div>
            <div class="form-group">
                <label for="updateStudentYear">Year</label>
                <input type="text" class="form-control" id="updateStudentYear" name="year" required>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="submit" class="btn btn-dark">Update Student</button>
            </div>
        </form>
      </div>
    </div>
  </div>
</div>

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/jquery@3.5.1/dist/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.js"></script>
<script src="https://cdn.datatables.net/fixedheader/3.4.0/js/dataTables.fixedHeader.min.js"></script>

<script>
$(document).ready(function() {
    var table = $('#studentTable').DataTable({
        scrollY: '400px',
        scrollX: true,
        scrollCollapse: true,
        paging: false,
        fixedHeader: true,
        info: false,
        columnDefs: [
            { orderable: false, targets: [0,5] }
        ]
    });

    // Select All checkbox
    $('#selectAll').click(function(){
        $('.studentCheckbox').prop('checked', this.checked);
    });

    // Delete Selected
    $('#deleteSelectedBtn').click(function(){
        var selected = $('.studentCheckbox:checked').map(function(){ return this.value; }).get();
        if(selected.length > 0 && confirm("Delete selected students?")) {
            window.location = "./endpoint/delete-selected-students.php?students=" + selected.join(",");
        } else {
            alert("No students selected.");
        }
    });
});

// Individual delete
function deleteStudent(id) {
    if(confirm("Do you want to delete this student?")) {
        window.location = "./endpoint/delete-student.php?student=" + id;
    }
}

// Open Update modal and fill values
function openUpdateModal(id){
    $("#updateStudentId").val(id);
    var row = $("#studentTable").find("input.studentCheckbox[value='"+id+"']").closest("tr");
    $("#updateStudentName").val(row.find("td:eq(0)").next().next().text()); // Name
    $("#updateStudentCourse").val(row.find("td:eq(0)").next().next().next().text()); // Course
    $("#updateStudentYear").val(row.find("td:eq(0)").next().next().next().next().text()); // Year
    $("#updateStudentModal").modal("show");
}
</script>

</body>
</html>

