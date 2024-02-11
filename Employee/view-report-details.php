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

            try 
            {
                // Fetch data from the database for the selected student, class, and exam
                $examSession = $session['session_id'];
                $className = $_GET['className'];
                $examName = $_GET['examName'];
                $studentName = $_GET['studentName'];

                $sqlReports = "SELECT * FROM tblreports WHERE ExamSession = :examSession AND ClassName = :className AND ExamName = :examName AND StudentName = :studentName AND IsDeleted = 0";
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
                    // Adding individual subject marks
                    $theoryMaxMarksTotal += $report['TheoryMaxMarks'];
                    $theoryObtMarksTotal += $report['TheoryMarksObtained'];
                    $pracMaxMarksTotal += $report['PracticalMaxMarks'];
                    $pracObtMarksTotal += $report['PracticalMarksObtained'];
                    $vivaMaxMarksTotal += $report['VivaMaxMarks'];
                    $vivaObtMarksTotal += $report['VivaMarksObtained'];
                }

                // Calculate grand total and total max marks
                $grandTotal = $theoryObtMarksTotal + $pracObtMarksTotal + $vivaObtMarksTotal;
                $totalMaxMarks = $theoryMaxMarksTotal + $pracMaxMarksTotal + $vivaMaxMarksTotal;

                // Calculate percentage
                $percentage = ($grandTotal / $totalMaxMarks) * 100;


                if (!$reports) 
                {
                    echo "<script>alert('No data found for the selected student, class, and exam.');</script>";
                }
            } 
            catch (PDOException $e) 
            {
                echo '<script>alert("Ops! An Error occurred.")</script>';
                // error_log($e->getMessage()); //-->This is only for debugging purposes
            }

            // Fetch subjects
            $sqlSubjects = "SELECT * FROM tblsubjects WHERE IsDeleted = 0";
            $querySubjects = $dbh->prepare($sqlSubjects);
            $querySubjects->execute();
            $subjects = $querySubjects->fetchAll(PDO::FETCH_ASSOC);// Fetch the logged-in employee ID
            $employeeID = $_SESSION['sturecmsEMPid'];
            
            // Fetch subjects assigned to the teacher from tblemployees
            $sqlAssignedSubjects = "SELECT AssignedSubjects FROM tblemployees WHERE ID = :employeeID AND IsDeleted = 0";
            $queryAssignedSubjects = $dbh->prepare($sqlAssignedSubjects);
            $queryAssignedSubjects->bindParam(':employeeID', $employeeID, PDO::PARAM_INT);
            $queryAssignedSubjects->execute();
            $assignedSubjects = $queryAssignedSubjects->fetchAll(PDO::FETCH_COLUMN);
            
            // Fetch only the assigned subjects from tblsubjects
            $sqlSubjects = "SELECT * FROM tblsubjects WHERE ID IN (" . implode(',', $assignedSubjects) . ") AND IsDeleted = 0";
            $querySubjects = $dbh->prepare($sqlSubjects);
            $querySubjects->execute();
            $subjects = $querySubjects->fetchAll(PDO::FETCH_ASSOC);

            if (isset($subjects) && isset($reports)) 
            {
            ?>
                <!DOCTYPE html>
                <html lang="en">

                <head>
                    <title>Student Management System || View Student Report</title>
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
                                                    if (isset($subjects) && isset($reports)) 
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
                                                                            <td><?php echo htmlentities($studentDetails['StudentSection']); ?></td>
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
                                                                        $subjectID = $report['Subjects'];
                                                                        $sqlSubjectsName = "SELECT * FROM tblsubjects WHERE ID = :subjectID AND IsDeleted = 0";
                                                                        $querySubjectsName = $dbh->prepare($sqlSubjectsName);
                                                                        $querySubjectsName->bindParam(':subjectID', $subjectID, PDO::PARAM_INT);
                                                                        $querySubjectsName->execute();
                                                                        $subjectName = $querySubjectsName->fetch(PDO::FETCH_ASSOC);
                                                                        ?>
                                                                        <tr>
                                                                            <!-- <td><?php echo htmlentities($report['Subjects']); ?></td> -->
                                                                            <td><?php echo htmlentities($subjectName['SubjectName']); ?></td>
                                                                            <td><?php echo htmlentities($report['TheoryMaxMarks']); ?></td>
                                                                            <td><?php echo htmlentities($report['TheoryMarksObtained']); ?></td>
                                                                            <td><?php echo htmlentities($report['PracticalMaxMarks']); ?></td>
                                                                            <td><?php echo htmlentities($report['PracticalMarksObtained']); ?></td>
                                                                            <td><?php echo htmlentities($report['VivaMaxMarks']); ?></td>
                                                                            <td><?php echo htmlentities($report['VivaMarksObtained']); ?></td>
                                                                        </tr>
                                                                        <?php
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
                                                <div class="d-flex justify-content-center mb-3">
                                                    <button class="btn btn-success" id="print-btn" type="button">Print</button>
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
                    <script src="./js/printReportCard.js"></script>
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
