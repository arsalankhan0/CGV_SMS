<?php
include('includes/dbconnection.php');

if (isset($_GET['classId'])) 
{
    $classId = $_GET['classId'];

    $sql = "SELECT Section FROM tblclass WHERE ID = :classId";
    $query = $dbh->prepare($sql);
    $query->bindParam(':classId', $classId, PDO::PARAM_INT);
    $query->execute();

    $result = $query->fetch(PDO::FETCH_ASSOC);

    if ($result) 
    {
        $sections = explode(',', $result['Section']);
        $sections = array_map('trim', $sections);

        echo json_encode($sections);
    } 
    else 
    {
        echo json_encode([]);
    }
} 
else 
{
    echo json_encode([]);
}
?>
