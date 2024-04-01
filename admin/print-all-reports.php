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
    if (isset($_GET['className']) && isset($_GET['examSession'])) 
    {

        $className = urldecode($_GET['className']);
        $examSession = urldecode($_GET['examSession']);

        // Fetch all students and their reports based on the specified criteria
        $sqlReports = "SELECT * FROM tblreports WHERE ClassName = :className AND ExamSession = :examSession AND IsDeleted = 0";
        $stmtReports = $dbh->prepare($sqlReports);
        $stmtReports->bindParam(':className', $className, PDO::PARAM_STR);
        $stmtReports->bindParam(':examSession', $examSession, PDO::PARAM_STR);
        $stmtReports->execute();
        $allReports = $stmtReports->fetchAll(PDO::FETCH_ASSOC);

        // Get the active session ID and Name
        $getSessionSql = "SELECT session_id, session_name FROM tblsessions WHERE session_id = :selectedSession AND IsDeleted = 0";
        $sessionQuery = $dbh->prepare($getSessionSql);
        $sessionQuery->bindParam(':selectedSession', $examSession, PDO::PARAM_STR);
        $sessionQuery->execute();
        $session = $sessionQuery->fetch(PDO::FETCH_ASSOC);
        $sessionID = $session['session_id'];
        $sessionName = $session['session_name'];

        if (!$allReports) 
        {
            echo "<script>alert('No data found for the selected criteria.');</script>";
        }
        ?>
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <title>Tibetan Public School || Student Reports</title>
            <link rel="stylesheet" href="vendors/simple-line-icons/css/simple-line-icons.css">
            <link rel="stylesheet" href="vendors/flag-icon-css/css/flag-icon.min.css">
            <link rel="stylesheet" href="vendors/css/vendor.bundle.base.css">
            <link rel="stylesheet" href="vendors/select2/select2.min.css">
            <link rel="stylesheet" href="vendors/select2-bootstrap-theme/select2-bootstrap.min.css">
            <link rel="stylesheet" href="css/style.css" />
        </head>
        <body>
            <div class="container-scroller">
                <div class="container page-body-wrapper d-flex flex-column">
                    <?php
                    $groupedReports = [];

                    foreach ($allReports as $report) 
                    {
                        // Assuming StudentName is the key to group by
                        $studentName = $report['StudentName'];

                        if (!isset($groupedReports[$studentName])) 
                        {
                            $groupedReports[$studentName] = [];
                        }

                        $groupedReports[$studentName][] = $report;
                    }

                    foreach ($groupedReports as $studentName => $studentReports) 
                    {
                        // Fetch student details
                        $studentDetailsSql = "SELECT ID, StudentName, StudentSection, StudentClass, RollNo, FatherName FROM tblstudent WHERE ID = :studentID AND IsDeleted = 0";
                        $studentDetailsQuery = $dbh->prepare($studentDetailsSql);
                        $studentDetailsQuery->bindParam(':studentID', $studentReports[0]['StudentName'], PDO::PARAM_INT);
                        $studentDetailsQuery->execute();
                        $studentDetails = $studentDetailsQuery->fetch(PDO::FETCH_ASSOC);

                        // Fetch Class Details
                        $studentClassSql = "SELECT ClassName FROM tblclass WHERE ID = :classID AND IsDeleted = 0";
                        $studentClassQuery = $dbh->prepare($studentClassSql);
                        $studentClassQuery->bindParam(':classID', $studentDetails['StudentClass'], PDO::PARAM_INT);
                        $studentClassQuery->execute();
                        $studentClass = $studentClassQuery->fetch(PDO::FETCH_COLUMN);

                        // Fetch sections from the database
                        $sectionSql = "SELECT SectionName FROM tblsections WHERE ID = :studentDetails AND IsDeleted = 0";
                        $sectionQuery = $dbh->prepare($sectionSql);
                        $sectionQuery->bindParam(':studentDetails', $studentDetails['StudentSection'], PDO::PARAM_STR);
                        $sectionQuery->execute();
                        $sectionRow = $sectionQuery->fetch(PDO::FETCH_ASSOC);
                        ?>
                        <div class="card">
                            <div class="card-body" id="report-card">
                                <h4 class="card-title" style="text-align: center;">MARKS CARD for the Academic Session <?php echo $sessionName; ?></h4>
                                
                                <!-- Student's Details -->
                                <div class="mt-4">
                                    <div class="d-flex flex-row justify-content-between">
                                        <div>
                                            <label>Student's Code No:</label><span class="border-bottom border-dark ml-2 px-5"></span>
                                        </div>
                                        <div>
                                            <label>Class:</label><span class="border-bottom border-dark ml-2 px-3"><?php echo htmlentities($studentClass); ?></span>
                                        </div>
                                        <div>
                                            <label>Section:</label><span class="border-bottom border-dark ml-2 px-3"><?php echo htmlentities($sectionRow['SectionName']); ?></span>
                                        </div>
                                        <div>
                                            <label>Roll No:</label><span class="border-bottom border-dark ml-2 px-3"><?php echo htmlentities($studentDetails['RollNo']); ?></span>
                                        </div>
                                    </div>
                                    <!-- Student's Name -->
                                    <div class="d-flex w-100 align-items-center">
                                        <label class="text-nowrap">Student's Name: </label>
                                        <p class="border-bottom border-dark ml-2 pl-3 w-100" style="box-sizing: border-box;"><?php echo htmlentities($studentDetails['StudentName']); ?></p>
                                    </div>
                                    <!-- Parent's Name -->
                                    <div class="d-flex w-100 align-items-center">
                                        <label class="text-nowrap">Parents'/Guardian's Name: </label>
                                        <p class="border-bottom border-dark ml-2 pl-3 w-100" style="box-sizing: border-box;"><?php echo htmlentities($studentDetails['FatherName']); ?></p>
                                    </div>
                                </div>
                                <div class="d-flex flex-column">
                                    <table class="table table-bordered">
                                        <thead>
                                            <tr>
                                                <th rowspan="2">Subjects</th>
                                                <th colspan="7">Formative Assessment</th>
                                                <th colspan="2">Co-Curricular Activities</th>
                                                <th colspan="2">Summative Assessment</th>
                                                <th colspan="2">Total (FA+CA+SA)</th>
                                            </tr>
                                            <tr>
                                                <?php
                                                // Fetch all Formative exam names
                                                $examNamesSql = "SELECT ExamName FROM tblexamination WHERE ExamType = 'Formative' AND IsDeleted = 0";
                                                $examNamesQuery = $dbh->prepare($examNamesSql);
                                                $examNamesQuery->execute();
                                                $examNames = $examNamesQuery->fetchAll(PDO::FETCH_COLUMN);

                                                foreach ($examNames as $examName) 
                                                {
                                                    echo "<th>$examName</th>";
                                                }?>
                                                <th>Total</th>
                                                <th colspan="2">Max Marks</th>
                                                <th colspan="2">Max Marks</th>
                                                <th colspan="2">Max Marks</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                                <?php
                                                $class = "%,$className,%";
                                                // Fetch only those subjects of the class whose IsOptional is 0
                                                $subjectsSql = "SELECT * FROM tblsubjects WHERE ClassName LIKE :className AND IsOptional = 0 AND IsCurricularSubject = 0 AND IsDeleted = 0";
                                                $subjectsQuery = $dbh->prepare($subjectsSql);
                                                $subjectsQuery->bindParam(':className', $class, PDO::PARAM_STR);
                                                $subjectsQuery->execute();
                                                $subjects = $subjectsQuery->fetchAll(PDO::FETCH_ASSOC);

                                                 // Loop through the subjects and display each one in its own table row
                                                foreach ($subjects as $subject) 
                                                {
                                                    // Initialize SubMarksObtained for the current subject
                                                    $subMarksObtained = '';

                                                    // Fetch SubjectsJSON for the current subject from tblreports
                                                    $fetchSubjectsJsonSql = "SELECT SubjectsJSON FROM tblreports WHERE ClassName = :className AND ExamSession = :examSession AND StudentName = :studentID";
                                                    $fetchSubjectsJsonQuery = $dbh->prepare($fetchSubjectsJsonSql);
                                                    $fetchSubjectsJsonQuery->bindParam(':className', $className, PDO::PARAM_STR);
                                                    $fetchSubjectsJsonQuery->bindParam(':examSession', $examSession, PDO::PARAM_STR);
                                                    $fetchSubjectsJsonQuery->bindParam(':studentID', $studentDetails['ID'], PDO::PARAM_STR);
                                                    $fetchSubjectsJsonQuery->execute();
                                                    $subjectsJson = $fetchSubjectsJsonQuery->fetch(PDO::FETCH_COLUMN);

                                                    // Decode the JSON to an associative array
                                                    $subjectsData = json_decode($subjectsJson, true);

                                                    // Loop through the decoded JSON to find the SubMarksObtained for the current subject
                                                    foreach ($subjectsData as $subjectData) 
                                                    {
                                                        if ($subjectData['SubjectID'] == $subject['ID']) 
                                                        {
                                                            $subMarksObtained = $subjectData['SubMarksObtained'];
                                                            break; 
                                                        }
                                                    }

                                                    // Display the subject name and SubMarksObtained in the table row
                                                    echo "<tr>
                                                            <td>{$subject['SubjectName']}</td>
                                                            <td>{$subMarksObtained}</td>
                                                        </tr>";
                                                }
                                                ?>
                                        </tbody>

                                    </table>
                                </div>
                            </div>
                        </div>
                    <?php
                    }
                    ?>
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
    } else {
        echo "<script>alert('Invalid Request');</script>";
    }
}
?>
