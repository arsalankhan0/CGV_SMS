<?php

include('includes/dbconnection.php');

if (isset($_POST['classId'])) 
{
    $classId = $_POST['classId'];

    $sql = "SELECT ID, SubjectName FROM tblsubjects WHERE ClassName LIKE '%" . $classId . "%' AND IsOptional = 0 AND IsCurricularSubject = 0 AND IsDeleted = 0";
    $query = $dbh->prepare($sql);
    $query->execute();
    $subjects = $query->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($subjects);
}
?>
