<?php
// Include necessary files and start session

// Assuming you have already established a database connection
// error_reporting(0);
include('includes/dbconnection.php');


if(isset($_POST['username'])){
    // Check availability of username in the database
    $username = $_POST['username'];
    
    // Execute query to check if username exists
    $query = "SELECT * FROM tblemployees WHERE UserName = :username AND IsDeleted = 0";
    $stmt = $dbh->prepare($query);
    $stmt->bindParam(':username', $username, PDO::PARAM_STR);
    $stmt->execute();
    
    // Check if username exists
    if($stmt->rowCount() > 0){
        // Username already exists
        echo "Username already exists";
    } 
}

if(isset($_POST['empid'])){
    // Check availability of employee ID in the database
    $empid = $_POST['empid'];
    
    // Execute query to check if employee ID exists
    $query = "SELECT * FROM tblemployees WHERE EmpId = :empid AND IsDeleted = 0";
    $stmt = $dbh->prepare($query);
    $stmt->bindParam(':empid', $empid, PDO::PARAM_STR);
    $stmt->execute();
    
    // Check if employee ID exists
    if($stmt->rowCount() > 0){
        // Employee ID already exists
        echo "Employee ID already exists";
    } 
}
?>
