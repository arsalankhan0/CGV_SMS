<?php
session_start();
// error_reporting(0);
include('includes/dbconnection.php');

if (strlen($_SESSION['sturecmsEMPid'] == 0)) 
{
    header('location:logout.php');
} 
else 
{
    try 
    {
        // Get the active session ID
        $getSessionSql = "SELECT session_id FROM tblsessions WHERE is_active = 1 AND IsDeleted = 0";
        $sessionQuery = $dbh->prepare($getSessionSql);
        $sessionQuery->execute();
        $sessionID = $sessionQuery->fetchColumn();

        if (isset($_POST['submit'])) 
        {
            $examName = filter_var($_POST['exam'], FILTER_SANITIZE_STRING);
            $classIDs = isset($_POST['classes']) ? $_POST['classes'] : [];

            if (empty($examName) || empty($classIDs)) 
            {
                echo '<script>alert("Please select at least one option in both fields!")</script>';
            } 
            else 
            {
                $_SESSION['sessionYear'] = $sessionID;
                $_SESSION['examName'] = $examName;
                $_SESSION['classIDs'] = serialize($classIDs);

                echo "<script>window.location.href ='create-marks-p2.php'</script>";
            }
        }
    } 
    catch (PDOException $e) 
    {
        echo '<script>alert("Ops! An Error occurred.")</script>';
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Student Management System || Create Student Report</title>
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
    <?php 
    include_once('includes/header.php'); 
    ?>
    <!-- partial -->
    <div class="container-fluid page-body-wrapper">
        <!-- partial:partials/_sidebar.html -->
        <?php include_once('includes/sidebar.php'); ?>
        <!-- partial -->
        <div class="main-panel">
            <div class="content-wrapper">
                <div class="page-header">
                    <h3 class="page-title"> Create Student Report </h3>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                            <li class="breadcrumb-item active" aria-current="page"> Create Student Report </li>
                        </ol>
                    </nav>
                </div>
                <div class="row">
                    <div class="col-12 grid-margin stretch-card">
                        <div class="card">
                            <div class="card-body">
                                <h4 class="card-title" style="text-align: center;">Create Student Report</h4>
                                <form class="forms-sample" method="post">

                                    <div class="form-group">
                                        <label for="exampleFormControlSelect2">Select Classes</label>
                                        <?php
                                        $assignedClassSql = "SELECT AssignedClasses FROM tblemployees WHERE ID = :empID AND IsDeleted = 0";
                                        $assignedClassQuery = $dbh->prepare($assignedClassSql);
                                        $assignedClassQuery->bindParam(':empID', $_SESSION['sturecmsEMPid'], PDO::PARAM_INT);
                                        $assignedClassQuery->execute();
                                        $assignedClasses = $assignedClassQuery->fetchColumn();

                                        if (!empty($assignedClasses)) 
                                        {
                                            $sql = "SELECT * FROM tblclass WHERE ID IN ($assignedClasses) AND IsDeleted = 0";
                                            $query = $dbh->prepare($sql);
                                            $query->execute();

                                            if ($query->rowCount() > 0) 
                                            {
                                                echo '<select multiple="multiple" name="classes[]" class="js-example-basic-multiple w-100">';
                                                $classResults = $query->fetchAll(PDO::FETCH_ASSOC);

                                                foreach ($classResults as $class) 
                                                {
                                                    // Display only class name without sections
                                                    echo "<option value='" . htmlentities($class['ID']) . "'>" . htmlentities($class['ClassName']) . "</option>";
                                                }

                                                echo '</select>';
                                            } 
                                            else 
                                            {
                                                echo '<p>No class assigned.</p>';
                                            }
                                        } 
                                        else 
                                        {
                                            echo '<p>No class assigned.</p>';
                                        }
                                        ?>
                                    </div>
                                    <?php 
                                    if (!empty($assignedClasses)) 
                                    {
                                    ?>
                                    <div class="form-group">
                                        <label for="exampleFormControlSelect2">Select Exam</label>
                                        <select name="exam" class="form-control w-100">
                                            <?php
                                                $sql = "SELECT * FROM tblexamination WHERE IsDeleted = 0";
                                                $query = $dbh->prepare($sql);
                                                $query->execute();

                                                if ($query->rowCount() > 0) {
                                                    $examResults = $query->fetchAll(PDO::FETCH_ASSOC);

                                                    foreach ($examResults as $exam) {
                                                        echo "<option value='" . htmlentities($exam['ID']) . "'>" . htmlentities($exam['ExamName']) . "</option>";
                                                    }
                                                }
                                            ?>
                                        </select>
                                    </div>
                                    <button type="submit" class="btn btn-primary mr-2" name="submit">Next</button>
                                    <?php 
                                    } 
                                    else
                                    {
                                        echo '<p></p>';
                                    }
                                    ?>
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
<!-- End custom js for this page -->
</body>
</html>
<?php 
} 
?>
