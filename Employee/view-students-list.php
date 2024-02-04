<?php
session_start();
// error_reporting(0);
include('includes/dbconnection.php');

if (strlen($_SESSION['sturecmsEMPid'] == 0)) {
    header('location:logout.php');
} else {
    // Code for deletion
    if (isset($_GET['delid'])) {
        $rid = intval($_GET['delid']);
        $sql = "UPDATE tblstudent SET IsDeleted = 1 WHERE ID=:rid";
        $query = $dbh->prepare($sql);
        $query->bindParam(':rid', $rid, PDO::PARAM_STR);
        $query->execute();
        echo "<script>alert('Data deleted');</script>";
        echo "<script>window.location.href = 'manage-students.php'</script>";
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>

    <title>Student Management System|||View Students</title>
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
                    <h3 class="page-title">View Students Report</h3>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                            <li class="breadcrumb-item active" aria-current="page">View Students Report</li>
                        </ol>
                    </nav>
                </div>
                <div class="row">
                    <div class="col-md-12 grid-margin stretch-card">
                        <div class="card">
                            <div class="card-body">
                                <div class="d-sm-flex align-items-center mb-4">
                                    <h4 class="card-title mb-sm-0">View Students Report</h4>
                                </div>
                                
                                <!-- Filter Form -->
                                <form method="post">
                                    <div class="form-group">
                                        <label for="class">Select Class:</label>
                                        <select name="class" id="class" class="form-control">
                                            <?php
                                            $sqlClasses = "SELECT DISTINCT ClassName FROM tblreports WHERE IsDeleted = 0";
                                            $queryClasses = $dbh->prepare($sqlClasses);
                                            $queryClasses->execute();
                                            $classes = $queryClasses->fetchAll(PDO::FETCH_COLUMN);
                                            
                                            foreach ($classes as $class) {
                                                echo "<option value='" . $class . "'>" . $class . "</option>";
                                            }
                                            ?>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label for="section">Select Section:</label>
                                        <select name="section" id="section" class="form-control">
                                            <?php
                                                $sqlSections = "SELECT DISTINCT ClassName FROM tblreports WHERE IsDeleted = 0";
                                                $querySections = $dbh->prepare($sqlSections);
                                                $querySections->execute();
                                                $sections = $querySections->fetchAll(PDO::FETCH_COLUMN);
                                                
                                                foreach ($sections as $section) 
                                                {
                                                    echo "<option value='" . $section . "'>" . $section . "</option>";
                                                }
                                            ?>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label for="section">Select Exam:</label>
                                        <select name="exam" id="exam" class="form-control">
                                            <?php
                                                $sqlExam = "SELECT DISTINCT ExamName FROM tblreports WHERE IsDeleted = 0";
                                                $queryExam = $dbh->prepare($sqlExam);
                                                $queryExam->execute();
                                                $Exams = $queryExam->fetchAll(PDO::FETCH_COLUMN);
                                                
                                                foreach ($Exams as $Exam) {
                                                    echo "<option value='" . $Exam . "'>" . $Exam . "</option>";
                                                }
                                            ?>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label for="session">Select Session:</label>
                                        <select name="session" id="session" class="form-control">
                                            <?php
                                            $sqlSessions = "SELECT DISTINCT ExamSession FROM tblreports WHERE IsDeleted = 0";
                                            $querySessions = $dbh->prepare($sqlSessions);
                                            $querySessions->execute();
                                            $sessions = $querySessions->fetchAll(PDO::FETCH_COLUMN);
                                            
                                            foreach ($sessions as $session) {
                                                echo "<option value='" . $session . "'>" . $session . "</option>";
                                            }
                                            ?>
                                        </select>
                                    </div>
                                    <button type="submit" name="filter" class="btn btn-primary">Filter</button>
                                </form>
                                
                                <?php
                                if (isset($_POST['filter'])) {
                                    $selectedClass = $_POST['class'];
                                    $selectedSection = $_POST['section'];
                                    $selectedExam = $_POST['exam'];
                                    $selectedSession = $_POST['session'];

                                    $sqlFilteredReports = "SELECT DISTINCT StudentName, ExamName, ClassName, ExamSession FROM tblreports WHERE ClassName = :class AND ClassName = :section AND ExamName = :exam AND ExamSession = :session AND IsDeleted = 0";
                                    $queryFilteredReports = $dbh->prepare($sqlFilteredReports);
                                    $queryFilteredReports->bindParam(':class', $selectedClass, PDO::PARAM_STR);
                                    $queryFilteredReports->bindParam(':section', $selectedSection, PDO::PARAM_STR);
                                    $queryFilteredReports->bindParam(':exam', $selectedExam, PDO::PARAM_STR);
                                    $queryFilteredReports->bindParam(':session', $selectedSession, PDO::PARAM_STR);
                                    $queryFilteredReports->execute();
                                    $filteredReports = $queryFilteredReports->fetchAll(PDO::FETCH_ASSOC);
                                    
                                    if (!empty($filteredReports)) 
                                    {
                                        echo "<div class='table-responsive border rounded p-1'>";
                                        echo "<table class='table'>";
                                        echo "<thead>";
                                        echo "<tr>";
                                        echo "<th class='font-weight-bold'>S.No</th>";
                                        echo "<th class='font-weight-bold'>Student Name</th>";
                                        echo "<th class='font-weight-bold'>Class</th>";
                                        echo "<th class='font-weight-bold'>Section</th>";
                                        echo "<th class='font-weight-bold'>Action</th>";
                                        echo "</tr>";
                                        echo "</thead>";
                                        echo "<tbody>";

                                        $cnt = 1;
                                        foreach ($filteredReports as $report) 
                                        {
                                            echo "<tr>";
                                            echo "<td>" . htmlentities($cnt) . "</td>";
                                            echo "<td>" . htmlentities($report['StudentName']) . "</td>";
                                            echo "<td>" . htmlentities($report['ClassName']) . "</td>";
                                            echo "<td>" . htmlentities($report['ClassName']) . "</td>";
                                            echo "<td>";
                                            echo "<div>";
                                            echo "<a href='view-report-details.php?examSession=" . urlencode($report['ExamSession']) . "&className=" . urlencode($report['ClassName']) . "&examName=" . urlencode($report['ExamName']) . "&studentName=" . urlencode($report['StudentName']) . "' title='View Report'><i class='icon-eye'></i></a>";
                                            echo "</div>";
                                            echo "</td>";
                                            echo "</tr>";
                                            $cnt = $cnt + 1;
                                        }

                                        echo "</tbody>";
                                        echo "</table>";
                                        echo "</div>";
                                    } 
                                    else 
                                    {
                                        echo "<p>No results found for the selected filter.</p>";
                                    }
                                }
                                ?>
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
<!-- End custom js for this page -->
</body>
</html>
<?php
}
?>
