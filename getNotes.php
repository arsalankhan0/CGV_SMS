<?php
session_start();
error_reporting(0);
include('./includes/dbconnection.php');

if (isset($_POST['classId']) && isset($_POST['subjectId'])) {
    $classId = $_POST['classId'];
    $subjectId = $_POST['subjectId'];

    $sqlNotes = "SELECT * FROM tblnotes WHERE Class = :classId AND Subject = :subjectId";
    $queryNotes = $dbh->prepare($sqlNotes);
    $queryNotes->bindParam(':classId', $classId, PDO::PARAM_INT);
    $queryNotes->bindParam(':subjectId', $subjectId, PDO::PARAM_INT);
    $queryNotes->execute();
    $notes = $queryNotes->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($notes);
} else {
    echo json_encode(array('error' => 'Class ID or Subject ID not provided'));
}
?>
