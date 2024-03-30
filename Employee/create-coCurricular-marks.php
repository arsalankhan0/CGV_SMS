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
    $eid = $_SESSION['sturecmsEMPid'];
    $sql = "SELECT * FROM tblemployees WHERE ID=:eid AND IsDeleted = 0";
    $query = $dbh->prepare($sql);
    $query->bindParam(':eid', $eid, PDO::PARAM_STR);
    $query->execute();
    $employeeData = $query->fetch(PDO::FETCH_ASSOC);
    
    // Check if the role is not "Teaching" or if there are no assigned subjects
    if ($employeeData['EmpType'] != "Teaching") 
    {
        echo "<h1>You have no permission to access this page!</h1>";
        exit;
    }
    // Fetch assigned subjects for the employee
    $assignedSubjects = explode(',', $employeeData['AssignedSubjects']);
    
    // Check if any assigned subject is co-curricular
    $coCurricularAssigned = false;
    
    if (!empty($assignedSubjects)) 
    {
        // Fetch subjects from tblsubjects
        $sqlSubjects = "SELECT * FROM tblsubjects WHERE ID IN (" . implode(",", $assignedSubjects) . ") AND IsCurricularSubject = 1 AND IsDeleted = 0";
        $querySubjects = $dbh->prepare($sqlSubjects);
        $querySubjects->execute();
        $subjects = $querySubjects->fetchAll(PDO::FETCH_ASSOC);
    
        // Check if any co-curricular subject is assigned
        foreach ($subjects as $subject) 
        {
            if ($subject['IsCurricularSubject'] == 1) 
            {
                $coCurricularAssigned = true;
                break;
            }
        }
    }
    // Check if no co-curricular subject is assigned
    if (!$coCurricularAssigned) 
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

        if (isset($_SESSION['classIDs']) && isset($_SESSION['sessionYear']) && isset($_SESSION['SectionIDs'])) 
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

                // Fetch assigned subjects
                $subjectSql = "SELECT * FROM tblsubjects WHERE ID IN (" . implode(",", $assignedSubjectsIDs) . ") AND IsCurricularSubject = 1 AND IsDeleted = 0";
                $subjectQuery = $dbh->prepare($subjectSql);
                $subjectQuery->execute();
                $subjects = $subjectQuery->fetchAll(PDO::FETCH_ASSOC);

                // Function to check if the max marks are assigned by the admin
                function getMaxMarks($classID, $sessionID, $subjectID, $type) 
                {
                    global $dbh;
                    
                    $sql = "SELECT * FROM tblmaxmarks 
                            WHERE ClassID = :classID 
                            AND SessionID = :sessionID 
                            AND SubjectID = :subjectID";
                    
                    $query = $dbh->prepare($sql);
                    $query->bindParam(':classID', $classID, PDO::PARAM_INT);
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
                try 
                {
                    $dbh->beginTransaction();
            
                    foreach ($students as $student) 
                    {
                        $totalPass = true;
                        // studentSubjectsData array
                        $studentSubjectsData = array();
                        
                        foreach ($subjects as $subject) 
                        {   
                            $coCurricularMaxMarks = isset($_POST['CoCurricularMaxMarks'][$student['ID']][$subject['ID']]) ? $_POST['CoCurricularMaxMarks'][$student['ID']][$subject['ID']] : 0;
                            $coCurricularMarksObtained = isset($_POST['CoCurricularMarksObtained'][$student['ID']][$subject['ID']]) ? $_POST['CoCurricularMarksObtained'][$student['ID']][$subject['ID']] : 0;

                            // An array for subject data
                            $subjectData = array(
                                'SubjectID' => $subject['ID'],
                                'CoCurricularMaxMarks' => $coCurricularMaxMarks,
                                'CoCurricularMarksObtained' => $coCurricularMarksObtained,
                            );
                            $studentSubjectsData[] = $subjectData;

                            $subjectsJSON = json_encode($studentSubjectsData);
                            
                            // Check for existing entry in tblreports
                            $checkExistingSql = "SELECT ExamSession, ClassName, StudentName FROM tblcocurricularreports 
                                                    WHERE ExamSession = :sessionID 
                                                    AND ClassName = :classID 
                                                    AND StudentName = :studentID 
                                                    AND SubjectsJSON = :subjectData
                                                    AND IsDeleted = 0";
                            $checkExistingQuery = $dbh->prepare($checkExistingSql);
                            $checkExistingQuery->bindParam(':sessionID', $sessionID, PDO::PARAM_INT);
                            $checkExistingQuery->bindParam(':classID', $student['StudentClass'], PDO::PARAM_INT);
                            $checkExistingQuery->bindParam(':studentID', $student['ID'], PDO::PARAM_INT);
                            $checkExistingQuery->bindParam(':subjectData', $subjectsJSON, PDO::PARAM_INT);
                            $checkExistingQuery->execute();
                            $existingReportDetails = $checkExistingQuery->fetch(PDO::FETCH_ASSOC);

                            // Check for existing entry in tblmaxmarks
                            $checkExistingMaxSql = "SELECT SessionID, ClassID, SubjectID, PassingPercentage FROM tblmaxmarks 
                                                    WHERE SessionID = :sessionID 
                                                    AND ClassID = :classID 
                                                    AND SubjectID = :subjectID
                                                    AND IsDeleted = 0";
                            $checkExistingMaxQuery = $dbh->prepare($checkExistingMaxSql);
                            $checkExistingMaxQuery->bindParam(':sessionID', $sessionID, PDO::PARAM_INT);
                            $checkExistingMaxQuery->bindParam(':classID', $student['StudentClass'], PDO::PARAM_INT);
                            $checkExistingMaxQuery->bindParam(':subjectID', $subject['ID'], PDO::PARAM_INT);
                            $checkExistingMaxQuery->execute();
                            $existingMaxReportDetails = $checkExistingMaxQuery->fetch(PDO::FETCH_ASSOC);

                            // Fetching the pass percentage
                            $passPercentID = 1;
                            $defaultPassMarksSql = "SELECT DefaultPassMarks FROM tblpasspercent WHERE ID = :passPercentID";
                            $defaultPassMarksQuery = $dbh->prepare($defaultPassMarksSql);
                            $defaultPassMarksQuery->bindParam(':passPercentID', $passPercentID, PDO::PARAM_INT);
                            $defaultPassMarksQuery->execute();
                            $defaultPassPercent = $defaultPassMarksQuery->fetch(PDO::FETCH_COLUMN);

                            // Calculate passing marks for co-curricular subject
                            $CoCurricularPassMarks = ($existingMaxReportDetails && isset($existingMaxReportDetails['PassingPercentage']))
                            ? $existingMaxReportDetails['PassingPercentage'] / 100 * $CoCurricularMaxMarks
                            : $defaultPassPercent / 100 * $CoCurricularMaxMarks;

                             // Check if marks obtained are less than passing marks for each subject
                            if ($CoCurricularMarksObtained < $FAPassMarks) 
                            {
                                $totalPass = false;
                            }

                            // Insert Max Marks in tblmaxmarks
                            if(!$existingMaxReportDetails)
                            {
                                    $insertAdminSql = "INSERT INTO tblmaxmarks (SessionID, ClassID, SubjectID, CoCurricularMaxMarks, PassingPercentage)
                                                VALUES (:sessionID, :classID, :subjectID, :CoCurricularMaxMarks, :passingPercentage)";
                
                                    $insertAdminMaxQuery = $dbh->prepare($insertAdminSql);
                                    $insertAdminMaxQuery->bindParam(':sessionID', $sessionID, PDO::PARAM_INT);
                                    $insertAdminMaxQuery->bindParam(':classID', $student['StudentClass'], PDO::PARAM_INT);
                                    $insertAdminMaxQuery->bindParam(':subjectID', $subject['ID'], PDO::PARAM_INT);
                                    $insertAdminMaxQuery->bindParam(':CoCurricularMaxMarks', $CoCurricularMaxMarks, PDO::PARAM_INT);
                                    $insertAdminMaxQuery->bindParam(':passingPercentage', $defaultPassPercent, PDO::PARAM_INT);            
                                    $insertAdminMaxQuery->execute();
                            }
                            // Update Max Marks in tblmaxmarks
                            else
                            {
                                // If an existing entry is found in tblmaxmarks, update the data
                                $updateAdminSql = "UPDATE tblmaxmarks SET 
                                                CoCurricularMaxMarks = :CoCurricularMaxMarks, 
                                                WHERE SessionID = :sessionID 
                                                AND ClassID = :classID 
                                                AND SubjectID = :subjectID 
                                                AND IsDeleted = 0";
                                $updateAdminMaxQuery = $dbh->prepare($updateAdminSql);
                                $updateAdminMaxQuery->bindParam(':CoCurricularMaxMarks', $FAMaxMarks, PDO::PARAM_INT);
                                $updateAdminMaxQuery->bindParam(':sessionID', $sessionID, PDO::PARAM_INT);
                                $updateAdminMaxQuery->bindParam(':classID', $student['StudentClass'], PDO::PARAM_INT);
                                $updateAdminMaxQuery->bindParam(':subjectID', $subject['ID'], PDO::PARAM_INT);
                                $updateAdminMaxQuery->execute();
                            }
                        }
                        // If the student is not in tblreports, insert the student
                        if (!$existingReportDetails) 
                        {
                            $insertSql = "INSERT INTO tblcocurricularreports (ExamSession, ClassName, StudentName, SubjectsJSON, IsPassed)
                                            VALUES (:sessionID, :classID, :studentID, :subjectsJSON, :isPassed)";
                            $insertQuery = $dbh->prepare($insertSql);
                            
                            
                            $subjectsJSON = json_encode($studentSubjectsData);
                            
                            $insertQuery->bindParam(':sessionID', $sessionID, PDO::PARAM_INT);
                            $insertQuery->bindParam(':classID', $student['StudentClass'], PDO::PARAM_INT);
                            $insertQuery->bindParam(':studentID', $student['ID'], PDO::PARAM_INT);
                            $insertQuery->bindParam(':subjectsJSON', $subjectsJSON, PDO::PARAM_STR);
                            $insertQuery->bindParam(':isPassed', $totalPass, PDO::PARAM_INT);

                            $insertQuery->execute();
                        } 
                        else 
                        {
                            $subjectsJSON = json_encode($studentSubjectsData);

                            // If an existing entry is found, update the data
                            $updateReportSql = "UPDATE tblcocurricularreports SET 
                                                SubjectsJSON = :subjectsJSON, 
                                                IsPassed = :isPassed
                                                WHERE ExamSession = :sessionID 
                                                AND ClassName = :classID 
                                                AND StudentName = :studentID 
                                                AND IsDeleted = 0";
                        
                            $updateReportQuery = $dbh->prepare($updateReportSql);
                            $updateReportQuery->bindParam(':subjectsJSON', $subjectsJSON, PDO::PARAM_STR);
                            $updateReportQuery->bindParam(':isPassed', $totalPass, PDO::PARAM_INT);
                            $updateReportQuery->bindParam(':sessionID', $sessionID, PDO::PARAM_INT);
                            $updateReportQuery->bindParam(':classID', $student['StudentClass'], PDO::PARAM_INT);
                            $updateReportQuery->bindParam(':studentID', $student['ID'], PDO::PARAM_INT);
                            $updateReportQuery->execute();
                        }
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
            header("Location:add-coCurricular-score.php");
        }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Tibetan Public School || Create Student Report</title>
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
                                    <h4 class="card-title" style="text-align: center;">Create Co-Curricular Report of Academic Session <?php echo '('.$sessionName.')'; ?></h4>
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

                                <form class="forms-sample" method="post">
                                    
                                        <?php 
                                            if ($publishedResult) 
                                            {
                                                echo '<p class="text-center text-danger">Score cannot be assigned or updated as the result is published.</p>';
                                            }
                                        ?>
                                    <div class="table-responsive">
                                        <table class="table text-center table-bordered">
                                                <tr>
                                                    <th rowspan="3">Student Name</th>
                                                    <?php foreach ($subjects as $subject) 
                                                    { 
                                                        // Check if subject is co-curricular
                                                        $isCurricularSubject = $subject['IsCurricularSubject'];

                                                        if ($isCurricularSubject === 1) 
                                                        {
                                                        ?>
                                                            <th colspan="2" rowspan="2" class="text-center font-weight-bold" style="font-size: 20px; letter-spacing: 2px;"><?php echo htmlentities($subject['SubjectName']); ?></th>
                                                        <?php
                                                        }
                                                    }
                                                        ?>
                                                </tr>
                                                <tr>
                                                    <?php foreach ($subjects as $subject) 
                                                    { 
                                                         // Check if subject is co-curricular
                                                        $isCurricularSubject = $subject['IsCurricularSubject'];

                                                        if ($isCurricularSubject == 1) 
                                                        {
                                                        ?>
                                                            <th colspan="2"></th>
                                                        <?php
                                                        } 
                                                    }
                                                        ?>
                                                </tr>
                                                <tr>
                                                    <?php foreach ($subjects as $subject) 
                                                    { 
                                                        // Check if subject is co-curricular
                                                        $isCurricularSubject = $subject['IsCurricularSubject'];
                                                        // Check if subject is optional
                                                        $isOptional = $subject['IsOptional'];

                                                        if ($isCurricularSubject == 1) 
                                                        {
                                                        ?>
                                                            <td>Max Marks</td>
                                                            <td>Marks Obtained</td>
                                                        <?php
                                                        }
                                                    }?>
                                                </tr>
                                            <tbody>
                                            <?php foreach ($students as $student) 
                                            { ?>
                                                <tr>
                                                    <td class="font-weight-bold"><?php echo htmlentities($student['StudentName']); ?></td>
                                                    <?php 
                                                    foreach ($subjects as $subject) 
                                                    { 
                                                        $subjectID = $subject['ID'];
                                                            // Check if marks exist in tblreports for the student, exam, and subject type
                                                            $checkMarksSql = "SELECT * FROM tblcocurricularreports 
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

                                                            // Find the subject in the SubjectsJSON array and extract the marks
                                                            foreach ($subjectsJSON as $subjectData) 
                                                            {
                                                                if ($subjectData['SubjectID'] == $subjectID) 
                                                                {
                                                                    $coCurricularMarksObtained = $subjectData['CoCurricularMarksObtained'] ?? '';
                                                                    break;
                                                                }
                                                            }
                                                            
                                                            // Storing max marks that admin gives, in variables.
                                                            $adminCoCurricularMaxMarks = getMaxMarks($student['StudentClass'], $_SESSION['examName'], $sessionID, $subject['ID'], 'CoCurricular');
                                                            
                                                            // Check if the teacher has assigned max marks, if not, fallback to admin's max marks
                                                            $coCurricularMaxMarksToShow = ($adminCoCurricularMaxMarks === null) ? getTeacherAssignedMaxMarks($student['StudentClass'], $_SESSION['examName'], $sessionID, $subject['ID'], 'CoCurricular') : $adminCoCurricularMaxMarks;
                                                            
                                                            // Disable the input fields if the condition matches. 
                                                            $disabledCoCurricular = ($publishedResult) ? 'disabled' : ''; 

                                                            // Check if subject is co-curricular
                                                            $isCurricularSubject = $subject['IsCurricularSubject'];
                                                            
                                                            if ($isCurricularSubject == 1) 
                                                            {
                                                            ?>
                                                                <td>
                                                                    <input type='number' min="0" class='border border-secondary' name="CoCurricularMaxMarks[<?php echo $student['ID']; ?>][<?php echo $subject['ID']; ?>]" 
                                                                        <?php echo $disabledCoCurricular; ?>
                                                                        value="<?php echo ($coCurricularMaxMarksToShow !== null) ? $coCurricularMaxMarksToShow : ''; ?>">
                                                                </td>
                                                                <td>
                                                                    <input type='number' class='border border-secondary' name="CoCurricularMarksObtained[<?php echo $student['ID']; ?>][<?php echo $subject['ID']; ?>]" 
                                                                        <?php echo $disabledCoCurricular; ?>    
                                                                        value="<?php echo $coCurricularMarksObtained; ?>">
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
                                        <button class="btn btn-primary mr-2"  
                                            <?php echo ($publishedResult) ? 'disabled' : 'type="button" data-toggle="modal" data-target="#confirmationModal" '; ?>
                                        >
                                            Assign Marks
                                        </button>
                                    </div>
                                    <!-- Confirmation Modal (Update) -->
                                    <div class="modal fade" id="confirmationModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" data-backdrop="static" data-keyboard="false">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                            <div class="modal-header">
                                                <h4 class="modal-title" id="myModalLabel">Confirmation</h4>
                                                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                            </div>
                                            <div class="modal-body">
                                                Are you sure you want to assign given marks to the students?
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                                                <button type="submit" class="btn btn-primary" name="submit">Assign</button>
                                            </div>
                                            </div>
                                        </div>
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
<script src="./js/manageAlert.js"></script>
<!-- End custom js for this page -->
</body>
</html>
<?php 

} 
?>