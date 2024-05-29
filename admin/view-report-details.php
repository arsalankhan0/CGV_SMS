<?php
session_start();
error_reporting(0);
include('includes/dbconnection.php');

if (!isset($_SESSION['sturecmsaid']) || empty($_SESSION['sturecmsaid'])) 
{
    header('location:logout.php');
} 
else 
{
    // Function to check if there is grading system
function hasOptionalSubjectWithGrading($dbh, $className, $examSession) 
{
    $class = "%$className%";
    $optionalGradingSql = "SELECT COUNT(*) FROM tblsubjects AS s
                        INNER JOIN tblmaxmarks AS m ON s.ID = m.SubjectID
                        WHERE s.ClassName LIKE :className 
                        AND s.IsOptional = 1 
                        AND s.IsCurricularSubject = 0 
                        AND s.IsDeleted = 0
                        AND m.GradingSystem = 1
                        AND m.ClassID = :className";
    $optionalGradingQuery = $dbh->prepare($optionalGradingSql);
    $optionalGradingQuery->bindParam(':className', $class, PDO::PARAM_STR);
    $optionalGradingQuery->execute();
    $optionalGradingCount = $optionalGradingQuery->fetchColumn();
    return $optionalGradingCount > 0;
}

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
                $studentName = $_GET['studentName'];

                $sqlReports = "SELECT * FROM tblreports WHERE ExamSession = :examSession AND ClassName = :className AND StudentName = :studentName AND IsDeleted = 0";
                $stmtReports = $dbh->prepare($sqlReports);
                $stmtReports->bindParam(':examSession', $examSession, PDO::PARAM_STR);
                $stmtReports->bindParam(':className', $className, PDO::PARAM_INT);
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
                echo "<script>console.error('Error:---> " . $e->getMessage() . "');</script>";
            }

            $examSession = $_GET['examSession'];
            $sqlSubjects = "SELECT * FROM tblsubjects WHERE SessionID = :examSession AND IsDeleted = 0";
            $querySubjects = $dbh->prepare($sqlSubjects);
            $querySubjects->bindParam(':examSession', $examSession, PDO::PARAM_INT);
            $querySubjects->execute();
            $subjects = $querySubjects->fetchAll(PDO::FETCH_ASSOC);


            // Get the active session ID and Name
            $getSessionSql = "SELECT session_id, session_name FROM tblsessions WHERE session_id = :selectedSession AND IsDeleted = 0";
            $sessionQuery = $dbh->prepare($getSessionSql);
            $sessionQuery->bindParam(':selectedSession', $examSession, PDO::PARAM_STR);
            $sessionQuery->execute();
            $session = $sessionQuery->fetch(PDO::FETCH_ASSOC);
            $sessionID = $session['session_id'];
            $sessionName = $session['session_name'];


            if (isset($reports)) {
            ?>
                <!DOCTYPE html>
                <html lang="en">

                <head>
                    <title>TPS || Student Report</title>
                    <meta name="viewport" content="width=device-width, initial-scale=1">
                    <link rel="stylesheet" href="css/style.css" />
                    <link rel="stylesheet" href="./css/finalReportCard.css">
                </head>

                <body>
                <div class="container-scroller">
                <div class="container page-body-wrapper d-flex flex-column">
                    <?php

                        // Fetch student details
                        $studentDetailsSql = "SELECT ID, StudentName, CodeNumber, StudentSection, StudentClass, RollNo, FatherName FROM tblstudent WHERE ID = :studentID AND IsDeleted = 0";
                        $studentDetailsQuery = $dbh->prepare($studentDetailsSql);
                        $studentDetailsQuery->bindParam(':studentID', $_GET['studentName'], PDO::PARAM_INT);
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
                                <img src="../Main/img/logo1.png" alt="img" class="watermark">
                                <!-- Student's Details -->
                                <div class="mt-4">
                                    <div class="d-flex flex-row justify-content-between">
                                        <div>
                                            <label>Student's Code No:</label><span class="border-bottom border-dark ml-2 px-3"><?php echo htmlentities($studentDetails['CodeNumber']); ?></span>
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
                                <!-- Main Subjects -->
                                <div class="d-flex flex-column">
                                    <table class="table ">
                                        <thead>
                                            <tr class="text-center">
                                                <th rowspan="2" colspan="2" style="vertical-align: middle;">Subjects</th>
                                                <th colspan="7">Formative Assessment <br><br>Max. Marks: 5x6 = 30</th>
                                                <th colspan="2" class="text-wrap">Co-Curricular Activities</th>
                                                <th colspan="2" class="text-wrap">Summative Assessment</th>
                                                <th colspan="2" class="text-wrap">Total (FA+CA+SA)</th>
                                            </tr>
                                            <tr class="text-center">
                                                <?php
                                                function fetchExamNames($dbh, $examType, $examSession) {
                                                    $examNamesSql = "SELECT ExamName, ID FROM tblexamination WHERE ExamType = :examType AND IsDeleted = 0 AND session_id = :examSession";
                                                    $examNamesQuery = $dbh->prepare($examNamesSql);
                                                    $examNamesQuery->bindParam(':examType', $examType, PDO::PARAM_STR);
                                                    $examNamesQuery->bindParam(':examSession', $examSession, PDO::PARAM_INT);
                                                    $examNamesQuery->execute();
                                                    return $examNamesQuery->fetchAll(PDO::FETCH_ASSOC);
                                                }
                                                // Fetch exam names and IDs as per the parameters
                                                $examNames = fetchExamNames($dbh, 'Formative', $examSession);
                                                $coCurricularExamNames = fetchExamNames($dbh, 'Co-Curricular', $examSession);
                                                $summativeExamNames = fetchExamNames($dbh, 'Summative', $examSession);

                                                foreach ($examNames as $exam) 
                                                {
                                                    echo "<th scope='col'>" . $exam['ExamName'] . "</th>";
                                                }
                                                ?>
                                                <th>Total</br>(30)</th>
                                                <th colspan="2">Max Marks: 20</th>
                                                <th colspan="2">Max Marks: 50</th>
                                                <th colspan="2">Max Marks: 100</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            $class = "%$className%";

                                            // Function to show subjects as per the condition
                                            function fetchSubjects($dbh, $className, $isOptional, $isCurricularSubject, $examSession) {
                                                $subjectsSql = "SELECT ID, SubjectName FROM tblsubjects 
                                                                WHERE ClassName LIKE :className 
                                                                AND IsOptional = :isOptional 
                                                                AND IsCurricularSubject = :isCurricularSubject 
                                                                AND IsDeleted = 0 
                                                                AND SessionID = :examSession";
                                                $subjectsQuery = $dbh->prepare($subjectsSql);
                                                $subjectsQuery->bindParam(':className', $className, PDO::PARAM_STR);
                                                $subjectsQuery->bindParam(':isOptional', $isOptional, PDO::PARAM_INT);
                                                $subjectsQuery->bindParam(':isCurricularSubject', $isCurricularSubject, PDO::PARAM_INT);
                                                $subjectsQuery->bindParam(':examSession', $examSession, PDO::PARAM_INT);
                                                $subjectsQuery->execute();
                                                return $subjectsQuery->fetchAll(PDO::FETCH_ASSOC);
                                            }
                                            // Fetch only those subjects of the class whose IsOptional is 0
                                            $subjects = fetchSubjects($dbh, $class, 0, 0, $examSession);
                                            

                                            // Array to store total marks obtained for each exam
                                            $totalMarks = array_fill(0, count($examNames), 0);
                                            $totalMaxMarks = array_fill(0, count($examNames), 0);
                                            $totalPercentage = array_fill(0, count($examNames), 0);

                                            // Array to store total marks obtained for co-curricular each exam
                                            $totalCoCurricularMarks = array_fill(0, count($coCurricularExamNames), 0);
                                            $totalCoCurricularMaxMarks = array_fill(0, count($coCurricularExamNames), 0);
                                            $totalCoCurricularPercentage = array_fill(0, count($coCurricularExamNames), 0);

                                            // Array to store total marks obtained for co-curricular each exam
                                            $totalSummativeMarks = array_fill(0, count($summativeExamNames), 0);
                                            $totalSummativeMaxMarks = array_fill(0, count($summativeExamNames), 0);
                                            $totalSummativePercentage = array_fill(0, count($summativeExamNames), 0);
                                            
                                            //Array to store marks for each exam
                                            $examMarksArray = array_fill(0, count($examNames), '');
                                            // Array to store co-curricular marks for each exam
                                            $coCurricularMarksArray = array_fill(count($examNames), count($coCurricularExamNames), '');
                                            // Array to store summative marks for each exam
                                            $summativeMarksArray = array_fill(count($examNames), count($summativeExamNames), '');

                                            // Fetch only those subjects of the class whose IsOptional is 0 and Co-curricular is 1
                                            $CCsubjects = fetchSubjects($dbh, $class, 0, 1, $examSession);

                                            foreach ($CCsubjects as $subject) 
                                            {
                                                // Initialize SubMarksObtained for the current subject
                                                $subMarksObtained = '';
                                                $subMaxMarks = '';

                                                // Fetch SubjectsJSON for the current subject from tblreports
                                                $fetchSubjectsJsonSql = "SELECT SubjectsJSON FROM tblcocurricularreports WHERE ClassName = :className AND ExamSession = :examSession AND StudentName = :studentID";
                                                $fetchSubjectsJsonQuery = $dbh->prepare($fetchSubjectsJsonSql);
                                                $fetchSubjectsJsonQuery->bindParam(':className', $className, PDO::PARAM_STR);
                                                $fetchSubjectsJsonQuery->bindParam(':examSession', $examSession, PDO::PARAM_STR);
                                                $fetchSubjectsJsonQuery->bindParam(':studentID', $studentDetails['ID'], PDO::PARAM_STR);
                                                $fetchSubjectsJsonQuery->execute();
                                                $subjectsJson = $fetchSubjectsJsonQuery->fetch(PDO::FETCH_COLUMN);

                                                $CCsubjectsData = !empty($subjectsJson) ? json_decode($subjectsJson, true) : [];
                                            }
                                            $CCtotalMarksObtained = array_sum(array_column($CCsubjectsData, 'CoCurricularMarksObtained'));
                                            $CCtotalMaxMarks = array_sum(array_column($CCsubjectsData, 'CoCurricularMaxMarks'));
                                            $CCGrandTotal = 0;
                                            $CCGrandMaxTotal = 0;

                                            foreach ($subjects as $subject) 
                                            {
                                                // Calculating the grand total of co-curricular subject 
                                                $CCGrandTotal += $CCtotalMarksObtained;
                                                $CCGrandMaxTotal += $CCtotalMaxMarks;

                                                // Fetch SubjectsJSON for the current subject from tblreports for all exam sessions
                                                $fetchSubjectsJsonSql = "SELECT SubjectsJSON FROM tblreports WHERE ClassName = :className AND StudentName = :studentID AND ExamSession = :sessionID";
                                                $fetchSubjectsJsonQuery = $dbh->prepare($fetchSubjectsJsonSql);
                                                $fetchSubjectsJsonQuery->bindParam(':className', $className, PDO::PARAM_STR);
                                                $fetchSubjectsJsonQuery->bindParam(':studentID', $studentDetails['ID'], PDO::PARAM_STR);
                                                $fetchSubjectsJsonQuery->bindParam(':sessionID', $examSession, PDO::PARAM_STR);
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
                                                            // Find the index of the exam ID in the $summativeExamNames array
                                                            $summativeExamIndex = array_search($subjectData['ExamName'], array_column($summativeExamNames, 'ID'));

                                                            // Update the corresponding index in the $examMarksArray with the marks obtained
                                                            if ($examIndex !== false) {
                                                                $examMarksArray[$examIndex] = $subjectData['SubMarksObtained'];
                                                            }
                                                            
                                                            // Add Summative marks if the exam type is Summative
                                                            foreach ($summativeExamNames as $summativeExam) {
                                                                if ($subjectData['ExamName'] == $summativeExam['ID']) {
                                                                    $summativeIndex = count($examNames) + array_search($subjectData['ExamName'], array_column($summativeExamNames, 'ID'));
                                                                    $summativeMarksArray[$summativeIndex] = $subjectData['SubMarksObtained'];
                                                                    // Getting total marks obtained in summative exam.
                                                                    $totalSummativeMarks[$summativeExamIndex] += (float)$subjectData['SubMarksObtained']; 
                                                                    // Getting total max marks in summative exam.
                                                                    $totalSummativeMaxMarks[$summativeExamIndex] += (float)$subjectData['SubMaxMarks']; 
                                                                    break;
                                                                }
                                                            }
                                                            
                                                             // Update total marks and max marks
                                                            if ($examIndex !== false) {
                                                                $totalMarks[$examIndex] += (float)$subjectData['SubMarksObtained'];
                                                                $totalMaxMarks[$examIndex] += (float)$subjectData['SubMaxMarks'];
                                                            }

                                                        }
                                                    }
                                                }

                                                echo "<tr>
                                                        <td colspan='2'>{$subject['SubjectName']}</td>";
                                                        foreach ($examMarksArray as $examMarks) {
                                                            echo "<td>$examMarks</td>";
                                                        }
                                                        // Total marks obtained for each subject
                                                        echo "<td>" . array_sum($examMarksArray) . "</td>";

                                                        //  Co-curricular marks
                                                        echo "<td colspan='2'>". $CCtotalMarksObtained ."</td>";

                                                        //  Summative marks
                                                        foreach ($summativeMarksArray as $summativeMarks) {
                                                            echo "<td colspan='2'>$summativeMarks</td>";
                                                        }
                                                        // Total marks obtained for all assessments (FA+CA+SA)
                                                        echo "<td colspan='2'>" . (array_sum($examMarksArray) + $CCtotalMarksObtained + array_sum($summativeMarksArray)) . "</td>";
                                                echo "</tr>";
                                            }

                                            ?>
                                            <!-- Marks Obtained -->
                                            <tr>
                                                <td class="font-weight-bold text-right" colspan="2">Marks Obtained</td>
                                                <?php
                                                // Total marks of each formative exam(column) in all subjects
                                                foreach ($totalMarks as $examTotalMarks) 
                                                {
                                                    if ($examTotalMarks > 0) 
                                                    {
                                                        echo "<td>".$examTotalMarks."</td>";
                                                    } 
                                                    else 
                                                    {
                                                        echo "<td></td>";
                                                    }
                                                }
                                                // Total marks obtained of all subjects in Formative
                                                echo "<td>" . array_sum($totalMarks) . "</td>";
                                                
                                                // Total marks of co-curricular exam(column) in all subjects
                                                echo "<td colspan='2'>". $CCGrandTotal ."</td>";

                                                // Total marks of summative exam(column) in all subjects
                                                foreach ($totalSummativeMarks as $examTotalMarks) 
                                                {
                                                    if ($examTotalMarks > 0) 
                                                    {
                                                        echo "<td colspan='2'>".$examTotalMarks."</td>";
                                                    } 
                                                    else 
                                                    {
                                                        echo "<td colspan='2'></td>";
                                                    }
                                                }
                                                //total marks obtained in all three FA+CA+SA
                                                echo "<td colspan='2'>" . array_sum($totalMarks) + $CCGrandTotal + array_sum($totalSummativeMarks) . "</td>"; 
                                                ?>
                                            </tr>
                                            <!-- Maximum Marks -->
                                            <tr>
                                                <td class="text-right" colspan="2">Maximum Marks</td>
                                                
                                                <?php
                                                // Total max marks of all formative exam(column) in all subjects
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
                                                // Total max marks of co-curricular exam(column) in all subjects
                                                echo "<td colspan='2'>". $CCGrandMaxTotal ."</td>";

                                                // Total max marks of summative exam(column) in all subjects
                                                foreach ($totalSummativeMaxMarks as $maxMarks) 
                                                {
                                                    if ($maxMarks > 0) 
                                                    {
                                                        echo "<td colspan='2'>$maxMarks</td>";
                                                    } 
                                                    else 
                                                    {
                                                        echo "<td colspan='2'></td>";
                                                    }
                                                }
                                                //total max marks in all three FA+CA+SA
                                                echo "<td colspan='2'>" . array_sum($totalMaxMarks) + $CCGrandMaxTotal + array_sum($totalSummativeMaxMarks) . "</td>";
                                                ?>
                                            </tr>
                                            <!-- Percentage -->
                                            <tr>
                                                <td class="text-right" colspan="2">Percentage</td>
                                                <?php
                                                // total marks obtained for each type of exam
                                                $totalMarksObtained = array_sum($totalMarks);
                                                $totalCoCurricularMarksObtained = array_sum($totalCoCurricularMarks);
                                                $totalSummativeMarksObtained = array_sum($totalSummativeMarks);

                                                // total maximum marks obtained for each type of exam
                                                $totalMaxMarksObtained = array_sum($totalMaxMarks);
                                                $totalCoCurricularMaxMarksObtained = array_sum($totalCoCurricularMaxMarks);
                                                $totalSummativeMaxMarksObtained = array_sum($totalSummativeMaxMarks);

                                                // percentage of each formative exam
                                                foreach ($totalMarks as $key => $examTotalMarks) {
                                                    $percentage = $examTotalMarks > 0 ? round(($examTotalMarks / $totalMaxMarks[$key]) * 100, 2).'%' : '';
                                                    echo "<td>$percentage</td>";
                                                }
                                                // total percentage of formative exams
                                                $totalFormativePercentage = $totalMaxMarksObtained > 0 ? round(($totalMarksObtained / $totalMaxMarksObtained) * 100, 2) : 0;
                                                echo "<td>$totalFormativePercentage%</td>";

                                                // percentage of co-curricular exam
                                                $CCpercentage = $CCGrandMaxTotal > 0 ? round(($CCGrandTotal / $CCGrandMaxTotal) * 100, 2) . '%' : '';
                                                echo "<td colspan='2'>". $CCpercentage ."</td>";

                                                // percentage of summative exam
                                                foreach ($totalSummativeMarks as $key => $examTotalMarks) {
                                                    $percentage = $examTotalMarks > 0 ? round(($examTotalMarks / $totalSummativeMaxMarks[$key]) * 100, 2).'%' : '';
                                                    echo "<td colspan='2'>$percentage</td>";
                                                }

                                                // Calculate the total percentage of all exams (FA+CA+SA) and display it
                                                $totalAllExamsPercentage = ($totalMarksObtained + $CCGrandTotal + $totalSummativeMarksObtained) > 0 ? round((($totalMarksObtained + $CCGrandTotal + $totalSummativeMarksObtained) / ($totalMaxMarksObtained + $CCGrandMaxTotal + $totalSummativeMaxMarksObtained)) * 100, 2) : 0;
                                                echo "<td colspan='2'>$totalAllExamsPercentage%</td>";
                                                ?>
                                            </tr>
                                            <!-- Grade -->
                                            <tr>
                                                <td class="text-right" colspan="2">Grade</td>
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
                                                    $totalFormativeGrade = '';
                                                    for ($i = 0; $i < count($gradingSystem[0]); $i++) {
                                                        if ($totalFormativePercentage >= $gradingSystem[1][$i] && $totalFormativePercentage <= $gradingSystem[2][$i]) {
                                                            $totalFormativeGrade = $gradingSystem[0][$i];
                                                            break;
                                                        }
                                                    }
                                                    // Display total grade for formative exams
                                                    echo "<td >$totalFormativeGrade</td>";

                                                    // Calculating Co-curricular grade
                                                    $CoCurricularGrade = '';
                                                    $CCpercentage = $CCGrandMaxTotal > 0 ? round(($CCGrandTotal / $CCGrandMaxTotal) * 100, 2) : '';
                                                    for ($i = 0; $i < count($gradingSystem[0]); $i++) 
                                                    {
                                                        if ($CCpercentage >= $gradingSystem[1][$i] && $CCpercentage <= $gradingSystem[2][$i]) 
                                                        {
                                                            $CoCurricularGrade = $gradingSystem[0][$i];
                                                            break;
                                                        }
                                                    }
                                                    echo "<td colspan='2'>$CoCurricularGrade</td>";

                                                    // Calculating Summative grade
                                                    foreach ($totalSummativeMarks as $key => $examTotalMarks) 
                                                    {
                                                        $percentage = $examTotalMarks > 0 ? round(($examTotalMarks / $totalSummativeMaxMarks[$key]) * 100, 2) : '';

                                                        // Determine grade based on percentage
                                                        $SummativeGrade = '';
                                                        for ($i = 0; $i < count($gradingSystem[0]); $i++) 
                                                        {
                                                            if ($percentage >= $gradingSystem[1][$i] && $percentage <= $gradingSystem[2][$i]) 
                                                            {
                                                                $SummativeGrade = $gradingSystem[0][$i];
                                                                break;
                                                            }
                                                        }
                                                        echo "<td colspan='2'>$SummativeGrade</td>";
                                                    }

                                                     // Calculate total percentage for all exams (FA+CA+SA)
                                                     $totalPercentage = ($totalMarksObtained + $CCGrandTotal + $totalSummativeMarksObtained) > 0 ? round((($totalMarksObtained + $CCGrandTotal + $totalSummativeMarksObtained) / ($totalMaxMarksObtained + $CCGrandMaxTotal + $totalSummativeMaxMarksObtained)) * 100, 2) : 0;

                                                    // Determine total grade based on total percentage
                                                    $totalGrade = '';
                                                    for ($i = 0; $i < count($gradingSystem[0]); $i++) {
                                                        if ($totalPercentage >= $gradingSystem[1][$i] && $totalPercentage <= $gradingSystem[2][$i]) {
                                                            $totalGrade = $gradingSystem[0][$i];
                                                            break;
                                                        }
                                                    }
                                                    // Display total grade
                                                    echo "<td colspan='2'>$totalGrade</td>";
                                                ?>
                                            </tr>
                                            <!-- Rank -->
                                            <tr>
                                                <td class="text-right" colspan="2">Rank</td>
                                                <?php
                                                // Rank mappings
                                                $rankMappings = array(
                                                    'A+' => 'SKY',
                                                    'A' => 'MOUNTAIN',
                                                    'B' => 'MOUNTAIN',
                                                    'C' => 'MOUNTAIN',
                                                    'D' => 'RIVER'
                                                );

                                                foreach ($totalMarks as $key => $examTotalMarks) {
                                                    $percentage = $examTotalMarks > 0 ? round(($examTotalMarks / $totalMaxMarks[$key]) * 100, 2) : '';

                                                    // Determine grade based on percentage
                                                    $grade = '';
                                                    for ($i = 0; $i < count($gradingSystem[0]); $i++) {
                                                        if ($percentage >= $gradingSystem[1][$i] && $percentage <= $gradingSystem[2][$i]) {
                                                            $grade = $gradingSystem[0][$i];
                                                            break;
                                                        }
                                                    }

                                                    // Determine rank based on grade
                                                    $rank = isset($rankMappings[$grade]) ? $rankMappings[$grade] : '';

                                                    // Display the rank
                                                    echo "<td class='text-wrap' style='font-size: 0.7rem'>$rank</td>";
                                                }

                                                // Determine total grade based on total percentage
                                                $totalFormativeGrade = '';
                                                for ($i = 0; $i < count($gradingSystem[0]); $i++) {
                                                    if ($totalFormativePercentage >= $gradingSystem[1][$i] && $totalFormativePercentage <= $gradingSystem[2][$i]) {
                                                        $totalFormativeGrade = $gradingSystem[0][$i];
                                                        break;
                                                    }
                                                }

                                                // Determine total rank based on total grade
                                                $totalRank = isset($rankMappings[$totalFormativeGrade]) ? $rankMappings[$totalFormativeGrade] : '';

                                                // Display the total rank
                                                echo "<td class='text-wrap' style='font-size: 0.7rem'>$totalRank</td>";

                                                // Calculating Co-curricular rank
                                                $CoCurricularGrade = '';
                                                for ($i = 0; $i < count($gradingSystem[0]); $i++) {
                                                    if ($CCpercentage >= $gradingSystem[1][$i] && $CCpercentage <= $gradingSystem[2][$i]) {
                                                        $CoCurricularGrade = $gradingSystem[0][$i];
                                                        break;
                                                    }
                                                }

                                                // Determine rank based on grade
                                                $CoCurricularRank = isset($rankMappings[$CoCurricularGrade]) ? $rankMappings[$CoCurricularGrade] : '';

                                                // Display the rank
                                                echo "<td colspan='2'>$CoCurricularRank</td>";


                                                // Calculating Summative rank
                                                foreach ($totalSummativeMarks as $key => $examTotalMarks) {
                                                    $percentage = $examTotalMarks > 0 ? round(($examTotalMarks / $totalSummativeMaxMarks[$key]) * 100, 2) : '';

                                                    // Determine grade based on percentage
                                                    $SummativeGrade = '';
                                                    for ($i = 0; $i < count($gradingSystem[0]); $i++) {
                                                        if ($percentage >= $gradingSystem[1][$i] && $percentage <= $gradingSystem[2][$i]) {
                                                            $SummativeGrade = $gradingSystem[0][$i];
                                                            break;
                                                        }
                                                    }

                                                    // Determine rank based on grade
                                                    $SummativeRank = isset($rankMappings[$SummativeGrade]) ? $rankMappings[$SummativeGrade] : '';

                                                    // Display the rank
                                                    echo "<td colspan='2'>$SummativeRank</td>";
                                                }

                                                // Determine total grade based on total percentage
                                                $totalPercentage = ($totalMarksObtained + $CCGrandTotal + $totalSummativeMarksObtained) > 0 ? round((($totalMarksObtained + $CCGrandTotal + $totalSummativeMarksObtained) / ($totalMaxMarksObtained + $CCGrandMaxTotal + $totalSummativeMaxMarksObtained)) * 100, 2) : 0;

                                                // Determine total grade based on total percentage
                                                $totalGrade = '';
                                                for ($i = 0; $i < count($gradingSystem[0]); $i++) {
                                                    if ($totalPercentage >= $gradingSystem[1][$i] && $totalPercentage <= $gradingSystem[2][$i]) {
                                                        $totalGrade = $gradingSystem[0][$i];
                                                        break;
                                                    }
                                                }

                                                // Determine total rank based on total grade
                                                $totalRank = isset($rankMappings[$totalGrade]) ? $rankMappings[$totalGrade] : '';

                                                // Display the total rank
                                                echo "<td colspan='2'>$totalRank</td>";
                                                ?>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>


                                <!-- Grading System -->
                                <div class="d-flex flex-column mt-3">
                                    <table class="table ">
                                        <thead>
                                            <tr>
                                                <th class="text-center text-wrap" style="vertical-align: middle;" rowspan="2" colspan="2">GRADING SYSTEM</th>
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
                                        <table class="table ">
                                            <thead>
                                                <tr class="text-center">
                                                    <th rowspan="3" colspan="2" class="text-wrap" style="vertical-align: middle;">OPTIONAL SUBJECTS</th>
                                                    <th colspan="12" class="text-wrap">FORMATIVE / SUMMATIVE ASSESSMENT</th>
                                                </tr>
                                                <tr class="text-center">
                                                    <th colspan="8">GRADE</th>
                                                    <th colspan="2" class="text-wrap">Summative Assessment</th>
                                                    <th colspan="2" class="text-warp">TOTAL (FA+SA)</th>
                                                </tr>
                                                <tr class="text-center">
                                                    <!-- FA Exam Names for Optional Subjects -->
                                                    <?php
                                                        foreach ($examNames as $examName) 
                                                        {
                                                            echo "<th scope='col'>". $examName['ExamName'] . "</th>";
                                                        }
                                                    ?>
                                                    <th colspan="2">GRADE</th>
                                                    <th colspan="2">GRADE</th>
                                                    <th colspan="2">GRADE</th>
                                                </tr>
                                                <?php
                                                    // Fetch only those subjects of the class whose IsOptional is 1 and Co-curricular is 0
                                                    $subjects = fetchSubjects($dbh, $class, 1, 0, $examSession);

                                                    //Array to store Grades for each exam
                                                    $examGradeMarksArray = array_fill(0, count($examNames), '');
                                                    // Array to store summative marks for each exam
                                                    $summativeGradeArray = array_fill(count($examNames), count($summativeExamNames), '');

                                                    foreach ($subjects as $subject) 
                                                    {
                                                        $fetchSubjectsJsonSql = "SELECT SubjectsJSON FROM tblreports WHERE ClassName = :className AND ExamSession = :examSession AND StudentName = :studentID AND IsDeleted = 0";
                                                        $fetchSubjectsJsonQuery = $dbh->prepare($fetchSubjectsJsonSql);
                                                        $fetchSubjectsJsonQuery->bindParam(':className', $className, PDO::PARAM_STR);
                                                        $fetchSubjectsJsonQuery->bindParam(':examSession', $examSession, PDO::PARAM_STR);
                                                        $fetchSubjectsJsonQuery->bindParam(':studentID', $studentDetails['ID'], PDO::PARAM_STR);
                                                        $fetchSubjectsJsonQuery->execute();
                                                        $subjectsJson = $fetchSubjectsJsonQuery->fetch(PDO::FETCH_COLUMN);

                                                        $subjectsData = json_decode($subjectsJson, true);
                                                        $subMarksObtained = '';

                                                        foreach ($subjectsData as $subjectData) 
                                                        {
                                                            if ($subjectData['SubjectID'] == $subject['ID']) 
                                                            {
                                                                // Find the index of the exam ID in the $examNames, $summativeExamNames arrays
                                                                $examGradeIndex = array_search($subjectData['ExamName'], array_column($examNames, 'ID'));
                                                                $summativeGradeIndex = array_search($subjectData['ExamName'], array_column($summativeExamNames, 'ID'));

                                                                if ($examGradeIndex !== false) {
                                                                    $examGradeMarksArray[$examGradeIndex] = $subjectData['SubMarksObtained'];
                                                                }
                                                                $subMarksObtained = $subjectData['SubMarksObtained'];

                                                                // Add Summative Grade if the exam type is Summative
                                                                foreach ($summativeExamNames as $summativeExam) {
                                                                    if ($subjectData['ExamName'] == $summativeExam['ID']) {
                                                                        $summativeGradeIndex = count($examNames) + array_search($subjectData['ExamName'], array_column($summativeExamNames, 'ID'));
                                                                        $summativeGradeArray[$summativeGradeIndex] = $subjectData['SubMarksObtained'];
                                                                        break;
                                                                    }
                                                                }
                                                            }
                                                        }
                                                        echo "<tr>
                                                                <td colspan='2'>{$subject['SubjectName']}</td>";
                                                                // Formative Grade
                                                                foreach ($examGradeMarksArray as $examMarks) {
                                                                    echo "<td>$examMarks</td>";
                                                                }
                                                                echo "<td colspan='2'></td>";
                                                                 //  Summative Grade
                                                                foreach ($summativeGradeArray as $coCurricularMarks) {
                                                                    echo "<td colspan='2'>$coCurricularMarks</td>";
                                                                }
                                                                echo "<td colspan='2'></td>";
                                                            echo "</tr>";
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
                                            <table class="table ">
                                                <thead>
                                                    <tr class="text-center">
                                                        <th rowspan="3" colspan="2" class="text-wrap" style="vertical-align: middle;">OPTIONAL SUBJECTS</th>
                                                        <th colspan="14">FORMATIVE / CO-CURRICULAR / SUMMATIVE ASSESSMENT</th>
                                                    </tr>
                                                    <tr class="text-center">
                                                        <th colspan="8">Formative Assessment<br><br> Max. Marks: 5x6 = 30</th>
                                                        <th colspan="2" class="text-wrap">Co-curricular Activities</th>
                                                        <th colspan="2" class="text-wrap">Summative Assessment</th>
                                                        <th colspan="2" class="text-wrap">TOTAL (FA+CA+SA)</th>
                                                    </tr>
                                                    <tr class="text-center">
                                                        <!-- FA Exam Names for Optional Subjects -->
                                                        <?php
                                                            foreach ($examNames as $examName) 
                                                            {
                                                                echo "<th scope='col'>".$examName['ExamName']."</th>";
                                                            }
                                                        ?>
                                                        <th colspan="2">TOTAL(30)</th>
                                                        <th colspan="2" class="text-wrap">Max Marks: 20</th>
                                                        <th colspan="2" class="text-wrap">Max Marks: 50</th>
                                                        <th colspan="2" class="text-wrap">Max Marks: 100</th>
                                                    </tr>
                                                    <?php
                                                        // Fetch only those subjects of the class whose IsOptional is 1 and Co-curricular is 0
                                                        $subjects = fetchSubjects($dbh, $class, 1, 0, $examSession);

                                                        //Array to store marks for each exam
                                                        $examMarksArrayOptional = array_fill(0, count($examNames), '');
                                                        // Array to store summative marks for each exam
                                                        $summativeMarksArrayOptional = array_fill(count($examNames), count($summativeExamNames), '');

                                                        foreach ($subjects as $subject) 
                                                        {
                                                            $subMarksObtained = '';

                                                            $fetchSubjectsJsonSql = "SELECT SubjectsJSON FROM tblreports WHERE ClassName = :className AND ExamSession = :examSession AND StudentName = :studentID";
                                                            $fetchSubjectsJsonQuery = $dbh->prepare($fetchSubjectsJsonSql);
                                                            $fetchSubjectsJsonQuery->bindParam(':className', $className, PDO::PARAM_STR);
                                                            $fetchSubjectsJsonQuery->bindParam(':examSession', $examSession, PDO::PARAM_STR);
                                                            $fetchSubjectsJsonQuery->bindParam(':studentID', $studentDetails['ID'], PDO::PARAM_STR);
                                                            $fetchSubjectsJsonQuery->execute();
                                                            $subjectsJson = $fetchSubjectsJsonQuery->fetch(PDO::FETCH_COLUMN);

                                                            $subjectsData = json_decode($subjectsJson, true);

                                                            foreach ($subjectsData as $subjectData) 
                                                            {
                                                                if ($subjectData['SubjectID'] == $subject['ID']) 
                                                                {
                                                                    // Find the index of the exam ID in the $examNames, $summativeExamNames arrays
                                                                    $examIndexOptional = array_search($subjectData['ExamName'], array_column($examNames, 'ID'));
                                                                    $summativeExamIndexOptional = array_search($subjectData['ExamName'], array_column($summativeExamNames, 'ID'));

                                                                    // Update the corresponding index in the $examMarksArrayOptional with the marks obtained
                                                                    if ($examIndexOptional !== false) {
                                                                        $examMarksArrayOptional[$examIndexOptional] = $subjectData['SubMarksObtained'];
                                                                    }
                                                                    foreach ($summativeExamNames as $summativeExam) {
                                                                        if ($subjectData['ExamName'] == $summativeExam['ID']) {
                                                                            $summativeIndexOptional = count($examNames) + array_search($subjectData['ExamName'], array_column($summativeExamNames, 'ID'));
                                                                            $summativeMarksArrayOptional[$summativeIndexOptional] = $subjectData['SubMarksObtained'];
                                                                            break;
                                                                        }
                                                                    }

                                                                    $subMarksObtained = $subjectData['SubMarksObtained'];
                                                                }
                                                            }

                                                            echo "<tr>
                                                                    <td colspan='2'>{$subject['SubjectName']}</td>";
                                                                    // All Formative Exams marks
                                                                    foreach ($examMarksArrayOptional as $examMarks) {
                                                                        echo "<td>$examMarks</td>";
                                                                    }
                                                                    echo "<td colspan='2'>" . array_sum($examMarksArrayOptional) . "</td>";
                                                                    //Co-Curricular Exam marks
                                                                    echo "<td colspan='2'>{$CCtotalMarksObtained}</td>";
                                                                    //Summative Exam marks
                                                                    foreach ($summativeMarksArrayOptional as $examMarks) {
                                                                        echo "<td colspan='2'>$examMarks</td>";
                                                                    }

                                                                    // Total marks obtained for all assessments (FA+CA+SA) of Optional Subjects
                                                                    echo "<td colspan='2'>" . (array_sum($examMarksArrayOptional) + $CCtotalMarksObtained + array_sum($summativeMarksArrayOptional)) . "</td>";

                                                            echo "</tr>";
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
                                    <table class="table ">
                                        <thead>
                                            <tr class="text-center">
                                            <?php
                                            // Fetch only those subjects of the class whose IsOptional is 0 and Co-curricular is 1
                                            $subjects = fetchSubjects($dbh, $class, 0, 1, $examSession);

                                            $totalColspan = count($subjects) * 2 + 2;
                                            echo "<th colspan='{$totalColspan}'>Marks Obtained in Co-curricular Component During the Academic Session</th>";
                                            ?>
                                            </tr>
                                            <tr class="text-center">
                                                <?php
                                                foreach ($subjects as $subject) 
                                                {
                                                    echo "<th class='text-wrap' colspan='2'>{$subject['SubjectName']}</th>";
                                                }
                                                ?>
                                                <th colspan='2'>Marks Obtained<br>(20)</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr class="text-center">
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

                                                    $subjectsData = !empty($subjectsJson) ? json_decode($subjectsJson, true) : [];

                                                    // Loop through the decoded JSON to find the SubMarksObtained for the current subject
                                                    foreach ($subjectsData as $subjectData) 
                                                    {
                                                        if ($subjectData['SubjectID'] == $subject['ID']) 
                                                        {
                                                            $subMarksObtained = $subjectData['CoCurricularMarksObtained'];
                                                            break;
                                                        }
                                                    }

                                                    echo "<td colspan='2'>" . $subMarksObtained . "</td>";
                                                }
                                                $totalMarksObtained = array_sum(array_column($subjectsData, 'CoCurricularMarksObtained'));

                                                echo "<td colspan='2'>". ($totalMarksObtained != NULL ? $totalMarksObtained : '') ."</td>";
                                                ?>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <footer class="d-flex justify-content-end mt-3">
                                    <div class="mt-5">
                                        <label>Signature of Tr. Incharge:</label><span class="border-bottom border-dark ml-2 signature-line"></span>
                                    </div>
                                </footer>
                            </div>
                        </div>
                </div>
            </div>
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
