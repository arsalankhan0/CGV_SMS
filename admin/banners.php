<?php
session_start();
error_reporting(0);
include('includes/dbconnection.php');

if (strlen($_SESSION['sturecmsaid'] == 0)) 
{
    header('location:logout.php');
} 
else 
{
    $successAlert = false;
    $dangerAlert = false;
    $msg = "";
    try 
    {
        if(isset($_POST['submit']))
        {
            $countSql = "SELECT COUNT(*) FROM tblbanners";
            $countQuery = $dbh->prepare($countSql);
            $countQuery->execute();
            $count = $countQuery->fetchColumn();

            
            if($count == 3 )
            {
                $dangerAlert = true;
                $msg = 'Cannot upload more than 3 banners! Please delete some banners and try again.';
            }
            else
            {
                $fileTmpPath = $_FILES['banner']['tmp_name'];
                $fileName = $_FILES['banner']['name'];
                $fileSize = $_FILES['banner']['size'];
                $fileType = $_FILES['banner']['type'];
                $fileNameCmps = explode(".", $fileName);
                $fileExtension = strtolower(end($fileNameCmps));
            
                $allowedExtensions = array("jpg", "jpeg", "png");
                $maxFileSize = 2485760; // 2MB
                    
                if (in_array($fileExtension, $allowedExtensions) && $fileSize <= $maxFileSize) 
                {
                    $newBannerName = "banner_" . time() . '.' . $fileExtension;
                    $uploadBannerDir = './images/MainBanners/';
                    $destPath = $uploadBannerDir . $newBannerName;
            
                    if (move_uploaded_file($fileTmpPath, $destPath)) 
                    {
                        $sql = "INSERT INTO tblbanners (ImagePath) VALUES (:imagePath)";
                        $query = $dbh->prepare($sql);
                        $query->bindParam(':imagePath', $newBannerName, PDO::PARAM_STR);
                        $query->execute();
            
                        header("location:banners.php");
                    } 
                    else 
                    {
                        $dangerAlert = true;
                        $msg = 'Failed to move uploaded Ad Banner!';
                    }
                } 
                else 
                {
                    $dangerAlert = true;
                    $msg = 'File must be a jpg/jpeg/png and size must be less than 2MB.';
                }
            }
        }
        if (isset($_POST['confirmDelete'])) 
        {
            $imageID = $_POST['imgID'];
            
            $sql = "SELECT ImagePath FROM tblbanners WHERE ID = :imageID";
            $query = $dbh->prepare($sql);
            $query->bindParam(':imageID', $imageID, PDO::PARAM_INT);
            $query->execute();
            $path = $query->fetchColumn();
        
            if ($path) 
            {
                $deleteSql = "DELETE FROM tblbanners WHERE ID = :imageID";
                $deleteQuery = $dbh->prepare($deleteSql);
                $deleteQuery->bindParam(':imageID', $imageID, PDO::PARAM_INT);
                
                if ($deleteQuery->execute()) 
                {
                    // Delete the image file from the MainBanners folder
                    $imagePath = './images/MainBanners/' . $path;
                    if (file_exists($imagePath)) 
                    {
                        unlink($imagePath);
                    }
                }
            }
            
            header("Location: banners.php");
            exit;
        }
    } 
    catch (PDOException $e) 
    {
        $msg = "Ops! An Error occurred.";
        $dangerAlert = true;
        echo "<script>console.error('Error:---> " . $e->getMessage() . "');</script>";
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>TPS || Banners</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- plugins:css -->
    <link rel="stylesheet" href="vendors/simple-line-icons/css/simple-line-icons.css">
    <link rel="stylesheet" href="vendors/flag-icon-css/css/flag-icon.min.css">
    <link rel="stylesheet" href="vendors/css/vendor.bundle.base.css">
    <!-- Plugin css for this page -->
    <link rel="stylesheet" href="vendors/select2/select2.min.css">
    <link rel="stylesheet" href="vendors/select2-bootstrap-theme/select2-bootstrap.min.css">
    <!-- End plugin css for this page -->
    <!-- Layout styles -->
    <link rel="stylesheet" href="css/style.css"/>
</head>
<body>
<div class="container-scroller">
    <!-- partial:partials/_navbar.html -->
    <?php include_once('includes/header.php'); ?>
    <!-- partial -->
    <div class="container-fluid page-body-wrapper">
        <!-- partial:partials/_sidebar.html -->
        <?php include_once('includes/sidebar.php'); ?>
        <!-- partial -->
        <div class="main-panel">
            <div class="content-wrapper">
                <div class="page-header">
                    <h3 class="page-title"> Banners </h3>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                            <li class="breadcrumb-item active" aria-current="page"> Banners</li>
                        </ol>
                    </nav>
                </div>
                <div class="row">
                    <div class="col-12 grid-margin stretch-card">
                        <div class="card">
                            <div class="card-body">
                                <h4 class="card-title" style="text-align: center;">Banners</h4>
                                    <!-- Dismissible Alert messages -->
                                    <?php 
                                    if ($successAlert) 
                                    {
                                        ?>
                                        <!-- Success -->
                                        <div id="success-alert" class="alert alert-success alert-dismissible" role="alert">
                                        <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                        <?php echo $msg; ?>
                                        </div>
                                    <?php 
                                    }
                                    if($dangerAlert)
                                    { 
                                    ?>
                                        <!-- Danger -->
                                        <div id="danger-alert" class="alert alert-danger alert-dismissible" role="alert">
                                        <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                        <?php echo $msg; ?>
                                        </div>
                                    <?php
                                    }
                                    ?>
                                <form class="forms-sample" method="post" enctype="multipart/form-data"> 
                                    <div class="form-group">
                                        <div class="d-flex flex-wrap">
                                            <div class="w-100">
                                                <label>Upload Banner (JPG/JPEG/PNG only)</label>
                                                <input type="file" name="banner" class="form-control" id="customFile" onchange="previewImage(this)" accept="image/jpeg, image/jpg, image/png">
                                                <p class="text-muted mt-2">Banner must be less than 2MB</p>
                                            </div>
                                        </div>
                                        <div id="imagePreview" style="width: 150px">
                                        </div>
                                        <button type="submit" id="uploadButton" class="btn btn-primary mr-2" name="submit">Upload Banner</button>
                                    </div>
                                    <div class="row mt-3">
                                        <?php
                                        $sql = "SELECT ID, ImagePath FROM tblbanners ORDER BY ID DESC";
                                        $stmt = $dbh->prepare($sql);
                                        $stmt->execute();
                                        $images = $stmt->fetchAll(PDO::FETCH_ASSOC);

                                        if (count($images) > 0) 
                                        {
                                            foreach ($images as $imagePath) 
                                            {
                                                ?>
                                                <div class="col-md-12 gallery-row">
                                                    <div class="row align-items-center row-container">
                                                        <div class="col-md-6">
                                                            <img class="img-fluid gallery-image" src="<?php echo './images/MainBanners/' . $imagePath['ImagePath']; ?>" alt="Image"  loading="lazy">
                                                        </div>
                                                        <div class="col-md-6 text-right">
                                                            <a href="" onclick="setDeleteId(<?php echo $imagePath['ID'];?>)" data-toggle="modal" data-target="#confirmationModal">
                                                                <i class="icon-trash delete-icon"></i>
                                                            </a>
                                                        </div>
                                                    </div>
                                                </div>
                                                <?php
                                            }
                                        } 
                                        else 
                                        {
                                            echo '<div class="col-lg-12"><h4 class="text-center">No Images to show!</h4></div>';
                                        }
                                        ?>
                                        
                                    </div>
                                    <!-- Confirmation Modal (Delete) -->
                                    <div class="modal fade" id="confirmationModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" data-backdrop="static" data-keyboard="false">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                            <div class="modal-header">
                                                <h4 class="modal-title" id="myModalLabel">Confirmation</h4>
                                                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                            </div>
                                            <div class="modal-body">
                                                Are you sure you want to delete this banner? 
                                            </div>
                                            <div class="modal-footer">
                                                <input type="hidden" name="imgID" id="imgID">
                                                <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                                                <button type="submit" class="btn btn-primary" name="confirmDelete">Delete</button>
                                            </div>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- content-wrapper ends -->
            <!-- partial:partials/_footer.html -->
            <?php include_once('includes/footer.php'); ?>
            <!-- partial -->
        </div>
        <!-- main-panel ends -->
    </div>
    <!-- page-body-wrapper ends -->
</div>

<!-- container-scroller -->
<!-- plugins:js -->
<script src="vendors/js/vendor.bundle.base.js"></script>
<!-- endinject -->
<!-- Plugin js for this page -->
<script src="vendors/select2/select2.min.js"></script>
<script src="vendors/typeahead.js/typeahead.bundle.min.js"></script>
<!-- End plugin js for this page -->
<!-- inject:js -->
<script src="js/off-canvas.js"></script>
<script src="js/misc.js"></script>
<!-- endinject -->
<!-- Custom js for this page -->
<script src="js/typeahead.js"></script>
<script src="js/select2.js"></script>
<script src="./js/manageAlert.js"></script>
<script>
    function setDeleteId(id) 
    {
        document.getElementById('imgID').value = id;
    }
    function previewImage(input) {
    let file = input.files[0];
    let reader = new FileReader();
    
    reader.onload = (e) => {
        let imgPreview = document.getElementById('imagePreview');
        imgPreview.innerHTML = '<span>Selected Banner</span><img src="' + e.target.result + '" alt="Preview" class="img-fluid my-2">';
    }
    reader.readAsDataURL(file);
}
</script>
<!-- End custom js for this page -->
</body>
</html>
<?php } ?>
