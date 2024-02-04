<?php
include('includes/dbconnection.php');
if (isset($_POST['class_id'])) {
    $classID = $_POST['class_id'];
    $sqlSections = "SELECT Section FROM tblclass WHERE ID = :classID AND IsDeleted = 0";
    $querySections = $dbh->prepare($sqlSections);
    $querySections->bindParam(':classID', $classID, PDO::PARAM_INT);
    $querySections->execute();
    $sections = $querySections->fetchAll(PDO::FETCH_COLUMN);

    foreach ($sections as $section) {
        echo "<option value='$section'>$section</option>";
    }
}
?>
