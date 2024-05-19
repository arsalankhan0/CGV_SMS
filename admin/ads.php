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

    // Check if the ad data already exists in the database
    $sqlCheck = "SELECT IsDisplayed, ImagePath FROM tblads";
    $queryCheck = $dbh->prepare($sqlCheck);
    $queryCheck->execute();
    $existingData = $queryCheck->fetch(PDO::FETCH_ASSOC);
    try 
    {
        if(isset($_POST['reset']))
        {
            $sql = "DELETE FROM tblads";
            $query = $dbh->prepare($sql);
            $query->execute();

            $uploadBannerDir = '../Main/img/Advertisement/';
            // Unlink previous image if it exists
            if ($existingData && file_exists($uploadBannerDir . $existingData['ImagePath'])) 
            {
                unlink($uploadBannerDir . $existingData['ImagePath']);
            }
            header("location:ads.php");
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
    <title>TPS || Advertisement</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- plugins:css -->
    <link rel="stylesheet" href="vendors/simple-line-icons/css/simple-line-icons.css">
    <link rel="stylesheet" href="vendors/flag-icon-css/css/flag-icon.min.css">
    <link rel="stylesheet" href="vendors/css/vendor.bundle.base.css">
    <!-- endinject -->
    <!-- Plugin css for this page -->
    <link rel="stylesheet" href="vendors/select2/select2.min.css">
    <link rel="stylesheet" href="vendors/select2-bootstrap-theme/select2-bootstrap.min.css">
    <!-- End plugin css for this page -->
    <!-- inject:css -->
    <!-- endinject -->
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
                    <h3 class="page-title"> Advertisement </h3>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                            <li class="breadcrumb-item active" aria-current="page"> Advertisement</li>
                        </ol>
                    </nav>
                </div>
                <div class="row">
                    <div class="col-12 grid-margin stretch-card">
                        <div class="card">
                            <div class="card-body">
                                <h4 class="card-title" style="text-align: center;">Advertisement</h4>
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
                                    $imagePath = '';
                                    $isDisplayed = 0;

                                    if($existingData)
                                    {
                                        $imagePath = $existingData['ImagePath'];
                                        $isDisplayed = $existingData['IsDisplayed'];
                                    }
                                    ?>
                                <form class="forms-sample" method="post" enctype="multipart/form-data">
                                    <div class="form-group">
                                        <div class="custom-control custom-switch">
                                            <input type="checkbox" class="custom-control-input" name="showAd" id="adSwitch" <?php echo ($isDisplayed == 1) ? 'checked' : '';?>>
                                            <label class="custom-control-label" for="adSwitch" id="adSwitchLabel"><?php echo ($isDisplayed == 1) ? 'Hide' : 'Show'; ?> Advertisement Banner on Homepage</label>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label>Upload Ad Banner (JPG/JPEG/PNG only)</label>
                                        <input type="file" name="banner" class="form-control" id="customFile" onchange="previewImage(this)" accept="image/jpeg, image/jpg, image/png">
                                        <p class="text-muted mt-2">Banner must be less than 2MB</p>
                                        <div id="imagePreview" style="width: 150px">
                                            <?php echo !empty($imagePath) ? '<span>Current Banner</span> <img src="../Main/img/Advertisement/'.$imagePath.'" alt="Selected Image" class="img-fluid">' : '';?>
                                        </div>
                                    </div>
                                    <button type="button" id="uploadButton" class="btn btn-primary mr-2" name="submit">Upload Banner</button>
                                    <button type="button" data-toggle="modal" data-target="#confirmationModal" class="btn btn-dark mr-2" <?php echo (!$existingData) ? 'disabled' : '';?>>Reset All</button>
                                    <!-- Confirmation Modal (Reset) -->
                                    <div class="modal fade" id="confirmationModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" data-backdrop="static" data-keyboard="false">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                            <div class="modal-header">
                                                <h4 class="modal-title" id="myModalLabel">Confirmation</h4>
                                                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                            </div>
                                            <div class="modal-body">
                                                Once Reset, you will not be able to revert the changes! Do you still want to Rest? 
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                                                <button type="submit" name="reset" class="btn btn-primary" name="confirmDelete">Reset</button>
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
<script src="./js/advertisement.js"></script>
<!-- End custom js for this page -->
</body>
</html>
<?php } ?>
