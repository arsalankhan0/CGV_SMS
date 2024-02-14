<?php
session_start();
// error_reporting(0);
include('includes/dbconnection.php');

if (strlen($_SESSION['sturecmsEMPid'] == 0)) 
{
    header('location:logout.php');
} 
else 
{

        // Get the active session ID
        $getSessionSql = "SELECT session_id FROM tblsessions WHERE is_active = 1 AND IsDeleted = 0";
        $sessionQuery = $dbh->prepare($getSessionSql);
        $sessionQuery->execute();
        $sessionID = $sessionQuery->fetchColumn();

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
        $checkResultPublishedSql = "SELECT IsPublished, session_id FROM tblexamination WHERE ID = :examId 
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
            $sql = "SELECT * FROM tblstudent WHERE StudentClass IN ($classIDs) AND IsDeleted = 0";
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

            // Get the active session ID
            $getSessionSql = "SELECT session_id FROM tblsessions WHERE is_active = 1 AND IsDeleted = 0";
            $sessionQuery = $dbh->prepare($getSessionSql);
            $sessionQuery->execute();
            $sessionID = $sessionQuery->fetchColumn();

            if (!empty($assignedSubjects)) 
            {
                $assignedSubjectsIDs = explode(',', $assignedSubjects);

                // Fetch subjects for the selected class
                $subjectSql = "SELECT * FROM tblsubjects WHERE ID IN (" . implode(",", $assignedSubjectsIDs) . ") AND SessionID = $sessionID AND IsDeleted = 0";
                $subjectQuery = $dbh->prepare($subjectSql);
                $subjectQuery->execute();
                $subjects = $subjectQuery->fetchAll(PDO::FETCH_ASSOC);

                // Function to check if a specific type is present in the comma-separated list
                function isSubjectType($type, $subject) 
                {
                    $types = explode(',', $subject['SubjectType']);
                    return in_array($type, $types);
                }
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
                function getTeacherAssignedMaxMarks($classID, $examID, $sessionID, $subjectID, $type) 
                {
                    global $dbh;
                    
                    $sql = "SELECT * FROM tblreports 
                            WHERE ClassName = :classID 
                            AND ExamSession = :sessionID 
                            AND ExamName = :examID 
                            AND Subjects = :subjectID
                            AND IsDeleted = 0";
                    
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
            } 
            else 
            {
                echo '<script>alert("No subjects assigned to the teacher.")</script>';
            }
            if (isset($_POST['submit'])) 
            {
                try 
                {
                    $dbh->beginTransaction();
                    
                    $examID = $_SESSION['examName'];
            
                    foreach ($students as $student) 
                    {
                        foreach ($subjects as $subject) 
                        {
                            // Form input names
                            $theoryMaxMarks = isset($_POST['theoryMaxMarks'][$student['ID']][$subject['ID']]) ? $_POST['theoryMaxMarks'][$student['ID']][$subject['ID']] : '';
                            $theoryMarksObtained = isset($_POST['theoryMarksObtained'][$student['ID']][$subject['ID']]) ? $_POST['theoryMarksObtained'][$student['ID']][$subject['ID']] : '';
        
                            $practicalMaxMarks = isset($_POST['practicalMaxMarks'][$student['ID']][$subject['ID']]) ? $_POST['practicalMaxMarks'][$student['ID']][$subject['ID']] : '';
                            $practicalMarksObtained = isset($_POST['practicalMarksObtained'][$student['ID']][$subject['ID']]) ? $_POST['practicalMarksObtained'][$student['ID']][$subject['ID']] : '';
        
                            $vivaMaxMarks = isset($_POST['vivaMaxMarks'][$student['ID']][$subject['ID']]) ? $_POST['vivaMaxMarks'][$student['ID']][$subject['ID']] : '';
                            $vivaMarksObtained = isset($_POST['vivaMarksObtained'][$student['ID']][$subject['ID']]) ? $_POST['vivaMarksObtained'][$student['ID']][$subject['ID']] : '';

                            // Check for existing entry in tblreports
                            $checkExistingSql = "SELECT ExamSession, ClassName, ExamName, StudentName, Subjects FROM tblreports 
                                                    WHERE ExamSession = :sessionID 
                                                    AND ClassName = :classID 
                                                    AND ExamName = :examID 
                                                    AND StudentName = :studentID 
                                                    AND Subjects = :subjectID
                                                    AND IsDeleted = 0";
                            $checkExistingQuery = $dbh->prepare($checkExistingSql);
                            $checkExistingQuery->bindParam(':sessionID', $sessionID, PDO::PARAM_INT);
                            $checkExistingQuery->bindParam(':classID', $student['StudentClass'], PDO::PARAM_INT);
                            $checkExistingQuery->bindParam(':examID', $examID, PDO::PARAM_INT);
                            $checkExistingQuery->bindParam(':studentID', $student['ID'], PDO::PARAM_INT);
                            $checkExistingQuery->bindParam(':subjectID', $subject['ID'], PDO::PARAM_INT);
                            $checkExistingQuery->execute();
                            $existingReportDetails = $checkExistingQuery->fetch(PDO::FETCH_ASSOC);

                            // Check for existing entry in tblmaxmarks
                            $checkExistingMaxSql = "SELECT SessionID, ClassID, ExamID, SubjectID FROM tblmaxmarks 
                                                    WHERE SessionID = :sessionID 
                                                    AND ClassID = :classID 
                                                    AND ExamID = :examID 
                                                    AND SubjectID = :subjectID
                                                    AND IsDeleted = 0";
                            $checkExistingMaxQuery = $dbh->prepare($checkExistingMaxSql);
                            $checkExistingMaxQuery->bindParam(':sessionID', $sessionID, PDO::PARAM_INT);
                            $checkExistingMaxQuery->bindParam(':classID', $student['StudentClass'], PDO::PARAM_INT);
                            $checkExistingMaxQuery->bindParam(':examID', $examID, PDO::PARAM_INT);
                            $checkExistingMaxQuery->bindParam(':subjectID', $subject['ID'], PDO::PARAM_INT);
                            $checkExistingMaxQuery->execute();
                            $existingMaxReportDetails = $checkExistingMaxQuery->fetch(PDO::FETCH_ASSOC);

                            if(!$existingMaxReportDetails)
                            {
                                $insertAdminSql = "INSERT INTO tblmaxmarks (SessionID, ClassID, ExamID, SubjectID, TheoryMaxMarks, PracticalMaxMarks, VivaMaxMarks)
                                            VALUES (:sessionID, :classID, :examID, :subjectID, :theoryMaxMarks, :practicalMaxMarks, :vivaMaxMarks)";
            
                                $insertAdminMaxQuery = $dbh->prepare($insertAdminSql);
                                $insertAdminMaxQuery->bindParam(':sessionID', $sessionID, PDO::PARAM_INT);
                                $insertAdminMaxQuery->bindParam(':classID', $student['StudentClass'], PDO::PARAM_INT);
                                $insertAdminMaxQuery->bindParam(':examID', $examID, PDO::PARAM_INT);
                                $insertAdminMaxQuery->bindParam(':subjectID', $subject['ID'], PDO::PARAM_INT);
                                $insertAdminMaxQuery->bindParam(':theoryMaxMarks', $theoryMaxMarks, PDO::PARAM_INT);
                                $insertAdminMaxQuery->bindParam(':practicalMaxMarks', $practicalMaxMarks, PDO::PARAM_INT);
                                $insertAdminMaxQuery->bindParam(':vivaMaxMarks', $vivaMaxMarks, PDO::PARAM_INT);            
                                $insertAdminMaxQuery->execute();
                            }
                            else
                            {
                                 // If an existing entry is found in tblmaxmarks, update the data
                                $updateAdminSql = "UPDATE tblmaxmarks SET 
                                                TheoryMaxMarks = :theoryMaxMarks, 
                                                PracticalMaxMarks = :practicalMaxMarks, 
                                                VivaMaxMarks = :vivaMaxMarks
                                                WHERE SessionID = :sessionID 
                                                AND ClassID = :classID 
                                                AND ExamID = :examID 
                                                AND SubjectID = :subjectID 
                                                AND IsDeleted = 0";
                            
                                $updateAdminMaxQuery = $dbh->prepare($updateAdminSql);
                                $updateAdminMaxQuery->bindParam(':theoryMaxMarks', $theoryMaxMarks, PDO::PARAM_INT);
                                $updateAdminMaxQuery->bindParam(':practicalMaxMarks', $practicalMaxMarks, PDO::PARAM_INT);
                                $updateAdminMaxQuery->bindParam(':vivaMaxMarks', $vivaMaxMarks, PDO::PARAM_INT);
                                $updateAdminMaxQuery->bindParam(':sessionID', $sessionID, PDO::PARAM_INT);
                                $updateAdminMaxQuery->bindParam(':classID', $student['StudentClass'], PDO::PARAM_INT);
                                $updateAdminMaxQuery->bindParam(':examID', $examID, PDO::PARAM_INT);
                                $updateAdminMaxQuery->bindParam(':subjectID', $subject['ID'], PDO::PARAM_INT);
                                $updateAdminMaxQuery->execute();
                            }
            
                            // If the student is not in tblreports, insert the student
                            if (!$existingReportDetails) 
                            {
                                $insertSql = "INSERT INTO tblreports (ExamSession, ClassName, ExamName, StudentName, Subjects, TheoryMaxMarks, TheoryMarksObtained, PracticalMaxMarks, PracticalMarksObtained, VivaMaxMarks, VivaMarksObtained)
                                            VALUES (:sessionID, :classID, :examID, :studentID, :subjectID, :theoryMaxMarks, :theoryMarksObtained, :practicalMaxMarks, :practicalMarksObtained, :vivaMaxMarks, :vivaMarksObtained)";
            
                                $insertQuery = $dbh->prepare($insertSql);
                                $insertQuery->bindParam(':sessionID', $sessionID, PDO::PARAM_INT);
                                $insertQuery->bindParam(':classID', $student['StudentClass'], PDO::PARAM_INT);
                                $insertQuery->bindParam(':examID', $examID, PDO::PARAM_INT);
                                $insertQuery->bindParam(':studentID', $student['ID'], PDO::PARAM_INT);
                                $insertQuery->bindParam(':subjectID', $subject['ID'], PDO::PARAM_INT);
                                $insertQuery->bindParam(':theoryMaxMarks', $theoryMaxMarks, PDO::PARAM_INT);
                                $insertQuery->bindParam(':theoryMarksObtained', $theoryMarksObtained, PDO::PARAM_INT);
                                $insertQuery->bindParam(':practicalMaxMarks', $practicalMaxMarks, PDO::PARAM_INT);
                                $insertQuery->bindParam(':practicalMarksObtained', $practicalMarksObtained, PDO::PARAM_INT);
                                $insertQuery->bindParam(':vivaMaxMarks', $vivaMaxMarks, PDO::PARAM_INT);
                                $insertQuery->bindParam(':vivaMarksObtained', $vivaMarksObtained, PDO::PARAM_INT);
            
                                $insertQuery->execute();
                            } 
                            else 
                            {
                                // If an existing entry is found, update the data
                                $updateSql = "UPDATE tblreports SET 
                                    TheoryMaxMarks = :theoryMaxMarks, 
                                    TheoryMarksObtained = :theoryMarksObtained, 
                                    PracticalMaxMarks = :practicalMaxMarks, 
                                    PracticalMarksObtained = :practicalMarksObtained, 
                                    VivaMaxMarks = :vivaMaxMarks, 
                                    VivaMarksObtained = :vivaMarksObtained
                                    WHERE ExamSession = :sessionID 
                                    AND ClassName = :classID 
                                    AND ExamName = :examID 
                                    AND StudentName = :studentID 
                                    AND Subjects = :subjectID 
                                    AND IsDeleted = 0";
                            
                                $updateQuery = $dbh->prepare($updateSql);
                                $updateQuery->bindParam(':theoryMaxMarks', $theoryMaxMarks, PDO::PARAM_INT);
                                $updateQuery->bindParam(':theoryMarksObtained', $theoryMarksObtained, PDO::PARAM_INT);
                                $updateQuery->bindParam(':practicalMaxMarks', $practicalMaxMarks, PDO::PARAM_INT);
                                $updateQuery->bindParam(':practicalMarksObtained', $practicalMarksObtained, PDO::PARAM_INT);
                                $updateQuery->bindParam(':vivaMaxMarks', $vivaMaxMarks, PDO::PARAM_INT);
                                $updateQuery->bindParam(':vivaMarksObtained', $vivaMarksObtained, PDO::PARAM_INT);
                                $updateQuery->bindParam(':sessionID', $sessionID, PDO::PARAM_INT);
                                $updateQuery->bindParam(':classID', $student['StudentClass'], PDO::PARAM_INT);
                                $updateQuery->bindParam(':examID', $examID, PDO::PARAM_INT);
                                $updateQuery->bindParam(':studentID', $student['ID'], PDO::PARAM_INT);
                                $updateQuery->bindParam(':subjectID', $subject['ID'], PDO::PARAM_INT);
                                $updateQuery->execute();

                                
                            }
                        }
                    }
                    $dbh->commit();
                    echo '<script>alert("Marks assigned successfully.")</script>';
                } 
                catch (PDOException $e) 
                {
                    $dbh->rollBack();
                    echo '<script>alert("Ops! An error occurred.'.$e->getMessage().'")</script>';
                }
            }
            
        } 
        else 
        {
            header("Location:create-marks.php");
        }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Student Management System || Create Student Report</title>
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

                                    foreach ($examinations as $exam) {
                                        echo htmlentities($exam['ExamName']);
                                    }
                                    ?></strong></h4>

                                <form class="forms-sample" method="post">
                                    
                                    <div class="table-responsive">
                                        <?php 
                                            if ($publishedResult) 
                                            {
                                                echo '<p class="text-center text-danger">Score cannot be assigned or updated as the result is published.</p>';
                                            }
                                        ?>
                                        <table class="table text-center table-bordered">
                                                <tr>
                                                    <th rowspan="3">Student Name</th>
                                                    <?php foreach ($subjects as $subject) { ?>
                                                        <th colspan="6" class="text-center font-weight-bold" style="font-size: 20px; letter-spacing: 2px;"><?php echo htmlentities($subject['SubjectName']); ?></th>
                                                    <?php } ?>
                                                </tr>
                                                <tr>
                                                    <?php foreach ($subjects as $subject) 
                                                    { ?>
                                                        <th colspan="2">Theory</th>
                                                        <th colspan="2">Practical</th>
                                                        <th colspan="2">Viva</th>
                                                    <?php } ?>
                                                </tr>
                                                <tr>
                                                    <?php foreach ($subjects as $subject) { ?>
                                                        <td>Max Marks</td>
                                                        <td>Marks Obtained</td>
                                                        <td>Max Marks</td>
                                                        <td>Marks Obtained</td>
                                                        <td>Max Marks</td>
                                                        <td>Marks Obtained</td>
                                                    <?php } ?>
                                                </tr>
                                            <tbody>
                                            <?php foreach ($students as $student) 
                                            { ?>
                                                <tr>
                                                    <td class="font-weight-bold"><?php echo htmlentities($student['StudentName']); ?></td>
                                                    <?php foreach ($subjects as $subject) 
                                                    { 
                                                            // Check if marks exist in tblreports for the student, exam, and subject type
                                                            $checkMarksSql = "SELECT * FROM tblreports 
                                                                                WHERE ExamSession = :sessionID 
                                                                                AND ClassName = :classID 
                                                                                AND ExamName = :examID 
                                                                                AND StudentName = :studentID 
                                                                                AND Subjects = :subjectID
                                                                                AND IsDeleted = 0";
                                                            $checkMarksQuery = $dbh->prepare($checkMarksSql);
                                                            $checkMarksQuery->bindParam(':sessionID', $sessionID, PDO::PARAM_INT);
                                                            $checkMarksQuery->bindParam(':classID', $student['StudentClass'], PDO::PARAM_INT);
                                                            $checkMarksQuery->bindParam(':examID', $_SESSION['examName'], PDO::PARAM_INT);
                                                            $checkMarksQuery->bindParam(':studentID', $student['ID'], PDO::PARAM_INT);
                                                            $checkMarksQuery->bindParam(':subjectID', $subject['ID'], PDO::PARAM_INT);
                                                            $checkMarksQuery->execute();
                                                            $marksData = $checkMarksQuery->fetch(PDO::FETCH_ASSOC);

                                                            // Display marks obtained if they exist; otherwise, display an empty field
                                                            $theoryMarksObtained = ($marksData && isset($marksData['TheoryMarksObtained'])) ? $marksData['TheoryMarksObtained'] : '';
                                                            $practicalMarksObtained = ($marksData && isset($marksData['PracticalMarksObtained'])) ? $marksData['PracticalMarksObtained'] : '';
                                                            $vivaMarksObtained = ($marksData && isset($marksData['VivaMarksObtained'])) ? $marksData['VivaMarksObtained'] : '';

                                                            // Storing max marks that admin gives, in variables.
                                                            $adminTheoryMaxMarks = getMaxMarks($student['StudentClass'], $_SESSION['examName'], $sessionID, $subject['ID'], 'Theory');
                                                            $adminPracticalMaxMarks = getMaxMarks($student['StudentClass'], $_SESSION['examName'], $sessionID, $subject['ID'], 'Practical');
                                                            $adminVivaMaxMarks = getMaxMarks($student['StudentClass'], $_SESSION['examName'], $sessionID, $subject['ID'], 'Viva');
                                                            
                                                            // Check if the teacher has assigned max marks, if not, fallback to admin's max marks
                                                            $theoryMaxMarksToShow = (isSubjectType('theory', $subject) && $adminTheoryMaxMarks === null) ? getTeacherAssignedMaxMarks($student['StudentClass'], $_SESSION['examName'], $sessionID, $subject['ID'], 'Theory') : $adminTheoryMaxMarks;
                                                            $practicalMaxMarksToShow = (isSubjectType('practical', $subject) && $practicalMarksObtained === null) ? getTeacherAssignedMaxMarks($student['StudentClass'], $_SESSION['examName'], $sessionID, $subject['ID'], 'Practical') : $adminPracticalMaxMarks;
                                                            $vivaMaxMarksToShow = (isSubjectType('viva', $subject) && $vivaMarksObtained === null) ? getTeacherAssignedMaxMarks($student['StudentClass'], $_SESSION['examName'], $sessionID, $subject['ID'], 'Viva') : $adminVivaMaxMarks;
                                                            
                                                            // Disable the input fields if the condition matches. 
                                                            $disabledTheory = (!isSubjectType('theory', $subject) || $publishedResult) ? 'disabled' : ''; 
                                                            $disabledPractical = (!isSubjectType('practical', $subject) || $publishedResult) ? 'disabled' : ''; 
                                                            $disabledViva = (!isSubjectType('viva', $subject) || $publishedResult) ? 'disabled' : ''; 
                                                        ?>
                                                            <?php if ($theoryMaxMarksToShow !== null || $practicalMaxMarksToShow !== null || $vivaMaxMarksToShow !== null) 
                                                            { 
                                                        ?>
                                                                <td>
                                                                    <input type='number' class='border border-secondary' name="theoryMaxMarks[<?php echo $student['ID']; ?>][<?php echo $subject['ID']; ?>]" 
                                                                        <?php echo $disabledTheory; ?>
                                                                        value="<?php echo $theoryMaxMarksToShow; ?>"
                                                                        >
                                                                </td>
                                                                <td>
                                                                        <input type='number' class='border border-secondary' name="theoryMarksObtained[<?php echo $student['ID']; ?>][<?php echo $subject['ID']; ?>]" 
                                                                            <?php echo $disabledTheory; ?>
                                                                            value="<?php echo $theoryMarksObtained; ?>">
                                                                </td>
                                                                <td>
                                                                    <input type='number' class='border border-secondary' name="practicalMaxMarks[<?php echo $student['ID']; ?>][<?php echo $subject['ID']; ?>]" 
                                                                        <?php echo $disabledPractical; ?>
                                                                        value="<?php echo $practicalMaxMarksToShow; ?>"
                                                                        >
                                                                </td>
                                                                <td>
                                                                        <input type='number' class='border border-secondary' name="practicalMarksObtained[<?php echo $student['ID']; ?>][<?php echo $subject['ID']; ?>]" 
                                                                            <?php echo $disabledPractical; ?>
                                                                            value="<?php echo $practicalMarksObtained; ?>">
                                                                </td>
                                                                <td>
                                                                    <input type='number' class='border border-secondary' name="vivaMaxMarks[<?php echo $student['ID']; ?>][<?php echo $subject['ID']; ?>]" 
                                                                        <?php echo $disabledViva; ?>
                                                                        value="<?php echo $vivaMaxMarksToShow; ?>"
                                                                        >
                                                                </td>
                                                                <td>
                                                                        <input type='number' class='border border-secondary' name="vivaMarksObtained[<?php echo $student['ID']; ?>][<?php echo $subject['ID']; ?>]" 
                                                                            <?php echo $disabledViva; ?>
                                                                            value="<?php echo $vivaMarksObtained; ?>">
                                                                </td>
                                                            <?php 
                                                            } 
                                                            else
                                                            {
                                                            ?>
                                                                <td>
                                                                    <input type='number' class='border border-secondary' name="theoryMaxMarks[<?php echo $student['ID']; ?>][<?php echo $subject['ID']; ?>]" 
                                                                            <?php echo $disabledTheory; ?>
                                                                            >
                                                                </td>
                                                                <td>
                                                                    <input type='number' class='border border-secondary' name="theoryMarksObtained[<?php echo $student['ID']; ?>][<?php echo $subject['ID']; ?>]" 
                                                                        <?php echo $disabledTheory; ?>
                                                                        value="<?php echo $theoryMarksObtained; ?>"
                                                                        >
                                                                </td>
                                                                <td>
                                                                    <input type='number' class='border border-secondary' name="practicalMaxMarks[<?php echo $student['ID']; ?>][<?php echo $subject['ID']; ?>]" 
                                                                        <?php echo $disabledPractical; ?>
                                                                        >
                                                                </td>
                                                                <td>
                                                                    <input type='number' class='border border-secondary' name="practicalMarksObtained[<?php echo $student['ID']; ?>][<?php echo $subject['ID']; ?>]" 
                                                                        <?php echo $disabledPractical; ?>
                                                                        value="<?php echo $practicalMarksObtained; ?>"
                                                                        >
                                                                </td>
                                                                <td>
                                                                    <input type='number' class='border border-secondary' name="vivaMaxMarks[<?php echo $student['ID']; ?>][<?php echo $subject['ID']; ?>]" 
                                                                        <?php echo $disabledViva; ?>
                                                                        >
                                                                </td>
                                                                <td>
                                                                    <input type='number' class='border border-secondary' name="vivaMarksObtained[<?php echo $student['ID']; ?>][<?php echo $subject['ID']; ?>]" 
                                                                        <?php echo $disabledViva ?>
                                                                        value="<?php echo $vivaMarksObtained; ?>"
                                                                        >
                                                                </td>
                                                            <?php
                                                                }
                                                    } ?>
                                                </tr>
                                                <?php 
                                            } 
                                            ?>
                                            </tbody>
                                        </table>
                                    </div>
                                    <div class="pt-3">
                                        <button type="submit" class="btn btn-primary mr-2" name="submit" 
                                            <?php echo ($publishedResult) ? 'disabled' : ''; ?>
                                        >
                                            Assign Marks
                                        </button>
                                    </div>
                                </form>
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
<!-- End custom js for this page -->
</body>
</html>
<?php 

} 
?>
