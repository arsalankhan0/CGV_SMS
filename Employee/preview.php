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
    // Fetch active session
    $sqlSessions = "SELECT session_id FROM tblsessions WHERE is_active = 1 AND IsDeleted = 0";
    $querySessions = $dbh->prepare($sqlSessions);
    $querySessions->execute();
    $SessionID = $querySessions->fetch(PDO::FETCH_COLUMN);
?>
<!DOCTYPE html>
<html lang="en">
<head>

    <title>TPS || View Preview</title>
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
                    <h3 class="page-title">View Preview</h3>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                            <li class="breadcrumb-item active" aria-current="page">View Preview</li>
                        </ol>
                    </nav>
                </div>
                <div class="row">
                    <div class="col-md-12 grid-margin stretch-card">
                        <div class="card">
                            <div class="card-body">
                                <div class="d-sm-flex align-items-center mb-4">
                                    <h4 class="card-title mb-sm-0">View Preview</h4>
                                </div>
                                
                                <!-- Filter this Form -->
                                <form method="post" class="mb-3">
                                    <div class="form-row">
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
                                                <?php
                                                $assignedClassSql = "SELECT AssignedClasses FROM tblemployees WHERE ID = :empID AND IsDeleted = 0";
                                                $assignedClassQuery = $dbh->prepare($assignedClassSql);
                                                $assignedClassQuery->bindParam(':empID', $_SESSION['sturecmsEMPid'], PDO::PARAM_INT);
                                                $assignedClassQuery->execute();
                                                $assignedClasses = $assignedClassQuery->fetchColumn();

                                                if (!empty($assignedClasses)) 
                                                {
                                                    $assignedClassesArray = explode(',', $assignedClasses);
                                                    $inClause = implode(',', array_fill(0, count($assignedClassesArray), '?'));

                                                    $sql = "SELECT * FROM tblclass WHERE ID IN ($inClause) AND IsDeleted = 0";
                                                    $query = $dbh->prepare($sql);
                                                    $query->execute($assignedClassesArray);

                                                    if ($query->rowCount() > 0) 
                                                    {
                                                        echo '<select name="classes" class="form-control">';
                                                        $classResults = $query->fetchAll(PDO::FETCH_ASSOC);

                                                        foreach ($classResults as $class) 
                                                        {
                                                            echo "<option value='" . htmlentities($class['ID']) . "'>" . htmlentities($class['ClassName']) . "</option>";
                                                        }

                                                        echo '</select>';
                                                    } 
                                                    else 
                                                    {
                                                        echo '<p>No class assigned.</p>';
                                                    }
                                                } 
                                                else 
                                                {
                                                    echo '<p>No class assigned.</p>';
                                                }
                                                ?>
                                        </div>
                                        <!-- Select Section -->
                                        <div class="form-group col-md-4">
                                            <label for="class">Select Section:</label>
                                                <?php
                                                $assignedSectionSql = "SELECT AssignedSections FROM tblemployees WHERE ID = :empID AND IsDeleted = 0";
                                                $assignedSectionQuery = $dbh->prepare($assignedSectionSql);
                                                $assignedSectionQuery->bindParam(':empID', $_SESSION['sturecmsEMPid'], PDO::PARAM_INT);
                                                $assignedSectionQuery->execute();
                                                $assignedSections = $assignedSectionQuery->fetchColumn();
        
                                                if (!empty($assignedSections)) 
                                                {
                                                    $assignedSectionsArray = explode(',', $assignedSections);
                                                    $inClause = implode(',', array_fill(0, count($assignedSectionsArray), '?'));
            
                                                    $sql = "SELECT ID, SectionName FROM tblsections WHERE ID IN ($inClause) AND IsDeleted = 0";
                                                    $query = $dbh->prepare($sql);
                                                    $query->execute($assignedSectionsArray);
            
                                                    if ($query->rowCount() > 0) 
                                                    {
                                                        echo '<select name="sections" class="form-control">';
                                                        $sectionResults = $query->fetchAll(PDO::FETCH_ASSOC);
            
                                                        foreach ($sectionResults as $section) 
                                                        {
                                                            echo "<option value='" . htmlentities($section['ID']) . "'>" . htmlentities($section['SectionName']) . "</option>";
                                                        }
                                                        echo '</select>';
                                                    } 
                                                    else 
                                                    {
                                                        echo '<p>No Section assigned.</p>';
                                                    }
                                                } 
                                                else 
                                                {
                                                    echo '<p>No Section assigned.</p>';
                                                }
                                                ?>
                                        </div>
                                    </div>
                                    <button type="submit" name="filter" class="btn btn-primary">Search</button>
                                </form>
                                    <?php
                                    if (isset($_POST['filter'])) 
                                    {
                                        $activeSession = $SessionID;
                                        $selectedExam = $_POST['exam'];
                                        $selectedClass = $_POST['classes'];
                                        $selectedSection = $_POST['sections'];

                                        $sqlFilteredReports = "SELECT StudentName, ClassName, ExamSession, SubjectsJSON FROM tblreports 
                                                                WHERE ClassName = :class 
                                                                AND SectionName = :section
                                                                AND ExamSession = :activeSession 
                                                                AND IsDeleted = 0";
                                        $queryFilteredReports = $dbh->prepare($sqlFilteredReports);
                                        $queryFilteredReports->bindParam(':class', $selectedClass, PDO::PARAM_STR);
                                        $queryFilteredReports->bindParam(':section', $selectedSection, PDO::PARAM_STR);
                                        $queryFilteredReports->bindParam(':activeSession', $activeSession, PDO::PARAM_STR);
                                        $queryFilteredReports->execute();
                                        $filteredReports = $queryFilteredReports->fetchAll(PDO::FETCH_ASSOC);

                                        // Filter the reports based on selectedExam
                                        $filteredReports = array_filter($filteredReports, function($report) use ($selectedExam) 
                                        {
                                            // Extract ExamName from SubjectsJSON and compare with selectedExam
                                            $subjectsJSON = json_decode($report['SubjectsJSON'], true);
                                            foreach ($subjectsJSON as $subject) 
                                            {
                                                if ($subject['ExamName'] === $selectedExam) 
                                                {
                                                    return true;
                                                }
                                            }
                                            return false;
                                        });


                                        // Fetch ClassName from tblclass
                                        $sqlSelectedClassName = "SELECT ID, ClassName FROM tblclass WHERE ID = :selectedClass AND IsDeleted = 0";
                                        $querySelectedClassName = $dbh->prepare($sqlSelectedClassName);
                                        $querySelectedClassName->bindParam(':selectedClass', $selectedClass, PDO::PARAM_STR);
                                        $querySelectedClassName->execute();
                                        $filteredClassName = $querySelectedClassName->fetch(PDO::FETCH_ASSOC);

                                        // Fetch SessionName from tblsessions
                                        $sqlactiveSessionName = "SELECT session_id, session_name FROM tblsessions WHERE session_id = :activeSession AND IsDeleted = 0";
                                        $queryactiveSessionName = $dbh->prepare($sqlactiveSessionName);
                                        $queryactiveSessionName->bindParam(':activeSession', $activeSession, PDO::PARAM_STR);
                                        $queryactiveSessionName->execute();
                                        $filteredSessionName = $queryactiveSessionName->fetch(PDO::FETCH_ASSOC);
                                        
                                        // Fetch SessionName from tblexamination
                                        $sqlSelectedExamName = "SELECT ID, ExamName FROM tblexamination WHERE ID = :SelectedExam AND IsDeleted = 0";
                                        $querySelectedExamName = $dbh->prepare($sqlSelectedExamName);
                                        $querySelectedExamName->bindParam(':SelectedExam', $selectedExam, PDO::PARAM_STR);
                                        $querySelectedExamName->execute();
                                        $filteredExamName = $querySelectedExamName->fetch(PDO::FETCH_ASSOC);

                                        // Fetch SectionName from tblsections
                                        $sqlSelectedSectionName = "SELECT SectionName FROM tblsections WHERE ID = :selectedSection AND IsDeleted = 0";
                                        $querySelectedSectionName = $dbh->prepare($sqlSelectedSectionName);
                                        $querySelectedSectionName->bindParam(':selectedSection', $selectedSection, PDO::PARAM_STR);
                                        $querySelectedSectionName->execute();
                                        $sectionName = $querySelectedSectionName->fetch(PDO::FETCH_COLUMN);

                                        foreach ($filteredReports as $student) 
                                        {
                                            // Fetch ID of student from tblstudent based on filtered StudentID available in the tblreports
                                            $sqlStudent = "SELECT ID, StudentSection FROM tblstudent WHERE ID = :studentID AND StudentSection = :sectionID AND IsDeleted = 0";
                                            $queryStudent = $dbh->prepare($sqlStudent);
                                            $queryStudent->bindParam(':studentID', $student['StudentName'], PDO::PARAM_STR);
                                            $queryStudent->bindParam(':sectionID', $selectedSection, PDO::PARAM_STR);
                                            $queryStudent->execute();
                                            $availableStudent = $queryStudent->fetch(PDO::FETCH_ASSOC);
                                            
                                        }
                                        
                                        if (!empty($filteredReports) && !empty($availableStudent)) 
                                        {
                                            // Display message indicating the filtered results
                                            echo "<div class='d-flex justify-content-between  flex-column flex-md-row align-items-center'>";
                                            echo "<strong class=''>Showing results of <span class='text-dark'>Exam: " . htmlspecialchars($filteredExamName['ExamName']) . "</span>, <span class='text-dark'>Class: " . htmlspecialchars($filteredClassName['ClassName']) . "</span>, <span class='text-dark'>Section: " . htmlspecialchars($sectionName) . "</span></strong>";
                                            echo "<button class='btn btn-info mt-3 mt-md-0' onclick='previewAll()'>Preview All</button>";
                                            echo "</div>";
                                            echo "<div class='table-responsive border rounded p-1 mt-4'>";
                                            echo "<table class='table'>";
                                            echo "<thead>";
                                            echo "<tr>";
                                            echo "<th class='font-weight-bold'>S.No</th>";
                                            echo "<th class='font-weight-bold'>Student Name</th>";
                                            echo "<th class='font-weight-bold'>Student Section</th>";
                                            echo "<th class='font-weight-bold'>Roll No</th>";
                                            echo "<th class='font-weight-bold'>Action</th>";
                                            echo "</tr>";
                                            echo "</thead>";
                                            echo "<tbody>";
                                            
                                            $cnt = 1;
                                            foreach ($filteredReports as $report) 
                                            {
                                                // Fetch Name of student from tblstudent based on the StudentID
                                                $sqlStudentDetails = "SELECT StudentName, RollNo, StudentSection FROM tblstudent WHERE ID = :studentID AND StudentSection IN (:sectionID) AND IsDeleted = 0";
                                                $queryStudentDetails = $dbh->prepare($sqlStudentDetails);
                                                $queryStudentDetails->bindParam(':studentID', $report['StudentName'], PDO::PARAM_STR);
                                                $queryStudentDetails->bindParam(':sectionID', $selectedSection, PDO::PARAM_STR);
                                                $queryStudentDetails->execute();
                                                $studentDetails = $queryStudentDetails->fetch(PDO::FETCH_ASSOC);
                                                
                                                // Decode the JSON data from SubjectsJSON field
                                                $subjectsJSON = json_decode($report['SubjectsJSON'], true);
                                                
                                                // Extract the exam name
                                                $examName = '';
                                                foreach ($subjectsJSON as $subject) {
                                                    if ($subject['ExamName'] === $selectedExam) {
                                                        $examName = $subject['ExamName'];
                                                        break;
                                                    }
                                                }

                                                
                                                echo "<tr>";
                                                echo "<td>" . htmlentities($cnt) . "</td>";
                                                echo "<td>". htmlentities($studentDetails['StudentName']) ."</td>";
                                                echo "<td>". htmlentities($sectionName) ."</td>";
                                                echo "<td>". htmlentities($studentDetails['RollNo']) ."</td>";
                                                echo "<td>";
                                                echo "<div>";
                                                echo "<a class='btn btn-info' target='_blank' href='fa-preview.php?className=" . urlencode($report['ClassName']) . "&sectionName=" . urlencode($selectedSection) . "&studentName=" . urlencode($report['StudentName']) . "&examName=" . urlencode($examName) . "&examSession=" . urlencode($report['ExamSession']) . "'>Preview</a>";
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
                                            echo "<strong>No Record found of <span class='text-danger'>Exam: " . htmlspecialchars($filteredExamName['ExamName']) . "</span>, <span class='text-danger'>Class: " . htmlspecialchars($filteredClassName['ClassName']) . "</span>, <span class='text-danger'>Section: " . htmlspecialchars($sectionName) . "</span></strong>";
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
    function printReportDetails(url) 
    {
        let newWindow = window.open(url, '_blank');
    }
    function previewAll() {
        <?php
        foreach ($filteredReports as $report) {
            echo "printReportDetails(\"fa-preview-all.php?className=" . urlencode($report['ClassName']) .  "&examName=" . urlencode($examName) . "&examSession=" . urlencode($report['ExamSession']) . "\");";
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
