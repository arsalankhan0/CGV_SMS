<?php
session_start();
error_reporting(0);
include ('includes/dbconnection.php');

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
    // Function To calculate grades and rank according to percentage
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

    if (isset($_GET['className']) && isset($_GET['examSession']) && isset($_GET['examNames']) && isset($_GET['SecName'])) {
        $className = base64_decode(urldecode($_GET['className']));
        $examSession = base64_decode(urldecode($_GET['examSession']));
        $examName = base64_decode(urldecode($_GET['examNames']));
        $sectionName = base64_decode(urldecode($_GET['SecName']));

        // Fetch all students and their reports based on the specified criteria
        $sqlReports = "SELECT * FROM tblreports 
                        WHERE ClassName = :className 
                        AND SectionName = :sectionName 
                        AND ExamSession = :examSession 
                        AND IsDeleted = 0";
        $stmtReports = $dbh->prepare($sqlReports);
        $stmtReports->bindParam(':className', $className, PDO::PARAM_STR);
        $stmtReports->bindParam(':sectionName', $sectionName, PDO::PARAM_STR);
        $stmtReports->bindParam(':examSession', $examSession, PDO::PARAM_STR);
        $stmtReports->execute();
        $allReports = $stmtReports->fetchAll(PDO::FETCH_ASSOC);

        $allReports = array_filter($allReports, function ($report) use ($examName) {
            $subjectsJSON = json_decode($report['SubjectsJSON'], true);
            $examList = explode(',', $examName);
            foreach ($subjectsJSON as $subject) {
                if (in_array($subject['ExamName'], $examList)) {
                    return true;
                }
            }
            return false;
        });


        // Get the active session ID and Name
        $getSessionSql = "SELECT session_id, session_name FROM tblsessions WHERE session_id = :selectedSession AND IsDeleted = 0";
        $sessionQuery = $dbh->prepare($getSessionSql);
        $sessionQuery->bindParam(':selectedSession', $examSession, PDO::PARAM_STR);
        $sessionQuery->execute();
        $session = $sessionQuery->fetch(PDO::FETCH_ASSOC);
        $sessionID = $session['session_id'];
        $sessionName = $session['session_name'];

        if (!$allReports) {
            echo "<script>alert('No data found for the selected criteria.');</script>";
        }
        ?>
        <!DOCTYPE html>
        <html lang="en">

        <head>
            <title>TPS || Student Reports</title>
            <link rel="stylesheet" href="vendors/select2-bootstrap-theme/select2-bootstrap.min.css">
            <link rel="stylesheet" href="css/style.css" />
            <link rel="stylesheet" href="./css/reportCard.css" />
        </head>

        <body>

            <div class="container-scroller">
                <div class="container-fluid page-body-wrapper d-flex flex-column">
                    <?php
                    $examDetailsQuery = $dbh->prepare("
                        SELECT GROUP_CONCAT(e.ExamName) as ExamNames, GROUP_CONCAT(e.ID) as ExamIDs
                        FROM tblexamination e
                        WHERE e.IsDeleted = 0 AND FIND_IN_SET(e.ID, '$examName');
                    ");
                    $examDetailsQuery->execute();
                    $examDetails = $examDetailsQuery->fetch(PDO::FETCH_ASSOC);
                    $allExamNames = $examDetails['ExamNames'];
                    $allExamIDs = $examDetails['ExamIDs'];



                    $groupedReports = [];
                    foreach ($allReports as $report) {
                        // key to group by
                        $studentName = $report['StudentName'];

                        if (!isset($groupedReports[$studentName])) {
                            $groupedReports[$studentName] = [];
                        }

                        $groupedReports[$studentName][] = $report;
                    }

                    foreach ($groupedReports as $studentName => $studentReports) {
                        $sql = "SELECT 
                                    s.ID, 
                                    s.CodeNumber, 
                                    s.StudentName, 
                                    c.ClassName, 
                                    sec.SectionName, 
                                    s.RollNo, 
                                    FIND_IN_SET(e.ID, '$allExamIDs') as HasExam,
                                    MIN(e.DurationFrom) as DurationFrom, 
                                    MAX(e.DurationTo) as DurationTo
                                FROM tblstudent s
                                INNER JOIN tblclass c ON (s.StudentClass = c.ID AND c.IsDeleted = 0)
                                INNER JOIN tblsections sec ON (s.StudentSection = sec.ID AND sec.IsDeleted = 0)
                                INNER JOIN tblexamination e ON FIND_IN_SET(e.ID, '$allExamIDs') AND e.IsDeleted = 0
                                WHERE s.ID = :studentID AND s.IsDeleted = 0";
                        $studentDetailsQuery = $dbh->prepare($sql);
                        $studentDetailsQuery->bindParam(':studentID', $studentReports[0]['StudentName'], PDO::PARAM_INT);
                        $studentDetailsQuery->execute();
                        $studentDetails = $studentDetailsQuery->fetch(PDO::FETCH_ASSOC);

                        $examNames = isset($allExamNames) ? $allExamNames : '';
                        $examIDs = isset($allExamIDs) ? $allExamIDs : '';

                        $durationFrom = isset($studentDetails['DurationFrom']) ? (new DateTime($studentDetails['DurationFrom']))->format('d-m-Y') : '';
                        $durationTo = isset($studentDetails['DurationFrom']) ? (new DateTime($studentDetails['DurationTo']))->format('d-m-Y') : '';

                        $selectedExams = explode(',', $examNames);
                        $selectedExamIDs = explode(',', $examIDs);
                        ?>
                        <div class="card d-flex justify-content-center align-items-center">
                            <div class="card-body" id="report-card">
                                <div class="site-name">tibetanpublicschool.com</div>
                                <img src="../Main/img/logo1.png" alt="TPS" class="watermark">
                                <div class="d-flex justify-content-center align-items-center pb-2 border-bottom border-secondary">
                                    <img src="../Main/img/logo1.png" width="120px" alt="TPS" class="img-fluid">
                                    <img src="../Main/img/reportLogo.png" alt="TPS" class="img-fluid mr-5 pr-5">
                                </div>
                                <div class="d-flex justify-content-center mt-4">
                                    <strong style="font-size: 1.3rem;">Result of
                                        <?php

                                        $selectedExamsArray = explode(',', $examNames);
                                        $lastExam = array_pop($selectedExamsArray);

                                        if (count($selectedExamsArray) >= 1) {
                                            $otherExams = implode(', ', $selectedExamsArray);
                                            echo htmlspecialchars($otherExams . (count($selectedExamsArray) > 1 ? ' and ' : ' and ') . $lastExam);
                                        } else {
                                            echo htmlspecialchars($examNames);
                                        }
                                        ?></strong>
                                </div>
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
                                <!-- Student Details -->
                                <div class="d-flex flex-column mb-4">
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th class="font-weight-bold text-center" colspan="4">STUDENT DETAILS</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td class="font-weight-bold" style="border-top: none; border-bottom: none;">Code No.
                                                </td>
                                                <td><?php echo htmlentities($studentDetails['CodeNumber']); ?></td>
                                                <td class="font-weight-bold" style="border-top: none; border-bottom: none;">Date
                                                </td>
                                                <td style="border-top: none; border-bottom: none;"></td>
                                            </tr>
                                            <tr>
                                                <td class="font-weight-bold">Name</td>
                                                <td class="text-capitalize">
                                                    <?php echo htmlentities($studentDetails['StudentName']); ?></td>
                                                <td class="font-weight-bold">Class</td>
                                                <td class="text-capitalize">
                                                    <?php echo htmlentities($studentDetails['ClassName']); ?></td>
                                            </tr>
                                            <tr>
                                                <td class="font-weight-bold">Roll No</td>
                                                <td><?php echo htmlentities($studentDetails['RollNo']); ?></td>
                                                <td class="font-weight-bold">Section</td>
                                                <td class="text-capitalize">
                                                    <?php echo htmlentities($studentDetails['SectionName']); ?></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <!-- Main Subjects -->
                                <div class="d-flex flex-column">
                                    <table class="table">
                                        <thead>
                                            <tr class="text-center">
                                                <th class="font-weight-bold" style="width:10%;">S.No.</th>
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
                                                $subjectMaxMarks = [];

                                                foreach ($selectedExamIDs as $examID) {
                                                    $marksObtained = '';
                                                    $maxMarks = '';

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
                                                                $maxMarks = $data['SubMaxMarks'];
                                                                break 2;
                                                            }
                                                        }
                                                    }
                                                    $subjectMarks[$examID] = $marksObtained ? $marksObtained : 'N/A';
                                                    $subjectMaxMarks[$examID] = $maxMarks ? $maxMarks : 'N/A';
                                                }
                                                // Display subject name, marks for each exam, and total marks
                                                echo "<tr>
                                                        <td class='text-center'>{$counter}</td>
                                                        <td>{$subject['SubjectName']}</td>";
                                                foreach ($subjectMarks as $exam => $marks) {
                                                    echo "<td class='text-center'>{$marks}</td>";
                                                }

                                                // Display the bar comparison for the first subject row
                                                if (count($selectedExamIDs) > 1 && $counter === 1) {
                                                    $studentID = $studentReports[0]['ID'];
                                                    $canvasID = "performanceChart_" . $studentID;
                                                    echo "<td class='text-center' rowspan='" . ($subjectCounter) . "'>
                                                                <canvas id='$canvasID' style='width: 100%; height: 100%;'></canvas>
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
                                <div class="d-flex flex-column mt-4">
                                    <strong>Marks Obtained in Co-curricular Component During the Assessment period</strong>
                                    <table class="table w-100">
                                        <thead>
                                            <tr class="text-center">
                                                <th class='font-weight-bold' style="vertical-align: middle">Exam</th>
                                                <?php
                                                $subjects = getSubjects($dbh, $class, $examSession, 0, 1);

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

                                                $subjectsData = !empty($subjectsJson) ? json_decode($subjectsJson, true) : [];

                                                $studentTotalMaxMarks = 0;
                                                foreach ($subjects as $subject) {
                                                    $maxMarks = '';

                                                    // Loop through the decoded JSON to find the max marks for the current subject
                                                    foreach ($subjectsData as $subjectData) {
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
                                                $examName = $selectedExams[$i];
                                                ?>
                                                <tr>
                                                    <td class="text-center"><strong><?php echo htmlspecialchars($examName); ?></strong>
                                                    </td>

                                                    <?php
                                                    $studentTotalMarks = 0;

                                                    foreach ($subjects as $subject) {
                                                        $subMarksObtained = '';

                                                        // Loop through the decoded JSON to find the SubMarksObtained for the current subject
                                                        foreach ($subjectsData as $subjectData) {
                                                            if ($subjectData['SubjectID'] == $subject['ID'] && $subjectData['ExamName'] == $examID) {
                                                                $subMarksObtained = $subjectData['SubMarksObtained'];
                                                                break;
                                                            }
                                                        }
                                                        echo "<td class='text-center'>" . $subMarksObtained . "</td>";

                                                        $studentTotalMarks += (float) $subMarksObtained;
                                                    }

                                                    echo "<td class='text-center font-weight-bold'>{$studentTotalMarks}</td>";
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
                                $optionalSubjects = getSubjects($dbh, $class, $examSession, 1, 0);
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
                                                    $examName = $selectedExams[$i];
                                                    ?>
                                                    <tr class="text-center">
                                                        <td class="font-weight-bold"><?php echo $examName; ?></td>
                                                        <?php
                                                        foreach ($optionalSubjects as $subject) {

                                                            foreach ($allSubjectsJsonArray as $row) {
                                                                $subjectData = json_decode($row['SubjectsJSON'], true);

                                                                foreach ($subjectData as $data) {
                                                                    if ($data['SubjectID'] == $subject['ID'] && $data['IsOptional'] == 1 && $data['ExamName'] == $examID) {
                                                                        $marksObtained = $data['SubMarksObtained'];
                                                                        break;
                                                                    }
                                                                }
                                                            }
                                                            echo "<td>{$marksObtained}</td>";
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
                                                                        break;
                                                                    }
                                                                }
                                                            }
                                                        }
                                                        $totalMaxMarks += $maxMarks;
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
                                                    $examName = $selectedExams[$i];
                                                    ?>
                                                    <tr class="text-center">
                                                        <td class="font-weight-bold"><?php echo $examName; ?></td>
                                                        <?php
                                                        $totalMarksObtained = 0;
                                                        foreach ($optionalSubjects as $subject) {
                                                            $marksObtained = '';
                                                            foreach ($allSubjectsJsonArray as $row) {
                                                                $subjectData = json_decode($row['SubjectsJSON'], true);

                                                                foreach ($subjectData as $data) {
                                                                    if ($data['SubjectID'] == $subject['ID'] && $data['IsOptional'] == 1 && $data['ExamName'] == $examID) {
                                                                        $marksObtained = $data['SubMarksObtained'];
                                                                        break;
                                                                    }
                                                                }
                                                            }
                                                            $totalMarksObtained += (float) $marksObtained;
                                                            echo "<td>{$marksObtained}</td>";
                                                        }
                                                        echo "<td class='font-weight-bold'>{$totalMarksObtained}</td>";
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
                        <?php

                        $studentID = $studentReports[0]['ID'];
                        $canvasID = "performanceChart_" . $studentID;
                        $examLabels = json_encode(array_values($selectedExams));
                        $examPercentagesData = json_encode(array_values($examPercentages));
                        ?>
                        <script>
                            document.addEventListener('DOMContentLoaded', function () {
                                var ctx = document.getElementById('<?php echo $canvasID; ?>').getContext('2d');
                                var performanceChart = new Chart(ctx, {
                                    type: 'bar',
                                    data: {
                                        labels: <?php echo $examLabels; ?>,
                                        datasets: [{
                                            label: 'Exam Performance',
                                            data: <?php echo $examPercentagesData; ?>,
                                            backgroundColor: 'rgba(128, 0, 0, 0.7)',
                                            borderColor: 'rgba(128, 0, 0, 1)',
                                            borderWidth: 1,
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
                                                    callback: function(value) {
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
                        <?php
                    }
                    ?>
                </div>
            </div>
            <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        </body>

        </html>
        <?php
    } else {
        echo "<script>alert('Invalid Request');</script>";
    }
}
?>