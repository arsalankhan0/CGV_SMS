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

    // Code for deletion
    if (isset($_POST['confirmDelete'])) 
    {
        $rid = intval($_POST['studentID']);

        try
        {
            // Check if there are any associated records in tblreports
            $checkReportSql = "SELECT COUNT(*) FROM tblreports WHERE StudentName = :rid";
            $checkReportQuery = $dbh->prepare($checkReportSql);
            $checkReportQuery->bindParam(':rid', $rid, PDO::PARAM_STR);
            $checkReportQuery->execute();
            $hasReport = $checkReportQuery->fetchColumn();

            if ($hasReport > 0) 
            {
                $dangerAlert = true;
                $msg = "Student cannot be deleted as there are associated records in the student's report!";
            }
            else
            {
                // Check whether the record is from tblstudenthistory or tblstudent
                $checkHistorySql = "SELECT COUNT(*) FROM tblstudenthistory WHERE ID = :rid AND IsDeleted = 0";
                $checkHistoryQuery = $dbh->prepare($checkHistorySql);
                $checkHistoryQuery->bindParam(':rid', $rid, PDO::PARAM_STR);
                $checkHistoryQuery->execute();
                $isHistoryRecord = $checkHistoryQuery->fetchColumn();

                if ($isHistoryRecord) 
                {
                    $sql = "UPDATE tblstudenthistory SET IsDeleted = 1 WHERE ID = :rid";
                } 
                else 
                {
                    $sql = "UPDATE tblstudent SET IsDeleted = 1 WHERE ID = :rid";
                }

                $query = $dbh->prepare($sql);
                $query->bindParam(':rid', $rid, PDO::PARAM_STR);
                $query->execute();
                
                $successAlert = true;
                $msg = "Student deleted successfully.";
            }
        }
        catch(PDOException $e)
        {
            $dangerAlert = true;
            $msg = "Ops! Something went wrong while deleting the student.";
            echo "<script>console.error('Error:---> " . $e->getMessage() . "');</script>";
        }

    }
     // Fetch sessions from tblsessions
    $sessionSql = "SELECT session_id, session_name FROM tblsessions WHERE IsDeleted = 0";
    $sessionQuery = $dbh->prepare($sessionSql);
    $sessionQuery->execute();
    $sessions = $sessionQuery->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>

    <title>TPS || Manage Students</title>
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
    <?php include_once('includes/header.php'); ?>
    <!-- partial -->
    <div class="container-fluid page-body-wrapper">
        <!-- partial:partials/_sidebar.html -->
        <?php include_once('includes/sidebar.php'); ?>
        <!-- partial -->
        <div class="main-panel">
            <div class="content-wrapper">
                <div class="page-header">
                    <h3 class="page-title"> Manage Students </h3>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                            <li class="breadcrumb-item active" aria-current="page"> Manage Students</li>
                        </ol>
                    </nav>
                </div>
                <div class="row">
                    <div class="col-md-12 grid-margin stretch-card">
                        <div class="card">
                            <div class="card-body">
                                <div class="d-sm-flex align-items-center mb-4">
                                    <h4 class="card-title mb-sm-0 mr-2">Manage Students</h4>
                                    <div class="d-flex justify-content-center align-items-center">
                                
                                        <select name="session" class="form-control" id="session" onchange="getSelectedSessionStudents()">
                                            <?php
                                            // Fetch active session from tblsessions
                                            $activeSessionSql = "SELECT session_id FROM tblsessions WHERE is_active = 1 AND IsDeleted = 0";
                                            $activeSessionQuery = $dbh->prepare($activeSessionSql);
                                            $activeSessionQuery->execute();
                                            $activeSession = $activeSessionQuery->fetch(PDO::FETCH_COLUMN);

                                            // Get the current active session
                                            $currentActiveSessionID = $activeSession;

                                            foreach ($sessions as $session) 
                                            {
                                                $selected = ($currentActiveSessionID == $session['session_id']) ? 'selected' : '';
                                                echo "<option value='" . $session['session_id'] . "' $selected>" . htmlentities($session['session_name']) . "</option>";
                                            }
                                            ?>
                                        </select>
                                        
                                    </div>
                                </div>
                                <div class="d-flex justify-content-center align-items-center">
                                    <select name="class" class="form-control" id="class" onchange="getSelectedSessionStudents()">
                                        <?php
                                            $classSql = "SELECT ID, ClassName FROM tblclass WHERE IsDeleted = 0";
                                            $classQuery = $dbh->prepare($classSql);
                                            $classQuery->execute();
                                            $classes = $classQuery->fetchAll(PDO::FETCH_ASSOC);
                                            foreach ($classes as $class) {
                                                echo "<option value='" . $class['ID'] . "'>" . htmlentities($class['ClassName']) . "</option>";
                                            }
                                        ?>
                                    </select>
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
                                <div id="student-list-container">
                                    
                                    <!-- TABLE WILL BE DYNAMICALLY POPULATED BASED ON SELECTED SESSION -->
                                </div>
                                
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
<script src="../Employee/js/manageStudents.js"></script>

<!-- End custom js for this page -->
</body>
</html>
<?php 
} 
?>
