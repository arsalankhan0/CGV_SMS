<?php
error_reporting(0);
session_start();
include('../../includes/dbconnection.php');

if (strlen($_SESSION['sturecmsaid']) == 0) 
{
    header('location:../../logout.php');
    exit();
}

$response = [
    'status' => 'error',
    'message' => 'An error occurred.'
];

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['showAd'])) 
{
    $showAd = intval($_POST['showAd']);
    try 
    {
        // Check if there is an existing image path
        $sqlCheck = "SELECT COUNT(*) as count, ImagePath FROM tblads";
        $queryCheck = $dbh->prepare($sqlCheck);
        $queryCheck->execute();
        $data = $queryCheck->fetch(PDO::FETCH_ASSOC);

        if ($data['count'] > 0 && !empty($data['ImagePath'])) 
        {
            $sql = "UPDATE tblads SET IsDisplayed = :showAd";
            $query = $dbh->prepare($sql);
            $query->bindParam(':showAd', $showAd, PDO::PARAM_INT);
            if ($query->execute()) 
            {
                $response['status'] = 'success';
                $response['message'] = 'Advertisement display status updated successfully.';
            } 
            else 
            {
                $response['message'] = 'Failed to update advertisement display status.';
            }
        } 
        else 
        {
            $response['message'] = 'No advertisement banner found. Please upload a banner first.';
        }
    } 
    catch (PDOException $e) 
    {
        $response['message'] = 'Database error: ' . $e->getMessage();
    }
}
header('Content-Type: application/json');
echo json_encode($response);
?>
