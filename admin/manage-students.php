<?php
session_start();
// error_reporting(0);
include('includes/dbconnection.php');
if (strlen($_SESSION['sturecmsaid'] == 0)) 
{
    header('location:logout.php');
} 
else 
{
    // Code for deletion
    if (isset($_GET['delid'])) 
    {
        $rid = intval($_GET['delid']);

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
        
        echo "<script>alert('Data deleted');</script>";
        echo "<script>window.location.href = 'manage-students.php'</script>";
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

    <title>Student  Management System|||Manage Students</title>
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
                                    <a href="#" class="text-dark ml-auto mb-3 mb-sm-0"> View all Students</a>
                                </div>
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
<script>
    // Function to get and display the student list for the selected session
    function getSelectedSessionStudents() {
        var selectedSession = document.getElementById("session").value;

        // AJAX request
        var xhr = new XMLHttpRequest();
        xhr.onreadystatechange = function () {
            if (xhr.readyState == 4 && xhr.status == 200) {
                // Update the content of student-list-container
                document.getElementById("student-list-container").innerHTML = xhr.responseText;
            }
        };
        
        // Use get_students.php with the selected session ID
        xhr.open("GET", "get_students.php?session_id=" + selectedSession, true);
        xhr.send();
    }

    // Call the function on page load to display the student list for the default selected session
    window.onload = getSelectedSessionStudents;
</script>

<!-- End custom js for this page -->
</body>
</html>
<?php 
} 
?>
