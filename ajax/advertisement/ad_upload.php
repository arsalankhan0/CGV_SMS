<?php
error_reporting(0);
session_start();
include('../../includes/dbconnection.php');

if (strlen($_SESSION['sturecmsaid']) == 0) {
    header('location:../../logout.php');
    exit();
}

$response = [
    'status' => 'error',
    'message' => 'An error occurred.'
];

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['banner'])) {
    try {
        $sqlCheck = "SELECT IsDisplayed, ImagePath FROM tblads";
        $queryCheck = $dbh->prepare($sqlCheck);
        $queryCheck->execute();
        $existingData = $queryCheck->fetch(PDO::FETCH_ASSOC);

        $fileTmpPath = $_FILES['banner']['tmp_name'];
        $fileName = $_FILES['banner']['name'];
        $fileSize = $_FILES['banner']['size'];
        $fileType = $_FILES['banner']['type'];
        $fileNameCmps = explode(".", $fileName);
        $fileExtension = strtolower(end($fileNameCmps));

        $allowedExtensions = array("jpg", "jpeg", "png");
        $maxFileSize = 2485760; // 2MB
        
        if (in_array($fileExtension, $allowedExtensions) && $fileSize <= $maxFileSize) {
            $newBannerName = "banner_" . time() . '.' . $fileExtension;
            $uploadBannerDir = '../../Main/img/Advertisement/';
            $destPath = $uploadBannerDir . $newBannerName;

            // Unlink previous image if it exists
            if ($existingData && file_exists($uploadBannerDir . $existingData['ImagePath'])) {
                unlink($uploadBannerDir . $existingData['ImagePath']);
            }
            if (move_uploaded_file($fileTmpPath, $destPath)) {
                $sql = ($existingData) ? "UPDATE tblads SET ImagePath = :imagePath" : "INSERT INTO tblads (ImagePath) VALUES (:imagePath)";
                $query = $dbh->prepare($sql);
                $query->bindParam(':imagePath', $newBannerName, PDO::PARAM_STR);
                $query->execute();

                $response['status'] = 'success';
                $response['message'] = 'Ad Banner has been uploaded successfully.';
            } else {
                $response['message'] = 'Failed to move uploaded Ad Banner.';
            }
        } else {
            $response['message'] = 'File must be a jpg/jpeg/png and size must be less than 2MB.';
        }
    } catch (PDOException $e) {
        $response['message'] = 'Database error: ' . $e->getMessage();
    }
}

header('Content-Type: application/json');
echo json_encode($response);
?>
