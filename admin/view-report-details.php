<?php
session_start();
error_reporting(E_ALL);
include('includes/dbconnection.php');

if (!isset($_SESSION['sturecmsaid']) || empty($_SESSION['sturecmsaid'])) {
    header('location:logout.php');
} else {

    $getSessionSql = "SELECT session_id, session_name FROM tblsessions WHERE is_active = 1 AND IsDeleted = 0";
    $sessionQuery = $dbh->prepare($getSessionSql);
    $sessionQuery->execute();
    $session = $sessionQuery->fetch(PDO::FETCH_ASSOC);

    // Function to check if there is grading system
    function hasOptionalSubjectWithGrading($dbh, $className, $sessionID)
    {
        $optionalGradingSql = "SELECT COUNT(*) FROM tblmaxmarks AS m
                                INNER JOIN tblexamination AS e ON m.ExamID = e.ID
                                WHERE m.GradingSystem = 1
                                AND m.ClassID = :className
                                AND m.SessionID = :sessionID
                                AND e.ExamType = 'Summative'";
        $optionalGradingQuery = $dbh->prepare($optionalGradingSql);
        $optionalGradingQuery->bindParam(':className', $className, PDO::PARAM_STR);
        $optionalGradingQuery->bindParam(':sessionID', $sessionID, PDO::PARAM_STR);
        $optionalGradingQuery->execute();
        $optionalGradingCount = $optionalGradingQuery->fetchColumn();
        return $optionalGradingCount > 0;
    }
    // Function to show the Exam Names as per the condition
    function fetchExamNames($dbh, $examType, $examSession)
    {
        // $examNamesSql = "SELECT ExamName, ID FROM tblexamination WHERE ExamType = :examType AND IsDeleted = 0 AND session_id = :examSession";
        $examNamesSql = "SELECT ExamName, ID FROM tblexamination WHERE ExamType = :examType AND IsDeleted = 0";
        $examNamesQuery = $dbh->prepare($examNamesSql);
        $examNamesQuery->bindParam(':examType', $examType, PDO::PARAM_STR);
        // $examNamesQuery->bindParam(':examSession', $examSession, PDO::PARAM_INT);
        $examNamesQuery->execute();
        return $examNamesQuery->fetchAll(PDO::FETCH_ASSOC);
    }
    // Function to show subjects as per the condition
    function fetchSubjects($dbh, $className, $isOptional, $isCurricularSubject, $examSession)
    {
        global $session;
        // Check if the exam session is active
        $isActiveSession = $session['session_id'];

        if ($isActiveSession == $examSession) {
            // Fetch subjects from tblsubjects if the session is active
            $subjectsSql = "SELECT ID, SubjectName FROM tblsubjects 
                            WHERE ClassName LIKE :className 
                            AND IsOptional = :isOptional 
                            AND IsCurricularSubject = :isCurricularSubject 
                            AND IsDeleted = 0";
        } else {
            // Fetch subjects from tblsubjects and join with tblsubjecthistory if the session is not active
            $subjectsSql = "SELECT s.ID, s.SubjectName 
                            FROM tblsubjects s
                            INNER JOIN tblsubjecthistory sh ON s.ID = sh.SubjectID
                            WHERE s.ClassName LIKE :className 
                            AND s.IsOptional = :isOptional 
                            AND s.IsCurricularSubject = :isCurricularSubject 
                            AND s.IsDeleted = 0 
                            AND sh.SessionID = :examSession"; // Ensure to filter by SessionID
        }

        $subjectsQuery = $dbh->prepare($subjectsSql);
        $subjectsQuery->bindParam(':className', $className, PDO::PARAM_STR);
        $subjectsQuery->bindParam(':isOptional', $isOptional, PDO::PARAM_INT);
        $subjectsQuery->bindParam(':isCurricularSubject', $isCurricularSubject, PDO::PARAM_INT);

        // Bind the examSession parameter only if the session is not active
        if ($isActiveSession != $examSession) {
            $subjectsQuery->bindParam(':examSession', $examSession, PDO::PARAM_INT);
        }

        $subjectsQuery->execute();
        return $subjectsQuery->fetchAll(PDO::FETCH_ASSOC);
    }
    function fetchSubjectsJson($dbh, $className, $studentID, $sessionID)
    {
        $fetchSubjectsJsonSql = "SELECT SubjectsJSON FROM tblreports WHERE ClassName = :className AND StudentName = :studentID AND ExamSession = :sessionID";
        $fetchSubjectsJsonQuery = $dbh->prepare($fetchSubjectsJsonSql);
        $fetchSubjectsJsonQuery->bindParam(':className', $className, PDO::PARAM_STR);
        $fetchSubjectsJsonQuery->bindParam(':studentID', $studentID, PDO::PARAM_STR);
        $fetchSubjectsJsonQuery->bindParam(':sessionID', $sessionID, PDO::PARAM_STR);
        $fetchSubjectsJsonQuery->execute();
        return $fetchSubjectsJsonQuery->fetchAll(PDO::FETCH_COLUMN);
    }

    // Function to calculate total maximum marks for each assessment type
    function calculateTotalMaxMarks($dbh, $className, $sessionID)
    {
        $totalMaxMarks = [
            'formative' => 0,
            'summative' => 0,
            'curricular' => 0,
            'formativeOptional' => 0,
            'summativeOptional' => 0,
        ];

        // SQL to get sum of max marks for one subject in Formative exams (non-optional)
        $formativeSql = "SELECT SUM(m.SubMaxMarks) AS MaxMarks
                            FROM tblmaxmarks AS m
                            INNER JOIN tblexamination AS e ON m.ExamID = e.ID
                            INNER JOIN tblsubjects AS s ON m.SubjectID = s.ID
                            WHERE e.ExamType = 'Formative'
                            AND m.ClassID = :className
                            AND m.SessionID = :sessionID
                            AND s.IsOptional = 0
                            AND s.IsCurricularSubject = 0
                            GROUP BY s.ID
                            LIMIT 1";
        $formativeQuery = $dbh->prepare($formativeSql);
        $formativeQuery->bindParam(':className', $className, PDO::PARAM_STR);
        $formativeQuery->bindParam(':sessionID', $sessionID, PDO::PARAM_STR);
        $formativeQuery->execute();
        $formativeMarks = $formativeQuery->fetch(PDO::FETCH_COLUMN);
        $totalMaxMarks['formative'] = $formativeMarks ?: 0;

        // SQL to get sum of max marks for one subject in Formative exams (optional)
        $formativeOptionalSql = "SELECT SUM(m.SubMaxMarks) AS MaxMarks
                            FROM tblmaxmarks AS m
                            INNER JOIN tblexamination AS e ON m.ExamID = e.ID
                            INNER JOIN tblsubjects AS s ON m.SubjectID = s.ID
                            WHERE e.ExamType = 'Formative'
                            AND m.ClassID = :className
                            AND m.SessionID = :sessionID
                            AND s.IsOptional = 1
                            AND s.IsCurricularSubject = 0
                            GROUP BY s.ID
                            LIMIT 1";
        $formativeOptionalQuery = $dbh->prepare($formativeOptionalSql);
        $formativeOptionalQuery->bindParam(':className', $className, PDO::PARAM_STR);
        $formativeOptionalQuery->bindParam(':sessionID', $sessionID, PDO::PARAM_STR);
        $formativeOptionalQuery->execute();
        $formativeOptionalMarks = $formativeOptionalQuery->fetch(PDO::FETCH_COLUMN);
        $totalMaxMarks['formativeOptional'] = $formativeOptionalMarks ?: 0;

        // SQL to get sum of max marks for one subject in Summative exams (non-optional)
        $summativeSql = "SELECT SUM(m.SubMaxMarks) AS MaxMarks
                            FROM tblmaxmarks AS m
                            INNER JOIN tblexamination AS e ON m.ExamID = e.ID
                            INNER JOIN tblsubjects AS s ON m.SubjectID = s.ID
                            WHERE e.ExamType = 'Summative'
                            AND m.ClassID = :className
                            AND m.SessionID = :sessionID
                            AND s.IsOptional = 0
                            AND s.IsCurricularSubject = 0
                            GROUP BY s.ID
                            LIMIT 1";
        $summativeQuery = $dbh->prepare($summativeSql);
        $summativeQuery->bindParam(':className', $className, PDO::PARAM_STR);
        $summativeQuery->bindParam(':sessionID', $sessionID, PDO::PARAM_STR);
        $summativeQuery->execute();
        $summativeMarks = $summativeQuery->fetch(PDO::FETCH_COLUMN);
        $totalMaxMarks['summative'] = $summativeMarks ?: 0;

        // SQL to get sum of max marks for one subject in Summative exams (optional)
        $summativeOptionalSql = "SELECT SUM(m.SubMaxMarks) AS MaxMarks
                            FROM tblmaxmarks AS m
                            INNER JOIN tblexamination AS e ON m.ExamID = e.ID
                            INNER JOIN tblsubjects AS s ON m.SubjectID = s.ID
                            WHERE e.ExamType = 'Summative'
                            AND m.ClassID = :className
                            AND m.SessionID = :sessionID
                            AND s.IsOptional = 1
                            AND s.IsCurricularSubject = 0
                            GROUP BY s.ID
                            LIMIT 1";
        $summativeOptionalQuery = $dbh->prepare($summativeOptionalSql);
        $summativeOptionalQuery->bindParam(':className', $className, PDO::PARAM_STR);
        $summativeOptionalQuery->bindParam(':sessionID', $sessionID, PDO::PARAM_STR);
        $summativeOptionalQuery->execute();
        $summativeOptionalMarks = $summativeOptionalQuery->fetch(PDO::FETCH_COLUMN);
        $totalMaxMarks['summativeOptional'] = $summativeOptionalMarks ?: 0;

        // SQL to calculate total max marks for Curricular subjects
        $curricularSql = "SELECT SUM(m.SubMaxMarks) AS MaxMarks
                            FROM tblmaxcocurricular AS m
                            INNER JOIN tblsubjects AS s ON m.SubjectID = s.ID
                            WHERE m.ClassID = :className
                            AND m.SessionID = :sessionID
                            AND s.IsCurricularSubject = 1
                            GROUP BY m.SubjectID";
        $curricularQuery = $dbh->prepare($curricularSql);
        $curricularQuery->bindParam(':className', $className, PDO::PARAM_STR);
        $curricularQuery->bindParam(':sessionID', $sessionID, PDO::PARAM_STR);
        $curricularQuery->execute();
        $curricularMarks = $curricularQuery->fetchAll(PDO::FETCH_COLUMN);
        $totalMaxMarks['curricular'] = array_sum($curricularMarks);

        return $totalMaxMarks;
    }

    // Function to get the particular CoCurricular subject's max marks for academic session
    function getCoCurricularSubMaxMarks($dbh, $subjectID, $classID, $sessionID)
    {
        $sql = "SELECT SubMaxMarks FROM tblmaxcocurricular WHERE SubjectID = :subjectID AND ClassID = :classID AND SessionID = :sessionID";
        $query = $dbh->prepare($sql);
        $query->bindParam(':subjectID', $subjectID, PDO::PARAM_INT);
        $query->bindParam(':classID', $classID, PDO::PARAM_INT);
        $query->bindParam(':sessionID', $sessionID, PDO::PARAM_STR);
        $query->execute();
        return $query->fetchColumn(); // Return the SubMaxMarks
    }

    if (isset($_GET['studentName'])) {

        $studentID = filter_var(base64_decode(urldecode($_GET['studentName'])), FILTER_VALIDATE_INT);
        $examSession = base64_decode(urldecode($_GET['examSession']));

        $sqlStudent = "SELECT * FROM tblstudent WHERE ID = :studentName AND IsDeleted = 0";
        $queryStudent = $dbh->prepare($sqlStudent);
        $queryStudent->bindParam(':studentName', $studentID, PDO::PARAM_INT);
        $queryStudent->execute();
        $studentDetails = $queryStudent->fetch(PDO::FETCH_ASSOC);


        if ($studentDetails) {
            $stdClassID = $studentDetails['StudentClass'];
            $sqlStudentClass = "SELECT * FROM tblclass WHERE ID = :stdClassID AND IsDeleted = 0";
            $queryStudentClass = $dbh->prepare($sqlStudentClass);
            $queryStudentClass->bindParam(':stdClassID', $stdClassID, PDO::PARAM_INT);
            $queryStudentClass->execute();
            $studentClass = $queryStudentClass->fetch(PDO::FETCH_ASSOC);

            try {
                $className = base64_decode(urldecode($_GET['className']));
                $studentName = base64_decode(urldecode($_GET['studentName']));

                $sqlReports = "SELECT * FROM tblreports WHERE ExamSession = :examSession AND ClassName = :className AND StudentName = :studentName AND IsDeleted = 0";
                $stmtReports = $dbh->prepare($sqlReports);
                $stmtReports->bindParam(':examSession', $examSession, PDO::PARAM_STR);
                $stmtReports->bindParam(':className', $className, PDO::PARAM_INT);
                $stmtReports->bindParam(':studentName', $studentName, PDO::PARAM_INT);
                $stmtReports->execute();
                $reports = $stmtReports->fetchAll(PDO::FETCH_ASSOC);

                if (!$reports) {
                    echo "<script>alert('No data found for the selected student, class, and exam.');</script>";
                }
            } catch (PDOException $e) {
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
                    <link rel="stylesheet" href="css/style.css" />
                    <link rel="stylesheet" href="./css/finalReportCard.css">
                </head>

                <body>
                    <div class="container-scroller">
                        <div class="container-fluid page-body-wrapper d-flex flex-column">
                            <?php
                            // First check if student has historical record for this session
                            $checkHistorySql = "SELECT COUNT(*) FROM tblstudenthistory 
                                           WHERE StudentID = :studentID AND SessionID = :sessionID";
                            $checkHistoryQuery = $dbh->prepare($checkHistorySql);
                            $checkHistoryQuery->bindParam(':studentID', $studentID, PDO::PARAM_INT);
                            $checkHistoryQuery->bindParam(':sessionID', $examSession, PDO::PARAM_INT);
                            $checkHistoryQuery->execute();
                            $hasHistoricalRecord = ($checkHistoryQuery->fetchColumn() > 0);

                            if ($hasHistoricalRecord) {
                                // Fetch student details from history table for past sessions
                                $sql = "SELECT 
                                    s.ID AS StudentID,
                                    s.StudentName,
                                    s.CodeNumber,
                                    sh.Section AS StudentSection,
                                    sh.ClassID AS StudentClass,
                                    sh.rollno AS RollNo,
                                    s.FatherName,
                                    c.ClassName,
                                    c.ID AS ClassID,
                                    sec.SectionName,
                                    sec.ID AS SectionID
                                FROM 
                                    tblstudent s
                                INNER JOIN 
                                    tblstudenthistory sh ON s.ID = sh.StudentID AND sh.SessionID = :sessionID
                                INNER JOIN 
                                    tblclass c ON sh.ClassID = c.ID
                                INNER JOIN 
                                    tblsections sec ON sh.Section = sec.ID
                                WHERE 
                                    s.ID = :studentID 
                                    AND s.IsDeleted = 0 
                                    AND c.IsDeleted = 0 
                                    AND sec.IsDeleted = 0";
                                $studentDetailsQuery = $dbh->prepare($sql);
                                $studentDetailsQuery->bindParam(':studentID', $studentID, PDO::PARAM_INT);
                                $studentDetailsQuery->bindParam(':sessionID', $sessionID, PDO::PARAM_INT);
                            } else {
                                // Fetch current student details for active session
                                $sql = "SELECT 
                                    s.ID AS StudentID,
                                    s.StudentName,
                                    s.CodeNumber,
                                    s.StudentSection,
                                    s.StudentClass,
                                    s.RollNo,
                                    s.FatherName,
                                    c.ClassName,
                                    c.ID AS ClassID,
                                    sec.SectionName,
                                    sec.ID AS SectionID
                                FROM 
                                    tblstudent s
                                INNER JOIN 
                                    tblclass c ON s.StudentClass = c.ID
                                INNER JOIN 
                                    tblsections sec ON s.StudentSection = sec.ID
                                WHERE 
                                    s.ID = :studentID 
                                    AND s.IsDeleted = 0 
                                    AND c.IsDeleted = 0 
                                    AND sec.IsDeleted = 0";
                                $studentDetailsQuery = $dbh->prepare($sql);
                                $studentDetailsQuery->bindParam(':studentID', $studentID, PDO::PARAM_INT);
                            }

                            $studentDetailsQuery->execute();
                            $studentDetails = $studentDetailsQuery->fetch(PDO::FETCH_ASSOC);

                            $tMaxMarks = calculateTotalMaxMarks($dbh, $studentDetails['StudentClass'], $sessionID);

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
                                    <h4 class="card-title mt-4 mb-5" style="text-align: center;">MARKS CARD for the Academic Session
                                        <?php echo $sessionName; ?>
                                    </h4>
                                    <!-- Student's Details -->
                                    <div class="mt-4">
                                        <div class="font-weight-bold d-flex flex-row justify-content-between">
                                            <div>
                                                <label>Student's Code No:</label><span
                                                    class="dark-line ml-2 px-3"><?php echo htmlentities($studentDetails['CodeNumber']); ?></span>
                                            </div>
                                            <div>
                                                <label>Class:</label><span
                                                    class="dark-line ml-2 px-3 text-capitalize"><?php echo htmlentities($studentDetails['ClassName']); ?></span>
                                            </div>
                                            <div>
                                                <label>Section:</label><span
                                                    class="dark-line ml-2 px-3 text-capitalize"><?php echo htmlentities($studentDetails['SectionName']); ?></span>
                                            </div>
                                            <div>
                                                <label>Roll No:</label><span
                                                    class="dark-line ml-2 px-3"><?php echo htmlentities($studentDetails['RollNo']); ?></span>
                                            </div>
                                        </div>
                                        <!-- Student's Name -->
                                        <div class="d-flex w-100 align-items-center font-weight-bold">
                                            <label class="text-nowrap">Student's Name: </label>
                                            <p class="ml-2 pl-3 w-100 text-capitalize dark-line" style="box-sizing: border-box">
                                                <span><?php echo htmlentities($studentDetails['StudentName']); ?></span>
                                            </p>
                                        </div>
                                        <!-- Parent's Name -->
                                        <div class="d-flex w-100 align-items-center font-weight-bold">
                                            <label class="text-nowrap">Parents'/Guardian's Name: </label>
                                            <p class="dark-line ml-2 pl-3 w-100 text-capitalize" style="box-sizing: border-box;">
                                                <span><?php echo htmlentities($studentDetails['FatherName']); ?></span>
                                            </p>
                                        </div>
                                    </div>
                                    <!-- Main Subjects -->
                                    <div class="d-flex flex-column">
                                        <table class="table ">
                                            <thead>
                                                <?php
                                                $examNames = fetchExamNames($dbh, 'Formative', $examSession);
                                                $coCurricularExamNames = fetchExamNames($dbh, 'Co-Curricular', $examSession);
                                                $summativeExamNames = fetchExamNames($dbh, 'Summative', $examSession);
                                                $showCC = ($tMaxMarks['curricular'] > 0);
                                                ?>
                                                <tr class="text-center">
                                                    <th rowspan="2" class="font-weight-bold" colspan="2"
                                                        style="vertical-align: middle;">Subjects</th>
                                                    <th colspan="7" class="font-weight-bold">Formative Assessment <br><br>Max. Marks:
                                                        <?php echo $tMaxMarks['formative']; ?>
                                                    </th>
                                                    <?php if ($showCC) { ?>
                                                        <th colspan="2" class="text-wrap font-weight-bold">Co-Curricular Activities</th>
                                                    <?php } ?>
                                                    <th colspan="2" class="text-wrap font-weight-bold">Summative Assessment</th>
                                                    <th colspan="2" class="text-wrap font-weight-bold">Total (FA+CA+SA)</th>
                                                </tr>
                                                <tr class="text-center">
                                                    <?php
                                                    foreach ($examNames as $exam) {
                                                        echo "<th scope='col'>" . $exam['ExamName'] . "</th>";
                                                    }
                                                    ?>
                                                    <th class="font-weight-bold">Total</br>(<?php echo $tMaxMarks['formative']; ?>)</th>
                                                    <?php if ($showCC) { ?>
                                                        <th colspan="2">Max Marks: <?php echo $tMaxMarks['curricular']; ?></th>
                                                    <?php } ?>
                                                    <th colspan="2">Max Marks: <?php echo $tMaxMarks['summative']; ?></th>
                                                    <th colspan="2" class="font-weight-bold">Max Marks:
                                                        <?php echo $tMaxMarks['formative'] + ($showCC ? $tMaxMarks['curricular'] : 0) + $tMaxMarks['summative']; ?>
                                                    </th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                $class = "%$className%";
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

                                                foreach ($CCsubjects as $subject) {
                                                    // Initialize SubMarksObtained for the current subject
                                                    $subMarksObtained = '';
                                                    $subMaxMarks = '';

                                                    // Fetch SubjectsJSON for the current subject from tblreports
                                                    $fetchSubjectsJsonSql = "SELECT SubjectsJSON FROM tblcocurricularreports WHERE ClassName = :className AND ExamSession = :examSession AND StudentName = :studentID";
                                                    $fetchSubjectsJsonQuery = $dbh->prepare($fetchSubjectsJsonSql);
                                                    $fetchSubjectsJsonQuery->bindParam(':className', $className, PDO::PARAM_STR);
                                                    $fetchSubjectsJsonQuery->bindParam(':examSession', $examSession, PDO::PARAM_STR);
                                                    $fetchSubjectsJsonQuery->bindParam(':studentID', $studentID, PDO::PARAM_STR);
                                                    $fetchSubjectsJsonQuery->execute();
                                                    $subjectsJson = $fetchSubjectsJsonQuery->fetch(PDO::FETCH_COLUMN);

                                                    $CCsubjectsData = !empty($subjectsJson) ? json_decode($subjectsJson, true) : [];
                                                }
                                                $CCtotalMarksObtained = array_sum(array_column($CCsubjectsData, 'CoCurricularMarksObtained'));
                                                $CCtotalMaxMarks = array_sum(array_column($CCsubjectsData, 'CoCurricularMaxMarks'));
                                                $CCGrandTotal = $CCtotalMarksObtained;
                                                $CCGrandMaxTotal = $CCtotalMaxMarks;

                                                foreach ($subjects as $subject) {

                                                    $allSubjectsJson = fetchSubjectsJson($dbh, $studentDetails['ClassID'], $studentDetails['StudentID'], $examSession);

                                                    // Loop through all subjects JSON data for the current subject
                                                    foreach ($allSubjectsJson as $subjectsJson) {
                                                        // Decode the JSON to an associative array
                                                        $subjectsData = json_decode($subjectsJson, true);

                                                        // Loop through the decoded JSON to find the SubMarksObtained for the current subject
                                                        foreach ($subjectsData as $subjectData) {
                                                            if ($subjectData['SubjectID'] == $subject['ID']) {
                                                                // Check if student was absent
                                                                $isAbsent = $subjectData['isAbsent'] ?? 0;
                                                                $marksValue = $isAbsent ? '<span class="absent-mark">a</span>' : $subjectData['SubMarksObtained'];

                                                                // Find the index of the exam ID in the $examNames array
                                                                $examIndex = array_search($subjectData['ExamName'], array_column($examNames, 'ID'));
                                                                // Find the index of the exam ID in the $summativeExamNames array
                                                                $summativeExamIndex = array_search($subjectData['ExamName'], array_column($summativeExamNames, 'ID'));

                                                                // Update the corresponding index in the $examMarksArray with the marks obtained
                                                                if ($examIndex !== false) {
                                                                    $examMarksArray[$examIndex] = $marksValue;
                                                                }

                                                                // Add Summative marks if the exam type is Summative
                                                                foreach ($summativeExamNames as $summativeExam) {
                                                                    if ($subjectData['ExamName'] == $summativeExam['ID']) {
                                                                        $summativeIndex = count($examNames) + array_search($subjectData['ExamName'], array_column($summativeExamNames, 'ID'));
                                                                        $summativeMarksArray[$summativeIndex] = $marksValue;
                                                                        // Getting total marks obtained in summative exam (treat absent as 0)
                                                                        $totalSummativeMarks[$summativeExamIndex] += $isAbsent ? 0 : (float) $subjectData['SubMarksObtained'];
                                                                        // Getting total max marks in summative exam.
                                                                        $totalSummativeMaxMarks[$summativeExamIndex] += (float) $subjectData['SubMaxMarks'];
                                                                        break;
                                                                    }
                                                                }

                                                                // Update total marks and max marks (treat absent as 0 for calculations)
                                                                if ($examIndex !== false) {
                                                                    $totalMarks[$examIndex] += $isAbsent ? 0 : (float) $subjectData['SubMarksObtained'];
                                                                    $totalMaxMarks[$examIndex] += (float) $subjectData['SubMaxMarks'];
                                                                }
                                                            }
                                                        }
                                                    }

                                                    echo "<tr class='text-center'>
                                                        <td colspan='2' class='text-left'>{$subject['SubjectName']}</td>";
                                                    foreach ($examMarksArray as $examMarks) {
                                                        echo "<td>$examMarks</td>";
                                                    }
                                                    // Total marks obtained for each subject (treat 'a' as 0 in sum)
                                                    $rowFaTotal = array_sum(array_map(function ($v) {
                                                        return is_numeric($v) ? $v : 0;
                                                    }, $examMarksArray));
                                                    echo "<td class='font-weight-bold'>" . $rowFaTotal . "</td>";

                                                    //  Co-curricular marks
                                                    if ($showCC) {
                                                        echo "<td colspan='2'>" . $CCtotalMarksObtained . "</td>";
                                                    }

                                                    //  Summative marks
                                                    foreach ($summativeMarksArray as $summativeMarks) {
                                                        echo "<td colspan='2'>$summativeMarks</td>";
                                                    }
                                                    // Total marks obtained for all assessments (FA+CA+SA)
                                                    $totalAllAssessments = $rowFaTotal
                                                        + ($showCC ? $CCtotalMarksObtained : 0)
                                                        + array_sum(array_map(function ($v) {
                                                            return is_numeric($v) ? $v : 0;
                                                        }, $summativeMarksArray));
                                                    echo "<td colspan='2' class='font-weight-bold'>$totalAllAssessments</td>";
                                                    echo "</tr>";
                                                }

                                                ?>
                                                <!-- Marks Obtained -->
                                                <tr class="text-center">
                                                    <td class="font-weight-bold text-right" colspan="2">Marks Obtained</td>
                                                    <?php
                                                    // Total marks of each formative exam(column) in all subjects
                                                    foreach ($totalMarks as $examTotalMarks) {
                                                        // if ($examTotalMarks > 0) 
                                                        // {
                                                        echo "<td>" . $examTotalMarks . "</td>";
                                                        // } 
                                                        // else 
                                                        // {
                                                        //     echo "<td></td>";
                                                        // }
                                                    }
                                                    // Total marks obtained of all subjects in Formative
                                                    echo "<td class='font-weight-bold'>" . array_sum($totalMarks) . "</td>";

                                                    if ($showCC) {
                                                        // Total marks of co-curricular exam(column) in all subjects
                                                        echo "<td colspan='2'>" . $CCGrandTotal . "</td>";
                                                    }

                                                    // Total marks of summative exam(column) in all subjects
                                                    foreach ($totalSummativeMarks as $examTotalMarks) {
                                                        if ($examTotalMarks > 0) {
                                                            echo "<td colspan='2'>" . $examTotalMarks . "</td>";
                                                        } else {
                                                            echo "<td colspan='2'></td>";
                                                        }
                                                    }
                                                    //total marks obtained in all three FA+CA+SA
                                                    echo "<td colspan='2' class='font-weight-bold'>" . (array_sum($totalMarks) + ($showCC ? $CCGrandTotal : 0) + array_sum($totalSummativeMarks)) . "</td>";
                                                    ?>
                                                </tr>
                                                <!-- Maximum Marks -->
                                                <tr class="text-center">
                                                    <td class="text-right font-weight-bold" colspan="2">Maximum Marks</td>

                                                    <?php
                                                    // Total max marks of all formative exam(column) in all subjects
                                                    foreach ($totalMaxMarks as $maxMarks) {
                                                        if ($maxMarks > 0) {
                                                            echo "<td>$maxMarks</td>";
                                                        } else {
                                                            echo "<td></td>";
                                                        }
                                                    }
                                                    // Total Max marks of all subjects
                                                    echo "<td class='font-weight-bold'>" . array_sum($totalMaxMarks) . "</td>";
                                                    if ($showCC) {
                                                        // Total max marks of co-curricular exam(column) in all subjects
                                                        echo "<td colspan='2'>" . $CCGrandMaxTotal . "</td>";
                                                    }

                                                    // Total max marks of summative exam(column) in all subjects
                                                    foreach ($totalSummativeMaxMarks as $maxMarks) {
                                                        if ($maxMarks > 0) {
                                                            echo "<td colspan='2'>$maxMarks</td>";
                                                        } else {
                                                            echo "<td colspan='2'></td>";
                                                        }
                                                    }
                                                    //total max marks in all three FA+CA+SA
                                                    echo "<td colspan='2' class='font-weight-bold'>" . (array_sum($totalMaxMarks) + ($showCC ? $CCGrandMaxTotal : 0) + array_sum($totalSummativeMaxMarks)) . "</td>";
                                                    ?>
                                                </tr>
                                                <!-- Percentage -->
                                                <tr class="text-center">
                                                    <td class="text-right font-weight-bold" colspan="2">Percentage</td>
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
                                                        $percentage = $examTotalMarks > 0 ? round(($examTotalMarks / $totalMaxMarks[$key]) * 100, 2) . '%' : '';
                                                        echo "<td class='font-weight-bold'>$percentage</td>";
                                                    }
                                                    // total percentage of formative exams
                                                    $totalFormativePercentage = $totalMaxMarksObtained > 0 ? round(($totalMarksObtained / $totalMaxMarksObtained) * 100, 2) : 0;
                                                    echo "<td class='font-weight-bold'>$totalFormativePercentage%</td>";

                                                    if ($showCC) {
                                                        // percentage of co-curricular exam
                                                        $CCpercentage = $CCGrandMaxTotal > 0 ? round(($CCGrandTotal / $CCGrandMaxTotal) * 100, 2) . '%' : '';
                                                        echo "<td colspan='2' class='font-weight-bold'>" . $CCpercentage . "</td>";
                                                    }

                                                    // percentage of summative exam
                                                    foreach ($totalSummativeMarks as $key => $examTotalMarks) {
                                                        $percentage = $examTotalMarks > 0 ? round(($examTotalMarks / $totalSummativeMaxMarks[$key]) * 100, 2) . '%' : '';
                                                        echo "<td colspan='2' class='font-weight-bold'>$percentage</td>";
                                                    }

                                                    // Calculate the total percentage of all exams (FA+CA+SA) and display it
                                                    $totalAllExamsPercentage = ($totalMarksObtained + ($showCC ? $CCGrandTotal : 0) + $totalSummativeMarksObtained) > 0 ? round((($totalMarksObtained + ($showCC ? $CCGrandTotal : 0) + $totalSummativeMarksObtained) / ($totalMaxMarksObtained + ($showCC ? $CCGrandMaxTotal : 0) + $totalSummativeMaxMarksObtained)) * 100, 2) : 0;
                                                    echo "<td colspan='2' class='font-weight-bold'>$totalAllExamsPercentage%</td>";
                                                    ?>
                                                </tr>
                                                <!-- Grade -->
                                                <tr class="text-center">
                                                    <td class="text-right font-weight-bold" colspan="2">Grade</td>
                                                    <?php
                                                    // Grading system thresholds
                                                    $gradingSystem = array(
                                                        array('A+', 'A', 'B', 'C', 'D'),
                                                        array(85, 70, 55, 40, 33),
                                                        array(100, 85, 70, 55, 40)
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
                                                        echo "<td class='font-weight-bold'>$grade</td>";
                                                    }
                                                    // Total grade based on total percentage
                                                    $totalFormativeGrade = '';
                                                    for ($i = 0; $i < count($gradingSystem[0]); $i++) {
                                                        if ($totalFormativePercentage >= $gradingSystem[1][$i] && $totalFormativePercentage <= $gradingSystem[2][$i]) {
                                                            $totalFormativeGrade = $gradingSystem[0][$i];
                                                            break;
                                                        }
                                                    }
                                                    // Total grade based on total percentage
                                                    echo "<td class='font-weight-bold'>$totalFormativeGrade</td>";

                                                    if ($showCC) {
                                                        // Calculating Co-curricular grade
                                                        $CoCurricularGrade = '';
                                                        $CCpercentageValue = $CCGrandMaxTotal > 0 ? round(($CCGrandTotal / $CCGrandMaxTotal) * 100, 2) : '';
                                                        for ($i = 0; $i < count($gradingSystem[0]); $i++) {
                                                            if ($CCpercentageValue >= $gradingSystem[1][$i] && $CCpercentageValue <= $gradingSystem[2][$i]) {
                                                                $CoCurricularGrade = $gradingSystem[0][$i];
                                                                break;
                                                            }
                                                        }
                                                        echo "<td colspan='2' class='font-weight-bold'>$CoCurricularGrade</td>";
                                                    }

                                                    // Calculating Summative grade
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
                                                        echo "<td colspan='2' class='font-weight-bold'>$SummativeGrade</td>";
                                                    }

                                                    // Calculate total percentage for all exams (FA+CA+SA)
                                                    $totalPercentageFinal = ($totalMarksObtained + ($showCC ? $CCGrandTotal : 0) + $totalSummativeMarksObtained) > 0 ? round((($totalMarksObtained + ($showCC ? $CCGrandTotal : 0) + $totalSummativeMarksObtained) / ($totalMaxMarksObtained + ($showCC ? $CCGrandMaxTotal : 0) + $totalSummativeMaxMarksObtained)) * 100, 2) : 0;

                                                    // Determine total grade based on total percentage
                                                    $totalGrade = '';
                                                    for ($i = 0; $i < count($gradingSystem[0]); $i++) {
                                                        if ($totalPercentageFinal >= $gradingSystem[1][$i] && $totalPercentageFinal <= $gradingSystem[2][$i]) {
                                                            $totalGrade = $gradingSystem[0][$i];
                                                            break;
                                                        }
                                                    }
                                                    // Display total grade
                                                    echo "<td colspan='2' class='font-weight-bold'>$totalGrade</td>";
                                                    ?>
                                                </tr>
                                                <!-- Rank -->
                                                <tr class="text-center">
                                                    <td class="text-right font-weight-bold" colspan="2">Rank</td>
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
                                                        echo "<td class='text-wrap font-weight-bold' style='font-size: 0.7rem !important'>$rank</td>";
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
                                                    echo "<td class='text-wrap font-weight-bold' style='font-size: 0.7rem !important'>$totalRank</td>";


                                                    if ($showCC) {
                                                        // Calculating Co-curricular rank
                                                        $CoCurricularGrade = '';
                                                        $CCpercentageValue = $CCGrandMaxTotal > 0 ? round(($CCGrandTotal / $CCGrandMaxTotal) * 100, 2) : '';
                                                        for ($i = 0; $i < count($gradingSystem[0]); $i++) {
                                                            if ($CCpercentageValue >= $gradingSystem[1][$i] && $CCpercentageValue <= $gradingSystem[2][$i]) {
                                                                $CoCurricularGrade = $gradingSystem[0][$i];
                                                                break;
                                                            }
                                                        }

                                                        // Determine rank based on grade
                                                        $CoCurricularRank = isset($rankMappings[$CoCurricularGrade]) ? $rankMappings[$CoCurricularGrade] : '';

                                                        // Display the rank
                                                        echo "<td colspan='2' class='font-weight-bold'>$CoCurricularRank</td>";
                                                    }


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
                                                        echo "<td colspan='2' class='font-weight-bold'>$SummativeRank</td>";
                                                    }

                                                    // Determine total grade based on total percentage
                                                    $totalPercentageFinal = ($totalMarksObtained + ($showCC ? $CCGrandTotal : 0) + $totalSummativeMarksObtained) > 0 ? round((($totalMarksObtained + ($showCC ? $CCGrandTotal : 0) + $totalSummativeMarksObtained) / ($totalMaxMarksObtained + ($showCC ? $CCGrandMaxTotal : 0) + $totalSummativeMaxMarksObtained)) * 100, 2) : 0;

                                                    // Determine total grade based on total percentage
                                                    $totalGrade = '';
                                                    for ($i = 0; $i < count($gradingSystem[0]); $i++) {
                                                        if ($totalPercentageFinal >= $gradingSystem[1][$i] && $totalPercentageFinal <= $gradingSystem[2][$i]) {
                                                            $totalGrade = $gradingSystem[0][$i];
                                                            break;
                                                        }
                                                    }

                                                    // Determine total rank based on total grade
                                                    $totalRankFinal = isset($rankMappings[$totalGrade]) ? $rankMappings[$totalGrade] : '';

                                                    // Display the total rank
                                                    echo "<td colspan='2' class='font-weight-bold'>$totalRankFinal</td>";
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
                                                    <th class="text-center text-wrap" style="vertical-align: middle;" rowspan="2"
                                                        colspan="2">GRADING SYSTEM</th>
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
                                    // Check if there are any optional subjects at all
                                    $optionalSubjectsAll = fetchSubjects($dbh, $class, 1, 0, $examSession);
                                    if (!empty($optionalSubjectsAll)) {
                                        // Check if any optional subject has a grading system
                                        if (hasOptionalSubjectWithGrading($dbh, $className, $examSession)) {
                                            ?>
                                            <!-- Optional Subjects in Grades-->
                                            <div class="d-flex flex-column mt-3">
                                                <table class="table ">
                                                    <thead>
                                                        <tr class="text-center">
                                                            <th rowspan="3" colspan="2" class="text-wrap font-weight-bold"
                                                                style="vertical-align: middle;">OPTIONAL SUBJECTS</th>
                                                            <th colspan="12" class="text-wrap font-weight-bold">FORMATIVE / SUMMATIVE ASSESSMENT
                                                            </th>
                                                        </tr>
                                                        <tr class="text-center">
                                                            <th colspan="8" class="font-weight-bold">GRADE</th>
                                                            <th colspan="2" class="text-wrap font-weight-bold">Summative Assessment</th>
                                                            <th colspan="2" class="text-warp font-weight-bold">TOTAL (FA+SA)</th>
                                                        </tr>
                                                        <tr class="text-center">
                                                            <!-- FA Exam Names for Optional Subjects -->
                                                            <?php
                                                            foreach ($examNames as $examName) {
                                                                echo "<th scope='col'>" . $examName['ExamName'] . "</th>";
                                                            }
                                                            ?>
                                                            <th colspan="2">GRADE</th>
                                                            <th colspan="2">GRADE</th>
                                                            <th colspan="2">GRADE</th>
                                                        </tr>
                                                        <?php
                                                        //Array to store Grades for each exam
                                                        $examGradeMarksArray = array_fill(0, count($examNames), '');
                                                        // Array to store summative marks for each exam
                                                        $summativeGradeArray = array_fill(count($examNames), count($summativeExamNames), '');

                                                        foreach ($optionalSubjectsAll as $subject) {
                                                            $allSubjectsJson = fetchSubjectsJson($dbh, $studentDetails['ClassID'], $studentDetails['StudentID'], $examSession);
                                                            $examGradeMarksArray = array_fill(0, count($examNames), '');
                                                            $summativeGradeArray = array_fill(count($examNames), count($summativeExamNames), '');

                                                            foreach ($allSubjectsJson as $subjectsJson) {
                                                                $subjectsData = json_decode($subjectsJson, true);
                                                                foreach ($subjectsData as $subjectData) {
                                                                    if ($subjectData['SubjectID'] == $subject['ID']) {
                                                                        // Find the index of the exam ID in the $examNames, $summativeExamNames arrays
                                                                        $examGradeIndex = array_search($subjectData['ExamName'], array_column($examNames, 'ID'));
                                                                        $summativeGradeIndex = array_search($subjectData['ExamName'], array_column($summativeExamNames, 'ID'));

                                                                        if ($examGradeIndex !== false) {
                                                                            $examGradeMarksArray[$examGradeIndex] = $subjectData['isAbsent'] ? '<span class="absent-mark">a</span>' : $subjectData['SubMarksObtained'];
                                                                        }
                                                                        $subMarksObtained = $subjectData['isAbsent'] ? 'a' : $subjectData['SubMarksObtained'];

                                                                        // Add Summative Grade if the exam type is Summative
                                                                        foreach ($summativeExamNames as $summativeExam) {
                                                                            if ($subjectData['ExamName'] == $summativeExam['ID']) {
                                                                                $summativeGradeIndex = array_search($subjectData['ExamName'], array_column($summativeExamNames, 'ID'));
                                                                                $summativeGradeArray[$summativeGradeIndex] = $subjectData['isAbsent'] ? 'a' : $subjectData['SubMarksObtained'];
                                                                                break;
                                                                            }
                                                                        }
                                                                    }
                                                                }
                                                            }

                                                            echo "<tr>
                                                                    <td colspan='2'>{$subject['SubjectName']}</td>";
                                                            // Formative Grade
                                                            foreach ($examGradeMarksArray as $examMarks) {
                                                                echo "<td class='text-center'>$examMarks</td>";
                                                            }
                                                            echo "<td colspan='2'></td>";
                                                            //  Summative Grade
                                                            foreach ($summativeGradeArray as $coCurricularMarks) {
                                                                echo "<td colspan='2' class='text-center'>$coCurricularMarks</td>";
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
                                        } else {
                                            ?>
                                            <!-- Optional Subjects in Marks-->
                                            <div class="d-flex flex-column mt-3">
                                                <table class="table ">
                                                    <thead>
                                                        <tr class="text-center">
                                                            <th rowspan="3" colspan="2" class="text-wrap font-weight-bold"
                                                                style="vertical-align: middle;">OPTIONAL SUBJECTS</th>
                                                            <th colspan="<?php echo $showCC ? 14 : 12; ?>" class="font-weight-bold">FORMATIVE /
                                                                <?php echo $showCC ? "CO-CURRICULAR / " : ""; ?>SUMMATIVE ASSESSMENT</th>
                                                        </tr>
                                                        <tr class="text-center">
                                                            <th colspan="8" class="font-weight-bold">Formative Assessment<br><br> Max. Marks:
                                                                <?php echo $tMaxMarks['formativeOptional']; ?>
                                                            </th>
                                                            <?php if ($showCC) { ?>
                                                                <th colspan="2" class="text-wrap font-weight-bold">Co-curricular Activities</th>
                                                            <?php } ?>
                                                            <th colspan="2" class="text-wrap font-weight-bold">Summative Assessment </th>
                                                            <th colspan="2" class="text-wrap font-weight-bold">TOTAL (FA+CA+SA)</th>
                                                        </tr>
                                                        <tr class="text-center">
                                                            <!-- FA Exam Names for Optional Subjects -->
                                                            <?php
                                                            foreach ($examNames as $examName) {
                                                                echo "<th scope='col'>" . $examName['ExamName'] . "</th>";
                                                            }
                                                            ?>
                                                            <th colspan="2" class="font-weight-bold">
                                                                TOTAL(<?php echo $tMaxMarks['formativeOptional']; ?>)</th>
                                                            <?php if ($showCC) { ?>
                                                                <th colspan="2" class="text-wrap">Max Marks: <?php echo $tMaxMarks['curricular']; ?>
                                                                </th>
                                                            <?php } ?>
                                                            <th colspan="2" class="text-wrap">Max Marks:
                                                                <?php echo $tMaxMarks['summativeOptional']; ?>
                                                            </th>
                                                            <th colspan="2" class="text-wrap font-weight-bold">Max Marks:
                                                                <?php echo $tMaxMarks['formativeOptional'] + ($showCC ? $tMaxMarks['curricular'] : 0) + $tMaxMarks['summativeOptional']; ?>
                                                            </th>
                                                        </tr>
                                                        <?php
                                                        foreach ($optionalSubjectsAll as $subject) {
                                                            $allSubjectsJsonOptional = fetchSubjectsJson($dbh, $studentDetails['ClassID'], $studentDetails['StudentID'], $examSession);
                                                            $examMarksArrayOptional = array_fill(0, count($examNames), '');
                                                            $summativeMarksArrayOptional = array_fill(0, count($summativeExamNames), '');
                                                            $CCtotalMarksValue = '';

                                                            foreach ($allSubjectsJsonOptional as $subjectsJson) {
                                                                $subjectsData = json_decode($subjectsJson, true) ?: [];
                                                                foreach ($subjectsData as $subjectData) {
                                                                    if ($subjectData['SubjectID'] == $subject['ID']) {
                                                                        $examIndexOptional = array_search($subjectData['ExamName'], array_column($examNames, 'ID'));
                                                                        $summativeExamIndexOptional = array_search($subjectData['ExamName'], array_column($summativeExamNames, 'ID'));
                                                                        $coCurricularIndex = array_search($subjectData['ExamName'], array_column($coCurricularExamNames, 'ID'));

                                                                        if ($examIndexOptional !== false) {
                                                                            $examMarksArrayOptional[$examIndexOptional] = isset($subjectData['isAbsent']) && $subjectData['isAbsent'] ? '<span class="absent-mark">a</span>' : $subjectData['SubMarksObtained'];
                                                                        }
                                                                        if ($summativeExamIndexOptional !== false) {
                                                                            $summativeMarksArrayOptional[$summativeExamIndexOptional] = isset($subjectData['isAbsent']) && $subjectData['isAbsent'] ? '<span class="absent-mark">a</span>' : $subjectData['SubMarksObtained'];
                                                                        }
                                                                        if ($coCurricularIndex !== false) {
                                                                            // This part was looking for per-subject CC marks, but the report uses global CC score correctly stored in CCGrandTotal
                                                                        }
                                                                    }
                                                                }
                                                            }
                                                            ?>
                                                            <tr class="text-center">
                                                                <td colspan='2' class='text-left'>
                                                                    <?php echo htmlentities($subject['SubjectName']); ?></td>
                                                                <?php
                                                                foreach ($examMarksArrayOptional as $examMarks) {
                                                                    echo "<td>$examMarks</td>";
                                                                }
                                                                ?>
                                                                <td colspan='2' class='font-weight-bold'>
                                                                    <?php echo array_sum(array_filter($examMarksArrayOptional, 'is_numeric')); ?>
                                                                </td>
                                                                <?php if ($showCC) { ?>
                                                                    <td colspan='2'><?php echo $CCGrandTotal; ?></td>
                                                                <?php } ?>
                                                                <?php
                                                                foreach ($summativeMarksArrayOptional as $examMarks) {
                                                                    echo "<td colspan='2'>$examMarks</td>";
                                                                }
                                                                ?>
                                                                <td colspan='2' class='font-weight-bold'>
                                                                    <?php echo (array_sum(array_filter($examMarksArrayOptional, 'is_numeric')) + ($showCC ? (float) $CCGrandTotal : 0) + array_sum(array_filter($summativeMarksArrayOptional, 'is_numeric'))); ?>
                                                                </td>
                                                            </tr>
                                                            <?php
                                                        }
                                                        ?>
                                                        </tbody>
                                                </table>
                                            </div>
                                            <?php
                                        }
                                    }
                                    ?>

                                    <?php
                                    $ccSubjectsFinalRaw = fetchSubjects($dbh, $class, 0, 1, $examSession);
                                    $ccSubjectsFinal = [];
                                    foreach ($ccSubjectsFinalRaw as $subject) {
                                        $subMax = getCoCurricularSubMaxMarks($dbh, $subject['ID'], $studentDetails['ClassID'], $examSession);
                                        if ($subMax > 0) {
                                            $ccSubjectsFinal[] = $subject;
                                        }
                                    }
                                    $fetchCCSubjectsJsonSqlFinal = "SELECT SubjectsJSON FROM tblcocurricularreports WHERE ClassName = :className AND ExamSession = :examSession AND StudentName = :studentID";
                                    $fetchCCSubjectsJsonQueryFinal = $dbh->prepare($fetchCCSubjectsJsonSqlFinal);
                                    $fetchCCSubjectsJsonQueryFinal->bindParam(':className', $className, PDO::PARAM_STR);
                                    $fetchCCSubjectsJsonQueryFinal->bindParam(':examSession', $examSession, PDO::PARAM_STR);
                                    $fetchCCSubjectsJsonQueryFinal->bindParam(':studentID', $studentID, PDO::PARAM_STR);
                                    $fetchCCSubjectsJsonQueryFinal->execute();
                                    $ccSubjectsJsonFinal = $fetchCCSubjectsJsonQueryFinal->fetch(PDO::FETCH_COLUMN);
                                    $ccSubjectsDataFinal = !empty($ccSubjectsJsonFinal) ? json_decode($ccSubjectsJsonFinal, true) : [];

                                    $hasCoCurricularMarksFinal = false;
                                    if (!empty($ccSubjectsFinal)) {
                                        foreach ($ccSubjectsFinal as $subject) {
                                            foreach ((is_array($ccSubjectsDataFinal) ? $ccSubjectsDataFinal : []) as $data) {
                                                if ($data['SubjectID'] == $subject['ID']) {
                                                    if (isset($data['CoCurricularMarksObtained']) && $data['CoCurricularMarksObtained'] !== '' && $data['CoCurricularMarksObtained'] !== null) {
                                                        $hasCoCurricularMarksFinal = true;
                                                        break 2;
                                                    }
                                                }
                                            }
                                        }
                                    }

                                    if ($hasCoCurricularMarksFinal) {
                                        ?>
                                        <div class="d-flex flex-column mt-3">
                                            <table class="table ">
                                                <thead>
                                                    <tr class="text-center">
                                                        <?php
                                                        $totalColspanCC = count($ccSubjectsFinal) * 2 + 2;
                                                        echo "<th colspan='{$totalColspanCC}' class='font-weight-bold'>Marks Obtained in Co-curricular Component During the Academic Session</th>";
                                                        ?>
                                                    </tr>
                                                    <tr class="text-center">
                                                        <?php
                                                        foreach ($ccSubjectsFinal as $subject) {
                                                            $subMaxMarks = getCoCurricularSubMaxMarks($dbh, $subject['ID'], $studentDetails['StudentClass'], $examSession);
                                                            echo "<th class='text-wrap font-weight-bold' colspan='2'>{$subject['SubjectName']}<br>({$subMaxMarks})</th>";
                                                        }
                                                        ?>
                                                        <th colspan='2' class='font-weight-bold'>Marks
                                                            Obtained<br>(<?php echo $tMaxMarks['curricular']; ?>)</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <tr class="text-center">
                                                        <?php
                                                        foreach ($ccSubjectsFinal as $subject) {
                                                            $subMarksObtained = '';
                                                            foreach ($ccSubjectsDataFinal as $subjectData) {
                                                                if ($subjectData['SubjectID'] == $subject['ID']) {
                                                                    $subMarksObtained = isset($subjectData['isAbsent']) && $subjectData['isAbsent'] ? '<span class="absent-mark">a</span>' : $subjectData['CoCurricularMarksObtained'];
                                                                    break;
                                                                }
                                                            }
                                                            echo "<td colspan='2'>" . $subMarksObtained . "</td>";
                                                        }
                                                        $totalCCMarksFinal = 0;
                                                        foreach ($ccSubjectsDataFinal as $subjectData) {
                                                            if (!isset($subjectData['isAbsent']) || !$subjectData['isAbsent']) {
                                                                $totalCCMarksFinal += (float) ($subjectData['CoCurricularMarksObtained'] ?? 0);
                                                            }
                                                        }
                                                        echo "<td colspan='2' class='font-weight-bold'>" . ($totalCCMarksFinal != 0 ? $totalCCMarksFinal : '') . "</td>";
                                                        ?>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    <?php } ?>
                                    <footer class="d-flex flex-column  mt-3">
                                        <div class="d-flex mt-5 w-100 align-items-center">
                                            <label class="text-nowrap font-weight-bold" style="font-size: 20px">Remarks: </label>
                                            <p class="dark-line ml-2 pt-4 pl-3 w-100" style="box-sizing: border-box;"></p>
                                        </div>
                                        <div class="d-flex justify-content-between mt-5">
                                            <div><label class="font-weight-bold">Date:</label><span
                                                    class="dark-line ml-2 signature-line"></span></div>
                                            <div><label class="font-weight-bold">Signature of Tr. Incharge:</label><span
                                                    class="dark-line ml-2 signature-line"></span></div>
                                        </div>
                                        <div class="d-flex justify-content-between mt-5">
                                            <div><label class="font-weight-bold">Promoted to Class:</label><span
                                                    class="dark-line ml-2 signature-line"></span></div>
                                            <div><label class="font-weight-bold">Supervisor/Principal:</label><span
                                                    class="dark-line ml-2 signature-line"></span></div>
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
        } else {
            echo "<script>alert('Student not found.'); window.location.href='view-result.php';</script>";
        }
    } else {
        echo "<script>alert('Invalid Request. Please select a student.'); window.location.href='view-result.php';</script>";
    }
}
?>