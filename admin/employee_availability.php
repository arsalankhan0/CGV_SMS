<?php
error_reporting(0);
include('includes/dbconnection.php');


if(isset($_POST['username']))
{
    $username = $_POST['username'];
    
    $query = "SELECT * FROM tblemployees WHERE UserName = :username AND IsDeleted = 0";
    $stmt = $dbh->prepare($query);
    $stmt->bindParam(':username', $username, PDO::PARAM_STR);
    $stmt->execute();
    
    if($stmt->rowCount() > 0)
    {
        echo "Username already exists";
    } 
}

if(isset($_POST['empid']))
{
    $empid = $_POST['empid'];
    
    $query = "SELECT * FROM tblemployees WHERE EmpId = :empid AND IsDeleted = 0";
    $stmt = $dbh->prepare($query);
    $stmt->bindParam(':empid', $empid, PDO::PARAM_STR);
    $stmt->execute();
    
    if($stmt->rowCount() > 0)
    {
        echo "Employee ID already exists";
    } 
}
?>
