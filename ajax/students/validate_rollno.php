<?php
include('../../includes/dbconnection.php');

if (isset($_POST['stuRollNo']) && isset($_POST['stuclass']) && isset($_POST['stusection'])) {
    $stuRollNo = $_POST['stuRollNo'];
    $stuclass = $_POST['stuclass'];
    $stusection = $_POST['stusection'];

    $sql = "SELECT COUNT(*) FROM tblstudent WHERE RollNo=:stuRollNo AND StudentClass=:stuclass AND StudentSection=:stusection AND IsDeleted = 0";
    $query = $dbh->prepare($sql);
    $query->bindParam(':stuRollNo', $stuRollNo, PDO::PARAM_STR);
    $query->bindParam(':stuclass', $stuclass, PDO::PARAM_STR);
    $query->bindParam(':stusection', $stusection, PDO::PARAM_STR);
    $query->execute();

    $count = $query->fetchColumn();

    if ($count > 0) {
        echo json_encode(array('status' => 'error', 'message' => 'Roll number already exists in the selected class and section.'));
    } else {
        echo json_encode(array('status' => 'success', 'message' => 'Roll number is available.'));
    }
} else {
    echo json_encode(array('status' => 'error', 'message' => 'Invalid input.'));
}
?>
