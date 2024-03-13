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
        $dbh->beginTransaction();

        if (isset($_POST['confirmDelete'])) 
        {
            $imageID = $_POST['imgID'];

            // Fetch the image path from the database using the ID
            $sqlImagePath = "SELECT imgPath FROM tblgallery WHERE ID = :imageID";
            $stmtImagePath = $dbh->prepare($sqlImagePath);
            $stmtImagePath->bindParam(':imageID', $imageID, PDO::PARAM_STR);
            $stmtImagePath->execute();
            $imagePathToDelete = $stmtImagePath->fetchColumn();

            // Delete the image from Gallery Folder
            $imagePath = 'gallery/' . $imagePathToDelete;
            if (file_exists($imagePath)) 
            {
                unlink($imagePath);
            }

            $sqlDelete = "DELETE FROM tblgallery WHERE ID = :imageID";
            $stmtDelete = $dbh->prepare($sqlDelete);
            $stmtDelete->bindParam(':imageID', $imageID, PDO::PARAM_STR);
            $stmtDelete->execute();

            $dbh->commit(); 
            $successAlert = true;
            $msg = "Image deleted successfully.";
        }
    } 
    catch (PDOException $e) 
    {
        $dbh->rollBack(); 
        $msg = "Ops! An error occurred while deleting the image.";
        $dangerAlert = true;
        echo "<script>console.error('Error:---> " . $e->getMessage() . "');</script>";
    }

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Student  Management System || Manage Gallery</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- plugins:css -->
    <link rel="stylesheet" href="vendors/simple-line-icons/css/simple-line-icons.css">
    <link rel="stylesheet" href="vendors/flag-icon-css/css/flag-icon.min.css">
    <link rel="stylesheet" href="vendors/css/vendor.bundle.base.css">
    <!-- endinject -->
    <!-- Plugin css for this page -->
    <link rel="stylesheet" href="./vendors/daterangepicker/daterangepicker.css">
    <link rel="stylesheet" href="./vendors/chartist/chartist.min.css">
    <!-- End plugin css for this page -->
    <!-- inject:css -->
    <!-- endinject -->
    <!-- Layout styles -->
    <link rel="stylesheet" href="./css/style.css">
    <!-- End layout styles -->
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
                <h3 class="page-title"> Manage Gallery </h3>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                    <li class="breadcrumb-item active" aria-current="page"> Manage Gallery</li>
                    </ol>
                </nav>
                </div>
                <div class="row">
                <div class="col-md-12 grid-margin stretch-card">
                    <div class="card">
                    <div class="card-body">
                        <div class="d-sm-flex align-items-center mb-4">
                        <h4 class="card-title mb-sm-0">Manage Gallery</h4>
                        </div>
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
                        <div class="row">
                            <?php
                            if (isset($_GET['pageno'])) 
                            {
                                $pageno = $_GET['pageno'];
                            } 
                            else 
                            {
                                $pageno = 1;
                            }
                            $no_of_records_per_page = 10;
                            $offset = ($pageno-1) * $no_of_records_per_page;

                            $sql = "SELECT ID, imgPath FROM tblgallery ORDER BY ID DESC LIMIT $offset, $no_of_records_per_page";
                            $stmt = $dbh->prepare($sql);
                            $stmt->execute();
                            $images = $stmt->fetchAll(PDO::FETCH_ASSOC);

                            // Calculate total pages
                            $total_images_sql = "SELECT COUNT(*) FROM tblgallery";
                            $total_images_query = $dbh->prepare($total_images_sql);
                            $total_images_query->execute();
                            $total_images = $total_images_query->fetchColumn();
                            $total_pages = ceil($total_images / $no_of_records_per_page);

                            if (count($images) > 0) 
                            {
                                foreach ($images as $imagePath) 
                                {
                                    ?>
                                    <div class="col-md-12 gallery-row">
                                        <div class="row align-items-center">
                                            <div class="col-md-4">
                                                <img class="img-fluid gallery-image" src="<?php echo 'gallery/' . $imagePath['imgPath']; ?>" alt="Image"  loading="lazy">
                                            </div>
                                            <div class="col-md-4">
                                                <?php echo $imagePath['imgPath']; ?>
                                            </div>
                                            <div class="col-md-4 text-right">
                                                <a href="" onclick="setDeleteId(<?php echo $imagePath['ID'];?>)" data-toggle="modal" data-target="#confirmationModal">
                                                    <i class="icon-trash delete-icon"></i>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                    <?php
                                }
                                ?>
                                <div align="left">
                                    <ul class="pagination" >
                                        <li><a href="?pageno=1"><strong>First></strong></a></li>
                                        <li class="<?php if($pageno <= 1){ echo 'disabled'; } ?>">
                                            <a href="<?php if($pageno <= 1){ echo '#'; } else { echo "?pageno=".($pageno - 1); } ?>"><strong style="padding-left: 10px">Prev></strong></a>
                                        </li>
                                        <li class="<?php if($pageno >= $total_pages){ echo 'disabled'; } ?>">
                                            <a href="<?php if($pageno >= $total_pages){ echo '#'; } else { echo "?pageno=".($pageno + 1); } ?>"><strong style="padding-left: 10px">Next></strong></a>
                                        </li>
                                        <li><a href="?pageno=<?php echo $total_pages; ?>"><strong style="padding-left: 10px">Last</strong></a></li>
                                    </ul>
                                </div>
                                <?php
                            } 
                            else 
                            {
                                echo '<div class="col-lg-12"><h4 class="text-center">No Images to show!</h4></div>';
                            }
                            ?>
                            
                        </div>
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
        <!-- Confirmation Modal (Delete) -->
        <div class="modal fade" id="confirmationModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" data-backdrop="static" data-keyboard="false">
            <div class="modal-dialog">
                <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="myModalLabel">Confirmation</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body">
                    Are you sure you want to delete this Image from gallery?
                </div>
                <div class="modal-footer">
                    <form id="deleteForm" action="" method="post">
                    <input type="hidden" name="imgID" id="imgID">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" name="confirmDelete">Delete</button>
                    </form>
                </div>
                </div>
            </div>
        </div>


    <!-- container-scroller -->
    <!-- plugins:js -->
    <script src="vendors/js/vendor.bundle.base.js"></script>
    <script src="./js/bootstrap.js"></script>
    <!-- endinject -->
    <!-- Plugin js for this page -->
    <script src="./vendors/chart.js/Chart.min.js"></script>
    <script src="./vendors/moment/moment.min.js"></script>
    <script src="./vendors/daterangepicker/daterangepicker.js"></script>
    <script src="./vendors/chartist/chartist.min.js"></script>
    <!-- End plugin js for this page -->
    <!-- inject:js -->
    <script src="js/off-canvas.js"></script>
    <script src="js/misc.js"></script>
    <!-- endinject -->
    <!-- Custom js for this page -->
    <script src="./js/dashboard.js"></script>
    <script src="./js/manageAlert.js"></script>
    <!-- End custom js for this page -->
    <script>
        function setDeleteId(id) 
        {
            document.getElementById('imgID').value = id;
        }
    </script>
</body>
</html>
<?php 
}  
?>