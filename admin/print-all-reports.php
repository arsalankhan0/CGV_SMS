<?php
session_start();
// error_reporting(0);
include('includes/dbconnection.php');

// Function to check if there is grading system
function hasOptionalSubjectWithGrading($dbh, $className, $examSession) 
{
    $class = "%$className%";
    $optionalGradingSql = "SELECT COUNT(*) FROM tblsubjects WHERE ClassName LIKE :className AND IsOptional = 1 AND IsCurricularSubject = 0 AND IsDeleted = 0";
    $optionalGradingQuery = $dbh->prepare($optionalGradingSql);
    $optionalGradingQuery->bindParam(':className', $class, PDO::PARAM_STR);
    $optionalGradingQuery->execute();
    $optionalGradingCount = $optionalGradingQuery->fetchColumn();
    return $optionalGradingCount > 0;
}

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
            <style>
                .card 
                {
                    page-break-after: always;
                }
                .signature-line
                {
                    padding: 0 100px;
                }

            </style>
        </head>
        <body>
            
            <div class="container-scroller">
                <div class="container page-body-wrapper d-flex flex-column">
                    <?php
                    $groupedReports = [];

                    foreach ($allReports as $report) 
                    {
                        // key to group by
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
                        <div class="card d-flex justify-content-center align-items-center">
                            <div class="card-body" id="report-card">
                                <h4 class="card-title" style="text-align: center;">MARKS CARD for the Academic Session <?php echo $sessionName; ?></h4>
                                
                                <!-- Student's Details -->
                                <div class="mt-4">
                                    <div class="d-flex flex-row justify-content-between">
                                        <div>
                                            <label>Student's Code No:</label><span class="border-bottom border-secondary ml-2 px-5"></span>
                                        </div>
                                        <div>
                                            <label>Class:</label><span class="border-bottom border-secondary ml-2 px-3"><?php echo htmlentities($studentClass); ?></span>
                                        </div>
                                        <div>
                                            <label>Section:</label><span class="border-bottom border-secondary ml-2 px-3"><?php echo htmlentities($sectionRow['SectionName']); ?></span>
                                        </div>
                                        <div>
                                            <label>Roll No:</label><span class="border-bottom border-secondary ml-2 px-3"><?php echo htmlentities($studentDetails['RollNo']); ?></span>
                                        </div>
                                    </div>
                                    <!-- Student's Name -->
                                    <div class="d-flex w-100 align-items-center">
                                        <label class="text-nowrap">Student's Name: </label>
                                        <p class="border-bottom border-secondary ml-2 pl-3 w-100" style="box-sizing: border-box;"><?php echo htmlentities($studentDetails['StudentName']); ?></p>
                                    </div>
                                    <!-- Parent's Name -->
                                    <div class="d-flex w-100 align-items-center">
                                        <label class="text-nowrap">Parents'/Guardian's Name: </label>
                                        <p class="border-bottom border-secondary ml-2 pl-3 w-100" style="box-sizing: border-box;"><?php echo htmlentities($studentDetails['FatherName']); ?></p>
                                    </div>
                                </div>
                                <!-- Main Subjects -->
                                <div class="d-flex flex-column">
                                    <table class="table table-bordered">
                                        <thead>
                                            <tr class="text-center">
                                                <th rowspan="2" style="vertical-align: middle;">Subjects</th>
                                                <th colspan="7">Formative Assessment</th>
                                                <th colspan="2">Co-Curricular Activities</th>
                                                <th colspan="2">Summative Assessment</th>
                                                <th colspan="2">Total (FA+CA+SA)</th>
                                            </tr>
                                            <tr class="text-center">
                                                <?php
                                                // Fetch all Formative exam names and IDs
                                                $examNamesSql = "SELECT ExamName, ID FROM tblexamination WHERE ExamType = 'Formative' AND IsDeleted = 0";
                                                $examNamesQuery = $dbh->prepare($examNamesSql);
                                                $examNamesQuery->execute();
                                                $examNames = $examNamesQuery->fetchAll(PDO::FETCH_ASSOC);

                                                // Co-curricular exam name
                                                $coCurricularExamNamesSql = "SELECT ExamName, ID FROM tblexamination WHERE ExamType = 'Co-Curricular' AND IsDeleted = 0";
                                                $coCurricularExamNamesQuery = $dbh->prepare($coCurricularExamNamesSql);
                                                $coCurricularExamNamesQuery->execute();
                                                $coCurricularExamNames = $coCurricularExamNamesQuery->fetchAll(PDO::FETCH_ASSOC);

                                                // Summative exam names
                                                $summativeExamNamesSql = "SELECT ExamName, ID FROM tblexamination WHERE ExamType = 'Summative' AND IsDeleted = 0";
                                                $summativeExamNamesQuery = $dbh->prepare($summativeExamNamesSql);
                                                $summativeExamNamesQuery->execute();
                                                $summativeExamNames = $summativeExamNamesQuery->fetchAll(PDO::FETCH_ASSOC);

                                                foreach ($examNames as $exam) 
                                                {
                                                    echo "<th scope='col'>" . $exam['ExamName'] . "</th>";
                                                }?>
                                                <th>Total</th>
                                                <th colspan="2">Max Marks</th>
                                                <th colspan="2">Max Marks</th>
                                                <th colspan="2">Max Marks</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            $class = "%$className%";
                                            // Fetch only those subjects of the class whose IsOptional is 0
                                            $subjectsSql = "SELECT * FROM tblsubjects WHERE ClassName LIKE :className AND IsOptional = 0 AND IsCurricularSubject = 0 AND IsDeleted = 0";
                                            $subjectsQuery = $dbh->prepare($subjectsSql);
                                            $subjectsQuery->bindParam(':className', $class, PDO::PARAM_STR);
                                            $subjectsQuery->execute();
                                            $subjects = $subjectsQuery->fetchAll(PDO::FETCH_ASSOC);

                                            // Array to store total marks obtained for each exam
                                            $totalMarks = array_fill(0, count($examNames), 0);
                                            $totalMaxMarks = array_fill(0, count($examNames), 0);
                                            $totalPercentage = array_fill(0, count($examNames), 0);

                                            foreach ($subjects as $subject) 
                                            {
                                                //Array to store marks for each exam
                                                $examMarksArray = array_fill(0, count($examNames), '');
                                                
                                                // Co-curricular marks
                                                $coCurricularMarksArray = array_fill(0, count($coCurricularExamNames), '');
                                                
                                                // Summative marks
                                                $summativeMarksArray = array_fill(0, count($summativeExamNames), '');


                                                // Fetch SubjectsJSON for the current subject from tblreports for all exam sessions
                                                $fetchSubjectsJsonSql = "SELECT SubjectsJSON FROM tblreports WHERE ClassName = :className AND StudentName = :studentID";
                                                $fetchSubjectsJsonQuery = $dbh->prepare($fetchSubjectsJsonSql);
                                                $fetchSubjectsJsonQuery->bindParam(':className', $className, PDO::PARAM_STR);
                                                $fetchSubjectsJsonQuery->bindParam(':studentID', $studentDetails['ID'], PDO::PARAM_STR);
                                                $fetchSubjectsJsonQuery->execute();
                                                $allSubjectsJson = $fetchSubjectsJsonQuery->fetchAll(PDO::FETCH_COLUMN);

                                                // Loop through all subjects JSON data for the current subject
                                                foreach ($allSubjectsJson as $subjectsJson) 
                                                {
                                                    // Decode the JSON to an associative array
                                                    $subjectsData = json_decode($subjectsJson, true);

                                                    // Loop through the decoded JSON to find the SubMarksObtained for the current subject
                                                    foreach ($subjectsData as $subjectData) 
                                                    {
                                                        if ($subjectData['SubjectID'] == $subject['ID']) 
                                                        {
                                                            // Find the index of the exam ID in the $examNames array
                                                            $examIndex = array_search($subjectData['ExamName'], array_column($examNames, 'ID'));

                                                            // Update the corresponding index in the $examMarksArray with the marks obtained
                                                            $examMarksArray[$examIndex] = $subjectData['SubMarksObtained'];

                                                            // Add co-curricular marks if the exam type is co-curricular
                                                            foreach ($coCurricularExamNames as $coCurricularExam) {
                                                                if ($subjectData['ExamName'] == $coCurricularExam['ID']) {
                                                                    $coCurricularIndex = count($examNames) + array_search($subjectData['ExamName'], array_column($coCurricularExamNames, 'ID'));
                                                                    $coCurricularMarksArray[$coCurricularIndex] = $subjectData['SubMarksObtained'];
                                                                    break;
                                                                }
                                                            }
                                                            // Add Summative marks if the exam type is Summative
                                                            foreach ($summativeExamNames as $summativeExam) {
                                                                if ($subjectData['ExamName'] == $summativeExam['ID']) {
                                                                    $summativeIndex = count($examNames) + array_search($subjectData['ExamName'], array_column($summativeExamNames, 'ID'));
                                                                    $summativeMarksArray[$summativeIndex] = $subjectData['SubMarksObtained'];
                                                                    break;
                                                                }
                                                            }

                                                            $totalMarks[$examIndex] += $subjectData['SubMarksObtained'];
                                                            $totalMaxMarks[$examIndex] += $subjectData['SubMaxMarks'];

                                                            break;
                                                        }
                                                    }
                                                }

                                                echo "<tr>
                                                        <td>{$subject['SubjectName']}</td>";

                                                foreach ($examMarksArray as $examMarks) {
                                                    echo "<td>$examMarks</td>";
                                                }
                                                // Total marks obtained for each subject
                                                echo "<td>" . array_sum($examMarksArray) . "</td>";

                                                 // Co-curricular marks
                                                foreach ($coCurricularMarksArray as $coCurricularMarks) {
                                                    echo "<td colspan=''>$coCurricularMarks</td>";
                                                }

                                                 // Summative marks
                                                foreach ($summativeMarksArray as $summativeMarks) {
                                                    echo "<td colspan=''>$summativeMarks</td>";
                                                }
                                                // Total marks obtained for all assessments (FA+CA+SA)
                                                echo "<td>" . (array_sum($examMarksArray) + array_sum($coCurricularMarksArray) + array_sum($summativeMarksArray)) . "</td>";
                                                
                                                echo "</tr>";
                                            }

                                            ?>
                                            <tr>
                                                <td class="font-weight-bold">Marks Obtained</td>
                                                <?php
                                                foreach ($totalMarks as $examTotalMarks) 
                                                {
                                                    if ($examTotalMarks > 0) 
                                                    {
                                                        echo "<td>$examTotalMarks</td>";
                                                    } 
                                                    else 
                                                    {
                                                        echo "<td></td>";
                                                    }
                                                }
                                                // Total marks obtained of all subjects
                                                echo "<td>" . array_sum($totalMarks) . "</td>";
                                                ?>
                                            </tr>
                                            <tr>
                                                <td>Maximum Marks</td>
                                                
                                                <?php
                                                foreach ($totalMaxMarks as $maxMarks) 
                                                {
                                                    if ($maxMarks > 0) 
                                                    {
                                                        echo "<td>$maxMarks</td>";
                                                    } 
                                                    else 
                                                    {
                                                        echo "<td></td>";
                                                    }
                                                }
                                                // Total Max marks of all subjects
                                                echo "<td>" . array_sum($totalMaxMarks) . "</td>";
                                                ?>
                                            </tr>
                                            <tr>
                                                <td>Percentage</td>
                                                <?php
                                                $totalMarksObtained = array_sum($totalMarks);
                                                $totalMaxMarksObtained = array_sum($totalMaxMarks);

                                                foreach ($totalMarks as $key => $examTotalMarks) 
                                                {
                                                    $percentage = $examTotalMarks > 0 ? round(($examTotalMarks / $totalMaxMarks[$key]) * 100, 2).'%' : '';
                                                    echo "<td>$percentage</td>";
                                                }
                                                // total Percentage
                                                $totalPercentage = round(($totalMarksObtained / $totalMaxMarksObtained) * 100, 2) . '%';
                                                echo "<td>$totalPercentage</td>";
                                                ?>
                                            </tr>
                                            <tr>
                                                <td>Grade</td>
                                                <?php
                                                    // Grading system thresholds
                                                    $gradingSystem = array(
                                                        array('A+', 'A', 'B', 'C', 'D'),
                                                        array(85, 70, 55, 40, 33),
                                                        array(100, 85, 70, 55, 40)
                                                    );

                                                    foreach ($totalMarks as $key => $examTotalMarks) 
                                                    {
                                                        $percentage = $examTotalMarks > 0 ? round(($examTotalMarks / $totalMaxMarks[$key]) * 100, 2) : '';

                                                        // Determine grade based on percentage
                                                        $grade = '';
                                                        for ($i = 0; $i < count($gradingSystem[0]); $i++) 
                                                        {
                                                            if ($percentage >= $gradingSystem[1][$i] && $percentage <= $gradingSystem[2][$i]) 
                                                            {
                                                                $grade = $gradingSystem[0][$i];
                                                                break;
                                                            }
                                                        }
                                                        echo "<td>$grade</td>";
                                                    }
                                                    // Total grade based on total percentage
                                                    $totalGrade = '';
                                                    for ($i = 0; $i < count($gradingSystem[0]); $i++) {
                                                        if ($totalPercentage >= $gradingSystem[1][$i] && $totalPercentage <= $gradingSystem[2][$i]) {
                                                            $totalGrade = $gradingSystem[0][$i];
                                                            break;
                                                        }
                                                    }
                                                    // Display total grade
                                                    echo "<td >$totalGrade</td>";
                                                ?>
                                            </tr>
                                            <tr>
                                                <td>Rank</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>


                                <!-- Grading System -->
                                <div class="d-flex flex-column mt-3">
                                    <table class="table table-bordered">
                                        <thead>
                                            <tr>
                                                <th class="text-center" style="vertical-align: middle;" rowspan="2">GRADING SYSTEM</th>
                                                <th colspan="2">A+</th>
                                                <th colspan="2">>85% upto 100%</th>
                                                <th colspan="2">B</th>
                                                <th colspan="2">>55% upto 70%</th>
                                                <th colspan="2">D</th>
                                                <th colspan="2">>33% upto 40%</th>
                                            </tr>
                                            <tr>
                                                <th colspan="2">A</th>
                                                <th colspan="2">>70% upto 85%</th>
                                                <th colspan="2">C</th>
                                                <th colspan="2">>40% upto 55%</th>
                                            </tr>
                                        </thead>
                                    </table>
                                </div>
                                <?php
                                // Check if any optional subject has a grading system
                                if (hasOptionalSubjectWithGrading($dbh, $className, $examSession)) 
                                {
                                ?>
                                    <!-- Optional Subjects in Grades-->
                                    <div class="d-flex flex-column mt-3">
                                        <table class="table table-bordered">
                                            <thead>
                                                <tr class="text-center">
                                                    <th rowspan="3" style="vertical-align: middle;">OPTIONAL SUBJECTS</th>
                                                    <th colspan="12">FORMATIVE / SUMMATIVE ASSESSMENT</th>
                                                </tr>
                                                <tr class="text-center">
                                                    <th colspan="8">GRADE</th>
                                                    <th colspan="2">Summative Assessment</th>
                                                    <th colspan="2">TOTAL (FA+SA)</th>
                                                </tr>
                                                <tr class="text-center">
                                                    <!-- FA Exam Names for Optional Subjects -->
                                                    <?php
                                                        foreach ($examNames as $examName) 
                                                        {
                                                            echo "<th scope=col'>". $examName['ExamName'] . "</th>";
                                                        }
                                                    ?>
                                                    <th colspan="2">GRADE</th>
                                                    <th colspan="2">GRADE</th>
                                                    <th colspan="2">GRADE</th>
                                                </tr>
                                                <?php
                                                    // Fetch only those subjects of the class whose IsOptional is 1
                                                    $subjectsSql = "SELECT * FROM tblsubjects WHERE ClassName LIKE :className AND IsOptional = 1 AND IsCurricularSubject = 0 AND IsDeleted = 0";
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
                                            </thead>
                                            <tbody>

                                            </tbody>
                                        </table>
                                    </div>
                                <?php
                                }
                                else
                                {
                                ?>
                                    <!-- Optional Subjects in Marks-->
                                    <div class="d-flex flex-column mt-3">
                                            <table class="table table-bordered">
                                                <thead>
                                                    <tr class="text-center">
                                                        <th rowspan="3" style="vertical-align: middle;">OPTIONAL SUBJECTS</th>
                                                        <th colspan="14">FORMATIVE / SUMMATIVE ASSESSMENT</th>
                                                    </tr>
                                                    <tr class="text-center">
                                                        <th colspan="8">Formative Assessment</th>
                                                        <th colspan="2">Co-curricular Activities</th>
                                                        <th colspan="2">Summative Assessment</th>
                                                        <th colspan="2">TOTAL (FA+CA+SA)</th>
                                                    </tr>
                                                    <tr class="text-center">
                                                        <!-- FA Exam Names for Optional Subjects -->
                                                        <?php
                                                            foreach ($examNames as $examName) 
                                                            {
                                                                echo "<th scope='col'>$examName</th>";
                                                            }
                                                        ?>
                                                        <th colspan="2">TOTAL</th>
                                                        <th colspan="2">Max Marks</th>
                                                        <th colspan="2">Max Marks</th>
                                                        <th colspan="2">Max Marks</th>
                                                    </tr>
                                                    <?php
                                                        // Fetch only those subjects of the class whose IsOptional is 1
                                                        $subjectsSql = "SELECT * FROM tblsubjects WHERE ClassName LIKE :className AND IsOptional = 1 AND IsCurricularSubject = 0 AND IsDeleted = 0";
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
                                                </thead>
                                                <tbody>

                                                </tbody>
                                            </table>
                                        </div>
                                    <?php
                                }
                                ?>
                                <!-- Co-Curricular Component of Academic Session -->
                                <div class="d-flex flex-column mt-3">
                                    <table class="table table-bordered">
                                        <thead>
                                            <tr class="text-center">
                                                <th colspan="14">Marks Obtained in Co-curricular Component During the Academic Session</th>
                                            </tr>
                                            <tr class="text-center">
                                                <?php
                                                // Fetch only those subjects of the class whose Co-curricular is 1
                                                $subjectsSql = "SELECT * FROM tblsubjects WHERE ClassName LIKE :className AND IsOptional = 0 AND IsCurricularSubject = 1 AND IsDeleted = 0";
                                                $subjectsQuery = $dbh->prepare($subjectsSql);
                                                $subjectsQuery->bindParam(':className', $class, PDO::PARAM_STR);
                                                $subjectsQuery->execute();
                                                $subjects = $subjectsQuery->fetchAll(PDO::FETCH_ASSOC);

                                                foreach ($subjects as $subject) 
                                                {
                                                    echo "<th colspan='2'>{$subject['SubjectName']}</th>";
                                                }
                                                ?>
                                                <th colspan='2'>Marks Obtained</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <?php
                                                foreach ($subjects as $subject) 
                                                {
                                                    // Initialize SubMarksObtained for the current subject
                                                    $subMarksObtained = '';

                                                    // Fetch SubjectsJSON for the current subject from tblreports
                                                    $fetchSubjectsJsonSql = "SELECT SubjectsJSON FROM tblcocurricularreports WHERE ClassName = :className AND ExamSession = :examSession AND StudentName = :studentID";
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

                                                    // Display the marks obtained for the current subject
                                                    echo "<td colspan='2'>$subMarksObtained</td>";
                                                }
                                                ?>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <footer class="d-flex justify-content-end mt-5">
                                    <div class="mt-5">
                                        <label>Signature of Tr. Incharge:</label><span class="border-bottom border-secondary ml-2 signature-line"></span>
                                    </div>
                                </footer>
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
