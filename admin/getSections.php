<?php
include('includes/dbconnection.php');

if (isset($_GET['class'])) {
    $selectedClass = $_GET['class'];

    // Fetch sections based on the selected class
    $sqlSections = "SELECT Section FROM tblclass WHERE ID = :selectedClass AND IsDeleted = 0";
    $querySections = $dbh->prepare($sqlSections);
    $querySections->bindParam(':selectedClass', $selectedClass, PDO::PARAM_STR);
    $querySections->execute();
    $sectionsString = $querySections->fetchColumn();

    // Split the sections into an array
    $sections = explode(',', $sectionsString);

    // Return the sections as JSON
    echo json_encode($sections);
}
?>
