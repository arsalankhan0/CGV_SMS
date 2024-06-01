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
    function hasOptionalSubjectWithGrading($dbh, $className) 
    {
        $class = $className;
        $optionalGradingSql = "SELECT COUNT(*) FROM tblmaxmarks AS m
                                INNER JOIN tblexamination AS e ON m.ExamID = e.ID
                                WHERE m.GradingSystem = 1
                                AND m.ClassID = :className
                                AND e.ExamType = 'Summative'";
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
                $examName = $_GET['examName'];
                $studentName = $_GET['studentName'];

                $sqlReports = "SELECT * FROM tblreports WHERE ClassName = :className AND ExamSession = :examSession AND StudentName = :studentName AND IsDeleted = 0";
                $stmtReports = $dbh->prepare($sqlReports);
                $stmtReports->bindParam(':className', $className, PDO::PARAM_STR);
                $stmtReports->bindParam(':examSession', $examSession, PDO::PARAM_STR);
                $stmtReports->bindParam(':studentName', $studentName, PDO::PARAM_INT);
                $stmtReports->execute();
                $reports = $stmtReports->fetchAll(PDO::FETCH_ASSOC);

                // Filter the reports based on examName
                $reports = array_filter($reports, function($report) use ($examName) {
                    // Extract ExamName from SubjectsJSON and compare with examName
                    $subjectsJSON = json_decode($report['SubjectsJSON'], true);
                    foreach ($subjectsJSON as $subject) {
                        if ($subject['ExamName'] === $examName) {
                            return true; // Keep the report if the examName matches
                        }
                    }
                    return false; // Exclude the report if no match is found
                });

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
                    <link rel="stylesheet" href="vendors/select2-bootstrap-theme/select2-bootstrap.min.css">
                    <link rel="stylesheet" href="css/style.css" />
                    <link rel="stylesheet" href="./css/reportCard.css">
                </head>

                <body>
                <div class="container-scroller">
                <div class="container page-body-wrapper d-flex flex-column">
                    <?php

                        // Fetch student details
                        $studentDetailsSql = "SELECT ID, StudentName,CodeNumber, StudentSection, StudentClass, RollNo, FatherName FROM tblstudent WHERE ID = :studentID AND IsDeleted = 0";
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

                        // Fetch Exam Name from the database
                        $examNameSql = "SELECT ExamName,DurationFrom, DurationTo FROM tblexamination WHERE ID = :examName AND IsDeleted = 0";
                        $examNameQuery = $dbh->prepare($examNameSql);
                        $examNameQuery->bindParam(':examName', $examName, PDO::PARAM_STR);
                        $examNameQuery->execute();
                        $examNameRow = $examNameQuery->fetch(PDO::FETCH_ASSOC);
                        ?>
                        <div class="card d-flex justify-content-center align-items-center">
                            <div class="card-body" id="report-card">
                                <img src="../Main/img/logo1.png" alt="TPS" class="watermark">
                                <div class="d-flex justify-content-center align-items-center">
                                    <img src="../Main/img/reportLogo.png" alt="TPS" class="img-fluid">
                                </div>
                                <div class="d-flex justify-content-center mt-4">
                                    <strong style="font-size: 1.3rem;">Result of <?php echo htmlspecialchars($examNameRow['ExamName']); ?></strong>
                                </div>
                                <!-- Student's Details -->
                                <div class="my-4">
                                    <!-- Row 1 -->
                                    <div class="d-flex flex-row align-items-start justify-content-end">
                                        <div class="mb-2">
                                            <label>Date:</label><span class="border-bottom border-dark ml-2 signature-line"></span>
                                        </div>
                                    </div>
                                    <!-- Row 1 -->
                                    <div class="d-flex flex-row align-items-start" style="gap: 30px;">
                                        <div class="d-flex align-items-center w-100">
                                            <label>Duration:</label>
                                            <span class="border-bottom border-dark ml-2 pl-3 w-100" style="box-sizing: border-box;"><?php echo htmlspecialchars($examNameRow['DurationFrom']); ?></span>
                                        </div>
                                        <div class="d-flex align-items-center w-100">
                                            <label class="text-nowrap">To:</label>
                                            <span class="border-bottom border-dark ml-2 pl-3 w-100" style="box-sizing: border-box;"><?php echo htmlspecialchars($examNameRow['DurationTo']); ?></span>
                                        </div>
                                    </div>
                                    <!-- Row 2 -->
                                    <div class="d-flex flex-row align-items-start my-2" style="white-space: nowrap;">
                                        <div class="mr-5">
                                            <label>Code No.:</label><span class="border-bottom border-dark ml-1 px-3"><?php echo htmlentities($studentDetails['CodeNumber']); ?></span>
                                        </div>
                                        <div class="d-flex align-items-center w-100">
                                            <label class="text-nowrap">Student's Name:</label><span class="border-bottom border-dark ml-2 pl-3 w-100" style="box-sizing: border-box;"><?php echo htmlentities($studentDetails['StudentName']); ?></span>
                                        </div>
                                    </div>
                                    <!-- Row 3 -->
                                    <div class="d-flex flex-row justify-content-between" style="gap: 30px">
                                        <div class="d-flex align-items-center w-100">
                                            <label>Class:</label><span class="border-bottom border-dark ml-2 pl-3 w-100" style="box-sizing: border-box;"><?php echo htmlentities($studentClass); ?></span>
                                        </div>
                                        <div class="d-flex align-items-center w-100">
                                            <label>Section:</label><span class="border-bottom border-dark ml-2 pl-3 w-100" style="box-sizing: border-box;"><?php echo htmlentities($sectionRow['SectionName']); ?></span>
                                        </div>
                                        <div class="d-flex align-items-center w-100">
                                            <label class="text-nowrap">Roll No:</label><span class="border-bottom border-dark ml-2 pl-3 w-100" style="box-sizing: border-box;"><?php echo htmlentities($studentDetails['RollNo']); ?></span>
                                        </div>
                                    </div>
                                </div>
                                <!-- Main Subjects -->
                                <div class="d-flex flex-column">
                                    <?php
                                        $maxMarks = '';
                                        // Query to fetch max marks based on classID, examID, and sessionID
                                        $maxMarksQuery = $dbh->prepare("SELECT SubMaxMarks FROM tblmaxmarks WHERE ClassID = :classID AND ExamID = :examID AND SessionID = :sessionID");
                                        $maxMarksQuery->bindParam(':classID', $className, PDO::PARAM_INT);
                                        $maxMarksQuery->bindParam(':examID', $examName, PDO::PARAM_INT);
                                        $maxMarksQuery->bindParam(':sessionID', $examSession, PDO::PARAM_INT);
                                        $maxMarksQuery->execute();
                                        $maxMarksRow = $maxMarksQuery->fetch(PDO::FETCH_ASSOC);

                                        if ($maxMarksRow) {
                                            $maxMarks = $maxMarksRow['SubMaxMarks'];
                                        } else {
                                            $maxMarks = 'N/A'; 
                                        }
                                    ?>
                                    <table class="table">
                                        <thead>
                                            <tr class="text-center">
                                                <th class="font-weight-bold" style="width: 10%;">S.No.</th>
                                                <th class="font-weight-bold">Subject</th>
                                                <th class="font-weight-bold">Marks Obtained (MM: <?php echo $maxMarks; ?>)</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            $class = "%$className%";
                                            // Fetch only those subjects of the class whose IsOptional is 0
                                            $subjectsSql = "SELECT * FROM tblsubjects WHERE ClassName LIKE :className AND IsOptional = 0 AND IsCurricularSubject = 0 AND IsDeleted = 0 AND SessionID = :examSession";
                                            $subjectsQuery = $dbh->prepare($subjectsSql);
                                            $subjectsQuery->bindParam(':className', $class, PDO::PARAM_STR);
                                            $subjectsQuery->bindParam(':examSession', $examSession, PDO::PARAM_STR);
                                            $subjectsQuery->execute();
                                            $subjects = $subjectsQuery->fetchAll(PDO::FETCH_ASSOC);

                                            $counter = 1;
                                            $totalMarksObtained = 0;
                                            $totalMaxMarks = 0;
                                            foreach ($subjects as $subject) 
                                            {
                                                // Fetch SubjectsJSON for the current subject from tblreports for all exam sessions
                                                $fetchSubjectsJsonSql = "SELECT SubjectsJSON FROM tblreports WHERE ClassName = :className AND StudentName = :studentID AND ExamSession = :sessionID AND JSON_EXTRACT(SubjectsJSON, '$[*].ExamName') LIKE :examName";
                                                $fetchSubjectsJsonQuery = $dbh->prepare($fetchSubjectsJsonSql);
                                                $fetchSubjectsJsonQuery->bindParam(':className', $className, PDO::PARAM_STR);
                                                $fetchSubjectsJsonQuery->bindParam(':studentID', $studentDetails['ID'], PDO::PARAM_STR);
                                                $fetchSubjectsJsonQuery->bindParam(':sessionID', $examSession, PDO::PARAM_STR);
                                                $fetchSubjectsJsonQuery->bindValue(':examName', '%' . $examName . '%', PDO::PARAM_STR);
                                                $fetchSubjectsJsonQuery->execute();
                                                $allSubjectsJsonArray = $fetchSubjectsJsonQuery->fetchAll(PDO::FETCH_ASSOC);

                                               // Loop through allSubjectsJson to find the matching subject and extract marks obtained
                                                $marksObtained = "";
                                                foreach ($allSubjectsJsonArray as $row) 
                                                {
                                                    $subjectData = json_decode($row['SubjectsJSON'], true);
                                                    foreach ($subjectData as $data) 
                                                    {
                                                        if ($data['ExamName'] === $examName && $data['SubjectID'] === $subject['ID']) 
                                                        {
                                                            $marksObtained = $data['SubMarksObtained'];
                                                            $totalMaxMarks += (float)$data['SubMaxMarks'];
                                                            break 2;
                                                        }
                                                    }
                                                }
                                                // total marks obtained and maximum marks for the current subject
                                                $totalMarksObtained += (float)$marksObtained;
                                                
                                                echo "<tr>
                                                        <td class='text-center'>{$counter}</td>
                                                        <td>{$subject['SubjectName']}</td>
                                                        <td class='text-center'>{$marksObtained}</td>";
                                                echo "</tr>";
                                                $counter++;
                                            }
                                            // Calculate percentage
                                            if ($totalMaxMarks > 0) 
                                            {
                                                $percentage = ($totalMarksObtained / $totalMaxMarks) * 100;
                                                $percentage = number_format($percentage, 2);

                                               //Grade based on the percentage
                                                if ($percentage >= 85) {
                                                    $grade = 'A+';
                                                    $rank = 'SKY';
                                                } elseif ($percentage >= 70 && $percentage < 85) {
                                                    $grade = 'A';
                                                    $rank = 'MOUNTAIN';
                                                } elseif ($percentage >= 55 && $percentage < 70) {
                                                    $grade = 'B';
                                                    $rank = 'MOUNTAIN';
                                                } elseif ($percentage >= 40 && $percentage < 55) {
                                                    $grade = 'C';
                                                    $rank = 'MOUNTAIN';
                                                } elseif ($percentage >= 33 && $percentage < 40) {
                                                    $grade = 'D';
                                                    $rank = 'RIVER';
                                                } else {
                                                    $grade = 'N/A';
                                                    $rank = 'N/A';
                                                }
                                            } 
                                            else 
                                            {
                                                $percentage = 0;
                                                $grade = 'N/A';
                                                $rank = 'N/A';
                                            }
                                            ?>
                                            <tr>
                                                <td></td>
                                                <td class="text-right font-weight-bold">Total Marks Obtained</td>
                                                <td class="text-center"><?php echo $totalMarksObtained; ?></td>
                                            </tr>
                                            <tr>
                                                <td></td>
                                                <td class="text-right font-weight-bold">Maximum Marks</td>
                                                <td class="text-center"><?php echo $totalMaxMarks; ?></td>
                                            </tr>
                                            <tr>
                                                <td></td>
                                                <td class="text-right font-weight-bold">Percentage</td>
                                                <td class="text-center font-weight-bold"><?php echo $percentage; ?>%</td>
                                            </tr>
                                            <tr>
                                                <td></td>
                                                <td class="text-right font-weight-bold">Grade</td>
                                                <td class="text-center font-weight-bold"><?php echo $grade; ?></td>
                                            </tr>
                                            <tr>
                                                <td></td>
                                                <td class="text-right font-weight-bold">Rank</td>
                                                <td class="text-center font-weight-bold"><?php echo $rank; ?></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>

                                <!-- Co-Curricular Component of Academic Session -->
                                <div class="d-flex flex-column mt-4">
                                    <strong>Marks Obtained in Co-curricular Component During the Assessment period</strong>
                                    <table class="table w-100">
                                        <thead>
                                            <tr class="text-center">
                                                <?php
                                                // Fetch only those subjects of the class whose Co-curricular is 1
                                                $subjectsSql = "SELECT * FROM tblsubjects WHERE ClassName LIKE :className AND IsOptional = 0 AND IsCurricularSubject = 1 AND IsDeleted = 0";
                                                $subjectsQuery = $dbh->prepare($subjectsSql);
                                                $subjectsQuery->bindParam(':className', $class, PDO::PARAM_STR);
                                                $subjectsQuery->execute();
                                                $subjects = $subjectsQuery->fetchAll(PDO::FETCH_ASSOC);

                                                // Fetch SubjectsJSON for the current student and session
                                                $fetchSubjectsJsonSql = "SELECT SubjectsJSON FROM tblreports WHERE ClassName = :className AND ExamSession = :examSession AND StudentName = :studentID";
                                                $fetchSubjectsJsonQuery = $dbh->prepare($fetchSubjectsJsonSql);
                                                $fetchSubjectsJsonQuery->bindParam(':className', $className, PDO::PARAM_STR);
                                                $fetchSubjectsJsonQuery->bindParam(':examSession', $examSession, PDO::PARAM_STR);
                                                $fetchSubjectsJsonQuery->bindParam(':studentID', $studentDetails['ID'], PDO::PARAM_STR);
                                                $fetchSubjectsJsonQuery->execute();
                                                $subjectsJson = $fetchSubjectsJsonQuery->fetch(PDO::FETCH_COLUMN);

                                                $subjectsData = !empty($subjectsJson) ? json_decode($subjectsJson, true) : [];
                                                
                                                $studentTotalMaxMarks = 0;
                                                foreach ($subjects as $subject) 
                                                {
                                                    $maxMarks = '';
                                                    // Loop through the decoded JSON to find the max marks for the current subject
                                                    foreach ($subjectsData as $subjectData) 
                                                    {
                                                        if ($subjectData['SubjectID'] == $subject['ID'] && $subjectData['ExamName'] == $examName) 
                                                        {
                                                            $maxMarks = $subjectData['SubMaxMarks'];
                                                            break;
                                                        }
                                                    }

                                                    echo "<th class='font-weight-bold'>{$subject['SubjectName']}<br><br>({$maxMarks})</th>";
                                                    $studentTotalMaxMarks += (float)$maxMarks;
                                                }
                                                ?>
                                                <th class="font-weight-bold">Total Marks Obtained<br><br><?php echo "(".htmlspecialchars($studentTotalMaxMarks).")"; ?></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <?php
                                                $studentTotalMarks = 0;
                                                
                                                foreach ($subjects as $subject) 
                                                {
                                                    $subMarksObtained = '';

                                                    // Loop through the decoded JSON to find the SubMarksObtained for the current subject
                                                    foreach ($subjectsData as $subjectData) 
                                                    {
                                                        if ($subjectData['SubjectID'] == $subject['ID']) 
                                                        {
                                                            $subMarksObtained = $subjectData['SubMarksObtained'];
                                                            break;
                                                        }
                                                    }
                                                    echo "<td class='text-center'>" . $subMarksObtained . "</td>";

                                                    $studentTotalMarks += (float)$subMarksObtained;
                                                }

                                                echo "<td class='text-center'>{$studentTotalMarks}</td>";
                                                ?>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>

                                <?php
                                if(hasOptionalSubjectWithGrading($dbh, $className))
                                {
                                ?>
                                    <!-- Optional Subjects in Grades-->
                                    <div class="d-flex flex-column mt-4">
                                        <strong>Grade in Optional Subjects:</strong>
                                        <table class="table ">
                                            <?php
                                            // Fetch only those subjects of the class whose IsOptional is 0
                                            $optionalSubjectsSql = "SELECT * FROM tblsubjects WHERE ClassName LIKE :className AND IsOptional = 1 AND IsCurricularSubject = 0 AND IsDeleted = 0 AND SessionID = :examSession";
                                            $optionalSubjectsQuery = $dbh->prepare($optionalSubjectsSql);
                                            $optionalSubjectsQuery->bindParam(':className', $class, PDO::PARAM_STR);
                                            $optionalSubjectsQuery->bindParam(':examSession', $examSession, PDO::PARAM_STR);
                                            $optionalSubjectsQuery->execute();
                                            $optionalSubjects = $optionalSubjectsQuery->fetchAll(PDO::FETCH_ASSOC);
                                            ?>
                                            <thead>
                                                <tr class="text-center">
                                                    <th class="font-weight-bold">Subjects</th>
                                                    <?php
                                                    foreach ($optionalSubjects as $subject) 
                                                    {
                                                        echo "<th>{$subject['SubjectName']}</th>";
                                                    }
                                                    ?>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr class="text-center">
                                                    <td class="font-weight-bold">Grade Obtained</td>
                                                    <?php
                                                        foreach ($optionalSubjects as $subject) {
                                                            $marksObtained = ""; 

                                                            foreach ($allSubjectsJsonArray as $row) {
                                                                $subjectData = json_decode($row['SubjectsJSON'], true); 

                                                                foreach ($subjectData as $data) {
                                                                    if ($data['SubjectID'] === $subject['ID'] && $data['IsOptional'] == 1) {
                                                                        $marksObtained = $data['SubMarksObtained'];
                                                                        break 2;
                                                                    }
                                                                }
                                                            }
                                                            echo "<td>{$marksObtained}</td>";
                                                        }
                                                    ?>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php
                                }
                                else
                                {
                                ?>
                                <!-- Optional Subjects in Marks-->
                                <div class="d-flex flex-column mt-4">
                                    <strong>Marks in Optional Subjects:</strong>
                                    <table class="table ">
                                        <?php
                                        // Fetch only those subjects of the class whose IsOptional is 0
                                        $optionalSubjectsSql = "SELECT * FROM tblsubjects WHERE ClassName LIKE :className AND IsOptional = 1 AND IsCurricularSubject = 0 AND IsDeleted = 0 AND SessionID = :examSession";
                                        $optionalSubjectsQuery = $dbh->prepare($optionalSubjectsSql);
                                        $optionalSubjectsQuery->bindParam(':className', $class, PDO::PARAM_STR);
                                        $optionalSubjectsQuery->bindParam(':examSession', $examSession, PDO::PARAM_STR);
                                        $optionalSubjectsQuery->execute();
                                        $optionalSubjects = $optionalSubjectsQuery->fetchAll(PDO::FETCH_ASSOC);
                                        ?>
                                        <thead>
                                            <tr class="text-center">
                                                <th class="align-middle font-weight-bold">Subjects</th>
                                                <?php
                                                $totalMaxMarks = 0;
                                                foreach ($optionalSubjects as $subject) 
                                                {
                                                    $maxMarks = "";
                                                    foreach ($allSubjectsJsonArray as $row) 
                                                    {
                                                        $subjectData = json_decode($row['SubjectsJSON'], true);
                                                        foreach ($subjectData as $data) 
                                                        {
                                                            if ($data['SubjectID'] === $subject['ID'] && $data['IsOptional'] == 1) 
                                                            {
                                                                $maxMarks = $data['SubMaxMarks'];
                                                                break 2;
                                                            }
                                                        }
                                                    }
                                                    $totalMaxMarks += $maxMarks;
                                                    echo "<th>{$subject['SubjectName']}<br><br>({$maxMarks})</th>";
                                                }
                                                echo "<th class='font-weight-bold'>Total <br><br>({$totalMaxMarks})</th>";
                                                ?>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr class="text-center">
                                                <td class="font-weight-bold">Marks Obtained</td>
                                                <?php
                                                    $totalMarksObtained = 0;
                                                    foreach ($optionalSubjects as $subject) 
                                                    {
                                                        $marksObtained = "";
                                                        foreach ($allSubjectsJsonArray as $row) 
                                                        {
                                                            $subjectData = json_decode($row['SubjectsJSON'], true);

                                                            foreach ($subjectData as $data) 
                                                            {
                                                                if ($data['SubjectID'] === $subject['ID'] && $data['IsOptional'] == 1) 
                                                                {
                                                                    $marksObtained = $data['SubMarksObtained'];
                                                                    break 2;
                                                                }
                                                            }
                                                        }
                                                        $totalMarksObtained += $marksObtained;
                                                        echo "<td>{$marksObtained}</td>";
                                                    }
                                                    echo "<td>{$totalMarksObtained}</td>";
                                                ?>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <?php
                                }
                                ?>

                                <footer class="d-flex justify-content-between mt-5">
                                    <div class="mt-5">
                                        <label>Supervisor/Principal's Signature:</label><span class="border-bottom border-dark ml-2 px-5"></span>
                                    </div>
                                    <div class="mt-5">
                                        <label>Class Teacher's Signature:</label><span class="border-bottom border-dark ml-2 px-5"></span>
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
