<?php
include('includes/dbconnection.php');

if (isset($_GET['classId'])) 
{
    $classId = intval($_GET['classId']);
    
    $sql = "SELECT s.ID, s.SectionName FROM tblclass c
            JOIN tblsections s ON FIND_IN_SET(s.ID, c.Section)
            WHERE c.ID = :classId";
    
    $query = $dbh->prepare($sql);
    $query->bindParam(':classId', $classId, PDO::PARAM_INT);
    $query->execute();

    $result = $query->fetchAll(PDO::FETCH_ASSOC);

    if ($result) 
    {
        echo json_encode($result);
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
