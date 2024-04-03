<?php
session_start();
// error_reporting(0);
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

                    // Check file size
                    $fileSize = $_FILES["images"]["size"][$key]; // Size in bytes
                    $maxFileSize = 1024 * 1024;

                    if ($fileSize > $maxFileSize) {
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

                        // Insert image path into tblgallery
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

    <title>Tibetan Public School || Gallery</title>
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
    <link rel="stylesheet" href="css/style.css" />
    <style>
        #uploadedImage 
        {
            max-width: 100%;
            width: 20vh;
        }

        /* For Upload Image */
        .multiple-uploader 
        {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            border-radius: 15px;
            border: 2px dashed #858585;
            min-height: 150px;
            margin: 20px auto;
            cursor: pointer;
            width: 80%;
        }

        .mup-msg 
        {
            text-align: center;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .mup-msg span 
        {
            margin-bottom: 10px;
        }

        .mup-msg .mup-main-msg 
        {
            color: #606060;
            font-size: 1.2rem;
            font-weight: 700;
        }

        .mup-msg .mup-msg 
        {
            color: #737373;
        }

        .image-container
        {
            margin: 1rem;
            width: 120px;
            height: 120px;
            position: relative;
            cursor: auto;
            pointer-events: unset;
        }

        .image-container:before 
        {
            z-index: 3;
            content: "\2716";
            align-content: center;
            font-size: 10px;
            font-weight: bold;
            text-align: center;
            line-height: 22px;
            color: white;
            position: absolute;
            top: -5px;
            left: -5px;
            width: 20px;
            height: 20px;
            border-radius: 50%;
            background: #e50000;
            pointer-events: all;
            cursor: pointer;
        }

        .image-preview 
        {
            position: absolute;
            width: 120px;
            height: 120px;
            border-radius: 12px;
        }

        .image-size 
        {
            position: absolute;
            z-index: 1;
            height: 120px;
            width: 120px;
            backdrop-filter: blur(4px);
            font-weight: bolder;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 12px;
            opacity: 0;
            pointer-events: unset;
        }

        .image-size:hover 
        {
            opacity: 1;
        }

        .exceeded-size
        {
            position: absolute;
            z-index: 2;
            height: 120px;
            width: 120px;
            display: flex;
            font-weight: bold;
            font-size: 12px;
            text-align: center;
            align-items: center;
            justify-content: center;
            border-radius: 12px;
            color: white;
            background: rgba(255, 0, 0, 0.6);
            pointer-events: unset;
        }

    </style>
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
                        <div class="d-flex justify-content-end">
                            <button type="button" class="btn btn-primary mr-2" id="uploadBtn" data-toggle="modal" data-target="#confirmationModal">Upload</button>
                            <a href="manage-gallery.php" class="btn btn-info mx-2">Manage Gallery</a>
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