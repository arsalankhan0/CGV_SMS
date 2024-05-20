<?php
session_start();
// error_reporting(0);
include('includes/dbconnection.php');

if (empty($_SESSION['sturecmsEMPid'])) 
{
    header('location:logout.php');
} 
else 
{
    $eid = $_SESSION['sturecmsEMPid'];
    $sql = "SELECT * FROM tblemployees WHERE ID=:eid";
    $query = $dbh->prepare($sql);
    $query->bindParam(':eid', $eid, PDO::PARAM_STR);
    $query->execute();
    $IsAccessible = $query->fetch(PDO::FETCH_ASSOC);

    // Check if the role is "Teaching"
    if ($IsAccessible['EmpType'] != "Teaching") 
    {
        echo "<h1>You have no permission to access this page!</h1>";
        exit;
    }

    $msg = "";
    $successAlert = false;
    $dangerAlert = false;

        // Get the active session ID
        $getSessionSql = "SELECT session_id FROM tblsessions WHERE is_active = 1 AND IsDeleted = 0";
        $sessionQuery = $dbh->prepare($getSessionSql);
        $sessionQuery->execute();
        $sessionID = $sessionQuery->fetchColumn();

        function checkExistingMaxMarks($dbh, $sessionID, $classID, $examID, $subjectID) 
        {
            $checkExistingMaxSql = "SELECT ID, SessionID, ClassID, ExamID, SubjectID, PassingPercentage FROM tblmaxmarks 
                                    WHERE SessionID = :sessionID 
                                    AND ClassID = :classID 
                                    AND ExamID = :examID 
                                    AND SubjectID = :subjectID
                                    AND IsDeleted = 0";
        
            $checkExistingMaxQuery = $dbh->prepare($checkExistingMaxSql);
            $checkExistingMaxQuery->bindParam(':sessionID', $sessionID, PDO::PARAM_INT);
            $checkExistingMaxQuery->bindParam(':classID', $classID, PDO::PARAM_INT);
            $checkExistingMaxQuery->bindParam(':examID', $examID, PDO::PARAM_INT);
            $checkExistingMaxQuery->bindParam(':subjectID', $subjectID, PDO::PARAM_INT);
            $checkExistingMaxQuery->execute();
            
            return $checkExistingMaxQuery->fetch(PDO::FETCH_ASSOC);
        }

        // Check if exam is published
        $checkPublishedSql = "SELECT * FROM tblexamination WHERE ID = :examId 
                                AND IsPublished = 1 
                                AND session_id = :session_id 
                                AND IsDeleted = 0";

        $checkPublishedQuery = $dbh->prepare($checkPublishedSql);
        $checkPublishedQuery->bindParam(':examId', $_SESSION['examName'], PDO::PARAM_STR);
        $checkPublishedQuery->bindParam(':session_id', $sessionID, PDO::PARAM_STR);
        $checkPublishedQuery->execute();
        $publish = $checkPublishedQuery->fetch(PDO::FETCH_ASSOC);
    
        // Check if Result is published
        $checkResultPublishedSql = "SELECT IsPublished, session_id FROM tblexamination 
                                    WHERE ID = :examId 
                                    AND IsResultPublished = 1
                                    AND session_id = :session_id
                                    AND IsDeleted = 0";
        $checkResultPublishedQuery = $dbh->prepare($checkResultPublishedSql);
        $checkResultPublishedQuery->bindParam(':examId', $_SESSION['examName'], PDO::PARAM_STR);
        $checkResultPublishedQuery->bindParam(':session_id', $sessionID, PDO::PARAM_STR);
        $checkResultPublishedQuery->execute();
        $publishedResult = $checkResultPublishedQuery->fetch(PDO::FETCH_ASSOC);
        

        if (isset($_SESSION['classIDs']) && isset($_SESSION['examName'])) 
        {
            // Fetch students
            $classIDs = unserialize($_SESSION['classIDs']);
            $sectionIDs = unserialize($_SESSION['SectionIDs']);

            $sql = "SELECT * FROM tblstudent WHERE StudentClass IN ($classIDs) AND StudentSection IN ($sectionIDs) AND IsDeleted = 0";
            $query = $dbh->prepare($sql);
            $query->execute();
            $students = $query->fetchAll(PDO::FETCH_ASSOC);

            // Fetch assigned subjects for the teacher
            $teacherID = $_SESSION['sturecmsEMPid'];
            $assignedSubjectsSql = "SELECT AssignedSubjects FROM tblemployees WHERE ID = :teacherID AND IsDeleted = 0";
            $assignedSubjectsQuery = $dbh->prepare($assignedSubjectsSql);
            $assignedSubjectsQuery->bindParam(':teacherID', $teacherID, PDO::PARAM_INT);
            $assignedSubjectsQuery->execute();
            $assignedSubjects = $assignedSubjectsQuery->fetchColumn();

            if (!empty($assignedSubjects)) 
            {
                $assignedSubjectsIDs = explode(',', $assignedSubjects);

                // Function to check if the max marks are assigned by the admin
                function getMaxMarks($classID, $examID, $sessionID, $subjectID, $type) 
                {
                    global $dbh;
                    
                    $sql = "SELECT * FROM tblmaxmarks 
                            WHERE ClassID = :classID 
                            AND ExamID = :examID 
                            AND SessionID = :sessionID 
                            AND SubjectID = :subjectID";
                    
                    $query = $dbh->prepare($sql);
                    $query->bindParam(':classID', $classID, PDO::PARAM_INT);
                    $query->bindParam(':examID', $examID, PDO::PARAM_INT);
                    $query->bindParam(':sessionID', $sessionID, PDO::PARAM_INT);
                    $query->bindParam(':subjectID', $subjectID, PDO::PARAM_INT);
                    
                    $query->execute();
                    $result = $query->fetch(PDO::FETCH_ASSOC);
                    
                    if ($result && $result[$type . 'MaxMarks'] > 0) 
                    {
                        return $result[$type . 'MaxMarks'];
                    }
                    
                    return null;
                }
                // Function to check if the max marks are assigned by the teacher
                function getTeacherAssignedMaxMarks($classID, $sessionID, $subjectID, $type)
                {
                    global $dbh;

                    $sql = "SELECT SubjectsJSON FROM tblreports 
                            WHERE ClassName = :classID 
                            AND ExamSession = :sessionID 
                            AND IsDeleted = 0";
                    $query = $dbh->prepare($sql);
                    $query->bindParam(':classID', $classID, PDO::PARAM_INT);
                    $query->bindParam(':sessionID', $sessionID, PDO::PARAM_INT);
                    $query->execute();
                    $result = $query->fetchAll(PDO::FETCH_ASSOC);
                    
                    if ($result) 
                    {
                        foreach ($result as $row) 
                        {
                            $subjectsJSON = json_decode($row['SubjectsJSON'], true);

                            // Check if the subject is present in the JSON data
                            foreach ($subjectsJSON as $subject) 
                            {
                                if ($subject['SubjectID'] == $subjectID && isset($subject[$type . 'MaxMarks']) && $subject[$type . 'MaxMarks'] > 0) 
                                {
                                    return $subject[$type . 'MaxMarks'];
                                }
                            }
                        }
                    }
                    return null;
                }
            } 
            else 
            {
                echo '<h2>No Subjects assigned to the teacher!</h2>';
            }
            if (isset($_POST['submit'])) 
            {
                // Fetch Assigned Subjects for the selected class
                $subjectSql = "SELECT * FROM tblsubjects WHERE ID IN (" . implode(",", $assignedSubjectsIDs) . ") AND ClassName LIKE :classID AND IsDeleted = 0";
                $subjectQuery = $dbh->prepare($subjectSql);
                $classID = '%' . unserialize($_SESSION['classIDs']) . '%';
                $subjectQuery->bindParam(':classID', $classID, PDO::PARAM_STR);
                $subjectQuery->execute();
                $subjects = $subjectQuery->fetchAll(PDO::FETCH_ASSOC);
                try 
                {
                    $dbh->beginTransaction();
                    $examID = $_SESSION['examName'];

                        $totalPass = true;
                        $studentID = $_POST['studentID'];
                        $classID = unserialize($_SESSION['classIDs']);
                        // studentSubjectsData array
                        $studentSubjectsData = array();

                        foreach ($subjects as $subject) 
                        {
                            $subjectID = $subject['ID'];

                            // Form input names
                            $SubMaxMarks = isset($_POST['SubMaxMarks'][$studentID][$subjectID]) ? $_POST['SubMaxMarks'][$studentID][$subjectID] : 0;
                            $SubMarksObtained = isset($_POST['SubMarksObtained'][$studentID][$subjectID]) ? $_POST['SubMarksObtained'][$studentID][$subjectID] : 0;
                            
                            // Check if the GradingSystem is 1 for the current subject
                            $gradingSystemSql = "SELECT GradingSystem FROM tblmaxmarks WHERE SubjectID = :subjectID AND GradingSystem = 1";
                            $gradingSystemQuery = $dbh->prepare($gradingSystemSql);
                            $gradingSystemQuery->bindParam(':subjectID', $subjectID, PDO::PARAM_INT);
                            $gradingSystemQuery->execute();
                            $gradingSystem = $gradingSystemQuery->fetch(PDO::FETCH_ASSOC);

                            $existingMaxReportDetails = checkExistingMaxMarks($dbh, $sessionID, $classID, $examID, $subject['ID']);

                            // Fetching the pass percentage
                            $passPercentID = 1;
                            $defaultPassMarksSql = "SELECT DefaultPassMarks FROM tblpasspercent WHERE ID = :passPercentID";
                            $defaultPassMarksQuery = $dbh->prepare($defaultPassMarksSql);
                            $defaultPassMarksQuery->bindParam(':passPercentID', $passPercentID, PDO::PARAM_INT);
                            $defaultPassMarksQuery->execute();
                            $defaultPassPercent = $defaultPassMarksQuery->fetch(PDO::FETCH_COLUMN);

                            // Calculate passing marks for each subject
                            $SubPassMarks = ($existingMaxReportDetails && isset($existingMaxReportDetails['PassingPercentage']))
                            ? $existingMaxReportDetails['PassingPercentage'] / 100 * (float)$SubMaxMarks
                            : $defaultPassPercent / 100 * (float)$SubMaxMarks;

                            // Check if the subject is optional
                            $isOptional = $subject['IsOptional'];

                            if ((empty($SubMaxMarks) && empty($SubMarksObtained)) || ($SubMaxMarks == 0 && $SubMarksObtained == 0) || empty($SubMarksObtained) || empty($SubMaxMarks)) 
                            {
                                $isPassed = 1;
                            } 
                            else 
                            {
                                // Calculate IsPassed based on whether the subject is optional
                                $isPassed = $isOptional ? 1 : ($SubMarksObtained >= $SubPassMarks ? 1 : 0);
                            }
                            

                            // An array for subject data
                            $subjectData = array(
                                'ExamName' => $examID,
                                'SubjectID' => $subject['ID'],
                                'SubMaxMarks' => $SubMaxMarks,
                                'SubMarksObtained' => $SubMarksObtained,
                                'IsOptional' => $subject['IsOptional'],
                                'IsCoCurricular' => $subject['IsCurricularSubject'],
                                'IsPassed' => $isPassed, 
                                'GradingSystem' =>  isset($gradingSystem['GradingSystem']) ? $gradingSystem['GradingSystem'] : 0,
                            );

                            $studentSubjectsData[] = $subjectData;

                            $subjectsJSON = json_encode($studentSubjectsData);
                            
                            // Check for existing entry in tblreports
                            $checkExistingSql = "SELECT ExamSession, ClassName, StudentName, SubjectsJSON, IsPassed FROM tblreports 
                                                    WHERE ExamSession = :sessionID 
                                                    AND ClassName = :classID 
                                                    AND StudentName = :studentID 
                                                    AND SubjectsJSON = :subjectData
                                                    AND IsDeleted = 0";
                            $checkExistingQuery = $dbh->prepare($checkExistingSql);
                            $checkExistingQuery->bindParam(':sessionID', $sessionID, PDO::PARAM_INT);
                            $checkExistingQuery->bindParam(':classID', $classID, PDO::PARAM_INT);
                            $checkExistingQuery->bindParam(':studentID', $studentID, PDO::PARAM_INT);
                            $checkExistingQuery->bindParam(':subjectData', $subjectsJSON, PDO::PARAM_INT);
                            $checkExistingQuery->execute();
                            $existingReportDetails = $checkExistingQuery->fetch(PDO::FETCH_ASSOC);

                            // Check for existing entry in tblmaxmarks
                            $existingMaxReportDetails = checkExistingMaxMarks($dbh, $sessionID, $classID, $examID, $subject['ID']);

                            // Insert Max Marks in tblmaxmarks
                            if(!$existingMaxReportDetails)
                            {
                                    $insertAdminSql = "INSERT INTO tblmaxmarks (SessionID, ClassID, ExamID, SubjectID, SubMaxMarks, PassingPercentage)
                                                VALUES (:sessionID, :classID, :examID, :subjectID, :SubMaxMarks, :passingPercentage)";
                
                                    $insertAdminMaxQuery = $dbh->prepare($insertAdminSql);
                                    $insertAdminMaxQuery->bindParam(':sessionID', $sessionID, PDO::PARAM_INT);
                                    $insertAdminMaxQuery->bindParam(':classID', $classID, PDO::PARAM_INT);
                                    $insertAdminMaxQuery->bindParam(':examID', $examID, PDO::PARAM_INT);
                                    $insertAdminMaxQuery->bindParam(':subjectID', $subject['ID'], PDO::PARAM_INT);
                                    $insertAdminMaxQuery->bindParam(':SubMaxMarks', $SubMaxMarks, PDO::PARAM_STR);           
                                    $insertAdminMaxQuery->bindParam(':passingPercentage', $defaultPassPercent, PDO::PARAM_INT);            
                                    $insertAdminMaxQuery->execute();
                            }
                            // Update Max Marks in tblmaxmarks
                            else
                            {
                                // If an existing entry is found in tblmaxmarks, update the data
                                $updateAdminSql = "UPDATE tblmaxmarks SET 
                                                SubMaxMarks = :SubMaxMarks
                                                WHERE SessionID = :sessionID 
                                                AND ClassID = :classID 
                                                AND ExamID = :examID 
                                                AND SubjectID = :subjectID 
                                                AND IsDeleted = 0";
                            
                                $updateAdminMaxQuery = $dbh->prepare($updateAdminSql);
                                $updateAdminMaxQuery->bindParam(':SubMaxMarks', $SubMaxMarks, PDO::PARAM_STR);
                                $updateAdminMaxQuery->bindParam(':sessionID', $sessionID, PDO::PARAM_INT);
                                $updateAdminMaxQuery->bindParam(':classID', $classID, PDO::PARAM_INT);
                                $updateAdminMaxQuery->bindParam(':examID', $examID, PDO::PARAM_INT);
                                $updateAdminMaxQuery->bindParam(':subjectID', $subject['ID'], PDO::PARAM_INT);
                                $updateAdminMaxQuery->execute();
                            }
                        }

                        foreach ($studentSubjectsData as $subjectData) {
                            // Check if the subject is not optional and not co-curricular
                            if ($subjectData['IsOptional'] == 0 && $subjectData['IsCoCurricular'] == 0) 
                            {
                                if ($subjectData['IsPassed'] == 0) 
                                {
                                    $totalPass = false;
                                    break; 
                                }
                            }
                        }
                        if (!$existingReportDetails) 
                        {
                            $insertSql = "INSERT INTO tblreports (ExamSession, ClassName, StudentName, SubjectsJSON, IsPassed)
                                            VALUES (:sessionID, :classID, :studentID, :subjectsJSON, :isPassed)";
                            $insertQuery = $dbh->prepare($insertSql);
                            
                            
                            $subjectsJSON = json_encode($studentSubjectsData);
                            
                            $insertQuery->bindParam(':sessionID', $sessionID, PDO::PARAM_INT);
                            $insertQuery->bindParam(':classID', $classID, PDO::PARAM_INT);
                            $insertQuery->bindParam(':studentID', $studentID, PDO::PARAM_INT);
                            $insertQuery->bindParam(':subjectsJSON', $subjectsJSON, PDO::PARAM_STR);
                            $insertQuery->bindParam(':isPassed', $totalPass, PDO::PARAM_INT);

                            $insertQuery->execute();
                        } 
                        else 
                        {
                            $existingSubjectsJSON = json_decode($existingReportDetails['SubjectsJSON'], true);                        
                            $subjectFound = false;
                            $isStudentPassed = 1; 
                        
                            foreach ($studentSubjectsData as $newSubjectData) 
                            {
                                $subjectFound = false;
                                foreach ($existingSubjectsJSON as &$existingSubjectData) 
                                {
                                    // Calculate passing marks for each subject
                                    $SubPassMarks = ($existingMaxReportDetails && isset($existingMaxReportDetails['PassingPercentage']))
                                    ? $existingMaxReportDetails['PassingPercentage'] / 100 * (float)$SubMaxMarks
                                    : $defaultPassPercent / 100 * (float)$SubMaxMarks;

                                    // Check if the subject is optional
                                    $isOptional = $subject['IsOptional'];
                                    // Calculate IsPassed based on whether the subject is optional
                                    $isStudentPassed = $isOptional ? 1 : ($SubMarksObtained >= $SubPassMarks ? 1 : 0);

                                    // Update $isStudentPassed flag if any subject is not passed
                                    if ($existingSubjectData['IsPassed'] == 0) 
                                    {
                                        $isStudentPassed = 0;
                                    }

                                    if ($existingSubjectData['SubjectID'] == $newSubjectData['SubjectID'] 
                                        && $existingSubjectData['ExamName'] == $newSubjectData['ExamName']
                                        ) 
                                    {
                                        // If the subject already exists, update its marks
                                        $existingSubjectData['SubMaxMarks'] = $newSubjectData['SubMaxMarks'];
                                        $existingSubjectData['SubMarksObtained'] = $newSubjectData['SubMarksObtained'];
                                        $existingSubjectData['IsPassed'] = $newSubjectData['IsPassed'];
                                        $subjectFound = true;
                                        break;
                                    }
                                }
                                // If the subject doesn't exist, add it to the existing subjects JSON
                                if (!$subjectFound) 
                                {
                                    $existingSubjectsJSON[] = $newSubjectData;
                                }
                            }
                    
                            $updatedSubjectsJSON = json_encode($existingSubjectsJSON);
                        
                            $updateReportSql = "UPDATE tblreports SET 
                                                SubjectsJSON = :subjectsJSON,
                                                IsPassed = :isPassed
                                                WHERE ExamSession = :sessionID 
                                                AND ClassName = :classID 
                                                AND StudentName = :studentID 
                                                AND IsDeleted = 0";
                        
                            $updateReportQuery = $dbh->prepare($updateReportSql);
                            $updateReportQuery->bindParam(':subjectsJSON', $updatedSubjectsJSON, PDO::PARAM_STR);
                            $updateReportQuery->bindParam(':isPassed', $isStudentPassed, PDO::PARAM_INT);
                            $updateReportQuery->bindParam(':sessionID', $sessionID, PDO::PARAM_INT);
                            $updateReportQuery->bindParam(':classID', $classID, PDO::PARAM_INT);
                            $updateReportQuery->bindParam(':studentID', $studentID, PDO::PARAM_INT);
                            $updateReportQuery->execute();
                        }
                    $dbh->commit();
                    $msg = "Marks assigned successfully.";
                    $successAlert = true;
                } 
                catch (PDOException $e) 
                {
                    $dbh->rollBack();
                    $msg = "Ops! An error occurred.";
                    $dangerAlert = true;
                    echo "<script>console.error('Error:---> " . $e->getMessage() . "');</script>";
                }
            }
        } 
        else 
        {
            header("Location:create-marks.php");
        }

        // Function to check whether the current subject and class have grading system or not
        function isGradingSystem1($dbh, $subjectID, $classID) 
        {
            $gradingSystemSql = "SELECT GradingSystem FROM tblmaxmarks WHERE SubjectID = :subjectID AND ClassID = :classID AND GradingSystem = 1";
            $gradingSystemQuery = $dbh->prepare($gradingSystemSql);
            $gradingSystemQuery->bindParam(':subjectID', $subjectID, PDO::PARAM_INT);
            $gradingSystemQuery->bindParam(':classID', $classID, PDO::PARAM_INT);
            $gradingSystemQuery->execute();
            $gradingSystemResult = $gradingSystemQuery->fetch(PDO::FETCH_ASSOC);
            
            // If the GradingSystem is 1, hide the Max Marks column and show the Marks Obtained column as input type text
            return $gradingSystemResult !== false;
        }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>TPS || Create Student Report</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- plugins:css -->
    <link rel="stylesheet" href="vendors/simple-line-icons/css/simple-line-icons.css">
    <link rel="stylesheet" href="vendors/flag-icon-css/css/flag-icon.min.css">
    <link rel="stylesheet" href="vendors/css/vendor.bundle.base.css">
    <!-- endinject -->
    <!-- Plugin css for this page -->
    <link rel="stylesheet" href="vendors/select2/select2.min.css">
    <link rel="stylesheet" href="vendors/select2-bootstrap-theme/select2-bootstrap.min.css">
    <!-- End plugin css for this page -->
    <!-- inject:css -->
    <!-- endinject -->
    <!-- Layout styles -->
    <link rel="stylesheet" href="css/style.css"/>
    <link rel="stylesheet" href="../css/remove-spinner.css"/>
    <style>
        .table-container {
            max-width: 100%;
            overflow-x: auto;
        }
        .table {
            /* width: auto; */
            border-collapse: collapse;
        }
        .table th, .table td {
            padding: 8px;
            border: 1px solid #ddd;
            text-align: center;
        }
        .table th {
            background-color: #f4f4f4;
            font-weight: bold !important;
        }

        .marks-input {
            padding: 5px;
            box-sizing: border-box;
        }

        @media (max-width: 576px) {
            .table th, .table td {
                padding: 4px;
            }
            .table th {
                font-size: 0.9rem;
            }
            .table td {
                font-size: 0.85rem;
            }
            .marks-input {
                width: 60px;
                padding: 3px;
            }
        }
        .student-info {
            background-color: #f9f9f9;
            padding: 10px;
            margin-top: 20px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        .student-info .roll-no,
        .student-info .student-name {
            font-size: 1.2rem; 
            font-weight: bold; 
            margin-bottom: 5px;
        }

        .student-info .roll-no label,
        .student-info .student-name label {
            color: #007bff;
            margin-right: 5px;
        }


    </style>

    
</head>
<body>
<div class="container-scroller">
    <!-- partial:partials/_navbar.html -->
    <?php include_once('includes/header.php'); ?>
    <!-- partial -->
    <div class="container-fluid page-body-wrapper">
        <!-- partial:partials/_sidebar.html -->
        <?php include_once('includes/sidebar.php'); ?>
        <!-- partial -->
        <div class="main-panel">
            <div class="content-wrapper">
                <div class="page-header">
                    <h3 class="page-title"> Create Student Report </h3>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                            <li class="breadcrumb-item active" aria-current="page"> Create Student Report </li>
                        </ol>
                    </nav>
                </div>
                <div class="row">
                    <div class="col-12 grid-margin stretch-card">
                        <div class="card">
                            <?php
                                if($publish)
                                {
                            ?>
                            <div class="card-body">
                                <h4 class="card-title" style="text-align: center;">Create Student Report For <strong><?php
                                    $sql = "SELECT * FROM tblexamination WHERE ID = " . $_SESSION['examName'] . " AND IsDeleted = 0";
                                    $query = $dbh->prepare($sql);
                                    $query->execute();
                                    $examinations = $query->fetchAll(PDO::FETCH_ASSOC);
                                    

                                    foreach ($examinations as $exam) 
                                    {
                                        echo htmlentities($exam['ExamName']);
                                    }
                                    ?></strong></h4>

                                    <!-- Dismissible Alert messages -->
                                    <?php 
                                    if ($successAlert) 
                                    {
                                        ?>
                                        <!-- Success -->
                                        <div id="success-alert" class="alert alert-success alert-dismissible" role="alert">
                                            <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                            <?php echo $msg; ?>
                                        </div>
                                    <?php 
                                    }
                                    if($dangerAlert)
                                    { 
                                    ?>
                                        <!-- Danger -->
                                        <div id="danger-alert" class="alert alert-danger alert-dismissible" role="alert">
                                            <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                            <?php echo $msg; ?>
                                        </div>
                                    <?php
                                    }
                                    ?>

                                
                                    
                                        <?php 
                                            if ($publishedResult) 
                                            {
                                                echo '<p class="text-center text-danger">Score cannot be assigned or updated as the result is published.</p>';
                                            }
                                        ?>
                                            <?php
                                            foreach ($students as $student) 
                                            { 
                                                ?>
                                            <form class="forms-sample" method="post">
                                                <div class="student-info">
                                                    <div class="roll-no">
                                                        <label>Roll No:</label>
                                                        <span><?php echo htmlentities($student['RollNo']); ?></span>
                                                    </div>
                                                    <div class="student-name">
                                                        <label>Name:</label>
                                                        <span><?php echo htmlentities($student['StudentName']); ?></span>
                                                    </div>
                                                </div>
                                                <div class="table-container">
                                                    <table class="table text-center table-bordered">
                                                        <thead>
                                                            <tr>
                                                                <th>Subjects</th>
                                                                <?php 
                                                                    $subjectID = $subject['ID'];

                                                                    $isGradingSystem1 = isGradingSystem1($dbh, $subjectID, $classIDs);

                                                                    if (!$isGradingSystem1)
                                                                    {
                                                                        ?>
                                                                        <th>Max Marks</th>
                                                                        <?php
                                                                    }
                                                                        ?>
                                                                        <th><?php echo ($isGradingSystem1) ? 'Grade' : 'Marks Obtained'; ?></th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <tr>
                                                                <?php 
                                                                // Fetch Assigned Subjects for the selected class
                                                                $subjectSql = "SELECT * FROM tblsubjects WHERE ID IN (" . implode(",", $assignedSubjectsIDs) . ") AND ClassName LIKE :classID AND IsDeleted = 0";
                                                                $subjectQuery = $dbh->prepare($subjectSql);
                                                                $classID = '%' . unserialize($_SESSION['classIDs']) . '%';
                                                                $subjectQuery->bindParam(':classID', $classID, PDO::PARAM_STR);
                                                                $subjectQuery->execute();
                                                                $subjects = $subjectQuery->fetchAll(PDO::FETCH_ASSOC);
                                                                    

                                                                foreach ($subjects as $subject) 
                                                                { 
                                                                    $subjectID = $subject['ID'];
                                                                    $isGradingSystem1 = isGradingSystem1($dbh, $subjectID, $classIDs);         
                                                                    
                                                                    // Check if marks exist in tblreports for the student, exam, and subject type
                                                                    $checkMarksSql = "SELECT SubjectsJSON FROM tblreports 
                                                                                        WHERE ExamSession = :sessionID 
                                                                                        AND ClassName = :classID 
                                                                                        AND StudentName = :studentID 
                                                                                        AND IsDeleted = 0";
                                                                    $checkMarksQuery = $dbh->prepare($checkMarksSql);
                                                                    $checkMarksQuery->bindParam(':sessionID', $sessionID, PDO::PARAM_INT);
                                                                    $checkMarksQuery->bindParam(':classID', $student['StudentClass'], PDO::PARAM_INT);
                                                                    $checkMarksQuery->bindParam(':studentID', $student['ID'], PDO::PARAM_INT);
                                                                    $checkMarksQuery->execute();
                                                                    $marksData = $checkMarksQuery->fetch(PDO::FETCH_ASSOC);
                                                                    
                                                                    // Display marks obtained if they exist; otherwise, display an empty field
                                                                    $subjectsJSON = json_decode($marksData['SubjectsJSON'], true);

                                                                    $SubMarksObtained = '';
                                                                    // Find the subject in the SubjectsJSON array and extract the marks
                                                                    foreach ($subjectsJSON as $subjectData) 
                                                                    {
                                                                        if ($subjectData['SubjectID'] == $subjectID && $subjectData['ExamName'] == $_SESSION['examName']) 
                                                                        {
                                                                            $SubMarksObtained = $subjectData['SubMarksObtained'] ?? '';
                                                                            break;
                                                                        }
                                                                    }
                                                                    
                                                                    // Storing max marks that admin gives, in variables.
                                                                    $adminSubMaxMarks = getMaxMarks($student['StudentClass'], $_SESSION['examName'], $sessionID, $subject['ID'], 'Sub');
                                                                    
                                                                    // Check if the teacher has assigned max marks, if not, fallback to admin's max marks
                                                                    $SubMaxMarksToShow = ($adminSubMaxMarks === null) ? getTeacherAssignedMaxMarks($student['StudentClass'], $sessionID, $subject['ID'], 'Sub') : $adminSubMaxMarks;
                                                                    
                                                                    // Disable the input fields if the condition matches. 
                                                                    $disabledSub = ($publishedResult) ? 'disabled' : '';  
                                                                    
                                                                    $subjectID = $subject['ID'];

                                                                    $isGradingSystem1 = isGradingSystem1($dbh, $subjectID, $classIDs);
                                                                            
                                                                            
                                                                    ?>
                                                                    <tr>
                                                                        <td class="text-left"><?php echo htmlentities($subject['SubjectName']);?></td>
                                                                            <?php
                                                                            if(!$isGradingSystem1)
                                                                            {
                                                                            ?>
                                                                                <td>
                                                                                    <input type='number' min="0" step="any" class='marks-input border border-secondary max-marks-input' name="SubMaxMarks[<?php echo $student['ID']; ?>][<?php echo $subject['ID']; ?>]" 
                                                                                        <?php echo $disabledSub; ?>
                                                                                        value="<?php echo ($SubMaxMarksToShow !== null) ? $SubMaxMarksToShow : ''; ?>"
                                                                                        data-subject-id="<?php echo $subject['ID']; ?>"
                                                                                        >
                                                                                </td>
                                                                            <?php
                                                                            }
                                                                            ?>
                                                                                <td colspan=<?php echo ($isGradingSystem1) ? '2' : '1'; ?>>
                                                                                    <input type=<?php echo ($isGradingSystem1) ? 'text' : 'number step="any"'?> class='marks-input border border-secondary marks-obtained-input' name="SubMarksObtained[<?php echo $student['ID']; ?>][<?php echo $subject['ID']; ?>]" 
                                                                                        <?php echo $disabledSub; ?>
                                                                                        value="<?php echo $SubMarksObtained; ?>"
                                                                                        <?php echo ($isGradingSystem1) ? "oninput='this.value = this.value.toUpperCase()' placeholder='Grade'" : "";

                                                                                        ?>>
                                                                                        <div class="error-message text-wrap"></div>
                                                                                </td>
                                                                            <?php 
                                                                            ?>
                                                                    
                                                                    </tr>
                                                                    <?php 
                                                                }
                                                                ?>
                                                            <tr>
                                                                <td colspan="3" class="text-right py-2">
                                                                    <button class="btn btn-primary assign-marks-btn"
                                                                        <?php echo ($publishedResult) ? 'disabled' : 'type="button" data-toggle="modal" data-target="#confirmationModal_'.$student['ID'].'" '; ?>
                                                                    >
                                                                        Assign Marks
                                                                    </button>
                                                                </td>
                                                            </tr>
                                                        </tbody>
                                                    </table>
                                                </div>

                                                <!-- Confirmation Modal (Update) -->
                                                <div class="modal fade" id="confirmationModal_<?php echo $student['ID']; ?>" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" data-backdrop="static" data-keyboard="false">
                                                    <div class="modal-dialog">
                                                        <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h4 class="modal-title" id="myModalLabel">Confirmation</h4>
                                                            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            Are you sure you want to assign given marks to <span class="font-weight-bold"><?php echo htmlentities($student['StudentName']);?></span>?
                                                        </div>
                                                        <div class="modal-footer">
                                                            <input type="hidden" name="studentID" value="<?php echo $student['ID']; ?>">
                                                            <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                                                            <button type="submit" class="btn btn-primary" name="submit">Assign</button>
                                                        </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </form>
                                            <?php
                                            }
                                            ?>
                            </div>
                            <?php
                                }
                                else
                                {
                                    echo "<h3 class='text-center'>Exam Not Published Yet!</h3>";
                                }
                            ?>
                        </div>
                    </div>
                </div>
            </div>
            <!-- content-wrapper ends -->
            <!-- partial:partials/_footer.html -->
            <?php include_once('includes/footer.php'); ?>
            <!-- partial -->
        </div>
        <!-- main-panel ends -->
    </div>
    <!-- page-body-wrapper ends -->
</div>
<!-- container-scroller -->
<!-- plugins:js -->
<script src="vendors/js/vendor.bundle.base.js"></script>
<!-- endinject -->
<!-- Plugin js for this page -->
<script src="vendors/select2/select2.min.js"></script>
<script src="vendors/typeahead.js/typeahead.bundle.min.js"></script>
<!-- End plugin js for this page -->
<!-- inject:js -->
<script src="js/off-canvas.js"></script>
<script src="js/misc.js"></script>
<!-- endinject -->
<!-- Custom js for this page -->
<script src="js/typeahead.js"></script>
<script src="js/select2.js"></script>
<script src="./js/manageAlert.js"></script>
<!-- Include this script in your HTML -->
<script src="./js/marksAssignValidation.js"></script>

<!-- End custom js for this page -->
</body>
</html>
<?php 

} 
?>
