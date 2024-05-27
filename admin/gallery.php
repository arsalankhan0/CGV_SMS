<?php
session_start();
error_reporting(0);
include('includes/dbconnection.php');
if (strlen($_SESSION['sturecmsaid'])==0)
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
        if (isset($_POST['submit'])) 
        {
            $existingImages = array();
            $uploadedImagesCount = count($_FILES["images"]["name"]);

            if ($uploadedImagesCount > 6) 
            {
                $dangerAlert = true;
                $msg = "You can only upload up to 6 images at a time.";
            } 
            else 
            {
                foreach ($_FILES["images"]["name"] as $key => $value) 
                {
                    $originalImageName = $_FILES["images"]["name"][$key];
                    $image = $originalImageName;

                    // Check if the file is empty
                    if ($_FILES["images"]["size"][$key] == 0) 
                    {
                        $dangerAlert = true;
                        $msg = "Please upload a valid image file!";
                        break;
                    }

                    // Check file size
                    $fileSize = $_FILES["images"]["size"][$key]; // Size in bytes
                    $maxFileSize = 1024 * 1024;

                    if ($fileSize > $maxFileSize) 
                    {
                        $dangerAlert = true;
                        $msg = "The file '" . $originalImageName . "' is larger than 1 MB. Please upload images smaller than 1 MB.";
                        break;
                    }
                    
                    $ret = "SELECT imgPath FROM tblgallery WHERE imgPath = :imgPath";
                    $query = $dbh->prepare($ret);
                    $query->bindParam(':imgPath', $image, PDO::PARAM_STR);
                    $query->execute();

                    if ($query->rowCount() > 0) 
                    {
                        $existingImages[] = $originalImageName;
                    }
                }

                if (!empty($existingImages)) 
                {
                    $dangerAlert = true;
                    $msg = "The image <strong>(" . implode(", ", $existingImages) . ")</strong> already exists in the gallery!";
                } 
                else 
                {
                    foreach ($_FILES["images"]["name"] as $key => $value) 
                    {
                        $originalImageName = $_FILES["images"]["name"][$key];
                        $image = $originalImageName;

                        // Move the uploaded image to a specific folder
                        $tmpFilePath = $_FILES["images"]["tmp_name"][$key];
                        $imagePath = "gallery/" . $originalImageName;
                        move_uploaded_file($tmpFilePath, $imagePath);

                       // Insert image path into tblgallery only if it's not empty
                        if (!empty($image)) 
                        {
                            $sql = "INSERT INTO tblgallery (imgPath) VALUES (:imgPath)";
                            $query = $dbh->prepare($sql);
                            $query->bindParam(':imgPath', $image, PDO::PARAM_STR);
                            $query->execute();

                            $lastInsertId = $dbh->lastInsertId();

                            if ($lastInsertId > 0) 
                            {
                                $successAlert = true;
                                $msg = "Images uploaded successfully.";
                            } 
                            else 
                            {
                                $dangerAlert = true;
                                $msg = "Something went wrong! Please try again.";
                            }
                        }
                    }
                }
            }
        }
    } 
    catch (PDOException $e) 
    {
        $dangerAlert = true;
        $msg = "Ops! An error occurred while uploading images.";
        echo "<script>console.error('Error:---> " . $e->getMessage() . "');</script>";
    }


?>
<!DOCTYPE html>
<html lang="en">
<head>

    <title>TPS || Gallery</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- plugins:css -->
    <link rel="stylesheet" href="vendors/simple-line-icons/css/simple-line-icons.css">
    <!-- endinject -->
    <!-- Plugin css for this page -->
    <link rel="stylesheet" href="vendors/select2/select2.min.css">
    <link rel="stylesheet" href="vendors/select2-bootstrap-theme/select2-bootstrap.min.css">
    <!-- End plugin css for this page -->
    <!-- Layout styles -->
    <link rel="stylesheet" href="./css/style.css" />
    <link rel="stylesheet" href="./css/customImgSelector.css" />
    </head>
    <body>
        <div class="container-scroller">
        <!-- partial:partials/_navbar.html -->
        <?php include_once('includes/header.php');?>
        <!-- partial -->
        <div class="container-fluid page-body-wrapper">
            <!-- partial:partials/_sidebar.html -->
        <?php include_once('includes/sidebar.php');?>
            <!-- partial -->
            <div class="main-panel">
            <div class="content-wrapper">
                <div class="page-header">
                <h3 class="page-title"> Gallery </h3>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                    <li class="breadcrumb-item active" aria-current="page"> Gallery</li>
                    </ol>
                </nav>
                </div>
                <div class="row">
            
                <div class="col-12 grid-margin stretch-card">
                    <div class="card">
                    <div class="card-body">
                        <h4 class="card-title" style="text-align: center;">Gallery</h4>
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

                    <form class="forms-sample" method="post" id="my-form" enctype="multipart/form-data">
                        <!-- Image Upload -->
                        <div class="multiple-uploader" id="multiple-uploader">
                            <div class="mup-msg">
                                <span class="mup-main-msg">click to upload images.</span>
                                <span class="mup-msg" id="max-upload-number"></span>
                            </div>
                        </div>  
                        <div class="d-flex justify-content-end ">
                            <button type="button" class="btn btn-primary mr-1" id="uploadBtn" data-toggle="modal" data-target="#confirmationModal">Upload</button>
                            <a href="manage-gallery.php" class="btn btn-info">Manage Gallery</a>
                        </div>
                        <!-- Confirmation Modal (Update) -->
                        <div class="modal fade" id="confirmationModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" data-backdrop="static" data-keyboard="false">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h4 class="modal-title" id="myModalLabel">Confirmation</h4>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                    </div>
                                    <div class="modal-body">
                                        Are you sure you want to Upload these Images in gallery?
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                                        <button type="submit" class="btn btn-primary" name="submit">Upload</button>
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
         <?php include_once('includes/footer.php');?>
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
    <script src="./js/uploadImage.js"></script>
    <!-- End custom js for this page -->
  </body>
</html><?php }  ?>