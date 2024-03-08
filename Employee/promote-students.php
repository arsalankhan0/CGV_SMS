<?php
session_start();
error_reporting(0);
include('includes/dbconnection.php');

// Define permissions array
$requiredPermissions = array(
    'promote-students' => 'Promotion',
);

if (strlen($_SESSION['sturecmsEMPid']) == 0) 
{
    header('location:logout.php');
} 
else 
{
    // Check if the employee has the required permission for this file
    $eid = $_SESSION['sturecmsEMPid'];
    $sql = "SELECT * FROM tblemployees WHERE ID=:eid";
    $query = $dbh->prepare($sql);
    $query->bindParam(':eid', $eid, PDO::PARAM_STR);
    $query->execute();
    $results = $query->fetch(PDO::FETCH_ASSOC);

    $employeeRole = $results['Role'];
    $requiredPermission = $requiredPermissions['promote-students']; 

    $sqlPermissions = "SELECT * FROM tblpermissions WHERE RoleID=:employeeRole AND Name=:requiredPermission";
    $queryPermissions = $dbh->prepare($sqlPermissions);
    $queryPermissions->bindParam(':employeeRole', $employeeRole, PDO::PARAM_STR);
    $queryPermissions->bindParam(':requiredPermission', $requiredPermission, PDO::PARAM_STR);
    $queryPermissions->execute();
    $permissions = $queryPermissions->fetch(PDO::FETCH_ASSOC);

    if (!$permissions || $permissions['UpdatePermission'] != 1) 
    {
        echo "<h1>You have no permission to access this page!</h1>";
        exit;
    }

    $successAlert = false;
    $dangerAlert = false;
    $msg = "";

    // Get the active session ID and name
    $getSessionSql = "SELECT session_id, session_name FROM tblsessions WHERE is_active = 1 AND IsDeleted = 0";
    $sessionQuery = $dbh->prepare($getSessionSql);
    $sessionQuery->execute();
    $session = $sessionQuery->fetch(PDO::FETCH_ASSOC);

try 
{
        
    // Promote selected students
    if (isset($_POST['promote'])) 
    {
        $selectedPromoteClass = $_POST['promoteClass'];
        $selectedPromoteSection = $_POST['promoteSection'];
        $selectedPromoteSession = $_POST['promoteSession'];

        // Check if the selected promotion class and session match the current active class and session
        $currentActiveClass = $filteredClassName['ClassName'];
        $currentActiveSession = $session['session_id'];

        if ($selectedPromoteClass == $currentActiveClass && $selectedPromoteSession == $currentActiveSession) 
        {
            $dangerAlert = true;
            $msg = "Cannot promote students to the same class and session!";
        } 
        else 
        {
            if (isset($_POST['selectedStudents'])) 
            {
                // Check for duplicate entries
                $duplicateEntry = false;

                foreach ($_POST['selectedStudents'] as $selectedStudentID) 
                {
                    $sqlCheckDuplicate = "SELECT COUNT(*) FROM tblstudenthistory 
                                            WHERE StudentID = :studentID 
                                            AND SessionID = :sessionID 
                                            AND ClassID = :classID 
                                            AND Section = :section";
                    $queryCheckDuplicate = $dbh->prepare($sqlCheckDuplicate);
                    $queryCheckDuplicate->bindParam(':studentID', $selectedStudentID, PDO::PARAM_STR);
                    $queryCheckDuplicate->bindParam(':sessionID', $selectedPromoteSession, PDO::PARAM_STR);
                    $queryCheckDuplicate->bindParam(':classID', $selectedPromoteClass, PDO::PARAM_STR);
                    $queryCheckDuplicate->bindParam(':section', $selectedPromoteSection, PDO::PARAM_STR);
                    $queryCheckDuplicate->execute();
                    $duplicateCount = $queryCheckDuplicate->fetchColumn();

                    if ($duplicateCount > 0) 
                    {
                        $duplicateEntry = true;
                        break;
                    }
                }

                if ($duplicateEntry) 
                {
                    $dangerAlert = true;
                    $msg = "Selected students already promoted to the specified class and session!";
                } 
                else 
                {
                    // If no duplicate entries found, proceed with promotion
                    foreach ($_POST['selectedStudents'] as $selectedStudentID) 
                    {
                        // Store previous information in tblstudenthistory
                        $sqlStudentDetails = "SELECT SessionID, StudentClass, StudentSection FROM tblstudent WHERE ID = :studentID AND IsDeleted = 0";
                        $queryStudentDetails = $dbh->prepare($sqlStudentDetails);
                        $queryStudentDetails->bindParam(':studentID', $selectedStudentID, PDO::PARAM_STR);
                        $queryStudentDetails->execute();
                        $previousInfo = $queryStudentDetails->fetch(PDO::FETCH_ASSOC);

                        $sqlPromoteStudent = "INSERT INTO tblstudenthistory (StudentID, SessionID, ClassID, Section) 
                                                VALUES (:studentID, :sessionID, :classID, :section)";
                        $queryPromoteStudent = $dbh->prepare($sqlPromoteStudent);
                        $queryPromoteStudent->bindParam(':studentID', $selectedStudentID, PDO::PARAM_STR);
                        $queryPromoteStudent->bindParam(':sessionID', $previousInfo['SessionID'], PDO::PARAM_STR);
                        $queryPromoteStudent->bindParam(':classID', $previousInfo['StudentClass'], PDO::PARAM_STR);
                        $queryPromoteStudent->bindParam(':section', $previousInfo['StudentSection'], PDO::PARAM_STR);
                        $queryPromoteStudent->execute();

                        // Update class, section, and session in tblstudent
                        $sqlUpdateStudent = "UPDATE tblstudent SET StudentClass = :classID, StudentSection = :section, SessionID = :sessionID
                                            WHERE ID = :studentID";
                        $queryUpdateStudent = $dbh->prepare($sqlUpdateStudent);
                        $queryUpdateStudent->bindParam(':studentID', $selectedStudentID, PDO::PARAM_STR);
                        $queryUpdateStudent->bindParam(':classID', $selectedPromoteClass, PDO::PARAM_STR);
                        $queryUpdateStudent->bindParam(':section', $selectedPromoteSection, PDO::PARAM_STR);
                        $queryUpdateStudent->bindParam(':sessionID', $selectedPromoteSession, PDO::PARAM_STR);
                        $queryUpdateStudent->execute();
                    }

                    $successAlert = true;
                    $msg = "Selected students have been promoted successfully.";
                }
            } 
            else 
            {
                $dangerAlert = true;
                $msg = "Please select at least one student to promote!";
            }
        }
    }
} 
catch (PDOException $e) 
{
    $dangerAlert = true;
    $msg = "Ops! An error occurred while promoting the students.";
}


?>
<!DOCTYPE html>
<html lang="en">
<head>

    <title>Student Management System|||Promote Students</title>
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
                                <!-- Filter this Form -->
                                <form method="post" class="mb-3">
                                    <div class="form-row">
                                        <!-- Select Class -->
                                        <div class="form-group col-md-6">
                                            <label for="class">Select Class:</label>
                                            <select name="class" id="class" class="form-control">
                                                <?php

                                                // Fetch class names based on class IDs
                                                $sqlClassNames = "SELECT DISTINCT ID, ClassName, Section FROM tblclass WHERE IsDeleted = 0";
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
                                                <?php
                                                     // Fetch sections from the database
                                                    $sectionSql = "SELECT ID, SectionName FROM tblsections WHERE IsDeleted = 0";
                                                    $sectionQuery = $dbh->prepare($sectionSql);
                                                    $sectionQuery->execute();

                                                    while ($sectionRow = $sectionQuery->fetch(PDO::FETCH_ASSOC)) 
                                                    {
                                                        echo "<option value='" . htmlentities($sectionRow['ID']) . "'>" . htmlentities($sectionRow['SectionName']) . "</option>";
                                                    }
                                                ?>
                                            </select>
                                        </div>
                                    </div>
                                    <button type="submit" name="filter" class="btn btn-primary">Search</button>
                                </form>
                                
                                <?php
                                try
                                {
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
                                    
                                        // // Fetch ClassName from tblclass
                                        $sqlSelectedClassName = "SELECT * FROM tblclass WHERE ID = :selectedClass AND IsDeleted = 0";
                                        $querySelectedClassName = $dbh->prepare($sqlSelectedClassName);
                                        $querySelectedClassName->bindParam(':selectedClass', $selectedClass, PDO::PARAM_STR);
                                        $querySelectedClassName->execute();
                                        $filteredClassName = $querySelectedClassName->fetch(PDO::FETCH_ASSOC);

                                        // Check if Result is published
                                        $checkResultPublishedSql = "SELECT IsPublished, session_id FROM tblexamination WHERE IsResultPublished = 1
                                                                    AND session_id = :session_id
                                                                    AND IsDeleted = 0";
                                        $checkResultPublishedQuery = $dbh->prepare($checkResultPublishedSql);
                                        $checkResultPublishedQuery->bindParam(':session_id', $session['session_id'], PDO::PARAM_STR);
                                        $checkResultPublishedQuery->execute();
                                        $publishedResult = $checkResultPublishedQuery->fetch(PDO::FETCH_ASSOC);
                                        
                                        // Check if all results are published
                                        // $checkResultPublishedSql = "SELECT COUNT(*) as rowCount FROM tblexamination WHERE IsResultPublished = 1
                                        //                             AND session_id = :session_id
                                        //                             AND IsDeleted = 0";
                                        // $checkResultPublishedQuery = $dbh->prepare($checkResultPublishedSql);
                                        // $checkResultPublishedQuery->bindParam(':session_id', $session['session_id'], PDO::PARAM_STR);
                                        // $checkResultPublishedQuery->execute();
                                        // $rowCountResult = $checkResultPublishedQuery->fetch(PDO::FETCH_ASSOC);


                                        
                                        // Fetch sections from the database
                                        $sectionSql = "SELECT SectionName FROM tblsections WHERE ID = :selectedSection AND IsDeleted = 0";
                                        $sectionQuery = $dbh->prepare($sectionSql);
                                        $sectionQuery->bindParam(':selectedSection', $selectedSection, PDO::PARAM_STR);
                                        $sectionQuery->execute();
                                        $selectedSec = $sectionQuery->fetch(PDO::FETCH_ASSOC);
                                        
                                        if (!empty($filteredReports) && $publishedResult) 
                                        {
                                            echo "<h4 class=''>Showing results for <span class='text-dark'>Class: " . htmlspecialchars($filteredClassName['ClassName']) . ", Section: " . htmlspecialchars($selectedSec['SectionName']) . "</span></strong>";
                                            echo "<form method='POST' id='promoteForm' class='mt-3'>";
                                            ?>
                                            <!-- Confirmation Modal (Update) -->
                                            <div class="modal fade" id="confirmationModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" data-backdrop="static" data-keyboard="false">
                                                <div class="modal-dialog">
                                                    <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h4 class="modal-title" id="myModalLabel">Confirmation</h4>
                                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        Are you sure you want to Promote Selected Students?
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                                                        <button type="submit" class="btn btn-primary" name="promote">Promote</button>
                                                    </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <?php
                                            echo "<h4 class='text-center'>Promote Selected Students To </h4>";
                                            echo "<hr>";
                                            echo "<div class='row mt-3'>";
                                            // Dropdown for classes
                                            echo "<div class='col-md-3 mb-3'>";
                                            echo "<label for='classDropdown'>Select Class:</label>";
                                            echo "<select id='classDropdown' name='promoteClass' class='form-control'>";
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

                                            // Dropdown for sections 
                                            echo "<div class='col-md-3 mb-3'>";
                                            echo "<label for='sectionDropdown'>Select Section:</label>";
                                            echo "<select id='sectionDropdown' name='promoteSection' class='form-control'>";
                                            // Fetch sections from the database
                                                $sectionSql = "SELECT ID, SectionName FROM tblsections WHERE IsDeleted = 0";
                                                $sectionQuery = $dbh->prepare($sectionSql);
                                                $sectionQuery->execute();

                                                while ($sectionRow = $sectionQuery->fetch(PDO::FETCH_ASSOC)) 
                                                {
                                                    echo "<option value='" . htmlentities($sectionRow['ID']) . "'>" . htmlentities($sectionRow['SectionName']) . "</option>";
                                                }
                                            echo "</select>";
                                            echo "</div>";

                                            // Dropdown for sessions
                                            echo "<div class='col-md-3 mb-3'>";
                                            echo "<label for='sessionDropdown'>Select Session:</label>";
                                            echo "<select id='sessionDropdown' name='promoteSession' class='form-control'>";
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
                                            echo "<button class='border-0 btn btn-primary' type='button'data-toggle='modal' data-target='#confirmationModal'>Promote</button>";
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
                                                echo "<input type='checkbox' class='student-checkbox' name='selectedStudents[]' id='checkbox' value='" . $report['StudentName'] . "'" . ($overallResult == '<span class="text-success font-weight-bold">PASS</span>' ? ' checked' : '') . ">";
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
                                            echo "</form>";
                                            echo "</div>";
                                        } 
                                        else 
                                        {
                                            echo "<strong>No Record found or the Result is not published for <span class='text-danger'>Class: " . htmlspecialchars($filteredClassName['ClassName']) . ", Section: " . htmlspecialchars($selectedSec['SectionName']) . "</span></strong>";
                                        }
                                    }
                                }
                                catch(PDOException $e)
                                {
                                    $dangerAlert = true;
                                    $msg = "Ops! An error occurred while fetching students.";
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
<script src="./js/promoteStudentCheckBoxes.js"></script>
<script src="./js/manageAlert.js"></script>

<!-- End custom js for this page -->

</body>
</html>
<?php


}
?>