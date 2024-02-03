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
        if (isset($_POST['submit'])) 
        {
            $examName = filter_var($_POST['exam'], FILTER_SANITIZE_STRING);
            $classIDs = isset($_POST['classes']) ? $_POST['classes'] : [];
            $sessionYear = $_POST['session'];

            if (empty($examName) || empty($classIDs) || empty($sessionYear)) 
            {
                echo '<script>alert("Please enter Exam Name, select at least one class, and choose a session year")</script>';
            } 
            else 
            {
                $_SESSION['sessionYear'] = $sessionYear;
                $_SESSION['examName'] = $examName;
                $_SESSION['classIDs'] = serialize($classIDs);

                echo "<script>window.location.href ='create-marks-p2.php'</script>";
            }
        }
    } 
    catch (PDOException $e) 
    {
        echo '<script>alert("Ops! An Error occurred.")</script>';
        // error_log($e->getMessage()); //-->This is only for debugging purpose
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
                                        <label for="dropdownYear">Session</label>
                                        <select id="dropdownYear" class="form-control" name="session">
                                        <?php
                                            $currentYear = date('Y');
                                            $startYear = $currentYear - 3;
                                            $endYear = $currentYear + 6;

                                            for ($year = $startYear; $year <= $endYear; $year++) 
                                            {
                                                $selected = ($year == $currentYear) ? 'selected' : '';
                                                echo "<option value='$year' $selected>$year</option>";
                                            }
                                        ?>
                                        </select>
                                    </div>

                                    <div class="form-group">
                                        <label for="exampleFormControlSelect2">Select Classes</label>
                                        <select multiple="multiple" name="classes[]"
                                                class="js-example-basic-multiple w-100">
                                            <?php
                                            $sql = "SELECT * FROM tblclass WHERE IsDeleted = 0";
                                            $query = $dbh->prepare($sql);
                                            $query->execute();

                                            if ($query->rowCount() > 0) 
                                            {
                                                $classResults = $query->fetchAll(PDO::FETCH_ASSOC);

                                                foreach ($classResults as $class) 
                                                {
                                                    $classNameWithSection = $class['ClassName'] . ' ' . $class['Section'];
                                                    echo "<option value='" . htmlentities($class['ID']) . "'>" . htmlentities($classNameWithSection) . "</option>";                                                
                                                }
                                                    
                                            }
                                            ?>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label for="exampleFormControlSelect2">Select Exam</label>
                                        <select name="exam"
                                                class="form-control w-100">
                                            <?php
                                            $sql = "SELECT * FROM tblexamination WHERE IsDeleted = 0";
                                            $query = $dbh->prepare($sql);
                                            $query->execute();

                                            if ($query->rowCount() > 0) 
                                            {
                                                $examResults = $query->fetchAll(PDO::FETCH_ASSOC);

                                                foreach ($examResults as $exam) 
                                                {
                                                    echo "<option value='" . htmlentities($exam['ID']) . "'>" . htmlentities($exam['ExamName']) . "</option>";    
                                                }
                                            }
                                            ?>
                                        </select>
                                    </div>

                                    <button type="submit" class="btn btn-primary mr-2" name="submit">Next</button>
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
