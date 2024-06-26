<?php
session_start();
error_reporting(0);
include ('includes/dbconnection.php');

if (!isset($_SESSION['sturecmsaid']) || empty($_SESSION['sturecmsaid'])) {
    header('location:logout.php');
} else {
    // Function to check if there is grading system
    function hasOptionalSubjectWithGrading($dbh, $className, $examID)
    {
        $class = $className;
        $optionalGradingSql = "SELECT COUNT(*) FROM tblmaxmarks AS m
                                INNER JOIN tblexamination AS e ON m.ExamID = e.ID
                                WHERE m.GradingSystem = 1
                                AND m.ClassID = :className
                                AND m.ExamID = :examID
                                AND e.ExamType = 'Formative'";
        $optionalGradingQuery = $dbh->prepare($optionalGradingSql);
        $optionalGradingQuery->bindParam(':className', $class, PDO::PARAM_STR);
        $optionalGradingQuery->bindParam(':examID', $examID, PDO::PARAM_STR);
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
            $examName = base64_decode(urldecode($_GET['examName']));
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
                    foreach ($subjectsJSON as $subject) {
                        if ($subject['ExamName'] === $examName) {
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
                        <div class="container page-body-wrapper d-flex flex-column">
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
                                                    e.ExamName, 
                                                    e.DurationFrom, 
                                                    e.DurationTo
                                            FROM tblstudent s
                                            INNER JOIN tblclass c ON s.StudentClass = c.ID AND c.IsDeleted = 0
                                            INNER JOIN tblsections sec ON s.StudentSection = sec.ID AND sec.IsDeleted = 0
                                            INNER JOIN tblexamination e ON e.ID = :examName AND e.IsDeleted = 0
                                            WHERE s.ID = :studentID AND s.IsDeleted = 0";
                            $studentDetailsQuery = $dbh->prepare($studentDetailsSql);
                            $studentDetailsQuery->bindParam(':studentID', $studentName, PDO::PARAM_INT);
                            $studentDetailsQuery->bindParam(':examName', $examName, PDO::PARAM_STR);
                            $studentDetailsQuery->execute();
                            $studentDetails = $studentDetailsQuery->fetch(PDO::FETCH_ASSOC);

                            $durationFrom = isset($studentDetails['DurationFrom']) ? (new DateTime($studentDetails['DurationFrom']))->format('d-m-Y') : '';
                            $durationTo = isset($studentDetails['DurationFrom']) ? (new DateTime($studentDetails['DurationTo']))->format('d-m-Y') : '';
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
                                            <?php echo htmlspecialchars($studentDetails['ExamName']); ?></strong>
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
                                                    <td><?php echo htmlentities($studentDetails['StudentName']); ?></td>
                                                    <td class="font-weight-bold">Class</td>
                                                    <td><?php echo htmlentities($studentDetails['ClassName']); ?></td>
                                                </tr>
                                                <tr>
                                                    <td class="font-weight-bold">Roll No</td>
                                                    <td><?php echo htmlentities($studentDetails['RollNo']); ?></td>
                                                    <td class="font-weight-bold">Section</td>
                                                    <td><?php echo htmlentities($studentDetails['SectionName']); ?></td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                    <!-- Main Subjects -->
                                    <div class="d-flex flex-row">
                                        <?php
                                        $maxMarks = '';
                                        // Query to fetch max marks based on classID, examID, and sessionID
                                        $maxMarksQuery = $dbh->prepare("SELECT SubMaxMarks FROM tblmaxmarks WHERE ClassID = :classID AND ExamID = :examID AND SessionID = :sessionID");
                                        $maxMarksQuery->bindParam(':classID', $className, PDO::PARAM_INT);
                                        $maxMarksQuery->bindParam(':examID', $examName, PDO::PARAM_INT);
                                        $maxMarksQuery->bindParam(':sessionID', $examSession, PDO::PARAM_INT);
                                        $maxMarksQuery->execute();
                                        $maxMarksRow = $maxMarksQuery->fetch(PDO::FETCH_ASSOC);

                                        $maxMarks = ($maxMarksRow) ? $maxMarksRow['SubMaxMarks'] : 'N/A';
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
                                                $subjects = getSubjects($dbh, $class, $examSession, 0, 0);

                                                $counter = 1;
                                                $totalMarksObtained = 0;
                                                $totalMaxMarks = 0;
                                                foreach ($subjects as $subject) {
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
                                                    foreach ($allSubjectsJsonArray as $row) {
                                                        $subjectData = json_decode($row['SubjectsJSON'], true);
                                                        foreach ($subjectData as $data) {
                                                            if ($data['ExamName'] === $examName && $data['SubjectID'] === $subject['ID']) {
                                                                $marksObtained = $data['SubMarksObtained'];
                                                                $totalMaxMarks += (float) $data['SubMaxMarks'];
                                                                break 2;
                                                            }
                                                        }
                                                    }
                                                    // total marks obtained and maximum marks for the current subject
                                                    $totalMarksObtained += (float) $marksObtained;

                                                    echo "<tr>
                                                        <td class='text-center'>{$counter}</td>
                                                        <td>{$subject['SubjectName']}</td>
                                                        <td class='text-center'>{$marksObtained}</td>";
                                                    echo "</tr>";
                                                    $counter++;
                                                }
                                                // Calculate percentage
                                                if ($totalMaxMarks > 0) {
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
                                                } else {
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
                                                    $subjects = getSubjects($dbh, $class, $examSession, 0, 1);

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
                                                    foreach ($subjects as $subject) {
                                                        $maxMarks = '';
                                                        // Loop through the decoded JSON to find the max marks for the current subject
                                                        foreach ($subjectsData as $subjectData) {
                                                            if ($subjectData['SubjectID'] == $subject['ID'] && $subjectData['ExamName'] == $examName) {
                                                                $maxMarks = $subjectData['SubMaxMarks'];
                                                                break;
                                                            }
                                                        }

                                                        echo "<th class='font-weight-bold' style='font-size: 1rem !important;'>{$subject['SubjectName']}<br><br>({$maxMarks})</th>";
                                                        $studentTotalMaxMarks += (float) $maxMarks;
                                                    }
                                                    ?>
                                                    <th class="font-weight-bold">Total Marks
                                                        Obtained<br><br><?php echo "(" . htmlspecialchars($studentTotalMaxMarks) . ")"; ?>
                                                    </th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <?php
                                                    $studentTotalMarks = 0;

                                                    foreach ($subjects as $subject) {
                                                        $subMarksObtained = '';

                                                        // Loop through the decoded JSON to find the SubMarksObtained for the current subject
                                                        foreach ($subjectsData as $subjectData) {
                                                            if ($subjectData['SubjectID'] == $subject['ID'] && $subjectData['ExamName'] == $examName) {
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
                                            </tbody>
                                        </table>
                                    </div>

                                    <?php
                                    $optionalSubjects = getSubjects($dbh, $class, $examSession, 1, 0);

                                    if (hasOptionalSubjectWithGrading($dbh, $className, $examName)) {
                                        ?>
                                        <!-- Optional Subjects in Grades-->
                                        <div class="d-flex flex-column mt-4">
                                            <strong>Grade in Optional Subjects:</strong>
                                            <table class="table ">
                                                <thead>
                                                    <tr class="text-center">
                                                        <th class="font-weight-bold">Subjects</th>
                                                        <?php
                                                        foreach ($optionalSubjects as $subject) {
                                                            echo "<th class='font-weight-bold'>{$subject['SubjectName']}</th>";
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
                                                                    if ($data['SubjectID'] === $subject['ID'] && $data['IsOptional'] == 1 && $data['ExamName'] == $examName) {
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
                                    } else {
                                        ?>
                                        <!-- Optional Subjects in Marks-->
                                        <div class="d-flex flex-column mt-4">
                                            <strong>Marks in Optional Subjects:</strong>
                                            <table class="table ">
                                                <thead>
                                                    <tr class="text-center">
                                                        <th class="align-middle font-weight-bold">Subjects</th>
                                                        <?php
                                                        $totalMaxMarks = 0;
                                                        foreach ($optionalSubjects as $subject) {
                                                            $maxMarks = "";
                                                            foreach ($allSubjectsJsonArray as $row) {
                                                                $subjectData = json_decode($row['SubjectsJSON'], true);
                                                                foreach ($subjectData as $data) {
                                                                    if ($data['SubjectID'] === $subject['ID'] && $data['IsOptional'] == 1 && $data['ExamName'] == $examName) {
                                                                        $maxMarks = $data['SubMaxMarks'];
                                                                        break 2;
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
                                                    <tr class="text-center">
                                                        <td class="font-weight-bold">Marks Obtained</td>
                                                        <?php
                                                        $totalMarksObtained = 0;
                                                        foreach ($optionalSubjects as $subject) {
                                                            $marksObtained = 0;
                                                            foreach ($allSubjectsJsonArray as $row) {
                                                                $subjectData = json_decode($row['SubjectsJSON'], true);

                                                                foreach ($subjectData as $data) {
                                                                    if ($data['SubjectID'] === $subject['ID'] && $data['IsOptional'] == 1 && $data['ExamName'] == $examName) {
                                                                        $marksObtained = (float) $data['SubMarksObtained'];
                                                                        break 2;
                                                                    }
                                                                }
                                                            }
                                                            $totalMarksObtained += $marksObtained;
                                                            echo "<td>{$marksObtained}</td>";
                                                        }
                                                        echo "<td class='font-weight-bold'>{$totalMarksObtained}</td>";
                                                        ?>
                                                    </tr>
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
                        </div>
                    </div>
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