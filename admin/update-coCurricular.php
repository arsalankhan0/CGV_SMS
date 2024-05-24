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
    $eid = $_SESSION['sturecmsaid'];
    
    // Fetch subjects from tblsubjects
    $sqlSubjects = "SELECT * FROM tblsubjects WHERE IsCurricularSubject = 1 AND IsDeleted = 0";
    $querySubjects = $dbh->prepare($sqlSubjects);
    $querySubjects->execute();
    $subjects = $querySubjects->fetchAll(PDO::FETCH_ASSOC);
    
    $dangerAlert = false;
    $msg = "";
    try 
    {
        
        if (isset($_POST['submit'])) 
        {
            $sectionID = $_POST['section'];
            $classID = $_POST['class'];
            $sessionID = $_POST['session'];

            if (empty($classID) || empty($sectionID) || empty($sessionID)) 
            {
                $msg = "Please select at least one option in all fields!";
                $dangerAlert = true;
            } 
            else 
            {
                $_SESSION['session'] = $sessionID;
                $_SESSION['Section'] = $sectionID;
                $_SESSION['class'] = $classID;

                echo "<script>window.location.href ='update-coCurricular-marks-p2.php'</script>";
            }
        }
    } 
    catch (PDOException $e) 
    {
        $msg = "Ops! An error occurred.";
        $dangerAlert = true;
        echo "<script>console.error('Error:---> " . $e->getMessage() . "');</script>";
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>TPS || Update Student Report</title>
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
                    <h3 class="page-title"> Update Student Report </h3>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                            <li class="breadcrumb-item active" aria-current="page"> Update Student Report </li>
                        </ol>
                    </nav>
                </div>
                <div class="row">
                    <div class="col-12 grid-margin stretch-card">
                        <div class="card">
                            <div class="card-body">
                                <h4 class="card-title" style="text-align: center;">Update Co-Curricular Report of Academic Session </h4>
                                <!-- Dismissible Alert message -->
                                <?php 
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
                                <form class="forms-sample" method="post">

                                    <div class="form-group">
                                        <label for="class">Select Class</label>
                                        <select name="class" id="class" class="form-control">
                                        <?php
                                            $sql = "SELECT ID, ClassName FROM tblclass WHERE IsDeleted = 0";
                                            $query = $dbh->prepare($sql);
                                            $query->execute();
    
                                            if ($query->rowCount() > 0) 
                                            {
                                                $classResults = $query->fetchAll(PDO::FETCH_ASSOC);
                                                foreach ($classResults as $class) 
                                                {
                                                    echo "<option value='" . htmlentities($class['ID']) . "'>" . htmlentities($class['ClassName']) . "</option>";
                                                }
                                            } 
                                            else 
                                            {
                                                echo '<option disabled>No Class Available.</option>';
                                            }
                                        ?>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label for="section">Select Section</label>
                                        <select name="section" id="section" class="form-control">
                                        <?php
                                            $sql = "SELECT ID, SectionName FROM tblsections WHERE IsDeleted = 0";
                                            $query = $dbh->prepare($sql);
                                            $query->execute($assignedSectionsArray);
                                            if ($query->rowCount() > 0) 
                                            {
                                                $sectionResults = $query->fetchAll(PDO::FETCH_ASSOC);
                                                foreach ($sectionResults as $section) 
                                                {
                                                    echo "<option value='" . htmlentities($section['ID']) . "'>" . htmlentities($section['SectionName']) . "</option>";
                                                }    
                                            } 
                                            else 
                                            {
                                                echo '<option disabled>No Section Available.</option>';
                                            }
                                        ?>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label for="session">Select Session</label>
                                        <select name="session" id="session" class="form-control">
                                        <?php
                                            $sql = "SELECT session_id, session_name FROM tblsessions WHERE IsDeleted = 0";
                                            $query = $dbh->prepare($sql);
                                            $query->execute();
                                            if ($query->rowCount() > 0) 
                                            {
                                                $sessionResult = $query->fetchAll(PDO::FETCH_ASSOC);
                                                foreach ($sessionResult as $session) 
                                                {
                                                    echo "<option value='" . htmlentities($session['session_id']) . "'>" . htmlentities($session['session_name']) . "</option>";
                                                }    
                                            } 
                                            else 
                                            {
                                                echo '<option disabled>No Session Available.</option>';
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
<script src="./js/manageAlert.js"></script>
<!-- End custom js for this page -->
</body>
</html>
<?php 
} 
?>
