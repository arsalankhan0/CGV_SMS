<?php
error_reporting(0);
include('../../includes/dbconnection.php');

if(isset($_POST['stuid']))
{
    $stuid = $_POST['stuid'];
    
    $query = "SELECT ID FROM tblstudent WHERE StuID = :stuid AND IsDeleted = 0";
    $stmt = $dbh->prepare($query);
    $stmt->bindParam(':stuid', $stuid, PDO::PARAM_STR);
    $stmt->execute();
    
    if($stmt->rowCount() > 0)
    {
        echo "Student ID already exists";
    } 
}
?>
