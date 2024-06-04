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
?>
<!DOCTYPE html>
<html lang="en">
<head>

    <title>TPS || View Results</title>
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
                                        <!-- Select Exam -->
                                        <div class="form-group col-md-4">
                                            <label for="exam">Select Exam:</label>
                                            <select name="exam" id="exam" class="form-control">
                                                <?php
                                                $examType = "Formative";
                                                // Fetch Exam names and IDs
                                                $sqlExam = "SELECT ID, ExamName FROM tblexamination WHERE ExamType = :examType AND IsDeleted = 0";
                                                $queryExam = $dbh->prepare($sqlExam);
                                                $queryExam->bindParam(':examType', $examType, PDO::PARAM_STR);
                                                $queryExam->execute();
                                                $Exams = $queryExam->fetchAll(PDO::FETCH_ASSOC);

                                                foreach ($Exams as $Exam) 
                                                {
                                                    echo "<option value='" . $Exam['ID'] . "'>" . $Exam['ExamName'] . "</option>";
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
                                    </div>
                                    <button type="submit" name="filter" class="btn btn-primary">Search</button>
                                </form>
                                    <?php
                                    if (isset($_POST['filter'])) 
                                    {
                                        $selectedSession = $_POST['session'];
                                        $selectedExam = $_POST['exam'];
                                        $selectedClass = $_POST['class'];

                                        $sqlFilteredReports = "SELECT
                                                                    s.StudentName, 
                                                                    c.ClassName, 
                                                                    r.ClassName as ClassID, 
                                                                    r.StudentName as StudentID, 
                                                                    r.ExamSession, 
                                                                    r.SubjectsJSON, 
                                                                    s.RollNo,
                                                                    ss.session_name,
                                                                    e.ExamName
                                                                FROM tblreports r
                                                                INNER JOIN tblstudent s ON r.StudentName = s.ID
                                                                INNER JOIN tblclass c ON r.ClassName = c.ID
                                                                INNER JOIN tblsessions ss ON r.ExamSession = ss.session_id
                                                                INNER JOIN tblexamination e ON JSON_CONTAINS(r.SubjectsJSON, CONCAT('{\"ExamName\":\"', :selectedExam, '\"}'))
                                                                WHERE r.ClassName = :class 
                                                                AND r.ExamSession = :selectedSession 
                                                                AND r.IsDeleted = 0
                                                                AND JSON_CONTAINS(r.SubjectsJSON, CONCAT('{\"ExamName\":\"', :selectedExam, '\"}'))
                                                                GROUP BY 
                                                                s.StudentName;";
                                        $queryFilteredReports = $dbh->prepare($sqlFilteredReports);
                                        $queryFilteredReports->bindParam(':class', $selectedClass, PDO::PARAM_STR);
                                        $queryFilteredReports->bindParam(':selectedSession', $selectedSession, PDO::PARAM_STR);
                                        $queryFilteredReports->bindParam(':selectedExam', $selectedExam, PDO::PARAM_STR);
                                        $queryFilteredReports->execute();
                                        $filteredReports = $queryFilteredReports->fetchAll(PDO::FETCH_ASSOC);
                                        
                                        
                                        if (!empty($filteredReports)) 
                                        {
                                            $filteredClassName = $filteredReports[0]['ClassName'];
                                            $filteredExamName = $filteredReports[0]['ExamName']; 
                                            $filteredSessionName = $filteredReports[0]['session_name'];
                                            
                                            echo "<div class='d-flex flex-md-row flex-column justify-content-between align-items-center'>";
                                            echo "<strong class=''>Showing results for <span class='text-dark'>Class: " . htmlspecialchars($filteredClassName) . "</span>, <span class='text-dark'>Exam: " . htmlspecialchars($filteredExamName) . "</span> and <span class='text-dark'>Session: " . htmlspecialchars($filteredSessionName) . "</span></strong>";
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
                                                $examName = '';
                                                $subjectsJSON = json_decode($report['SubjectsJSON'], true);
                                                foreach ($subjectsJSON as $subject) 
                                                {
                                                    if ($subject['ExamName'] === $selectedExam) 
                                                    {
                                                        $examName = $subject['ExamName'];
                                                        break;
                                                    }
                                                }
                                            
                                                echo "<tr>";
                                                echo "<td>" . htmlentities($cnt) . "</td>";
                                                echo "<td>" . htmlentities($report['StudentName']) . "</td>";
                                                echo "<td>" . htmlentities($report['RollNo']) . "</td>";
                                                echo "<td>";
                                                echo "<div>";
                                                echo "<button class='btn btn-info' onclick='printReportDetails(\"view-particular-report-details.php?className=" . urlencode(base64_encode($report['ClassID'])) . "&studentName=" . urlencode(base64_encode($report['StudentID'])) . "&examName=" . urlencode(base64_encode($examName)) . "&examSession=" . urlencode(base64_encode($report['ExamSession'])) . "\")'>Print</button>";
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
                                            echo "<strong>No record found!</strong>";
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
        let newWindow = window.open(url, '_blank');
        newWindow.print();
    }
    function printAllReports() {
        <?php
        foreach ($filteredReports as $report) {
            echo "printReportDetails(\"print-all-particular-reports.php?className=" . urlencode(base64_encode($report['ClassID'])) .  "&examName=" . urlencode(base64_encode($examName)) . "&examSession=" . urlencode(base64_encode($report['ExamSession'])) . "\");";
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
