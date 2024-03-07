<?php
session_start();
// error_reporting(0);
include('includes/dbconnection.php');

if (!isset($_SESSION['sturecmsEMPid']) || empty($_SESSION['sturecmsEMPid'])) 
{
    header('location:logout.php');
} 
else 
{
    if (isset($_GET['studentName'])) 
    {
        // Get the filtered Session of studentName.
        $studentID = filter_var($_GET['studentName'], FILTER_VALIDATE_INT);

        // Fetch student details
        $sqlStudent = "SELECT * FROM tblstudent WHERE ID = :studentName AND IsDeleted = 0";
        $queryStudent = $dbh->prepare($sqlStudent);
        $queryStudent->bindParam(':studentName', $studentID, PDO::PARAM_INT);
        $queryStudent->execute();
        $studentDetails = $queryStudent->fetch(PDO::FETCH_ASSOC);

        // Get the active session ID
        $getSessionSql = "SELECT session_id, session_name FROM tblsessions WHERE is_active = 1 AND IsDeleted = 0";
        $sessionQuery = $dbh->prepare($getSessionSql);
        $sessionQuery->execute();
        $session = $sessionQuery->fetch(PDO::FETCH_ASSOC);

        if ($studentDetails) 
        {
            // Fetch student Class
            $stdClassID = $studentDetails['StudentClass'];
            $sqlStudentClass = "SELECT * FROM tblclass WHERE ID = :stdClassID AND IsDeleted = 0";
            $queryStudentClass = $dbh->prepare($sqlStudentClass);
            $queryStudentClass->bindParam(':stdClassID', $stdClassID, PDO::PARAM_INT);
            $queryStudentClass->execute();
            $studentClass = $queryStudentClass->fetch(PDO::FETCH_ASSOC);

            // Fetch subjects assigned to the teacher from tblemployees
            $sqlAssignedSubjects = "SELECT AssignedSubjects FROM tblemployees WHERE ID = :employeeID AND IsDeleted = 0";
            $queryAssignedSubjects = $dbh->prepare($sqlAssignedSubjects);
            $queryAssignedSubjects->bindParam(':employeeID', $_SESSION['sturecmsEMPid'], PDO::PARAM_INT);
            $queryAssignedSubjects->execute();
            $assignedSubjects = $queryAssignedSubjects->fetch(PDO::FETCH_ASSOC);

            try 
            {
                // Fetch data from the database for the selected student, class, and exam
                $examSession = $session['session_id'];
                $className = $_GET['className'];
                $examName = $_GET['examName'];
                $studentName = $_GET['studentName'];

                $sqlReports = "SELECT * FROM tblreports 
                                WHERE ExamSession = :examSession 
                                AND ClassName = :className 
                                AND ExamName = :examName 
                                AND StudentName = :studentName 
                                AND IsDeleted = 0";
                $stmtReports = $dbh->prepare($sqlReports);
                $stmtReports->bindParam(':examSession', $examSession, PDO::PARAM_STR);
                $stmtReports->bindParam(':className', $className, PDO::PARAM_INT);
                $stmtReports->bindParam(':examName', $examName, PDO::PARAM_STR);
                $stmtReports->bindParam(':studentName', $studentName, PDO::PARAM_INT);
                $stmtReports->execute();
                $reports = $stmtReports->fetchAll(PDO::FETCH_ASSOC);

                // Initialize variables for totals
                $theoryMaxMarksTotal = 0;
                $theoryObtMarksTotal = 0;
                $pracMaxMarksTotal = 0;
                $pracObtMarksTotal = 0;
                $vivaMaxMarksTotal = 0;
                $vivaObtMarksTotal = 0;

                foreach ($reports as $report) 
                {
                    $subjectsJSON = json_decode($report['SubjectsJSON'], true);
                
                    foreach ($subjectsJSON as $subjectData) 
                    {
                        // Ensure the necessary keys are present in $subjectData
                        if (
                            isset($subjectData['TheoryMaxMarks'], $subjectData['TheoryMarksObtained'],
                            $subjectData['PracticalMaxMarks'], $subjectData['PracticalMarksObtained'],
                            $subjectData['VivaMaxMarks'], $subjectData['VivaMarksObtained'])
                        ) 
                        {
                            // Adding individual subject marks
                            $theoryMaxMarksTotal += $subjectData['TheoryMaxMarks'];
                            $theoryObtMarksTotal += $subjectData['TheoryMarksObtained'];
                            $pracMaxMarksTotal += $subjectData['PracticalMaxMarks'];
                            $pracObtMarksTotal += $subjectData['PracticalMarksObtained'];
                            $vivaMaxMarksTotal += $subjectData['VivaMaxMarks'];
                            $vivaObtMarksTotal += $subjectData['VivaMarksObtained'];
                        } 
                        else 
                        {
                            echo "<script>console.error('Invalid format for subject data in JSON:', " . json_encode($subjectData) . ");</script>";
                        }
                    }
                }
                
                if (!$reports) 
                {
                    echo "<script>alert('No data found for the selected student, class, and exam.'); window.location.href='view-students-list.php';</script>";

                }
            } 
            catch (PDOException $e) 
            {
                echo '<script>alert("Ops! An Error occurred.")</script>';
                // error_log($e->getMessage()); //-->This is only for debugging purposes
            }

            $employeeID = $_SESSION['sturecmsEMPid'];
            
            // Fetch only the assigned subjects from tblsubjects
            $sqlSubjects = "SELECT * FROM tblsubjects WHERE ID IN (" . $assignedSubjects['AssignedSubjects'] . ") AND IsDeleted = 0";
            $querySubjects = $dbh->prepare($sqlSubjects);
            $querySubjects->execute();
            $subjects = $querySubjects->fetchAll(PDO::FETCH_ASSOC);

            $assignedSubjectIDs = explode(",", $assignedSubjects['AssignedSubjects']);

            // Filter subjects to only include assigned subjects
            $assignedSubjects = array_filter($subjects, function ($subject) use ($assignedSubjectIDs) {
                return in_array($subject['ID'], $assignedSubjectIDs);
            });

            if (isset($assignedSubjects) && isset($reports)) 
            {
            ?>
                <!DOCTYPE html>
                <html lang="en">

                <head>
                    <title>Student Management System || View Student Report</title>
                    <meta name="viewport" content="width=device-width, initial-scale=1">
                    <!-- plugins:css -->
                    <link rel="stylesheet" href="vendors/simple-line-icons/css/simple-line-icons.css">
                    <link rel="stylesheet" href="vendors/flag-icon-css/css/flag-icon.min.css">
                    <link rel="stylesheet" href="vendors/css/vendor.bundle.base.css">
                    <!-- endinject -->
                    <!-- Plugin css for this page -->
                    <link rel="stylesheet" href="vendors/select2/select2.min.css">
                    <link rel="stylesheet" href="vendors/select2-bootstrap-theme/select2-bootstrap.min.css">
                    <!-- End plugin css for this page -->
                    <!-- inject:css -->
                    <!-- endinject -->
                    <!-- Layout styles -->
                    <link rel="stylesheet" href="css/style.css" />
                </head>

                <body>
                    <div class="container-scroller">
                        <!-- partial:partials/_navbar.html -->
                        <?php
                        include_once('includes/header.php');
                        ?>
                        <!-- partial -->
                        <div class="container-fluid page-body-wrapper">
                            <!-- partial:partials/_sidebar.html -->
                            <?php include_once('includes/sidebar.php'); ?>
                            <!-- partial -->
                            <div class="main-panel">
                                <div class="content-wrapper">
                                    <div class="page-header">
                                        <h3 class="page-title"> View Student Report </h3>
                                        <nav aria-label="breadcrumb">
                                            <ol class="breadcrumb">
                                                <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                                                <li class="breadcrumb-item active" aria-current="page"> View Student Report </li>
                                            </ol>
                                        </nav>
                                    </div>
                                    <div class="row">
                                        <div class="col-12 grid-margin stretch-card">
                                            <div class="card">
                                                <div class="card-body" id="report-card">
                                                    <h4 class="card-title" style="text-align: center;">Student Report of
                                                        <strong><?php
                                                                $sql = "SELECT * FROM tblexamination WHERE ID = " . $_GET['examName'] . " AND IsDeleted = 0";
                                                                $query = $dbh->prepare($sql);
                                                                $query->execute();
                                                                $examinations = $query->fetchAll(PDO::FETCH_ASSOC);
                                                                foreach ($examinations as $exam) 
                                                                {
                                                                    echo htmlentities($exam['ExamName']);
                                                                }
                                                                ?>
                                                        </strong>(<?php echo $session['session_name']; ?>)
                                                    </h4>

                                                    <?php
                                                    if (isset($assignedSubjects) && isset($reports)) 
                                                    {
                                                    ?>
                                                        <div class="d-flex flex-column">
                                                            <?php
                                                            if (isset($studentDetails)) 
                                                            {
                                                            ?>
                                                                <table class="table table-bordered col-md-6">
                                                                    <tbody>
                                                                        <tr>
                                                                            <td>Student Name:</td>
                                                                            <td><?php echo htmlentities($studentDetails['StudentName']); ?></td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td>Roll No:</td>
                                                                            <td><?php
                                                                                echo htmlentities($studentDetails['RollNo']); 
                                                                                ?></td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td>Class:</td>
                                                                            <td><?php echo htmlentities($studentClass['ClassName']); ?></td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td>Section:</td>
                                                                            <td><?php 
                                                                            // Fetch sections of particular student from the database
                                                                            $sectionSql = "SELECT SectionName FROM tblsections WHERE ID = :studentSection AND IsDeleted = 0";
                                                                            $sectionQuery = $dbh->prepare($sectionSql);
                                                                            $sectionQuery->bindParam(':studentSection', $studentDetails['StudentSection'], PDO::PARAM_STR);
                                                                            $sectionQuery->execute();
                                                                            $studentSection = $sectionQuery->fetch(PDO::FETCH_ASSOC);

                                                                            echo htmlentities($studentSection['SectionName']); ?></td>
                                                                        </tr>
                                                                    </tbody>
                                                                </table>
                                                            <?php
                                                            }
                                                            ?>
                                                            <table class="table table-bordered">
                                                                <thead>
                                                                    <tr>
                                                                        <th></th>
                                                                        <th colspan="2" class="text-center font-weight-bold">THEORY</th>
                                                                        <th colspan="2" class="text-center font-weight-bold">PRACTICAL</th>
                                                                        <th colspan="2" class="text-center font-weight-bold">VIVA</th>
                                                                    </tr>
                                                                    <tr>
                                                                        <th class="font-weight-bold">Subjects</th>
                                                                        <th class="font-weight-bold">Max Marks</th>
                                                                        <th class="font-weight-bold">Marks Obtained</th>
                                                                        <th class="font-weight-bold">Max Marks</th>
                                                                        <th class="font-weight-bold">Marks Obtained</th>
                                                                        <th class="font-weight-bold">Max Marks</th>
                                                                        <th class="font-weight-bold">Marks Obtained</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody>
                                                                    <?php
                                                                    foreach ($reports as $report) 
                                                                    {
                                                                        $subjectsJSON = json_decode($report['SubjectsJSON'], true);

                                                                        // if SubjectsJSON is a valid JSON string
                                                                        if (json_last_error() === JSON_ERROR_NONE) 
                                                                        {
                                                                            foreach ($subjectsJSON as $subjectData) 
                                                                            {
                                                                                $subjectID = $subjectData['SubjectID'];

                                                                                if (in_array($subjectID, $assignedSubjectIDs)) 
                                                                                {
                                                                                    // Fetch subjects Name
                                                                                    $sqlSubjects = "SELECT SubjectName FROM tblsubjects WHERE ID = :subjectID AND IsDeleted = 0";
                                                                                    $querySubjects = $dbh->prepare($sqlSubjects);
                                                                                    $querySubjects->bindParam(':subjectID', $subjectData['SubjectID'], PDO::PARAM_INT);
                                                                                    $querySubjects->execute();
                                                                                    $subject = $querySubjects->fetch(PDO::FETCH_ASSOC);

                                                                                    $subjectName = htmlentities($subject['SubjectName']);
                                                                                    $theoryMaxMarks = htmlentities($subjectData['TheoryMaxMarks']);
                                                                                    $theoryMarksObtained = htmlentities($subjectData['TheoryMarksObtained']);
                                                                                    $practicalMaxMarks = htmlentities($subjectData['PracticalMaxMarks']);
                                                                                    $practicalMarksObtained = htmlentities($subjectData['PracticalMarksObtained']);
                                                                                    $vivaMaxMarks = htmlentities($subjectData['VivaMaxMarks']);
                                                                                    $vivaMarksObtained = htmlentities($subjectData['VivaMarksObtained']);
                                                                                    
                                                                                    echo "
                                                                                        <tr>
                                                                                            <td>{$subjectName}</td>
                                                                                            <td>{$theoryMaxMarks}</td>
                                                                                            <td>{$theoryMarksObtained}</td>
                                                                                            <td>{$practicalMaxMarks}</td>
                                                                                            <td>{$practicalMarksObtained}</td>
                                                                                            <td>{$vivaMaxMarks}</td>
                                                                                            <td>{$vivaMarksObtained}</td>
                                                                                        </tr>
                                                                                    ";
                                                                                }
                                                                                else 
                                                                                {
                                                                                    echo "<script>console.error('Subject with ID " . $subjectID . " is not assigned to the employee.');</script>";                                                                                
                                                                                } 
                                                                            }
                                                                        } 
                                                                    }
                                                                    ?>
                                                                    <tr class=" table-secondary">
                                                                        <td class="font-weight-bold">TOTAL</td>
                                                                        <td id="th-max-marks"><?php echo $theoryMaxMarksTotal; ?></td>
                                                                        <td id="th-obt-marks"><?php echo $theoryObtMarksTotal; ?></td>
                                                                        <td id="prac-max-marks"><?php echo $pracMaxMarksTotal; ?></td>
                                                                        <td id="prac-obt-marks"><?php echo $pracObtMarksTotal; ?></td>
                                                                        <td id="viva-max-marks"><?php echo $vivaMaxMarksTotal; ?></td>
                                                                        <td id="viva-obt-marks"><?php echo $vivaObtMarksTotal; ?></td>
                                                                    </tr>
                                                                    
                                                                </tbody>
                                                            </table>
                                                            
                                                        </div>
                                                    <?php
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
                    <script src="vendors/select2/select2.min.js"></script>
                    <script src="vendors/typeahead.js/typeahead.bundle.min.js"></script>
                    <!-- End plugin js for this page -->
                    <!-- inject:js -->
                    <script src="js/off-canvas.js"></script>
                    <script src="js/misc.js"></script>
                    <!-- endinject -->
                    <!-- Custom js for this page -->
                    <script src="js/typeahead.js"></script>
                    <script src="js/select2.js"></script>
                    <script src="./js/resultGeneration.js"></script>
                    <!-- End custom js for this page -->
                </body>
                </html>
<?php
            } 
            else 
            {
                echo "<script>alert('Student not found.'); window.location.href='view-students-list.php';</script>";
            }
        } 
        else 
        {
            echo "<script>alert('Student not selected.'); window.location.href='view-students-list.php';</script>";
        }
    } 
    else 
    {
        echo "<script>alert('Invalid Request.'); window.location.href='view-students-list.php';</script>";
    }
}
?>
