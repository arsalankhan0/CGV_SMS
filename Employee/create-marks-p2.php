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
    if (isset($_SESSION['classIDs']) && isset($_SESSION['examName']) && isset($_SESSION['sessionYear']))
    {
        try 
        {
            if (isset($_POST['submit'])) 
            {
                $studentName = filter_var($_POST['student'], FILTER_VALIDATE_INT);

                if (empty($studentName)) 
                {
                    echo '<script>alert("Please select a valid student!")</script>';
                } 
                else 
                {
                    $_SESSION['studentName'] = $studentName;
                    
                    echo "<script>window.location.href ='create-marks-p3.php'</script>";
                }
            }
        } 
        catch (PDOException $e) 
        {
            echo '<script>alert("Ops! An Error occurred.")</script>';
            // error_log($e->getMessage()); //-->This is only for debugging purpose
        }
    }
    else
    {
        header("Location:create-marks.php");
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
    <?php include_once('includes/header.php'); ?>
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
                                <h4 class="card-title" style="text-align: center;">Create Student Report For <strong><?php 
                                    $sql = "SELECT * FROM tblexamination WHERE ID = ". $_SESSION['examName'] ." AND IsDeleted = 0";
                                    $query = $dbh->prepare($sql);
                                    $query->execute();
                                    $examinations = $query->fetchAll(PDO::FETCH_ASSOC);

                                    foreach ($examinations as $exam) 
                                    {
                                        echo htmlentities($exam['ExamName']);
                                    }
                                ?></strong></h4>

                                <form class="forms-sample" method="post">
                                    <div class="form-group">
                                        <label for="exampleFormControlSelect2">Select Student</label>
                                        <select name="student"
                                                class="form-control w-100">
                                            <?php
                                            $classIDs = unserialize($_SESSION['classIDs']);

                                            $sql = "SELECT * FROM tblstudent WHERE StudentClass IN (" . implode(",", $classIDs) . ") AND IsDeleted = 0";
                                            $query = $dbh->prepare($sql);
                                            $query->execute();

                                            if ($query->rowCount() > 0) 
                                            {
                                                $students = $query->fetchAll(PDO::FETCH_ASSOC);

                                                foreach ($students as $student) 
                                                {
                                                    echo "<option value='" . htmlentities($student['ID']) . "'>" . htmlentities($student['StudentName']) . "</option>";    
                                                }
                                            }
                                            else
                                            {
                                                echo "<option value='' disabled>No Student Found.</option>";
                                            }
                                            ?>
                                        </select>
                                    </div>

                                    <button type="submit" class="btn btn-primary mr-2" name="submit">Assign Marks</button>
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
