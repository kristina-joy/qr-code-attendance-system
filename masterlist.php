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

.bold-btn { font-weight: bold; }

#studentTable thead th {
    background-color: #343a40;
    color: #fff;
}
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
                        <th><input type="checkbox" id="selectAll"></th>
                        <th>#</th>
                        <th>Name</th>
                        <th>Course</th>
                        <th>Year</th>
                        <th>Student ID</th>
                        <th>Action</th>
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
                    $studentNumber = $row["student_id"];
                    $qrCode = $row["generated_code"];

                    if(empty($qrCode)){
                        $qrCode = bin2hex(random_bytes(5));
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
                        <td><?= htmlspecialchars($studentNumber) ?></td>
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
                                            <img src="https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=<?= $qrCode ?>" width="300">
                                            <br><br>
                                            <a href="https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=<?= $qrCode ?>" download="qr_<?= $studentNumber ?>.png" class="btn btn-dark">Download</a>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <button class="btn btn-secondary btn-sm" onclick="openUpdateModal(<?= $studentID ?>)">✎</button>
                            <button class="btn btn-danger btn-sm" onclick="deleteStudent(<?= $studentID ?>)">✕</button>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add Student Modal -->
<div class="modal fade" id="addStudentModal" data-backdrop="static" data-keyboard="false">
  <div class="modal-dialog">
    <div class="modal-content">
      <form action="./endpoint/add-student.php" method="post">
        <div class="modal-header">
          <h5 class="modal-title">Add Student</h5>
          <button type="button" class="close" data-dismiss="modal">&times;</button>
        </div>
        <div class="modal-body">
            <div class="form-group">
                <label>Student ID</label>
                <input type="text" class="form-control" name="student_id" required>
            </div>
            <div class="form-group">
                <label>Name</label>
                <input type="text" class="form-control" name="student_name" required>
            </div>
            <div class="form-group">
                <label>Course</label>
                <input type="text" class="form-control" name="course" required>
            </div>
            <div class="form-group">
                <label>Year</label>
                <input type="text" class="form-control" name="year" required>
            </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
          <button type="submit" class="btn btn-dark">Add Student</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Update Modal -->
<div class="modal fade" id="updateStudentModal" data-backdrop="static" data-keyboard="false">
  <div class="modal-dialog">
    <div class="modal-content">
      <form action="./endpoint/update-student.php" method="post">
        <div class="modal-header">
          <h5 class="modal-title">Update Student</h5>
          <button type="button" class="close" data-dismiss="modal">&times;</button>
        </div>
        <div class="modal-body">
            <input type="hidden" id="updateStudentId" name="tbl_student_id">
            <div class="form-group">
                <label>Student ID</label>
                <input type="text" class="form-control" id="updateStudentNumber" name="student_id" required>
            </div>
            <div class="form-group">
                <label>Name</label>
                <input type="text" class="form-control" id="updateStudentName" name="student_name" required>
            </div>
            <div class="form-group">
                <label>Course</label>
                <input type="text" class="form-control" id="updateStudentCourse" name="course" required>
            </div>
            <div class="form-group">
                <label>Year</label>
                <input type="text" class="form-control" id="updateStudentYear" name="year" required>
            </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
          <button type="submit" class="btn btn-dark">Update Student</button>
        </div>
      </form>
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
        columnDefs: [{ orderable: false, targets: [0,6] }]
    });

    $('#selectAll').click(function(){
        $('.studentCheckbox').prop('checked', this.checked);
    });

    $('#deleteSelectedBtn').click(function(){
        var selected = $('.studentCheckbox:checked').map(function(){ return this.value; }).get();
        if(selected.length > 0 && confirm("Delete selected students?")) {
            window.location = "./endpoint/delete-selected-students.php?students=" + selected.join(",");
        } else {
            alert("No students selected.");
        }
    });
});

function deleteStudent(id) {
    if(confirm("Do you want to delete this student?")) {
        window.location = "./endpoint/delete-student.php?student=" + id;
    }
}

function openUpdateModal(id){
    var row = $("input.studentCheckbox[value='"+id+"']").closest("tr");
    $("#updateStudentId").val(id);
    $("#updateStudentName").val(row.find("td:eq(1)").text());
    $("#updateStudentCourse").val(row.find("td:eq(2)").text());
    $("#updateStudentYear").val(row.find("td:eq(3)").text());
    $("#updateStudentNumber").val(row.find("td:eq(4)").text());
    $("#updateStudentModal").modal("show");
}
</script>
</body>
</html>
