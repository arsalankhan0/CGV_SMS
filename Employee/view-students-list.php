<?php
session_start();
error_reporting(0);
include('includes/dbconnection.php');

if (strlen($_SESSION['sturecmsEMPid'] == 0)) 
{
    header('location:logout.php');
} 
else 
{
    $eid = $_SESSION['sturecmsEMPid'];
    $sql = "SELECT * FROM tblemployees WHERE ID=:eid";
    $query = $dbh->prepare($sql);
    $query->bindParam(':eid', $eid, PDO::PARAM_STR);
    $query->execute();
    $IsAccessible = $query->fetch(PDO::FETCH_ASSOC);

    // Check if the role is "Teaching"
    if ($IsAccessible['EmpType'] != "Teaching") 
    {
        echo "<h1>You have no permission to access this page!</h1>";
        exit;
    }


?>
<!DOCTYPE html>
<html lang="en">
<head>

    <title>Student Management System|||View Students</title>
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
                                
                                <!-- Filter this Form -->
                                <form method="post" class="mb-3">
                                    <div class="form-row">
                                        <!-- Select Class -->
                                        <div class="form-group col-md-6">
                                            <label for="class">Select Class:</label>
                                            <select name="class" id="class" class="form-control">
                                                <?php
                                                $teacherID = $_SESSION['sturecmsEMPid'];

                                                // Fetch classes assigned to the teacher
                                                $sqlClasses = "SELECT DISTINCT AssignedClasses FROM tblemployees WHERE ID = :teacherID AND IsDeleted = 0";
                                                $queryClasses = $dbh->prepare($sqlClasses);
                                                $queryClasses->bindParam(':teacherID', $teacherID, PDO::PARAM_STR);
                                                $queryClasses->execute();
                                                $assignedClasses = $queryClasses->fetch(PDO::FETCH_COLUMN);

                                                $classIDs = explode(',', $assignedClasses);

                                                // Fetch class names based on class IDs
                                                $sqlClassNames = "SELECT DISTINCT ID, ClassName FROM tblclass WHERE ID IN (" . implode(',', $classIDs) . ") AND IsDeleted = 0";
                                                $queryClassNames = $dbh->prepare($sqlClassNames);
                                                $queryClassNames->execute();
                                                $classes = $queryClassNames->fetchAll(PDO::FETCH_ASSOC);

                                                foreach ($classes as $class) {
                                                    echo "<option value='" . $class['ID'] . "'>" . $class['ClassName'] . "</option>";
                                                }
                                                ?>
                                            </select>
                                        </div>


                                        <!-- Select Exam -->
                                        <div class="form-group col-md-6">
                                            <label for="exam">Select Exam:</label>
                                            <select name="exam" id="exam" class="form-control">
                                                <?php
                                                $sqlExam = "SELECT * FROM tblexamination WHERE IsDeleted = 0";
                                                $queryExam = $dbh->prepare($sqlExam);
                                                $queryExam->execute();
                                                $exams = $queryExam->fetchAll(PDO::FETCH_ASSOC);

                                                foreach ($exams as $exam) {
                                                    echo "<option value='" . $exam['ID'] . "'>" . $exam['ExamName'] . "</option>";
                                                }
                                                ?>
                                            </select>
                                        </div>
                                    </div>
                                    <button type="submit" name="filter" class="btn btn-primary">Search</button>
                                </form>
                                
                                <?php
                                if (isset($_POST['filter'])) 
                                {
                                    $selectedClass = $_POST['class'];
                                    $selectedExamID = $_POST['exam'];

                                    // Get the active session ID
                                    $getSessionSql = "SELECT session_id FROM tblsessions WHERE is_active = 1 AND IsDeleted = 0";
                                    $sessionQuery = $dbh->prepare($getSessionSql);
                                    $sessionQuery->execute();
                                    $sessionID = $sessionQuery->fetchColumn();

                                    $sqlFilteredReports = "SELECT DISTINCT StudentName, ExamName, ClassName, ExamSession FROM tblreports WHERE ClassName = :class AND ExamName = :exam AND ExamSession = :sessionID AND IsDeleted = 0";
                                    $queryFilteredReports = $dbh->prepare($sqlFilteredReports);
                                    $queryFilteredReports->bindParam(':class', $selectedClass, PDO::PARAM_STR);
                                    $queryFilteredReports->bindParam(':exam', $selectedExamID, PDO::PARAM_STR);
                                    $queryFilteredReports->bindParam(':sessionID', $sessionID, PDO::PARAM_STR);
                                    $queryFilteredReports->execute();
                                    $filteredReports = $queryFilteredReports->fetchAll(PDO::FETCH_ASSOC);

                                    // Fetch ClassName from tblclass
                                    $sqlSelectedClassName = "SELECT * FROM tblclass WHERE ID = :selectedClass AND IsDeleted = 0";
                                    $querySelectedClassName = $dbh->prepare($sqlSelectedClassName);
                                    $querySelectedClassName->bindParam(':selectedClass', $selectedClass, PDO::PARAM_STR);
                                    $querySelectedClassName->execute();
                                    $filteredClassName = $querySelectedClassName->fetch(PDO::FETCH_ASSOC);

                                    // Fetch ExamName from tblexamination
                                    $sqlSelectedExamName = "SELECT * FROM tblexamination WHERE ID = :selectedExamID AND IsDeleted = 0";
                                    $querySelectedExamName = $dbh->prepare($sqlSelectedExamName);
                                    $querySelectedExamName->bindParam(':selectedExamID', $selectedExamID, PDO::PARAM_STR);
                                    $querySelectedExamName->execute();
                                    $filteredExamName = $querySelectedExamName->fetch(PDO::FETCH_ASSOC);
                                    
                                    
                                    if (!empty($filteredReports)) 
                                    {
                                        // Display message indicating the filtered results
                                        echo "<strong class=''>Showing results for <span class='text-dark'>Class: " . htmlspecialchars($filteredClassName['ClassName']) . "</span> and <span class='text-dark'>Exam: " . htmlspecialchars($filteredExamName['ExamName']) . "</span></strong>";

                                        echo "<div class='table-responsive border rounded p-1 mt-4'>";
                                        echo "<table class='table'>";
                                        echo "<thead>";
                                        echo "<tr>";
                                        echo "<th class='font-weight-bold'>S.No</th>";
                                        echo "<th class='font-weight-bold'>Student Name</th>";
                                        echo "<th class='font-weight-bold'>Roll No</th>";
                                        echo "<th class='font-weight-bold'>Action</th>";
                                        echo "</tr>";
                                        echo "</thead>";
                                        echo "<tbody>";
                                        
                                        $cnt = 1;
                                        foreach ($filteredReports as $report) 
                                        {
                                            // Fetch Name of student from tblstudent based on the StudentID
                                            $sqlStudentDetails = "SELECT StudentName, RollNo FROM tblstudent WHERE ID = :studentID AND IsDeleted = 0";
                                            $queryStudentDetails = $dbh->prepare($sqlStudentDetails);
                                            $queryStudentDetails->bindParam(':studentID', $report['StudentName'], PDO::PARAM_STR);
                                            $queryStudentDetails->execute();
                                            $studentDetails = $queryStudentDetails->fetch(PDO::FETCH_ASSOC);
                                            
                                            echo "<tr>";
                                            echo "<td>" . htmlentities($cnt) . "</td>";
                                            echo "<td>". htmlentities($studentDetails['StudentName']) ."</td>";
                                            echo "<td>". htmlentities($studentDetails['RollNo']) ."</td>";
                                            echo "<td>";
                                            echo "<div>";
                                            echo "<a href='view-report-details.php?className=" . urlencode($report['ClassName']) . "&examName=" . urlencode($report['ExamName']) . "&studentName=" . urlencode($report['StudentName']) . "' title='View Report'><i class='icon-eye'></i></a>";
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
                                        // Display message indicating the filtered results
                                        echo "<strong>No results found for <span class='text-danger'>Class: " . htmlspecialchars($filteredClassName['ClassName']) . "</span> and <span class='text-danger'>Exam: " . htmlspecialchars($filteredExamName['ExamName']) . "</span></strong>";
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
