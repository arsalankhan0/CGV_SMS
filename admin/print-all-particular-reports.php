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
    if (isset($_GET['className']) && isset($_GET['examSession']) && isset($_GET['examName'])) 
    {
        $className = urldecode($_GET['className']);
        $examSession = urldecode($_GET['examSession']);
        $examName = urldecode($_GET['examName']);

        // Fetch all students and their reports based on the specified criteria
        $sqlReports = "SELECT * FROM tblreports WHERE ClassName = :className AND ExamSession = :examSession AND IsDeleted = 0";
        $stmtReports = $dbh->prepare($sqlReports);
        $stmtReports->bindParam(':className', $className, PDO::PARAM_STR);
        $stmtReports->bindParam(':examSession', $examSession, PDO::PARAM_STR);
        $stmtReports->execute();
        $allReports = $stmtReports->fetchAll(PDO::FETCH_ASSOC);

        // Filter the reports based on examName
        $allReports = array_filter($allReports, function($report) use ($examName) {
            // Extract ExamName from SubjectsJSON and compare with examName
            $subjectsJSON = json_decode($report['SubjectsJSON'], true);
            foreach ($subjectsJSON as $subject) {
                if ($subject['ExamName'] === $examName) {
                    return true; // Keep the report if the examName matches
                }
            }
            return false; // Exclude the report if no match is found
        });

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
            <title>TPS || Student Reports</title>
            <link rel="stylesheet" href="vendors/select2-bootstrap-theme/select2-bootstrap.min.css">
            <link rel="stylesheet" href="css/style.css" />
            <link rel="stylesheet" href="./css/reportCard.css" />
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

                        // Fetch Exam Name from the database
                        $examNameSql = "SELECT ExamName FROM tblexamination WHERE ID = :examName AND IsDeleted = 0";
                        $examNameQuery = $dbh->prepare($examNameSql);
                        $examNameQuery->bindParam(':examName', $examName, PDO::PARAM_STR);
                        $examNameQuery->execute();
                        $examNameRow = $examNameQuery->fetch(PDO::FETCH_COLUMN);
                        ?>
                        <div class="card d-flex justify-content-center align-items-center">
                            <div class="card-body" id="report-card">
                                <img src="../Main/img/logo1.png" alt="TPS" class="watermark">
                                <h4 class="card-title" style="text-align: center;">TIBETAN PUBLIC SCHOOL</h4>
                                <div class="d-flex justify-content-center mt-4">
                                    <strong>Result of <?php echo htmlspecialchars($examNameRow); ?></strong>
                                </div>
                                <!-- Student's Details -->
                                <div class="my-4">
                                    <!-- Row 1 -->
                                    <div class="d-flex flex-row align-items-start" style="gap: 30px;">
                                        <div class="d-flex align-items-center w-100">
                                            <label>Duration:</label><span class="border-bottom border-dark ml-2 pl-3 w-100" style="box-sizing: border-box;"></span>
                                        </div>
                                        <div class="d-flex align-items-center w-100">
                                            <label class="text-nowrap">To:</label><span class="border-bottom border-dark ml-2 pl-3 w-100" style="box-sizing: border-box;"></span>
                                        </div>
                                    </div>
                                    <!-- Row 2 -->
                                    <div class="d-flex flex-row align-items-start my-2">
                                        <div class=" mr-5">
                                            <label>Code No.:</label><span class="border-bottom border-dark ml-2 px-5"></span>
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
                                                <th style="width:10%;">S.No.</th>
                                                <th>Subject</th>
                                                <th>Marks Obtained (MM: <?php echo $maxMarks; ?>)</th>
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
                                                        <td>{$subject['SubjectName']}</td>";
                                                        echo "<td>{$marksObtained}</td>";
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
                                                <td class="text-right">Total Marks Obtained</td>
                                                <td><?php echo $totalMarksObtained; ?></td>
                                            </tr>
                                            <tr>
                                                <td></td>
                                                <td class="text-right">Maximum Marks</td>
                                                <td><?php echo $totalMaxMarks; ?></td>
                                            </tr>
                                            <tr>
                                                <td></td>
                                                <td class="text-right">Percentage</td>
                                                <td><?php echo $percentage; ?>%</td>
                                            </tr>
                                            <tr>
                                                <td></td>
                                                <td class="text-right">Grade</td>
                                                <td><?php echo $grade; ?></td>
                                            </tr>
                                            <tr>
                                                <td></td>
                                                <td class="text-right">Rank</td>
                                                <td><?php echo $rank; ?></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>

                                <!-- Co-Curricular Component of Academic Session -->
                                <div class="d-flex flex-column mt-4">
                                    <strong>Marks Obtained in Co-curricular Component During the Assessment period</strong>
                                    <table class="table  w-100">
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

                                                    echo "<th>{$subject['SubjectName']}<br><br>({$maxMarks})</th>";
                                                    $studentTotalMaxMarks += (float)$maxMarks;
                                                }
                                                ?>
                                                <th>Total Marks Obtained<br><br><?php echo "(".htmlspecialchars($studentTotalMaxMarks).")"; ?></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr class="text-center">
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
                                                    echo "<td>" . $subMarksObtained . "</td>";

                                                    $studentTotalMarks += (float)$subMarksObtained;
                                                }

                                                echo "<td>{$studentTotalMarks}</td>";
                                                ?>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>

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
                                                <th>Subjects</th>
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
                                                <td>Grade Obtained</td>
                                                <?php
                                                    // Loop through each optional subject
                                                    foreach ($optionalSubjects as $subject) {
                                                        $marksObtained = ""; // Initialize marks obtained for the current subject

                                                        // Loop through allSubjectsJson to find the matching subject and extract marks obtained
                                                        foreach ($allSubjectsJsonArray as $row) {
                                                            $subjectData = json_decode($row['SubjectsJSON'], true); // Decode the JSON data into an associative array

                                                            // Loop through subject data to find the matching subject ID and check if it's optional
                                                            foreach ($subjectData as $data) {
                                                                if ($data['SubjectID'] === $subject['ID'] && $data['IsOptional'] == 1) {
                                                                    // Marks obtained found for the current optional subject
                                                                    $marksObtained = $data['SubMarksObtained'];
                                                                    break 2; // Break out of both inner loops
                                                                }
                                                            }
                                                        }

                                                        // Display the marks obtained
                                                        echo "<td>{$marksObtained}</td>";
                                                    }
                                                ?>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>

                                <footer class="d-flex justify-content-between mt-5">
                                    <div class="mt-5">
                                        <label>Date:</label><span class="border-bottom border-dark ml-2 signature-line"></span>
                                    </div>
                                    <div class="mt-5">
                                        <label>Class Teacher's Signature:</label><span class="border-bottom border-dark ml-2 signature-line"></span>
                                    </div>
                                </footer>
                            </div>
                        </div>
                    <?php
                    }
                    ?>
                </div>
            </div>
        </body>
        </html>
        <?php
    } else {
        echo "<script>alert('Invalid Request');</script>";
    }
}
?>
