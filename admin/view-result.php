<?php
session_start();
error_reporting(0);
include ('includes/dbconnection.php');

if (strlen($_SESSION['sturecmsaid'] == 0)) {
    header('location:logout.php');
} else {
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
            <?php include_once ('includes/header.php'); ?>
            <!-- partial -->
            <div class="container-fluid page-body-wrapper">
                <!-- partial:partials/_sidebar.html -->
                <?php include_once ('includes/sidebar.php'); ?>
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
                                                <?php
                                                $examType = "Formative";
                                                $sql = "SELECT 
                                                    session_id, 
                                                    session_name,
                                                    NULL as exam_id,
                                                    NULL as exam_name,
                                                    NULL as class_id,
                                                    NULL as class_name,
                                                    NULL as section_id,
                                                    NULL as section_name
                                                FROM tblsessions
                                                WHERE IsDeleted = 0
                                            UNION ALL
                                                SELECT 
                                                    NULL as session_id, 
                                                    NULL as session_name,
                                                    ID as exam_id,
                                                    ExamName as exam_name,
                                                    NULL as class_id,
                                                    NULL as class_name,
                                                    NULL as section_id,
                                                    NULL as section_name
                                                FROM tblexamination
                                                WHERE ExamType = :examType AND IsDeleted = 0
                                            UNION ALL
                                                SELECT 
                                                    NULL as session_id, 
                                                    NULL as session_name,
                                                    NULL as exam_id,
                                                    NULL as exam_name,
                                                    ID as class_id,
                                                    ClassName as class_name,
                                                    NULL as section_id,
                                                    NULL as section_name
                                                FROM tblclass
                                                WHERE IsDeleted = 0
                                            UNION ALL
                                                SELECT 
                                                    NULL as session_id, 
                                                    NULL as session_name,
                                                    NULL as exam_id,
                                                    NULL as exam_name,
                                                    NULL as class_id,
                                                    NULL as class_name,
                                                    ID as section_id,
                                                    SectionName as section_name
                                                FROM tblsections
                                                WHERE IsDeleted = 0";

                                                $query = $dbh->prepare($sql);
                                                $query->bindParam(':examType', $examType, PDO::PARAM_STR);
                                                $query->execute();
                                                $data = $query->fetchAll(PDO::FETCH_ASSOC);
                                                ?>
                                                <!-- Select Session -->
                                                <div class="form-group col-md-3">
                                                    <label for="session">Select Session:</label>
                                                    <select name="session" id="session" class="form-control">
                                                        <?php
                                                        foreach ($data as $Session) {
                                                            if ($Session['session_id'] !== null) {
                                                                echo "<option value='" . $Session['session_id'] . "'>" . $Session['session_name'] . "</option>";
                                                            }
                                                        }
                                                        ?>
                                                    </select>
                                                </div>
                                                <!-- Select Exam -->
                                                <div class="form-group col-md-3">
                                                    <label for="exam">Select Exam:</label>
                                                    <select name="exam" id="exam" class="form-control">
                                                        <?php
                                                        foreach ($data as $Exam) {
                                                            if ($Exam['exam_id'] !== null) {
                                                                echo "<option value='" . $Exam['exam_id'] . "'>" . $Exam['exam_name'] . "</option>";
                                                            }
                                                        }
                                                        ?>
                                                    </select>
                                                </div>
                                                <!-- Select Class -->
                                                <div class="form-group col-md-3">
                                                    <label for="class">Select Class:</label>
                                                    <select name="class" id="class" class="form-control">
                                                        <?php
                                                        foreach ($data as $class) {
                                                            if ($class['class_id'] !== null) {
                                                                echo "<option value='" . $class['class_id'] . "'>" . $class['class_name'] . "</option>";
                                                            }
                                                        }
                                                        ?>
                                                    </select>
                                                </div>
                                                <!-- Select Section -->
                                                <div class="form-group col-md-3">
                                                    <label for="section">Select Section:</label>
                                                    <select name="section" id="section" class="form-control">
                                                        <?php
                                                        foreach ($data as $section) {
                                                            if ($section['section_id'] !== null) {
                                                                echo "<option value='" . $section['section_id'] . "'>" . $section['section_name'] . "</option>";
                                                            }
                                                        }
                                                        ?>
                                                    </select>
                                                </div>
                                            </div>
                                            <button type="submit" name="filter" class="btn btn-primary">Search</button>
                                        </form>
                                        <?php
                                        if (isset($_POST['filter'])) {
                                            $selectedSession = filter_input(INPUT_POST, 'session', FILTER_SANITIZE_STRING);
                                            $selectedExam = filter_input(INPUT_POST, 'exam', FILTER_SANITIZE_STRING);
                                            $selectedClass = filter_input(INPUT_POST, 'class', FILTER_SANITIZE_STRING);
                                            $selectedSection = filter_input(INPUT_POST, 'section', FILTER_SANITIZE_STRING);

                                            $sqlFilteredReports = "SELECT
                                                                        s.StudentName, 
                                                                        c.ClassName, 
                                                                        sec.SectionName, 
                                                                        r.ClassName as ClassID, 
                                                                        r.SectionName as SectionID, 
                                                                        r.StudentName as StudentID, 
                                                                        r.ExamSession, 
                                                                        r.SubjectsJSON, 
                                                                        s.RollNo,
                                                                        ss.session_name,
                                                                        e.ExamName
                                                                    FROM tblreports r
                                                                    INNER JOIN tblstudent s ON r.StudentName = s.ID
                                                                    INNER JOIN tblclass c ON r.ClassName = c.ID
                                                                    INNER JOIN tblsections sec ON r.SectionName = sec.ID
                                                                    INNER JOIN tblsessions ss ON r.ExamSession = ss.session_id
                                                                    INNER JOIN tblexamination e ON :selectedExam = e.ID
                                                                    WHERE r.ClassName = :class 
                                                                    AND r.SectionName = :section 
                                                                    AND r.ExamSession = :selectedSession 
                                                                    AND r.IsDeleted = 0
                                                                    AND JSON_CONTAINS(r.SubjectsJSON, CONCAT('{\"ExamName\":\"', :selectedExam, '\"}'))
                                                                    GROUP BY s.StudentName
                                                                    ORDER BY s.RollNo ASC;";

                                            $queryFilteredReports = $dbh->prepare($sqlFilteredReports);
                                            $queryFilteredReports->bindParam(':class', $selectedClass, PDO::PARAM_STR);
                                            $queryFilteredReports->bindParam(':section', $selectedSection, PDO::PARAM_STR);
                                            $queryFilteredReports->bindParam(':selectedSession', $selectedSession, PDO::PARAM_STR);
                                            $queryFilteredReports->bindParam(':selectedExam', $selectedExam, PDO::PARAM_STR);
                                            $queryFilteredReports->execute();


                                            $filteredReports = $queryFilteredReports->fetchAll(PDO::FETCH_ASSOC);

                                            if (!empty($filteredReports)) {
                                                $filteredClassName = $filteredReports[0]['ClassName'];
                                                $filteredExamName = $filteredReports[0]['ExamName'];
                                                $filteredSessionName = $filteredReports[0]['session_name'];
                                                $filteredSectionName = $filteredReports[0]['SectionName'];

                                                echo "<div class='d-flex flex-md-row flex-column justify-content-between align-items-center'>";
                                                echo "<strong class=''>Showing results for <span class='text-dark'>Class: " . htmlspecialchars($filteredClassName) . "</span>, <span class='text-dark'>Section: " . htmlspecialchars($filteredSectionName) . "</span>, <span class='text-dark'>Exam: " . htmlspecialchars($filteredExamName) . "</span> and <span class='text-dark'>Session: " . htmlspecialchars($filteredSessionName) . "</span></strong>";
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
                                                foreach ($filteredReports as $report) {
                                                    $examName = '';
                                                    $subjectsJSON = json_decode($report['SubjectsJSON'], true);
                                                    foreach ($subjectsJSON as $subject) {
                                                        if ($subject['ExamName'] === $selectedExam) {
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
                                            } else {
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
                    <?php include_once ('includes/footer.php'); ?>
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
                    // echo "printReportDetails(\"print-all-particular-reports.php?className=" . urlencode(base64_encode($report['ClassID'])) . "&SecName=" . urlencode(base64_encode($report['SectionID'])) . "&examName=" . urlencode(base64_encode($examName)) . "&examSession=" . urlencode(base64_encode($report['ExamSession'])) . "\");";
                    echo "printReportDetails(\"print-all-particular-reports.php?className=" . urlencode(base64_encode($report['ClassID'])) . "&SecName=" . urlencode(base64_encode($report['SectionID'])) . "&examName=" . urlencode(base64_encode($selectedExam)) . "&examSession=" . urlencode(base64_encode($report['ExamSession'])) . "\");";
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