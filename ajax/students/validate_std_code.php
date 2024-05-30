<?php
include('../../includes/dbconnection.php');

if (isset($_POST['codeNumber'])) {
    $codeNumber = $_POST['codeNumber'];
    $sql = "SELECT ID FROM tblstudent WHERE CodeNumber=:codeNumber AND IsDeleted = 0";
    $query = $dbh->prepare($sql);
    $query->bindParam(':codeNumber', $codeNumber, PDO::PARAM_STR);
    $query->execute();
    if ($query->rowCount() > 0) {
        echo "exists";
    } else {
        echo "available";
    }
    exit();
}
?>
