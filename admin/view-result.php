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

    <title>Student Management System|||View Results</title>
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
                    <h3 class="page-title">View Results</h3>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                            <li class="breadcrumb-item active" aria-current="page">View Results</li>
                        </ol>
                    </nav>
                </div>
                <div class="row">
                    <div class="col-md-12 grid-margin stretch-card">
                        <div class="card">
                            <div class="card-body">
                                <div class="d-sm-flex align-items-center mb-4">
                                    <h4 class="card-title mb-sm-0">View Results</h4>
                                </div>
                                
                                <!-- Filter this Form -->
                                <form method="post" class="mb-3">
                                    <div class="form-row">
                                        <!-- Select Session -->
                                        <div class="form-group col-md-4">
                                            <label for="session">Select Session:</label>
                                            <select name="session" id="session" class="form-control">
                                                <?php

                                                // Fetch session names and IDs
                                                $sqlSessions = "SELECT session_id, session_name FROM tblsessions WHERE IsDeleted = 0";
                                                $querySessions = $dbh->prepare($sqlSessions);
                                                $querySessions->execute();
                                                $Sessions = $querySessions->fetchAll(PDO::FETCH_ASSOC);

                                                foreach ($Sessions as $Session) 
                                                {
                                                    echo "<option value='" . $Session['session_id'] . "'>" . $Session['session_name'] . "</option>";
                                                }
                                                ?>
                                            </select>
                                            
                                        </div>

                                        <!-- Select Class -->
                                        <div class="form-group col-md-4">
                                            <label for="class">Select Class:</label>
                                            <select name="class" id="class" class="form-control">
                                                <?php

                                                // Fetch class names and IDs
                                                $sqlClassNames = "SELECT ID, ClassName FROM tblclass WHERE IsDeleted = 0";
                                                $queryClassNames = $dbh->prepare($sqlClassNames);
                                                $queryClassNames->execute();
                                                $classes = $queryClassNames->fetchAll(PDO::FETCH_ASSOC);

                                                foreach ($classes as $class) 
                                                {
                                                    echo "<option value='" . $class['ID'] . "'>" . $class['ClassName'] . "</option>";
                                                }
                                                ?>
                                            </select>
                                        </div>

                                        <!-- Select Exam -->
                                        <div class="form-group col-md-4">
                                            <label for="exam">Select Exam:</label>
                                            <select name="exam" id="exam" class="form-control">
                                                <?php

                                                // Fetch exam names and IDs
                                                $sqlExam = "SELECT * FROM tblexamination WHERE IsDeleted = 0";
                                                $queryExam = $dbh->prepare($sqlExam);
                                                $queryExam->execute();
                                                $exams = $queryExam->fetchAll(PDO::FETCH_ASSOC);

                                                foreach ($exams as $exam) 
                                                {
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
                                        $selectedSession = $_POST['session'];
                                        $selectedClass = $_POST['class'];
                                        $selectedExamID = $_POST['exam'];

                                        $sqlFilteredReports = "SELECT DISTINCT StudentName, ExamName, ClassName, ExamSession, ExamSession FROM tblreports WHERE ClassName = :class AND ExamName = :exam AND ExamSession = :SelectedSession AND IsDeleted = 0";
                                        $queryFilteredReports = $dbh->prepare($sqlFilteredReports);
                                        $queryFilteredReports->bindParam(':class', $selectedClass, PDO::PARAM_STR);
                                        $queryFilteredReports->bindParam(':exam', $selectedExamID, PDO::PARAM_STR);
                                        $queryFilteredReports->bindParam(':SelectedSession', $selectedSession, PDO::PARAM_STR);
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
                                        // $querySelectedExamName->bindParam(':sessionID', , PDO::PARAM_STR);
                                        $querySelectedExamName->execute();
                                        $filteredExamName = $querySelectedExamName->fetch(PDO::FETCH_ASSOC);

                                        // Fetch SessionName from tblsessions
                                        $sqlSelectedSessionName = "SELECT * FROM tblsessions WHERE session_id = :selectedSession AND IsDeleted = 0";
                                        $querySelectedSessionName = $dbh->prepare($sqlSelectedSessionName);
                                        $querySelectedSessionName->bindParam(':selectedSession', $selectedSession, PDO::PARAM_STR);
                                        $querySelectedSessionName->execute();
                                        $filteredSessionName = $querySelectedSessionName->fetch(PDO::FETCH_ASSOC);
                                        
                                        // Check if Result is published
                                        // $checkResultPublishedSql = "SELECT IsResultPublished, session_id FROM tblexamination 
                                        //                                     WHERE ID = :selectedExamID 
                                        //                                     AND IsResultPublished = 1
                                        //                                     AND session_id = :selectedSession
                                        //                                     AND IsDeleted = 0";
                                        // $checkResultPublishedQuery = $dbh->prepare($checkResultPublishedSql);
                                        // $checkResultPublishedQuery->bindParam(':selectedExamID', $selectedExamID, PDO::PARAM_STR);
                                        // $checkResultPublishedQuery->bindParam(':selectedSession', $selectedSession, PDO::PARAM_STR);
                                        // $checkResultPublishedQuery->execute();
                                        // $publishedResult = $checkResultPublishedQuery->fetch(PDO::FETCH_ASSOC);
                                        
                                        // if (!empty($filteredReports) && $publishedResult) 
                                        if (!empty($filteredReports)) 
                                        {
                                            // Display message indicating the filtered results
                                            echo "<div class='d-flex justify-content-between align-items-center'>";
                                            echo "<strong class=''>Showing results for <span class='text-dark'>Class: " . htmlspecialchars($filteredClassName['ClassName']) . "</span>, <span class='text-dark'>Exam: " . htmlspecialchars($filteredExamName['ExamName']) . "</span> and <span class='text-dark'>Session: " . htmlspecialchars($filteredSessionName['session_name']) . "</span></strong>";
                                            echo "<button class='btn btn-info' onclick='printAllReports()'>Print All</button>";
                                            echo "</div>";
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
                                                echo "<button class='btn btn-info' onclick='printReportDetails(\"view-report-details.php?className=" . urlencode($report['ClassName']) . "&examName=" . urlencode($report['ExamName']) . "&studentName=" . urlencode($report['StudentName']) . "&examSession=" . urlencode($report['ExamSession']) . "\")'>Print</button>";
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
                                            echo "<strong>No Record found or the Result is not published for <span class='text-danger'>Class: " . htmlspecialchars($filteredClassName['ClassName']) . "</span>, <span class='text-danger'>Exam: " . htmlspecialchars($filteredExamName['ExamName']) . "</span> and <span class='text-danger'>Session: " . htmlspecialchars($filteredSessionName['session_name']) . "</span></strong>";
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
<script>
    function printReportDetails(url) {
        var newWindow = window.open(url, '_blank');
        newWindow.print();
    }
    function printAllReports() {
        <?php
        foreach ($filteredReports as $report) {
            echo "printReportDetails(\"print-all-reports.php?className=" . urlencode($report['ClassName']) . "&examName=" . urlencode($report['ExamName']) ."&examSession=" . urlencode($report['ExamSession']) . "\");";
        }
        ?>
    }
</script>
<!-- End custom js for this page -->
</body>
</html>
<?php
}
?>
