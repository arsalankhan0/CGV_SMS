<?php
error_reporting(0);
include('includes/dbconnection.php');


if(isset($_POST['uname']))
{
    $username = $_POST['uname'];
    
    $query = "SELECT * FROM tblstudent WHERE UserName = :username AND IsDeleted = 0";
    $stmt = $dbh->prepare($query);
    $stmt->bindParam(':username', $username, PDO::PARAM_STR);
    $stmt->execute();
    
    if($stmt->rowCount() > 0)
    {
        echo "Username already exists";
    } 
}

if(isset($_POST['stuid']))
{
    $stuid = $_POST['stuid'];
    
    $query = "SELECT * FROM tblstudent WHERE StuID = :stuid AND IsDeleted = 0";
    $stmt = $dbh->prepare($query);
    $stmt->bindParam(':stuid', $stuid, PDO::PARAM_STR);
    $stmt->execute();
    
    if($stmt->rowCount() > 0)
    {
        echo "Student ID already exists";
    } 
}
?>
