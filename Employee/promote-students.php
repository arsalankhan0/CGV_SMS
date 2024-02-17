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

    // Get the active session ID and name
    $getSessionSql = "SELECT session_id, session_name FROM tblsessions WHERE is_active = 1 AND IsDeleted = 0";
    $sessionQuery = $dbh->prepare($getSessionSql);
    $sessionQuery->execute();
    $session = $sessionQuery->fetch(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>

    <title>Student Management System|||Promote Students</title>
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
                    <h3 class="page-title">Promote Students</h3>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Promote Students</li>
                        </ol>
                    </nav>
                </div>
                <div class="row">
                    <div class="col-md-12 grid-margin stretch-card">
                        <div class="card">
                            <div class="card-body">
                                <div class="d-sm-flex align-items-center justify-content-between mb-4">
                                    <h4 class="card-title mb-sm-0">Promote Students</h4>
                                    <div>
                                        Session: <span class="border border-secondary px-3 py-2"><?php echo $session['session_name']; ?></span>
                                    </div>
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
                                                $sqlClassNames = "SELECT DISTINCT ID, ClassName, Section FROM tblclass WHERE ID IN (" . implode(',', $classIDs) . ") AND IsDeleted = 0";
                                                $queryClassNames = $dbh->prepare($sqlClassNames);
                                                $queryClassNames->execute();
                                                $classes = $queryClassNames->fetchAll(PDO::FETCH_ASSOC);

                                                foreach ($classes as $class) {
                                                    echo "<option value='" . $class['ID'] . "'>" . $class['ClassName'] . "</option>";
                                                }
                                                ?>
                                            </select>
                                        </div>


                                        <!-- Select Section -->
                                        <div class="form-group col-md-6">
                                            <label for="section">Select Section:</label>
                                            <select name="section" id="section" class="form-control">
                                                <!-- Options will be dynamically populated based on selected class, using JavaScript AJAX-->
                                            </select>
                                        </div>
                                    </div>
                                    <button type="submit" name="filter" class="btn btn-primary">Search</button>
                                </form>
                                
                                <?php
                                if (isset($_POST['filter'])) 
                                {
                                    $selectedClass = $_POST['class'];
                                    $selectedSection = $_POST['section'];
                                    $activeSession = $session['session_id'];
                                
                                    // Fetch reports that match the selected class and session
                                    $sqlFilteredReports = "SELECT DISTINCT r.StudentName, r.ExamName, r.ClassName, r.ExamSession, s.StudentSection
                                                            FROM tblreports r
                                                            JOIN tblstudent s ON r.StudentName = s.ID
                                                            WHERE r.ClassName = :class AND r.ExamSession = :sessionID AND s.StudentSection = :section
                                                            AND r.IsDeleted = 0 AND s.IsDeleted = 0";
                                    $queryFilteredReports = $dbh->prepare($sqlFilteredReports);
                                    $queryFilteredReports->bindParam(':class', $selectedClass, PDO::PARAM_STR);
                                    $queryFilteredReports->bindParam(':sessionID', $activeSession, PDO::PARAM_STR);
                                    $queryFilteredReports->bindParam(':section', $selectedSection, PDO::PARAM_STR);
                                    $queryFilteredReports->execute();
                                    $filteredReports = $queryFilteredReports->fetchAll(PDO::FETCH_ASSOC);
                                
                                    // Fetch ClassName from tblclass
                                    $sqlSelectedClassName = "SELECT * FROM tblclass WHERE ID = :selectedClass AND IsDeleted = 0";
                                    $querySelectedClassName = $dbh->prepare($sqlSelectedClassName);
                                    $querySelectedClassName->bindParam(':selectedClass', $selectedClass, PDO::PARAM_STR);
                                    $querySelectedClassName->execute();
                                    $filteredClassName = $querySelectedClassName->fetch(PDO::FETCH_ASSOC);
                                
                                    if (!empty($filteredReports)) 
                                    {
                                        echo "<strong class=''>Showing results for <span class='text-dark'>Class: " . htmlspecialchars($filteredClassName['ClassName']) . ", Section: " . htmlspecialchars($selectedSection) . "</span></strong>";
                                
                                        echo "<div class='row mt-4'>";
                                        // Dropdown for classes
                                        echo "<div class='col-md-3 mb-3'>";
                                        echo "<label for='classDropdown'>Select Class:</label>";
                                        echo "<select id='classDropdown' class='form-control'>";
                                        // Fetch all classes from tblclass
                                        $sqlAllClasses = "SELECT ID, ClassName FROM tblclass WHERE IsDeleted = 0";
                                        $queryAllClasses = $dbh->prepare($sqlAllClasses);
                                        $queryAllClasses->execute();
                                        $allClasses = $queryAllClasses->fetchAll(PDO::FETCH_ASSOC);
                                        foreach ($allClasses as $class) {
                                            echo "<option value='" . $class['ID'] . "'>" . htmlspecialchars($class['ClassName']) . "</option>";
                                        }
                                        echo "</select>";
                                        echo "</div>";

                                        // Dropdown for sections (hardcoded A-F)
                                        echo "<div class='col-md-3 mb-3'>";
                                        echo "<label for='sectionDropdown'>Select Section:</label>";
                                        echo "<select id='sectionDropdown' class='form-control'>";
                                        $sections = ['A', 'B', 'C', 'D', 'E', 'F']; // You can modify this array as needed
                                        foreach ($sections as $section) {
                                            echo "<option value='" . $section . "'>" . $section . "</option>";
                                        }
                                        echo "</select>";
                                        echo "</div>";

                                        // Dropdown for sessions
                                        echo "<div class='col-md-3 mb-3'>";
                                        echo "<label for='sessionDropdown'>Select Session:</label>";
                                        echo "<select id='sessionDropdown' class='form-control'>";
                                        $sqlAllSessions = "SELECT session_id, session_name FROM tblsessions WHERE IsDeleted = 0";
                                        $queryAllSessions = $dbh->prepare($sqlAllSessions);
                                        $queryAllSessions->execute();
                                        $allSessions = $queryAllSessions->fetchAll(PDO::FETCH_ASSOC);
                                        foreach ($allSessions as $sessionOption) {
                                            echo "<option value='" . $sessionOption['session_id'] . "'>" . htmlspecialchars($sessionOption['session_name']) . "</option>";
                                        }
                                        echo "</select>";
                                        echo "</div>";

                                        // Promote button
                                        echo "<div class='col-md-3 mb-3 d-flex align-items-end'>";
                                        echo "<button class='border-0 btn btn-primary' onclick='promoteStudents()'>Promote</button>";
                                        echo "</div>";
                                        echo "</div>";

                                        echo "<div class='table-responsive border rounded p-1'>";
                                        echo "<table class='table'>";
                                        echo "<thead>";
                                        echo "<tr>";
                                        echo "<th class='font-weight-bold'>
                                                <div class='form-check m-0'>
                                                <label class='form-check-label' for='checkAll'>
                                                    <input type='checkbox' id='checkAll'> 
                                                </label>
                                                </div>
                                            </th>";
                                        echo "<th class='font-weight-bold'>S.No</th>";
                                        echo "<th class='font-weight-bold'>Student Name</th>";
                                        echo "<th class='font-weight-bold'>Roll No</th>";
                                        echo "<th class='font-weight-bold'>Result</th>";
                                        echo "</tr>";
                                        echo "</thead>";
                                        echo "<tbody>";
                                
                                        $cnt = 1;
                                        foreach ($filteredReports as $report) 
                                        {
                                            // Fetch Name and RollNo of student from tblstudent based on the StudentID
                                            $sqlStudentDetails = "SELECT StudentName, RollNo FROM tblstudent WHERE ID = :studentID AND IsDeleted = 0";
                                            $queryStudentDetails = $dbh->prepare($sqlStudentDetails);
                                            $queryStudentDetails->bindParam(':studentID', $report['StudentName'], PDO::PARAM_STR);
                                            $queryStudentDetails->execute();
                                            $studentDetails = $queryStudentDetails->fetch(PDO::FETCH_ASSOC);
                                
                                            // Check if the student passed or failed based on subjects
                                            $sqlPassOrFail = "SELECT IsPassed FROM tblreports WHERE StudentName = :studentID AND ClassName = :class AND ExamSession = :sessionID AND IsDeleted = 0";
                                            $queryPassOrFail = $dbh->prepare($sqlPassOrFail);
                                            $queryPassOrFail->bindParam(':studentID', $report['StudentName'], PDO::PARAM_STR);
                                            $queryPassOrFail->bindParam(':class', $selectedClass, PDO::PARAM_STR);
                                            $queryPassOrFail->bindParam(':sessionID', $activeSession, PDO::PARAM_STR);
                                            $queryPassOrFail->execute();
                                            $results = $queryPassOrFail->fetchAll(PDO::FETCH_COLUMN);
                                
                                            // Check if all subjects have IsPassed set to 1 (True)
                                            $overallResult = (count(array_unique($results)) == 1 && end($results) == 1) ? '<span class="text-success font-weight-bold">PASS</span>' : '<span class="text-danger font-weight-bold">FAIL</span>';
                                
                                            echo "<tr>";
                                            echo "<td>";
                                            echo "<div class='form-check mt-0'>";
                                            echo "<label class='form-check-label' for='checkbox'>";
                                            echo "<input type='checkbox' class='student-checkbox' id='checkbox' value='" . $report['StudentName'] . "'" . ($overallResult == '<span class="text-success font-weight-bold">PASS</span>' ? ' checked' : '') . ">";
                                            echo "</label>";
                                            echo "</div>";
                                            echo "</td>";
                                            echo "<td>" . htmlentities($cnt) . "</td>";
                                            echo "<td>" . htmlentities($studentDetails['StudentName']) . "</td>";
                                            echo "<td>" . htmlentities($studentDetails['RollNo']) . "</td>";
                                            echo "<td>" . $overallResult . "</td>";
                                            echo "</tr>";
                                            $cnt = $cnt + 1;
                                        }
                                
                                        echo "</tbody>";
                                        echo "</table>";
                                        echo "</div>";
                                    } 
                                    else 
                                    {
                                        echo "<strong>No results found for <span class='text-danger'>Class: " . htmlspecialchars($filteredClassName['ClassName']) . ", Section: " . htmlspecialchars($selectedSection) . "</span></strong>";
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
<script src="./js/populateSections.js"></script>
<script src="./js/promoteStudentCheckBoxes.js"></script>
<!-- End custom js for this page -->

</body>
</html>
<?php
}
?>
