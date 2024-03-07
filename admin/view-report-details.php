<?php
session_start();
// error_reporting(0);
include('includes/dbconnection.php');

if (!isset($_SESSION['sturecmsaid']) || empty($_SESSION['sturecmsaid'])) 
{
    header('location:logout.php');
} 
else 
{
    if (isset($_GET['studentName'])) 
    {
        $studentID = filter_var($_GET['studentName'], FILTER_VALIDATE_INT);

        $sqlStudent = "SELECT * FROM tblstudent WHERE ID = :studentName AND IsDeleted = 0";
        $queryStudent = $dbh->prepare($sqlStudent);
        $queryStudent->bindParam(':studentName', $studentID, PDO::PARAM_INT);
        $queryStudent->execute();
        $studentDetails = $queryStudent->fetch(PDO::FETCH_ASSOC);

        $getSessionSql = "SELECT session_id, session_name FROM tblsessions WHERE is_active = 1 AND IsDeleted = 0";
        $sessionQuery = $dbh->prepare($getSessionSql);
        $sessionQuery->execute();
        $session = $sessionQuery->fetch(PDO::FETCH_ASSOC);

        if ($studentDetails) 
        {
            $stdClassID = $studentDetails['StudentClass'];
            $sqlStudentClass = "SELECT * FROM tblclass WHERE ID = :stdClassID AND IsDeleted = 0";
            $queryStudentClass = $dbh->prepare($sqlStudentClass);
            $queryStudentClass->bindParam(':stdClassID', $stdClassID, PDO::PARAM_INT);
            $queryStudentClass->execute();
            $studentClass = $queryStudentClass->fetch(PDO::FETCH_ASSOC);

            try 
            {
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

            $examSession = $_GET['examSession'];
            $sqlSubjects = "SELECT * FROM tblsubjects WHERE SessionID = :examSession AND IsDeleted = 0";
            $querySubjects = $dbh->prepare($sqlSubjects);
            $querySubjects->bindParam(':examSession', $examSession, PDO::PARAM_INT);
            $querySubjects->execute();
            $subjects = $querySubjects->fetchAll(PDO::FETCH_ASSOC);

            if (isset($subjects) && isset($reports)) {
            ?>
                <!DOCTYPE html>
                <html lang="en">

                <head>
                    <title>Student Management System || Student Report</title>
                    <meta name="viewport" content="width=device-width, initial-scale=1">
                    <link rel="stylesheet" href="vendors/simple-line-icons/css/simple-line-icons.css">
                    <link rel="stylesheet" href="vendors/flag-icon-css/css/flag-icon.min.css">
                    <link rel="stylesheet" href="vendors/css/vendor.bundle.base.css">
                    <link rel="stylesheet" href="vendors/select2/select2.min.css">
                    <link rel="stylesheet" href="vendors/select2-bootstrap-theme/select2-bootstrap.min.css">
                    <link rel="stylesheet" href="css/style.css" />
                </head>

                <body>
                    <div class="container-scroller">
                        <div class="container-fluid page-body-wrapper">
                            <div class="card">
                                <div class="card-body" id="report-card">
                                    <h4 class="card-title" style="text-align: center;">Student Report of
                                        <strong><?php
                                                $sql = "SELECT * FROM tblexamination WHERE ID = " . $_GET['examName'] . " AND IsDeleted = 0";
                                                $query = $dbh->prepare($sql);
                                                $query->execute();
                                                $examinations = $query->fetchAll(PDO::FETCH_ASSOC);
                                                foreach ($examinations as $exam) {
                                                    echo htmlentities($exam['ExamName']);
                                                }
                                                ?>
                                        </strong>(<?php echo $session['session_name']; ?>)
                                    </h4>

                                    <?php
                                    if (isset($subjects) && isset($reports)) {
                                    ?>
                                        <div class="d-flex flex-column">
                                            <?php
                                            if (isset($studentDetails)) {
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
                                                                $sectionSql = "SELECT SectionName FROM tblsections WHERE ID = :studentDetails AND IsDeleted = 0";
                                                                $sectionQuery = $dbh->prepare($sectionSql);
                                                                $sectionQuery->bindParam(':studentDetails', $studentDetails['StudentSection'], PDO::PARAM_STR);
                                                                $sectionQuery->execute();
                                                                $sectionRow = $sectionQuery->fetch(PDO::FETCH_ASSOC);
                                                                echo htmlentities($sectionRow['SectionName']);
                                                                ?></td>
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
                                            // A flag to check if the student has passed in all subjects
                                            $allSubjectsPassed = true;

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

                                                    if (isset($subjectData['TheoryMaxMarks'], $subjectData['TheoryMarksObtained'],
                                                        $subjectData['PracticalMaxMarks'], $subjectData['PracticalMarksObtained'],
                                                        $subjectData['VivaMaxMarks'], $subjectData['VivaMarksObtained'])
                                                    ) 
                                                    {
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
                                                    ?>
                                                    <tr>
                                                        <td><?php echo htmlentities($subjectName); ?></td>
                                                        <td><?php echo htmlentities($theoryMaxMarks); ?></td>
                                                        <td><?php echo htmlentities($theoryMarksObtained); ?></td>
                                                        <td><?php echo htmlentities($practicalMaxMarks); ?></td>
                                                        <td><?php echo htmlentities($practicalMarksObtained); ?></td>
                                                        <td><?php echo htmlentities($vivaMaxMarks); ?></td>
                                                        <td><?php echo htmlentities($vivaMarksObtained); ?></td>
                                                    </tr>
                                                <?php
                                                }

                                                // Calculate grand total and total max marks
                                                $grandTotal = $theoryObtMarksTotal + $pracObtMarksTotal + $vivaObtMarksTotal;
                                                $totalMaxMarks = $theoryMaxMarksTotal + $pracMaxMarksTotal + $vivaMaxMarksTotal;

                                                // Calculate percentage
                                                $percentage = ($grandTotal / $totalMaxMarks) * 100;

                                                // Check if the student has passed in this subject
                                                if ($report['IsPassed'] != 1) 
                                                {
                                                    $allSubjectsPassed = false;
                                                }

                                            }
                                            // Set $resultText based on the overall result
                                            $resultText = $allSubjectsPassed ? "<span class='text-success font-weight-bold'>PASS</span>" : "<span class='text-danger font-weight-bold'>FAIL</span>";
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

                                            <tr>
                                                <td colspan="2"></td>
                                                <td class="font-weight-bold">TOTAL MAX MARKS</td>
                                                <td class="font-weight-bold">TOTAL OBTAINED MARKS</td>
                                                <td class="font-weight-bold">PERCENTAGE</td>
                                                <td class="font-weight-bold" colspan="2">RESULT</td>
                                            </tr>
                                            <tr class=" table-secondary">
                                                <td class="font-weight-bold" colspan="2">GRAND TOTAL</td>
                                                <td id="total-max-marks"><?php echo $totalMaxMarks; ?></td>
                                                <td id="total-obt-marks"><?php echo $grandTotal; ?></td>
                                                <td id="percentage"><?php echo number_format($percentage, 2) . "%"; ?></td>
                                                <td id="result" colspan="2"><?php echo $resultText; ?></td>
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

                    <script src="vendors/js/vendor.bundle.base.js"></script>
                    <script src="vendors/select2/select2.min.js"></script>
                    <script src="vendors/typeahead.js/typeahead.bundle.min.js"></script>
                    <script src="js/off-canvas.js"></script>
                    <script src="js/misc.js"></script>
                    <script src="js/typeahead.js"></script>
                    <script src="js/select2.js"></script>
                    <script src="./js/resultGeneration.js"></script>
                    <script src="./js/printReportCard.js"></script>
                </body>
                </html>
<?php
            } 
            else 
            {
                echo "<script>alert('Student not found.'); window.location.href='view-result.php';</script>";
            }
        } 
        else 
        {
            echo "<script>alert('Student not selected.'); window.location.href='view-result.php';</script>";
        }
    }
    else 
    {
        echo "<script>alert('Invalid Request.'); window.location.href='view-result.php';</script>";
    }
}
?>
