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
    try 
    {
        if (isset($_POST['submit'])) 
        {
            $eid = $_GET['editid'];
            $classes = isset($_POST['classes']) ? $_POST['classes'] : [];

            if (empty($classes)) 
            {
                echo '<script>alert("Please select at least one class")</script>';
            } 
            else 
            {
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

                // Fetch selected subject types
                $subjectTypes = isset($_POST['subjectTypes']) ? $_POST['subjectTypes'] : [];

                if (empty($subjectTypes)) 
                {
                    echo '<script>alert("Please select at least one subject type")</script>';
                } 
                else 
                {
                    $cName = implode(",", $selectedClassIds);
                    $subjectTypeString = implode(",", $subjectTypes);

                    $sql = "UPDATE tblsubjects SET ClassName=:cName, SubjectType=:subjectTypes WHERE ID=:eid";
                    $query = $dbh->prepare($sql);
                    $query->bindParam(':cName', $cName, PDO::PARAM_STR);
                    $query->bindParam(':subjectTypes', $subjectTypeString, PDO::PARAM_STR);
                    $query->bindParam(':eid', $eid, PDO::PARAM_STR);

                    $query->execute();

                    echo '<script>alert("Subject has been updated.")</script>';
                    echo "<script>window.location.href ='manage-subjects.php'</script>"; 
                }
            }
        } 
        else
        {
            $eid = $_GET['editid'];

            // Fetching subject details
            $subjectDetailsSql = "SELECT SubjectName, ClassName, SubjectType FROM tblsubjects WHERE ID = :eid";
            $subjectDetailsQuery = $dbh->prepare($subjectDetailsSql);
            $subjectDetailsQuery->bindParam(':eid', $eid, PDO::PARAM_STR);
            $subjectDetailsQuery->execute();
            $subjectDetailsRow = $subjectDetailsQuery->fetch(PDO::FETCH_ASSOC);
            $selectedClasses = explode(",", $subjectDetailsRow['ClassName']);
            $selectedSubjectTypes = explode(",", $subjectDetailsRow['SubjectType']);
        }
    } 
    catch (PDOException $e) 
    {
        echo '<script>alert("Ops! An Error occurred.'. $e->getMessage() .'")</script>';
    }
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <title>Student  Management System || Update Subject</title>
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
                                    <form class="forms-sample" method="post">

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
                                            <label>Subject Type</label>
                                            <div class="checkbox-group d-flex justify-content-start">
                                                <div class="form-check mr-4">
                                                    <label class="form-check-label" for="theory">
                                                        Theory
                                                        <input class="form-check-input" type="checkbox" <?php echo in_array('theory', $selectedSubjectTypes) ? 'checked' : ''; ?> name="subjectTypes[]" value="theory" id="theory">
                                                    </label>
                                                </div>
                                                <div class="form-check mr-4">
                                                    <label class="form-check-label" for="practical">
                                                        Practical
                                                        <input class="form-check-input" type="checkbox" <?php echo in_array('practical', $selectedSubjectTypes) ? 'checked' : ''; ?> name="subjectTypes[]" value="practical" id="practical">
                                                    </label>
                                                </div>
                                                <div class="form-check mr-4">
                                                    <label class="form-check-label" for="viva">
                                                        Viva
                                                        <input class="form-check-input" type="checkbox" <?php echo in_array('viva', $selectedSubjectTypes) ? 'checked' : ''; ?> name="subjectTypes[]" value="viva" id="viva">
                                                    </label>
                                                </div>
                                            </div>
                                        </div>

                                        <button type="submit" class="btn btn-primary mr-2" name="submit">Update</button>
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
  </body>
</html><?php }  ?>
