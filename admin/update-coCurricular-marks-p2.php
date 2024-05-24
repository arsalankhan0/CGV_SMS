<?php
session_start();
error_reporting(0);
include('includes/dbconnection.php');

if (strlen($_SESSION['sturecmsaid'] == 0)) 
{
    header('location:logout.php');
} 
else 
{
    
    function checkExistingMaxMarks($dbh, $sessionID, $classID, $subjectID) 
    {
        $checkExistingMaxSql = "SELECT ID, SessionID, ClassID, SubjectID, PassingPercentage FROM tblmaxcocurricular 
                                WHERE SessionID = :sessionID 
                                AND ClassID = :classID 
                                AND SubjectID = :subjectID";

        $checkExistingMaxQuery = $dbh->prepare($checkExistingMaxSql);
        $checkExistingMaxQuery->bindParam(':sessionID', $sessionID, PDO::PARAM_INT);
        $checkExistingMaxQuery->bindParam(':classID', $classID, PDO::PARAM_INT);
        $checkExistingMaxQuery->bindParam(':subjectID', $subjectID, PDO::PARAM_INT);
        $checkExistingMaxQuery->execute();
        
        return $checkExistingMaxQuery->fetch(PDO::FETCH_ASSOC);
    }
    // Get the session ID and Name as per selected Option 
    $getSessionSql = "SELECT session_id, session_name FROM tblsessions WHERE session_id = :sessionID AND IsDeleted = 0";
    $sessionQuery = $dbh->prepare($getSessionSql);
    $sessionQuery->bindParam(':sessionID',$_SESSION['session'], PDO::PARAM_INT);
    $sessionQuery->execute();
    $session = $sessionQuery->fetch(PDO::FETCH_ASSOC);

    $sessionID = $session['session_id'];
    $sessionName = $session['session_name'];
    $msg = "";
    $successAlert = false;
    $dangerAlert = false;
    $classIDs = $_SESSION['class'];
    $sectionIDs = $_SESSION['Section'];

    // Fetch subjects from tblsubjects of selected class
    $classIDArray = array_map('intval', explode(',', $classIDs));
    $subjectSql = "SELECT * FROM tblsubjects WHERE IsCurricularSubject = 1 AND IsDeleted = 0 AND ClassName IN (" . implode(',', $classIDArray) . ")";
    $subjectQuery = $dbh->prepare($subjectSql);
    $subjectQuery->execute();
    $subjects = $subjectQuery->fetchAll(PDO::FETCH_ASSOC);
    
        // Check if Student Marks has been assigned
        $checkMarksAssignedSql = "SELECT ID FROM tblcocurricularreports 
                                    WHERE ExamSession = :sessionID 
                                    AND ClassName = :classID
                                    AND SectionName = :sectionID
                                    AND IsDeleted = 0";
        $checkMarksAssignedQuery = $dbh->prepare($checkMarksAssignedSql);
        $checkMarksAssignedQuery->bindParam(':sessionID', $sessionID, PDO::PARAM_INT);
        $checkMarksAssignedQuery->bindParam(':classID', $classIDs, PDO::PARAM_INT);
        $checkMarksAssignedQuery->bindParam(':sectionID', $sectionIDs, PDO::PARAM_INT);
        $checkMarksAssignedQuery->execute();
        $marksAssigned = $checkMarksAssignedQuery->fetch(PDO::FETCH_ASSOC);

        if (isset($_SESSION['class']) && isset($_SESSION['session']) && isset($_SESSION['Section'])) 
        {
            $sql = "SELECT * FROM tblstudent WHERE StudentClass = :classID AND StudentSection = :sectionID AND IsDeleted = 0";
            $query = $dbh->prepare($sql);
            $query->bindParam(':classID',$classIDs, PDO::PARAM_INT);
            $query->bindParam(':sectionID',$sectionIDs, PDO::PARAM_INT);
            $query->execute();
            $students = $query->fetchAll(PDO::FETCH_ASSOC);

            // Function to check if the max marks are assigned by the admin
            function getMaxMarks($classIDs, $sessionID, $subjectID, $type) 
            {
                global $dbh;
                
                $sql = "SELECT * FROM tblmaxcocurricular 
                        WHERE ClassID = :classID 
                        AND SessionID = :sessionID 
                        AND SubjectID = :subjectID";
                
                $query = $dbh->prepare($sql);
                $query->bindParam(':classID', $classIDs, PDO::PARAM_INT);
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
            function getTeacherAssignedMaxMarks($classIDs, $sessionID, $subjectID, $type)
            {
                global $dbh;

                $sql = "SELECT SubjectsJSON FROM tblcocurricularreports 
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

            if (isset($_POST['submit'])) 
            {
                try 
                {
                    $dbh->beginTransaction();
            
                        $totalPass = true;
                        $studentID = $_POST['studentID'];
                        $studentSubjectsData = array();
                        
                        foreach ($subjects as $subject) 
                        {   
                            $coCurricularMaxMarks = isset($_POST['SubMaxMarks'][$studentID][$subject['ID']]) ? (float)$_POST['SubMaxMarks'][$studentID][$subject['ID']] : 0;
                            $coCurricularMarksObtained = isset($_POST['SubMarksObtained'][$studentID][$subject['ID']]) ? $_POST['SubMarksObtained'][$studentID][$subject['ID']] : 0;

                            // An array for subject data
                            $subjectData = array(
                                'SubjectID' => $subject['ID'],
                                'CoCurricularMaxMarks' => $coCurricularMaxMarks,
                                'CoCurricularMarksObtained' => $coCurricularMarksObtained,
                            );
                            $studentSubjectsData[] = $subjectData;

                            $subjectsJSON = json_encode($studentSubjectsData);
                            
                            // Check for existing entry in tblcocurricularreports
                            $checkExistingSql = "SELECT ExamSession, ClassName, StudentName, SubjectsJSON FROM tblcocurricularreports 
                                                    WHERE ExamSession = :sessionID 
                                                    AND ClassName = :classID 
                                                    AND StudentName = :studentID 
                                                    AND SubjectsJSON = :subjectData
                                                    AND IsDeleted = 0";
                            $checkExistingQuery = $dbh->prepare($checkExistingSql);
                            $checkExistingQuery->bindParam(':sessionID', $sessionID, PDO::PARAM_INT);
                            $checkExistingQuery->bindParam(':classID', $classIDs, PDO::PARAM_INT);
                            $checkExistingQuery->bindParam(':studentID', $studentID, PDO::PARAM_INT);
                            $checkExistingQuery->bindParam(':subjectData', $subjectsJSON, PDO::PARAM_INT);
                            $checkExistingQuery->execute();
                            $existingReportDetails = $checkExistingQuery->fetch(PDO::FETCH_ASSOC);

                            // Fetching the pass percentage
                            $passPercentID = 1;
                            $defaultPassMarksSql = "SELECT DefaultPassMarks FROM tblpasspercent WHERE ID = :passPercentID";
                            $defaultPassMarksQuery = $dbh->prepare($defaultPassMarksSql);
                            $defaultPassMarksQuery->bindParam(':passPercentID', $passPercentID, PDO::PARAM_INT);
                            $defaultPassMarksQuery->execute();
                            $defaultPassPercent = $defaultPassMarksQuery->fetch(PDO::FETCH_COLUMN);

                            // Calculate passing marks for co-curricular subject
                            $CoCurricularPassMarks = $defaultPassPercent / 100 * $coCurricularMaxMarks;

                             // Check if marks obtained are less than passing marks for each subject
                            if ($coCurricularMarksObtained < $CoCurricularPassMarks) 
                            {
                                $totalPass = false;
                            }
                            // Check for existing entry in tblmaxmarks
                            $existingMaxReportDetails = checkExistingMaxMarks($dbh, $sessionID, $classIDs, $subject['ID']);

                            // Insert Max Marks in tblmaxcocurricular
                            if(!$existingMaxReportDetails)
                            {
                                    $insertAdminSql = "INSERT INTO tblmaxcocurricular (SessionID, ClassID, SubjectID, SubMaxMarks, PassingPercentage)
                                                        VALUES (:sessionID, :classID, :subjectID, :SubMaxMarks, :passingPercentage)";
                                    $insertAdminMaxQuery = $dbh->prepare($insertAdminSql);
                                    $insertAdminMaxQuery->bindParam(':sessionID', $sessionID, PDO::PARAM_INT);
                                    $insertAdminMaxQuery->bindParam(':classID', $classIDs, PDO::PARAM_INT);
                                    $insertAdminMaxQuery->bindParam(':subjectID', $subject['ID'], PDO::PARAM_INT);
                                    $insertAdminMaxQuery->bindParam(':SubMaxMarks', $coCurricularMaxMarks, PDO::PARAM_STR);           
                                    $insertAdminMaxQuery->bindParam(':passingPercentage', $defaultPassPercent, PDO::PARAM_INT);            
                                    $insertAdminMaxQuery->execute();
                            }
                            // Update Max Marks in tblmaxcocurricular
                            else
                            {
                                // If an existing entry is found in tblmaxmarks, update the data
                                $updateAdminSql = "UPDATE tblmaxcocurricular SET 
                                                SubMaxMarks = :SubMaxMarks
                                                WHERE SessionID = :sessionID 
                                                AND ClassID = :classID 
                                                AND SubjectID = :subjectID";
                            
                                $updateAdminMaxQuery = $dbh->prepare($updateAdminSql);
                                $updateAdminMaxQuery->bindParam(':SubMaxMarks', $coCurricularMaxMarks, PDO::PARAM_STR);
                                $updateAdminMaxQuery->bindParam(':sessionID', $sessionID, PDO::PARAM_INT);
                                $updateAdminMaxQuery->bindParam(':classID', $classIDs, PDO::PARAM_INT);
                                $updateAdminMaxQuery->bindParam(':subjectID', $subject['ID'], PDO::PARAM_INT);
                                $updateAdminMaxQuery->execute();
                            }
                        }
                        // If the student is not in tblcocurricularreports, insert the student
                        if (!$existingReportDetails) 
                        {
                            $insertSql = "INSERT INTO tblcocurricularreports (ExamSession, ClassName,SectionName, StudentName, SubjectsJSON, IsPassed)
                                            VALUES (:sessionID, :classID,:sectionID, :studentID, :subjectsJSON, :isPassed)";
                            $insertQuery = $dbh->prepare($insertSql);
                            $subjectsJSON = json_encode($studentSubjectsData);

                            $insertQuery->bindParam(':sessionID', $sessionID, PDO::PARAM_INT);
                            $insertQuery->bindParam(':classID', $classIDs, PDO::PARAM_INT);
                            $insertQuery->bindParam(':sectionID', $sectionIDs, PDO::PARAM_INT);
                            $insertQuery->bindParam(':studentID', $studentID, PDO::PARAM_INT);
                            $insertQuery->bindParam(':subjectsJSON', $subjectsJSON, PDO::PARAM_STR);
                            $insertQuery->bindParam(':isPassed', $totalPass, PDO::PARAM_INT);

                            $insertQuery->execute();
                        } 
                        else 
                        {
                            $existingSubjectsJSON = json_decode($existingReportDetails['SubjectsJSON'], true);                        
                            $subjectFound = false;
                        
                            foreach ($studentSubjectsData as $newSubjectData) 
                            {
                                foreach ($existingSubjectsJSON as &$existingSubjectData) 
                                {
                                    if ($existingSubjectData['SubjectID'] == $newSubjectData['SubjectID']) 
                                    {
                                        // If the subject already exists, update its marks
                                        $existingSubjectData['CoCurricularMaxMarks'] = $newSubjectData['CoCurricularMaxMarks'];
                                        $existingSubjectData['CoCurricularMarksObtained'] = $newSubjectData['CoCurricularMarksObtained'];
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
                        
                            $updateReportSql = "UPDATE tblcocurricularreports SET 
                                                SubjectsJSON = :subjectsJSON, 
                                                IsPassed = :isPassed
                                                WHERE ExamSession = :sessionID 
                                                AND ClassName = :classID 
                                                AND SectionName = :sectionID 
                                                AND StudentName = :studentID 
                                                AND IsDeleted = 0";
                        
                            $updateReportQuery = $dbh->prepare($updateReportSql);
                            $updateReportQuery->bindParam(':subjectsJSON', $updatedSubjectsJSON, PDO::PARAM_STR);
                            $updateReportQuery->bindParam(':isPassed', $totalPass, PDO::PARAM_INT);
                            $updateReportQuery->bindParam(':sessionID', $sessionID, PDO::PARAM_INT);
                            $updateReportQuery->bindParam(':sectionID', $sectionIDs, PDO::PARAM_INT);
                            $updateReportQuery->bindParam(':classID', $classIDs, PDO::PARAM_INT);
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
            header("Location:add-coCurricular-score.php");
        }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>TPS || Update Student Report</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- plugins:css -->
    <link rel="stylesheet" href="vendors/simple-line-icons/css/simple-line-icons.css">
    <link rel="stylesheet" href="css/style.css"/>
    <link rel="stylesheet" href="../css/remove-spinner.css"/>
    <link rel="stylesheet" href="../Employee/css/assignMarks.css"/>
    
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
                    <h3 class="page-title"> Update Student Report </h3>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                            <li class="breadcrumb-item active" aria-current="page"> Update Student Report </li>
                        </ol>
                    </nav>
                </div>
                <div class="row">
                    <div class="col-12 grid-margin stretch-card">
                        <div class="card">
                                <div class="card-body">
                                        <h4 class="card-title" style="text-align: center;">Update Co-Curricular Report of Academic Session <?php echo '('.$sessionName.')'; ?></h4>
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
                                        if ($marksAssigned) 
                                        { 
                                        ?>
                                                <!-- Search -->
                                                <div class="input-group mb-2">
                                                    <input type="search" class="form-control" placeholder="Search Name or Roll no" aria-label="Search Student" aria-describedby="search-btn" id="search-input">
                                                    <div class="input-group-append w-25">
                                                        <button class="btn btn-sm w-100 btn-outline-secondary" type="button" id="search-btn"><i class="icon-magnifier"></i></button>
                                                    </div>
                                                </div>
                                                <!-- Search Result -->
                                                <div class="w-100 p-2" id="search-result-container">
                                                    <div class="d-flex justify-content-between align-items-center">
                                                        <span id="search-result" class="font-weight-bold text-center w-100"></span>
                                                        <button type="button" class="close" aria-label="Close" id="close-search-result">
                                                            <span aria-hidden="true">&times;</span>
                                                        </button>
                                                    </div>
                                                </div>
                                                <?php 
                                                foreach ($students as $student) 
                                                { 
                                                    ?>
                                                    <form class="forms-sample" method="post" id="student-<?php echo htmlentities($student['ID']); ?>">
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
                                                                        <th>Max Marks</th>
                                                                        <th>Marks Obtained</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody>
                                                                    <tr>
                                                                        <?php 
                                                                        foreach ($subjects as $subject) 
                                                                        { 
                                                                            $subjectID = $subject['ID'];
                                                                            // Check if marks exist in tblreports for the student, exam, and subject type
                                                                            $checkMarksSql = "SELECT SubjectsJSON FROM tblcocurricularreports 
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
                                                                            

                                                                            $subjectsJSON = [];
                                                                            if ($marksData && isset($marksData['SubjectsJSON'])) {
                                                                                $subjectsJSON = json_decode($marksData['SubjectsJSON'], true);
                                                                            }
                                                                            $SubMarksObtained = '';
                                                                        // Check if the JSON is an array and not empty
                                                                            if (is_array($subjectsJSON) && !empty($subjectsJSON)) {
                                                                                // Find the subject in the SubjectsJSON array and extract the marks
                                                                                foreach ($subjectsJSON as $subjectData) {
                                                                                    if (isset($subjectData['SubjectID']) && $subjectData['SubjectID'] == $subjectID) {
                                                                                        $SubMaxMarks = $subjectData['CoCurricularMaxMarks'] ?? '';
                                                                                        $SubMarksObtained = $subjectData['CoCurricularMarksObtained'] ?? '';
                                                                                        break;
                                                                                    }
                                                                                }
                                                                            }
                                                                            
                                                                            // Storing max marks that admin gives, in variables.
                                                                            $adminSubMaxMarks = getMaxMarks($student['StudentClass'], $sessionID, $subject['ID'], 'Sub');
                                                                            // Check if the teacher has assigned max marks, if not, fallback to admin's max marks
                                                                            $SubMaxMarksToShow = ($adminSubMaxMarks === null) ? getTeacherAssignedMaxMarks($student['StudentClass'], $sessionID, $subject['ID'], 'Sub') : $adminSubMaxMarks;
                                                                            ?>
                                                                            <tr>
                                                                                <td class="text-left"><?php echo htmlentities($subject['SubjectName']);?></td>
                                                                                    <td>
                                                                                        <input type='number' min="0" step="any" class='marks-input border border-secondary max-marks-input' name="SubMaxMarks[<?php echo $student['ID']; ?>][<?php echo $subject['ID']; ?>]" 
                                                                                            value="<?php echo ($SubMaxMarksToShow !== null) ? $SubMaxMarksToShow : ''; ?>"
                                                                                            data-subject-id="<?php echo $subject['ID']; ?>"
                                                                                            >
                                                                                    </td>
                                                                                    <td>
                                                                                        <input type="number" step="any" class='marks-input border border-secondary marks-obtained-input' name="SubMarksObtained[<?php echo $student['ID']; ?>][<?php echo $subject['ID']; ?>]" 
                                                                                            value="<?php echo $SubMarksObtained; ?>">
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
                                                                                <?php echo 'type="button" data-toggle="modal" data-target="#confirmationModal_'.$student['ID'].'" '; ?>
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
                                            } 
                                            else
                                            {
                                                echo '<h3 class="text-center">No marks assigned for the selected options!</h3>';
                                            }
                                        ?>
                                </div>
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
<script src="../Employee/js/marksAssignValidation.js"></script>
<!-- End custom js for this page -->
</body>
</html>
<?php 

} 
?>
