<?php
include ('../conn/conn.php');

if (isset($_GET['student'])) {
    $student = $_GET['student'];

    try {
        // Hard delete
        $query = "DELETE FROM tbl_student WHERE tbl_student_id = :student";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':student', $student, PDO::PARAM_INT);
        $stmt->execute();

        echo "
            <script>
                alert('Student deleted successfully!');
                window.location.href = '../masterlist.php';
            </script>
        ";
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
} else {
    echo "<script>
            alert('No student selected!');
            window.location.href='../masterlist.php';
          </script>";
}
?>
