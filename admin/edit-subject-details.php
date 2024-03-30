<?php
session_start();
error_reporting(0);
include('includes/dbconnection.php');

if (strlen($_SESSION['sturecmsaid']) == 0) 
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
        $eid = $_GET['editid'];

        if (isset($_POST['submit'])) 
        {
            $classes = isset($_POST['classes']) ? $_POST['classes'] : [];

            // Fetch IDs of selected classes
            $selectedClassIds = [];
            foreach ($classes as $className) 
            {
                $classSql = "SELECT ID FROM tblclass WHERE ID = :className";
                $classQuery = $dbh->prepare($classSql);
                $classQuery->bindParam(':className', $className, PDO::PARAM_STR);
                $classQuery->execute();
                $classId = $classQuery->fetchColumn();

                if ($classId) 
                {
                    $selectedClassIds[] = $classId;
                }
            }                

            $cName = implode(",", $selectedClassIds);
            $isOptional = isset($_POST['isOptional']) ? ($_POST['isOptional'] == 'yes' ? 1 : 0) : 0;

            $sql = "UPDATE tblsubjects SET ClassName=:cName, IsOptional=:optional WHERE ID=:eid";
            $query = $dbh->prepare($sql);
            $query->bindParam(':cName', $cName, PDO::PARAM_STR);
            $query->bindParam(':optional', $isOptional, PDO::PARAM_INT);
            $query->bindParam(':eid', $eid, PDO::PARAM_STR);

            $query->execute();

            $successAlert = true;
            $msg = "Subject has been updated successfully.";
        } 
    } 
    catch (PDOException $e) 
    {
        $dangerAlert = true;
        $msg = "Ops! An error occurred.";
        echo "<script>console.error('Error:---> ".$e->getMessage()."');</script>";
    }

    // Fetching subject details
    $subjectDetailsSql = "SELECT SubjectName, ClassName, IsOptional FROM tblsubjects WHERE ID = :eid";
    $subjectDetailsQuery = $dbh->prepare($subjectDetailsSql);
    $subjectDetailsQuery->bindParam(':eid', $eid, PDO::PARAM_STR);
    $subjectDetailsQuery->execute();
    $subjectDetailsRow = $subjectDetailsQuery->fetch(PDO::FETCH_ASSOC);
            
    $selectedClasses = explode(",", $subjectDetailsRow['ClassName']);?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <title>Tibetan Public School || Update Subject</title>
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
    </head>
    <body>
        <div class="container-scroller">
            <!-- partial:partials/_navbar.html -->
            <?php 
            include_once('includes/header.php');
            ?>
            <!-- partial -->
            <div class="container-fluid page-body-wrapper">
            <!-- partial:partials/_sidebar.html -->
            <?php include_once('includes/sidebar.php');?>
            <!-- partial -->
            <div class="main-panel">
                <div class="content-wrapper">
                    <div class="page-header">
                        <h3 class="page-title"> Update <?php echo $subjectDetailsRow['SubjectName']; ?></span> Subject </h3>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                                <li class="breadcrumb-item active" aria-current="page"> Update Subject</li>
                            </ol>
                        </nav>
                    </div>
                    <div class="row">
                        <div class="col-12 grid-margin stretch-card">
                            <div class="card">
                                <div class="card-body">
                                    <h4 class="card-title" style="text-align: center;">Update Subject</h4>
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

                                    <form class="forms-sample" id="form" method="post">

                                        <div class="form-group">
                                            <label for="exampleFormControlSelect2">Assign Classes to <span id="subject-name"><?php echo $subjectDetailsRow['SubjectName']; ?></span> subject</label>
                                            <select multiple="multiple" name="classes[]" class="js-example-basic-multiple w-100">
                                                <?php
                                                $classSql = "SELECT ID, ClassName FROM tblclass WHERE IsDeleted = 0";
                                                $classQuery = $dbh->prepare($classSql);
                                                $classQuery->execute();
                                                $classResults = $classQuery->fetchAll(PDO::FETCH_ASSOC);

                                                foreach ($classResults as $class) 
                                                {
                                                    $selected = in_array($class['ID'], $selectedClasses) ? 'selected' : '';
                                                    echo "<option value='" . htmlentities($class['ID']) . "' $selected>" . htmlentities($class['ClassName']) . "</option>";
                                                }
                                                ?>
                                            </select>
                                        </div>

                                        <div class="form-group">
                                            <label>Is this subject Optional?</label>
                                            <div class="d-flex align-items-center my-4">
                                                <div class="form-check-inline d-flex mr-4">
                                                    <input class="form-check-input" type="radio" name="isOptional" id="optionalYes" value="yes" <?php echo ($subjectDetailsRow['IsOptional'] == 1) ? 'checked' : ''; ?>>
                                                    <label class="form-check-label" for="optionalYes">Yes</label>
                                                </div>
                                                <div class="form-check-inline d-flex">
                                                    <input class="form-check-input" type="radio" name="isOptional" id="optionalNo" value="no" <?php echo ($subjectDetailsRow['IsOptional'] != 1) ? 'checked' : ''; ?>>
                                                    <label class="form-check-label" for="optionalNo">No</label>
                                                </div>
                                            </div>
                                        </div>

                                        <button type="button" class="btn btn-primary mr-2" data-toggle="modal" data-target="#confirmationModal">Update</button>

                                        <!-- Confirmation Modal (Update) -->
                                        <div class="modal fade" id="confirmationModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" data-backdrop="static" data-keyboard="false">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                <div class="modal-header">
                                                    <h4 class="modal-title" id="myModalLabel">Confirmation</h4>
                                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                                </div>
                                                <div class="modal-body">
                                                    Are you sure you want to update <?php echo $subjectDetailsRow['SubjectName']; ?> Subject?
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                                                    <button type="submit" class="btn btn-primary" id="submit" name="submit">Update</button>
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
    <!-- End custom js for this page -->
    <script src="./js/dataBinding.js"></script>
    <script src="./js/manageAlert.js"></script>
  </body>
</html><?php }  ?>
