<?php
session_start();
error_reporting(0);
include('includes/dbconnection.php');

if (!isset($_SESSION['sturecmsaid']) || empty($_SESSION['sturecmsaid'])) {
    header('location:logout.php');
} else {
    // Function to check if there is grading system
    function hasOptionalSubjectWithGrading($dbh, $className, $examID, $sessionID)
    {
        $class = $className;
        $optionalGradingSql = "SELECT COUNT(*) FROM tblmaxmarks AS m
                                INNER JOIN tblexamination AS e ON m.ExamID = e.ID
                                WHERE m.GradingSystem = 1
                                AND m.ClassID = :className
                                AND m.ExamID IN (:examIDs)
                                AND m.SessionID = :sessionID
                                AND e.ExamType = 'Formative'";
        $optionalGradingQuery = $dbh->prepare($optionalGradingSql);
        $optionalGradingQuery->bindParam(':className', $class, PDO::PARAM_STR);
        $optionalGradingQuery->bindParam(':examIDs', $examID, PDO::PARAM_STR);
        $optionalGradingQuery->bindParam(':sessionID', $sessionID, PDO::PARAM_STR);
        $optionalGradingQuery->execute();
        $optionalGradingCount = $optionalGradingQuery->fetchColumn();
        return $optionalGradingCount > 0;
    }
    // Function to get the subjects based on parameters
    function getSubjects($dbh, $className, $examSession, $isOptional, $isCurricularSubject)
    {
        $subjectsSql = "SELECT * FROM tblsubjects WHERE ClassName LIKE :className AND IsOptional = :isOptional AND IsCurricularSubject = :isCurricularSubject AND IsDeleted = 0 AND SessionID = :examSession";
        $subjectsQuery = $dbh->prepare($subjectsSql);
        $subjectsQuery->bindParam(':className', $className, PDO::PARAM_STR);
        $subjectsQuery->bindParam(':isOptional', $isOptional, PDO::PARAM_INT);
        $subjectsQuery->bindParam(':isCurricularSubject', $isCurricularSubject, PDO::PARAM_INT);
        $subjectsQuery->bindParam(':examSession', $examSession, PDO::PARAM_STR);
        $subjectsQuery->execute();
        return $subjectsQuery->fetchAll(PDO::FETCH_ASSOC);
    }
    // Function to calculate the grades and rank according to percentage
    function calculateGrade($percentage)
    {
        if ($percentage >= 85) {
            return ['grade' => 'A+', 'rank' => 'SKY'];
        } elseif ($percentage >= 70 && $percentage < 85) {
            return ['grade' => 'A', 'rank' => 'MOUNTAIN'];
        } elseif ($percentage >= 55 && $percentage < 70) {
            return ['grade' => 'B', 'rank' => 'MOUNTAIN'];
        } elseif ($percentage >= 40 && $percentage < 55) {
            return ['grade' => 'C', 'rank' => 'MOUNTAIN'];
        } elseif ($percentage >= 33 && $percentage < 40) {
            return ['grade' => 'D', 'rank' => 'RIVER'];
        } else {
            return ['grade' => 'N/A', 'rank' => 'N/A'];
        }
    }

    if (isset($_GET['studentName'])) {
        $studentID = filter_var(base64_decode(urldecode($_GET['studentName'])), FILTER_VALIDATE_INT);

        $sqlStudent = "SELECT * FROM tblstudent WHERE ID = :studentName AND IsDeleted = 0";
        $queryStudent = $dbh->prepare($sqlStudent);
        $queryStudent->bindParam(':studentName', $studentID, PDO::PARAM_INT);
        $queryStudent->execute();
        $studentDetails = $queryStudent->fetch(PDO::FETCH_ASSOC);

        $getSessionSql = "SELECT session_id, session_name FROM tblsessions WHERE is_active = 1 AND IsDeleted = 0";
        $sessionQuery = $dbh->prepare($getSessionSql);
        $sessionQuery->execute();
        $session = $sessionQuery->fetch(PDO::FETCH_ASSOC);

        if ($studentDetails) {
            $stdClassID = $studentDetails['StudentClass'];
            $sqlStudentClass = "SELECT * FROM tblclass WHERE ID = :stdClassID AND IsDeleted = 0";
            $queryStudentClass = $dbh->prepare($sqlStudentClass);
            $queryStudentClass->bindParam(':stdClassID', $stdClassID, PDO::PARAM_INT);
            $queryStudentClass->execute();
            $studentClass = $queryStudentClass->fetch(PDO::FETCH_ASSOC);

            $examSession = $session['session_id'];
            $className = base64_decode(urldecode($_GET['className']));
            $examName = base64_decode(urldecode($_GET['examNames']));
            $studentName = base64_decode(urldecode($_GET['studentName']));
            try {

                $sqlReports = "SELECT * FROM tblreports WHERE ClassName = :className AND ExamSession = :examSession AND StudentName = :studentName AND IsDeleted = 0";
                $stmtReports = $dbh->prepare($sqlReports);
                $stmtReports->bindParam(':className', $className, PDO::PARAM_STR);
                $stmtReports->bindParam(':examSession', $examSession, PDO::PARAM_STR);
                $stmtReports->bindParam(':studentName', $studentName, PDO::PARAM_INT);
                $stmtReports->execute();
                $reports = $stmtReports->fetchAll(PDO::FETCH_ASSOC);

                $reports = array_filter($reports, function ($report) use ($examName) {
                    $subjectsJSON = json_decode($report['SubjectsJSON'], true);

                    $examList = explode(',', $examName);

                    // Use in_array to check if any exam in $examList exists in the subjects
                    foreach ($subjectsJSON as $subject) {
                        if (in_array($subject['ExamName'], $examList)) {
                            return true;
                        }
                    }
                    return false;
                });


                if (!$reports) {
                    echo "<script>alert('No data found for the selected student, class, and exam.');</script>";
                }
            } catch (PDOException $e) {
                echo '<script>alert("Ops! An Error occurred.")</script>';
                echo "<script>console.error('Error:---> " . $e->getMessage() . "');</script>";
            }
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
                        <div class="container-fluid page-body-wrapper d-flex flex-column">
                            <?php
                            // Fetch student details along with class name, section name, and exam details            
                            $studentDetailsSql = "SELECT 
                                                    s.ID, 
                                                    s.CodeNumber, 
                                                    s.StudentName,  
                                                    c.ClassName, 
                                                    sec.SectionName,
                                                    s.RollNo, 
                                                    s.FatherName,
                                                    GROUP_CONCAT(e.ExamName) as ExamNames, 
                                                    GROUP_CONCAT(e.ID) as ExamIDs, 
                                                    MIN(e.DurationFrom) as DurationFrom, 
                                                    MAX(e.DurationTo) as DurationTo
                                                FROM tblstudent s
                                                INNER JOIN tblclass c ON s.StudentClass = c.ID AND c.IsDeleted = 0
                                                INNER JOIN tblsections sec ON s.StudentSection = sec.ID AND sec.IsDeleted = 0
                                                INNER JOIN tblexamination e ON FIND_IN_SET(e.ID, :examIDs) AND e.IsDeleted = 0
                                                WHERE s.ID = :studentID AND s.IsDeleted = 0
                                                GROUP BY s.ID";
                            $studentDetailsQuery = $dbh->prepare($studentDetailsSql);
                            $studentDetailsQuery->bindParam(':studentID', $studentID, PDO::PARAM_INT);
                            $studentDetailsQuery->bindParam(':examIDs', $examName, PDO::PARAM_STR);
                            $studentDetailsQuery->execute();
                            $studentDetails = $studentDetailsQuery->fetch(PDO::FETCH_ASSOC);

                            $examNames = isset($studentDetails['ExamNames']) ? $studentDetails['ExamNames'] : '';
                            $examIDs = isset($studentDetails['ExamIDs']) ? $studentDetails['ExamIDs'] : '';

                            $durationFrom = isset($studentDetails['DurationFrom']) ? (new DateTime($studentDetails['DurationFrom']))->format('d-m-Y') : '';
                            $durationTo = isset($studentDetails['DurationFrom']) ? (new DateTime($studentDetails['DurationTo']))->format('d-m-Y') : '';

                            $selectedExams = explode(',', $examNames);
                            $selectedExamIDs = explode(',', $examIDs);
                            ?>
                            <div class="card d-flex justify-content-center align-items-center">
                                <div class="card-body" id="report-card">
                                    <div class="site-name">www.tibetanpublicschool.com</div>
                                    <div class="report-header">
                                        <img src="../Main/img/logo1.png" alt="TPS" class="header-logo">
                                        <div class="header-text">
                                            <h1 class="school-name">Tibetan Public School</h1>
                                            <span class="school-address">Badamwari, Hawal, Srinagar, J&K - 190003</span>
                                        </div>
                                    </div>
                                    <div class="watermark-container">
                                        <img src="../Main/img/logo1.png" alt="TPS" class="watermark">
                                    </div>
                                    <h4 class="card-title mt-4 mb-4" style="text-align: center;">Result of
                                        <?php

                                        $selectedExamsArray = explode(',', $examNames);
                                        $lastExam = array_pop($selectedExamsArray);

                                        if (count($selectedExamsArray) >= 1) {
                                            $otherExams = implode(', ', $selectedExamsArray);
                                            echo htmlspecialchars($otherExams . (count($selectedExamsArray) > 1 ? ' and ' : ' and ') . $lastExam);
                                        } else {
                                            echo htmlspecialchars($examNames);
                                        }
                                        ?>
                                    </h4>
                                    <!-- Duration -->
                                    <div class="container mt-4">
                                        <div class="d-flex flex-row align-items-start mb-3" style="gap: 30px;">
                                            <div class="d-flex align-items-center w-100 font-weight-bold">
                                                <label>Duration:</label>
                                                <span class="border-bottom border-dark ml-2 pl-3 w-100" style="box-sizing: border-box;">
                                                    <?php echo htmlspecialchars($durationFrom); ?>
                                                </span>
                                            </div>
                                            <div class="d-flex align-items-center w-100 font-weight-bold">
                                                <label>To:</label>
                                                <span class="border-bottom border-dark ml-2 pl-3 w-100" style="box-sizing: border-box;">
                                                    <?php echo htmlspecialchars($durationTo); ?>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mt-4">
                                        <div class="d-flex flex-row justify-content-between font-weight-bold">
                                            <div>
                                                <label>Student's Code No:</label><span
                                                    class="dark-line ml-2 px-3"><?php echo htmlentities($studentDetails['CodeNumber']); ?></span>
                                            </div>
                                            <div>
                                                <label>Class:</label><span
                                                    class="border-bottom border-dark ml-2 px-3 text-capitalize"><?php echo htmlentities($studentDetails['ClassName']); ?></span>
                                            </div>
                                            <div>
                                                <label>Section:</label><span
                                                    class="border-bottom border-dark ml-2 px-3 text-capitalize"><?php echo htmlentities($studentDetails['SectionName']); ?></span>
                                            </div>
                                            <div>
                                                <label>Roll No:</label><span
                                                    class="border-bottom border-dark ml-2 px-3"><?php echo htmlentities($studentDetails['RollNo']); ?></span>
                                            </div>
                                        </div>
                                        <!-- Student's Name -->
                                        <div class="d-flex w-100 align-items-center font-weight-bold">
                                            <label class="text-nowrap">Student's Name: </label>
                                            <p class="border-bottom border-dark ml-2 pl-3 w-100 text-capitalize"
                                                style="box-sizing: border-box;">
                                                <span><?php echo htmlentities($studentDetails['StudentName']); ?></span></p>
                                        </div>
                                        <!-- Parent's Name -->
                                        <div class="d-flex w-100 align-items-center font-weight-bold">
                                            <label class="text-nowrap">Parents'/Guardian's Name: </label>
                                            <p class="border-bottom border-dark ml-2 pl-3 w-100 text-capitalize"
                                                style="box-sizing: border-box;">
                                                <span><?php echo htmlentities($studentDetails['FatherName']); ?></span></p>
                                        </div>
                                    </div>
                                    <!-- Main Subjects -->
                                    <div class="d-flex flex-row">
                                        <table class="table">
                                            <thead>
                                                <tr class="text-center">
                                                    <th class="font-weight-bold" style="width: 10%;">S.No.</th>
                                                    <th class="font-weight-bold">Subject</th>
                                                    <?php
                                                    $i = 0;
                                                    foreach ($selectedExamIDs as $examID) {
                                                        $examName = $selectedExams[$i];

                                                        $currentMaxMarksQuery = $dbh->prepare("SELECT SubMaxMarks FROM tblmaxmarks WHERE ClassID = :classID AND ExamID = :examID AND SessionID = :sessionID");
                                                        $currentMaxMarksQuery->bindParam(':classID', $className, PDO::PARAM_INT);
                                                        $currentMaxMarksQuery->bindParam(':examID', $examID, PDO::PARAM_INT);
                                                        $currentMaxMarksQuery->bindParam(':sessionID', $examSession, PDO::PARAM_INT);
                                                        $currentMaxMarksQuery->execute();

                                                        $currentMaxMarksRow = $currentMaxMarksQuery->fetch(PDO::FETCH_ASSOC);
                                                        $currentMaxMarks = ($currentMaxMarksRow) ? $currentMaxMarksRow['SubMaxMarks'] : 'N/A';

                                                        echo '<th class="font-weight-bold">' . $examName . '<br>(MM: ' . $currentMaxMarks . ')</th>';

                                                        $i++;
                                                    }


                                                    if (count($selectedExamIDs) > 1) {
                                                        echo '<th class="font-weight-bold">Performance Chart</th>';
                                                    }
                                                    ?>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                $class = "%$className%";
                                                $subjects = getSubjects($dbh, $class, $examSession, 0, 0);

                                                $counter = 1;
                                                $totalMarksObtained = 0;
                                                $allSubjectsMarks = [];
                                                $totalMaxMarks = [];

                                                $subjectCounter = count($subjects) + 5;
                                                foreach ($subjects as $subject) {
                                                    $subjectMarks = [];
                                                    $subjectAbsentStatus = [];
                                                    $subjectMaxMarks = [];

                                                    foreach ($selectedExamIDs as $examID) {
                                                        $marksObtained = '';
                                                        $maxMarks = '';
                                                        $isAbsent = 0;

                                                        $fetchSubjectsJsonSql = "SELECT SubjectsJSON FROM tblreports 
                                                                                    WHERE ClassName = :className 
                                                                                    AND StudentName = :studentID 
                                                                                    AND ExamSession = :sessionID 
                                                                                    AND JSON_EXTRACT(SubjectsJSON, '$[*].ExamName') LIKE :examName";
                                                        $fetchSubjectsJsonQuery = $dbh->prepare($fetchSubjectsJsonSql);
                                                        $fetchSubjectsJsonQuery->bindParam(':className', $className, PDO::PARAM_STR);
                                                        $fetchSubjectsJsonQuery->bindParam(':studentID', $studentDetails['ID'], PDO::PARAM_STR);
                                                        $fetchSubjectsJsonQuery->bindParam(':sessionID', $examSession, PDO::PARAM_STR);
                                                        $fetchSubjectsJsonQuery->bindValue(':examName', '%' . $examID . '%', PDO::PARAM_STR);
                                                        $fetchSubjectsJsonQuery->execute();

                                                        $allSubjectsJsonArray = $fetchSubjectsJsonQuery->fetchAll(PDO::FETCH_ASSOC);

                                                        foreach ($allSubjectsJsonArray as $row) {
                                                            $subjectData = json_decode($row['SubjectsJSON'], true);
                                                            foreach ($subjectData as $data) {
                                                                if ($data['ExamName'] === $examID && $data['SubjectID'] === $subject['ID']) {
                                                                    $marksObtained = $data['SubMarksObtained'];
                                                                    $isAbsent = $data['isAbsent'] ?? 0;
                                                                    $maxMarks = $data['SubMaxMarks'];
                                                                    break 2;
                                                                }
                                                            }
                                                        }

                                                        $subjectMarks[$examID] = $isAbsent ? 'a' : ($marksObtained ?: 'N/A');
                                                        $subjectAbsentStatus[$examID] = $isAbsent;
                                                        $subjectMaxMarks[$examID] = $maxMarks ?: 'N/A';
                                                    }
                                                    // Display subject name, marks for each exam, and total marks
                                                    echo "<tr>
                                                        <td class='text-center'>{$counter}</td>
                                                        <td>{$subject['SubjectName']}</td>";
                                                    foreach ($subjectMarks as $exam => $mark) {
                                                        $isAbsent = $subjectAbsentStatus[$exam];
                                                        echo "<td class='text-center'>" . ($isAbsent ? '<span class="absent-mark">a</span>' : $mark) . "</td>";
                                                    }

                                                    // Display the bar comparison for the first subject row
                                                    if (count($selectedExamIDs) > 1 && $counter === 1) {
                                                        echo "<td class='text-center' rowspan='" . ($subjectCounter) . "'>
                                                                <canvas id='performanceChart' style='width: 100%; height: 100%;'></canvas>
                                                        </div></td>";
                                                    }

                                                    echo "</tr>";
                                                    $counter++;
                                                    $allSubjectsMarks[$subject['ID']] = $subjectMarks;
                                                    $totalMaxMarks[$subject['ID']] = $subjectMaxMarks;
                                                }
                                                ?>
                                                <!-- Total Marks Obtained in each Exam -->
                                                <tr>
                                                    <td></td>
                                                    <td class="text-right font-weight-bold">Total Marks Obtained</td>
                                                    <?php
                                                    $totalExamMarks = [];
                                                    foreach ($selectedExamIDs as $exam) {
                                                        $totalExamMarks[$exam] = 0;
                                                    }
                                                    foreach ($subjects as $subject) {
                                                        foreach ($selectedExamIDs as $exam) {
                                                            if (isset($allSubjectsMarks[$subject['ID']][$exam])) {
                                                                $totalExamMarks[$exam] += (float) $allSubjectsMarks[$subject['ID']][$exam];
                                                            }
                                                        }
                                                    }
                                                    ?>
                                                    <?php foreach ($selectedExamIDs as $exam): ?>
                                                        <td class="text-center font-weight-bold">
                                                            <?php
                                                            echo isset($totalExamMarks[$exam]) ? $totalExamMarks[$exam] : '0';
                                                            ?>
                                                        </td>
                                                    <?php endforeach; ?>

                                                </tr>
                                                <!-- Total Max Marks in each Exam -->
                                                <tr>
                                                    <td></td>
                                                    <td class="text-right font-weight-bold">Maximum Marks</td>
                                                    <?php
                                                    $totalExamMaxMarks = [];
                                                    foreach ($selectedExamIDs as $exam) {
                                                        $totalExamMaxMarks[$exam] = 0;
                                                    }
                                                    foreach ($subjects as $subject) {
                                                        foreach ($selectedExamIDs as $exam) {
                                                            if (isset($totalMaxMarks[$subject['ID']][$exam])) {
                                                                $totalExamMaxMarks[$exam] += (float) $totalMaxMarks[$subject['ID']][$exam];
                                                            }
                                                        }
                                                    }
                                                    ?>
                                                    <?php foreach ($selectedExamIDs as $exam): ?>
                                                        <td class="text-center font-weight-bold">
                                                            <?php
                                                            echo isset($totalExamMaxMarks[$exam]) ? $totalExamMaxMarks[$exam] : '0';
                                                            ?>
                                                        </td>
                                                    <?php endforeach; ?>
                                                </tr>
                                                <!-- Percentage in each Exam -->
                                                <tr>
                                                    <td></td>
                                                    <td class="text-right font-weight-bold">Percentage</td>
                                                    <?php foreach ($selectedExamIDs as $exam): ?>
                                                        <td class="text-center font-weight-bold">
                                                            <?php
                                                            if (isset($totalExamMarks[$exam]) && isset($totalExamMaxMarks[$exam])) {
                                                                if ($totalExamMaxMarks[$exam] != 0) {
                                                                    $percentage = round((float) $totalExamMarks[$exam] / (float) $totalExamMaxMarks[$exam] * 100, 2);
                                                                    echo $percentage . '%';
                                                                } else {
                                                                    echo 'N/A';
                                                                }
                                                            } else {
                                                                echo 'N/A';
                                                            }
                                                            ?>
                                                        </td>
                                                    <?php endforeach; ?>
                                                </tr>
                                                <?php

                                                $examPercentages = [];

                                                foreach ($selectedExamIDs as $exam) {
                                                    if (isset($totalExamMarks[$exam]) && isset($totalExamMaxMarks[$exam])) {
                                                        if ($totalExamMaxMarks[$exam] != 0) {
                                                            $examPercentages[$exam] = round((float) $totalExamMarks[$exam] / (float) $totalExamMaxMarks[$exam] * 100, 2);
                                                        } else {
                                                            $examPercentages[$exam] = 'N/A';
                                                        }
                                                    } else {
                                                        $examPercentages[$exam] = 'N/A';
                                                    }
                                                }
                                                echo "<tr>
                                                    <td></td>
                                                    <td class='text-right font-weight-bold'>Grade</td>";

                                                foreach ($selectedExamIDs as $exam) {
                                                    if (isset($examPercentages[$exam]) && $examPercentages[$exam] !== 'N/A') {
                                                        $percentage = (float) $examPercentages[$exam];
                                                        $gradeRank = calculateGrade($percentage);
                                                        $grade = $gradeRank['grade'];
                                                        echo "<td class='text-center font-weight-bold'>{$grade}</td>";
                                                    } else {
                                                        echo "<td class='text-center font-weight-bold'>N/A</td>";
                                                    }
                                                }

                                                echo "</tr>";

                                                echo "<tr>
                                                    <td></td>
                                                    <td class='text-right font-weight-bold'>Rank</td>";

                                                foreach ($selectedExamIDs as $exam) {
                                                    if (isset($examPercentages[$exam]) && $examPercentages[$exam] !== 'N/A') {
                                                        $percentage = (float) $examPercentages[$exam];
                                                        $gradeRank = calculateGrade($percentage);
                                                        $rank = $gradeRank['rank'];
                                                        echo "<td class='text-center font-weight-bold'>{$rank}</td>";
                                                    } else {
                                                        echo "<td class='text-center font-weight-bold'>N/A</td>";
                                                    }
                                                }

                                                echo "</tr>";
                                                ?>

                                            </tbody>
                                        </table>
                                    </div>

                                    <!-- Co-Curricular Component of Academic Session -->
                                    <?php
                                    // Fetch SubjectsJSON for the current student and session
                                    $fetchSubjectsJsonSql = "SELECT SubjectsJSON FROM tblreports 
                                                                WHERE ClassName = :className 
                                                                    AND ExamSession = :examSession 
                                                                    AND StudentName = :studentID";
                                    $fetchSubjectsJsonQuery = $dbh->prepare($fetchSubjectsJsonSql);
                                    $fetchSubjectsJsonQuery->bindParam(':className', $className, PDO::PARAM_STR);
                                    $fetchSubjectsJsonQuery->bindParam(':examSession', $examSession, PDO::PARAM_STR);
                                    $fetchSubjectsJsonQuery->bindParam(':studentID', $studentDetails['ID'], PDO::PARAM_STR);
                                    $fetchSubjectsJsonQuery->execute();
                                    $subjectsJson = $fetchSubjectsJsonQuery->fetch(PDO::FETCH_COLUMN);

                                    $ccSubjectsData = !empty($subjectsJson) ? json_decode($subjectsJson, true) : [];

                                    $ccSubjectsRaw = getSubjects($dbh, $class, $examSession, 0, 1);
                                    $ccSubjects = [];
                                    foreach ($ccSubjectsRaw as $subject) {
                                        $hasMaxMarks = false;
                                        foreach ($ccSubjectsData as $data) {
                                            if ($data['SubjectID'] == $subject['ID'] && in_array($data['ExamName'], $selectedExamIDs)) {
                                                if (isset($data['SubMaxMarks']) && $data['SubMaxMarks'] > 0) {
                                                    $hasMaxMarks = true;
                                                    break;
                                                }
                                            }
                                        }
                                        if ($hasMaxMarks) {
                                            $ccSubjects[] = $subject;
                                        }
                                    }

                                    // Check if any marks are assigned for co-curricular subjects
                                    $hasCoCurricularMarks = false;
                                    if (!empty($ccSubjects)) {
                                        foreach ($selectedExamIDs as $examID) {
                                            foreach ($ccSubjects as $subject) {
                                                foreach ((is_array($ccSubjectsData) ? $ccSubjectsData : []) as $data) {
                                                    if ($data['SubjectID'] == $subject['ID'] && $data['ExamName'] == $examID) {
                                                        if (isset($data['SubMarksObtained']) && $data['SubMarksObtained'] !== '' && $data['SubMarksObtained'] !== null) {
                                                            $hasCoCurricularMarks = true;
                                                            break 3;
                                                        }
                                                    }
                                                }
                                            }
                                        }
                                    }

                                    if ($hasCoCurricularMarks) {
                                        ?>
                                        <div class="d-flex flex-column mt-4">
                                            <strong>Marks Obtained in Co-curricular Component During the Assessment period</strong>
                                            <table class="table w-100">
                                                <thead>
                                                    <tr class="text-center">
                                                        <th class='font-weight-bold' style="vertical-align: middle">Exam</th>
                                                        <?php
                                                        $studentTotalMaxMarks = 0;
                                                        foreach ($ccSubjects as $subject) {
                                                            $maxMarks = '';

                                                            // Loop through the decoded JSON to find the max marks for the current subject
                                                            foreach ($ccSubjectsData as $subjectData) {
                                                                foreach ($selectedExamIDs as $examName) {
                                                                    if ($subjectData['SubjectID'] == $subject['ID'] && $subjectData['ExamName'] == $examName) {
                                                                        $maxMarks = $subjectData['SubMaxMarks'];
                                                                        break;
                                                                    }
                                                                }
                                                            }

                                                            echo "<th class='font-weight-bold' style='font-size: 1rem !important;'>{$subject['SubjectName']}<br><br>({$maxMarks})</th>";
                                                            $studentTotalMaxMarks += (float) $maxMarks;
                                                        }
                                                        ?>
                                                        <th class="font-weight-bold">Total
                                                            Marks<br><br><?php echo "(" . htmlspecialchars($studentTotalMaxMarks) . ")"; ?>
                                                        </th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php
                                                    $i = 0;
                                                    foreach ($selectedExamIDs as $examID) {
                                                        $i = array_search($examID, $selectedExamIDs);
                                                        $examName = $selectedExams[$i];
                                                        ?>
                                                        <tr>
                                                            <td class="text-center"><strong><?php echo htmlspecialchars($examName); ?></strong>
                                                            </td>
                                                            <?php
                                                            $studentTotalMarks = 0;
                                                            foreach ($ccSubjects as $subject) {
                                                                $subMarksObtained = '';
                                                                $isAbsent = 0;
                                                                foreach ($ccSubjectsData as $subjectData) {
                                                                    if (
                                                                        $subjectData['SubjectID'] == $subject['ID'] &&
                                                                        $subjectData['ExamName'] == $examID
                                                                    ) {
                                                                        $subMarksObtained = $subjectData['SubMarksObtained'];
                                                                        $isAbsent = $subjectData['isAbsent'] ?? 0;
                                                                        break;
                                                                    }
                                                                }
                                                                echo "<td class='text-center'>" . ($isAbsent ? '<span class="absent-mark">a</span>' : $subMarksObtained) . "</td>";
                                                                $studentTotalMarks += (float) ($isAbsent ? 0 : $subMarksObtained);
                                                            }
                                                            echo "<td class='text-center font-weight-bold'>" . ($studentTotalMarks) . "</td>";
                                                            ?>
                                                        </tr>
                                                        <?php
                                                        $i++;
                                                    }
                                                    ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    <?php } ?>

                                    <?php
                                    $optionalSubjects = getSubjects($dbh, $class, $examSession, 1, 0);
                                    if (!empty($optionalSubjects)) {
                                        if (hasOptionalSubjectWithGrading($dbh, $className, base64_decode(urldecode($_GET['examNames'])), $examSession)) {
                                            ?>
                                            <!-- Optional Subjects in Grades-->
                                            <div class="d-flex flex-column mt-4">
                                                <strong>Grade in Optional Subjects:</strong>
                                                <table class="table ">
                                                    <thead>
                                                        <tr class="text-center">
                                                            <th class="font-weight-bold">Exam</th>
                                                            <?php
                                                            foreach ($optionalSubjects as $subject) {
                                                                echo "<th class='font-weight-bold'>{$subject['SubjectName']}</th>";
                                                            }
                                                            ?>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php
                                                        $i = 0;
                                                        $marksObtained = "";
                                                        foreach ($selectedExamIDs as $examID) {
                                                            $i = array_search($examID, $selectedExamIDs);
                                                            $examName = $selectedExams[$i];
                                                            ?>
                                                            <tr class="text-center">
                                                                <td class="font-weight-bold"><?php echo $examName; ?></td>
                                                                <?php
                                                                foreach ($optionalSubjects as $subject) {
                                                                    $marksObtained = '';
                                                                    $isAbsent = 0;
                                                                    foreach ($allSubjectsJsonArray as $row) {
                                                                        $subjectData = json_decode($row['SubjectsJSON'], true);
                                                                        foreach ($subjectData as $data) {
                                                                            if (
                                                                                $data['SubjectID'] == $subject['ID'] &&
                                                                                $data['IsOptional'] == 1 &&
                                                                                $data['ExamName'] == $examID
                                                                            ) {
                                                                                $marksObtained = $data['SubMarksObtained'];
                                                                                $isAbsent = $data['isAbsent'] ?? 0;
                                                                                break 2;
                                                                            }
                                                                        }
                                                                    }
                                                                    echo "<td>" . ($isAbsent ? '<span class="absent-mark">a</span>' : $marksObtained) . "</td>";
                                                                }
                                                                ?>
                                                            </tr>
                                                            <?php
                                                            $i++;
                                                        }
                                                        ?>
                                                    </tbody>
                                                </table>
                                            </div>
                                            <?php
                                        } else {
                                            ?>
                                            <!-- Optional Subjects in Marks-->
                                            <div class="d-flex flex-column mt-4">
                                                <strong>Marks in Optional Subjects:</strong>
                                                <table class="table ">
                                                    <thead>
                                                        <tr class="text-center">
                                                            <th class="align-middle font-weight-bold">Exam</th>
                                                            <?php
                                                            $totalMaxMarks = 0;
                                                            foreach ($optionalSubjects as $subject) {
                                                                $maxMarks = '';
                                                                foreach ($allSubjectsJsonArray as $row) {
                                                                    $subjectData = json_decode($row['SubjectsJSON'], true);
                                                                    foreach ($subjectData as $data) {
                                                                        foreach ($selectedExamIDs as $examName) {
                                                                            if ($data['SubjectID'] == $subject['ID'] && $data['IsOptional'] == 1 && $data['ExamName'] == $examName) {
                                                                                $maxMarks = $data['SubMaxMarks'];
                                                                                break 3;
                                                                            }
                                                                        }
                                                                    }
                                                                }
                                                                $totalMaxMarks += (float) $maxMarks;
                                                                echo "<th class='font-weight-bold'>{$subject['SubjectName']}<br><br>({$maxMarks})</th>";
                                                            }
                                                            echo "<th class='font-weight-bold'>Total <br><br>({$totalMaxMarks})</th>";
                                                            ?>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php
                                                        $i = 0;
                                                        foreach ($selectedExamIDs as $examID) {
                                                            $i = array_search($examID, $selectedExamIDs);
                                                            $examName = $selectedExams[$i];
                                                            ?>
                                                            <tr class="text-center">
                                                                <td class="font-weight-bold"><?php echo $examName; ?></td>
                                                                <?php
                                                                $totalMarksObtained = 0;
                                                                foreach ($optionalSubjects as $subject) {
                                                                    $marksObtained = '';
                                                                    $isAbsent = 0;
                                                                    foreach ($allSubjectsJsonArray as $row) {
                                                                        $subjectData = json_decode($row['SubjectsJSON'], true);

                                                                        foreach ($subjectData as $data) {
                                                                            if (
                                                                                $data['SubjectID'] == $subject['ID'] &&
                                                                                $data['IsOptional'] == 1 &&
                                                                                $data['ExamName'] == $examID
                                                                            ) {
                                                                                $marksObtained = $data['SubMarksObtained'];
                                                                                $isAbsent = $data['isAbsent'] ?? 0;
                                                                                break 2;
                                                                            }
                                                                        }
                                                                    }
                                                                    echo "<td>" . ($isAbsent ? '<span class="absent-mark">a</span>' : $marksObtained) . "</td>";
                                                                    $totalMarksObtained += (float) ($isAbsent ? 0 : $marksObtained);
                                                                }
                                                                echo "<td class='font-weight-bold'>" . ($totalMarksObtained ?: '') . "</td>";
                                                                ?>
                                                            </tr>
                                                            <?php
                                                            $i++;
                                                        }
                                                        ?>
                                                    </tbody>
                                                </table>
                                            </div>
                                            <?php
                                        }
                                    }
                                    ?>

                                    <footer class="d-flex justify-content-between mt-5 font-weight-bold">
                                        <div class="mt-5">
                                            <label>Supervisor/Principal</label>
                                        </div>
                                        <div class="mt-5">
                                            <label>Teacher Incharge</label>
                                        </div>
                                    </footer>
                                </div>
                            </div>
                        </div>
                    </div>

                    <?php
                    $examLabels = json_encode(array_values($selectedExams));
                    $examPercentagesData = json_encode(array_values($examPercentages));
                    ?>
                    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
                    <script>
                        document.addEventListener('DOMContentLoaded', function () {
                            var ctx = document.getElementById('performanceChart').getContext('2d');
                            var performanceChart = new Chart(ctx, {
                                type: 'bar',
                                data: {
                                    labels: <?php echo $examLabels; ?>,
                                    datasets: [{
                                        label: 'Exam Performance',
                                        data: <?php echo $examPercentagesData; ?>,
                                        backgroundColor: 'rgba(128, 0, 0, 0.7)',
                                        borderColor: 'rgba(128, 0, 0, 1)',
                                        borderWidth: 1
                                    }]
                                },
                                options: {
                                    animation: false,
                                    responsive: true,
                                    maintainAspectRatio: false,
                                    scales: {
                                        y: {
                                            beginAtZero: true,
                                            max: 100,
                                            ticks: {
                                                callback: function (value) {
                                                    const customTicks = [10, 40, 60, 80, 100];
                                                    if (customTicks.includes(value)) {
                                                        return value;
                                                    }
                                                    return null;
                                                },
                                            }
                                        }
                                    }
                                }
                            });
                        });
                    </script>
                </body>

                </html>
                <?php
            } else {
                echo "<script>alert('Student not found.'); window.location.href='view-result.php';</script>";
            }
        } else {
            echo "<script>alert('Student not selected.'); window.location.href='view-result.php';</script>";
        }
    } else {
        echo "<script>alert('Invalid Request.'); window.location.href='view-result.php';</script>";
    }
}
?>