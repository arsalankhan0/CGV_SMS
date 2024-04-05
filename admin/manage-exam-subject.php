<?php
session_start();
error_reporting(0);
include('includes/dbconnection.php');

if (strlen($_SESSION['sturecmsaid']) == 0) 
{
    header('location:logout.php');
} 
else 
{
    $successAlert = false;
    $dangerAlert = false;
    $msg = "";

    $eid = $_GET['editid'];
    $examid = $_GET['examid'];

    // Get the active session ID
    $getSessionSql = "SELECT session_id FROM tblsessions WHERE is_active = 1 AND IsDeleted = 0";
    $sessionQuery = $dbh->prepare($getSessionSql);
    $sessionQuery->execute();
    $sessionID = $sessionQuery->fetchColumn();

    $insertFlag = true;

    try 
    {
        // Function to check if a subject is optional
        function isSubjectOptional($subjectId, $dbh, $eid, $sessionID)
        {
            $subjectSql = "SELECT IsOptional FROM tblsubjects WHERE ID = :subjectId 
                            AND FIND_IN_SET(:editid, ClassName) 
                            AND SubjectName IS NOT NULL 
                            AND IsDeleted = 0 
                            AND SessionID = :sessionID 
                            AND IsCurricularSubject = 0";
            $subjectQuery = $dbh->prepare($subjectSql);
            $subjectQuery->bindParam(':subjectId', $subjectId, PDO::PARAM_INT);
            $subjectQuery->bindParam(':editid', $eid, PDO::PARAM_STR);
            $subjectQuery->bindParam(':sessionID', $sessionID, PDO::PARAM_STR);
            $subjectQuery->execute();
            $result = $subjectQuery->fetch(PDO::FETCH_ASSOC);
    
            return $result['IsOptional'] == 1;
        }
        if ($_SERVER['REQUEST_METHOD'] == 'POST') 
        {
            if (isset($_POST['submit'])) 
            {
                // Check if the checkbox for grading system is checked
                $gradingSystem = isset($_POST['optionalGrading']) ? 1 : 0;

                $insertFlag = true; // Flag to track insertion status

                // Loop through academic subjects
                foreach ($_POST['subMaxMarks'] as $subjectId => $SubMaxMarks) 
                {
                    // Set default values for disabled fields
                    $SubMaxMarks = isset($_POST['subMaxMarks'][$subjectId]) ? $_POST['subMaxMarks'][$subjectId] : 0;
                    $passMarks = $_POST['passMarks'][$subjectId];
                    // Check if the subject is optional and the checkbox for grading system is checked
                    if ($gradingSystem == 1 && isSubjectOptional($subjectId, $dbh, $eid, $sessionID)) 
                    {
                        $gradingSystemValue = 1;
                    } 
                    else 
                    {
                        $gradingSystemValue = 0;
                    }

                    // Check if the entry already exists
                    $checkDuplicateSql = "SELECT * FROM tblmaxmarks WHERE ClassID = :classID AND ExamID = :examID AND SessionID = :sessionID AND SubjectID = :subjectID";
                    $checkDuplicateQuery = $dbh->prepare($checkDuplicateSql);
                    $checkDuplicateQuery->bindParam(':classID', $eid, PDO::PARAM_INT);
                    $checkDuplicateQuery->bindParam(':examID', $examid, PDO::PARAM_INT);
                    $checkDuplicateQuery->bindParam(':sessionID', $sessionID, PDO::PARAM_INT);
                    $checkDuplicateQuery->bindParam(':subjectID', $subjectId, PDO::PARAM_INT);
                    $checkDuplicateQuery->execute();

                    if ($checkDuplicateQuery->rowCount() > 0) 
                    {
                        // Update existing record
                        $updateMaxMarksSql = "UPDATE tblmaxmarks 
                                                SET SubMaxMarks = :SubMaxMarks, 
                                                    PassingPercentage = :passPercent,
                                                    GradingSystem = :gradingSystemValue
                                                WHERE ClassID = :classID 
                                                AND ExamID = :examID 
                                                AND SessionID = :sessionID 
                                                AND SubjectID = :subjectID";
                        $updateMaxMarksQuery = $dbh->prepare($updateMaxMarksSql);
                        $updateMaxMarksQuery->bindParam(':classID', $eid, PDO::PARAM_INT);
                        $updateMaxMarksQuery->bindParam(':examID', $examid, PDO::PARAM_INT);
                        $updateMaxMarksQuery->bindParam(':sessionID', $sessionID, PDO::PARAM_INT);
                        $updateMaxMarksQuery->bindParam(':subjectID', $subjectId, PDO::PARAM_INT);
                        $updateMaxMarksQuery->bindParam(':SubMaxMarks', $SubMaxMarks, PDO::PARAM_STR);
                        $updateMaxMarksQuery->bindParam(':passPercent', $passMarks, PDO::PARAM_INT);
                        $updateMaxMarksQuery->bindParam(':gradingSystemValue', $gradingSystemValue, PDO::PARAM_INT);
                        $updateMaxMarksQuery->execute();
                    } 
                    else 
                    {
                        // Insert new record
                        $insertMaxMarksSql = "INSERT INTO tblmaxmarks (ClassID, ExamID, SessionID, SubjectID, SubMaxMarks, PassingPercentage, GradingSystem) 
                            VALUES (:classID, :examID, :sessionID, :subjectID, :SubMaxMarks, :passPercent, :gradingSystemValue)";
                        $insertMaxMarksQuery = $dbh->prepare($insertMaxMarksSql);
                        $insertMaxMarksQuery->bindParam(':classID', $eid, PDO::PARAM_INT);
                        $insertMaxMarksQuery->bindParam(':examID', $examid, PDO::PARAM_INT);
                        $insertMaxMarksQuery->bindParam(':sessionID', $sessionID, PDO::PARAM_INT);
                        $insertMaxMarksQuery->bindParam(':subjectID', $subjectId, PDO::PARAM_INT);
                        $insertMaxMarksQuery->bindParam(':SubMaxMarks', $SubMaxMarks, PDO::PARAM_STR);
                        $insertMaxMarksQuery->bindParam(':passPercent', $passMarks, PDO::PARAM_INT);
                        $insertMaxMarksQuery->bindParam(':gradingSystemValue', $gradingSystemValue, PDO::PARAM_INT); // Add this line
                        $insertMaxMarksQuery->execute();
                    }
                }

                // Display success or failure messages
                if ($insertFlag) 
                {
                    $successAlert = true;
                    $msg = "Max Marks assigned successfully.";
                } 
                else 
                {
                    $dangerAlert = true;
                    $msg = "Failed to assign Max Marks.";
                }
            }
        }
        // Get values from the submitted form
        $examId = $_GET['examid'];
    
        // Check if already published
        $checkPublishedSql = "SELECT IsPublished, session_id FROM tblexamination WHERE ID = :examId 
                                AND IsPublished = 1
                                AND session_id = :session_id
                                AND IsDeleted = 0";
        $checkPublishedQuery = $dbh->prepare($checkPublishedSql);
        $checkPublishedQuery->bindParam(':examId', $examId, PDO::PARAM_STR);
        $checkPublishedQuery->bindParam(':session_id', $sessionID, PDO::PARAM_STR);
        $checkPublishedQuery->execute();
        $published = $checkPublishedQuery->fetch(PDO::FETCH_ASSOC);
    } 
    catch (PDOException $e) 
    {
        $dangerAlert = true;
        $msg = "Ops! Something went wrong.";
        echo "<script>console.error('Error:---> " . $e->getMessage() . "');</script>";
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>TPS || Assign Max Marks</title>
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
    <link rel="stylesheet" href="css/style.css" />
    <link rel="stylesheet" href="../css/remove-spinner.css" />
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
                    <?php
                    $examNameSql = "SELECT ExamName, ClassName FROM tblexamination WHERE ID = :examid";
                    $examNameQuery = $dbh->prepare($examNameSql);
                    $examNameQuery->bindParam(':examid', $examid, PDO::PARAM_STR);
                    $examNameQuery->execute();
                    $examNameRow = $examNameQuery->fetch(PDO::FETCH_ASSOC);
                    $examName = $examNameRow['ExamName'];
                    $classIds = explode(",", $examNameRow['ClassName']);

                    if (isset($examName)) { ?>
                        <h3 class="page-title"> Assign Max Marks for '<?php echo $examName; ?>' Exam</h3>
                    <?php } else { ?>
                        <h3 class="page-title"> Assign Max Marks </h3>
                    <?php } ?>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                            <li class="breadcrumb-item active" aria-current="page"> Assign Max Marks</li>
                        </ol>
                    </nav>
                </div>
                <div class="row">
                    <div class="col-12 grid-margin stretch-card">
                        <div class="card">
                            <div class="card-body">
                                <h4 class="card-title" style="text-align: center;">Assign Max Marks of <?php echo '('.$examName.')'; ?></h4>
                                <form method="POST">
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
                                    <!-- MAIN SUBJECTS -->
                                    <div class="table-responsive border rounded p-1">
                                        <table class="table">
                                            <thead>
                                                <tr>
                                                    <th class="font-weight-bold">Subjects</th>
                                                    <th class="font-weight-bold">Max Marks</th>
                                                    <th class="font-weight-bold">Pass Marks (%)</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                
                                                // Query to show main subjects
                                                $subjectSql = "SELECT ID, SubjectName, ClassName FROM tblsubjects WHERE FIND_IN_SET(:editid, ClassName) AND SubjectName IS NOT NULL AND IsDeleted = 0 AND SessionID = :sessionID AND IsCurricularSubject = 0 AND IsOptional = 0";
                                                $subjectQuery = $dbh->prepare($subjectSql);
                                                $subjectQuery->bindParam(':editid', $eid, PDO::PARAM_STR);
                                                $subjectQuery->bindParam(':sessionID', $sessionID, PDO::PARAM_STR);
                                                $subjectQuery->execute();
                                                $subjects = $subjectQuery->fetchAll(PDO::FETCH_ASSOC);

                                                foreach ($subjects as $subject) {
                                                    ?>
                                                    <tr>
                                                        <td><?php echo htmlentities($subject['SubjectName']);?></td>
                                                        <?php
                                                        $subjectId = $subject['ID'];
                                                        $disabledSub = ($published) ? 'disabled' : '';
                                                        $disabledPassMarks = ($published) ? 'disabled' : '';

                                                        // Fetch the Default Passing Marks
                                                        $passPercentID = 1; 
                                                        $defaultPassMarksSql = "SELECT DefaultPassMarks FROM tblpasspercent
                                                                                WHERE ID = :id";
                                                        $defaultPassMarksQuery = $dbh->prepare($defaultPassMarksSql);
                                                        $defaultPassMarksQuery->bindParam(':id', $passPercentID, PDO::PARAM_INT);
                                                        $defaultPassMarksQuery->execute();
                                                        $defaultPassMarks = $defaultPassMarksQuery->fetch(PDO::FETCH_COLUMN);


                                                        // Fetch existing record from tblmaxmarks
                                                        $getMaxMarksSql = "SELECT SubMaxMarks, PassingPercentage 
                                                                            FROM tblmaxmarks 
                                                                            WHERE ClassID = :classID 
                                                                            AND ExamID = :examID 
                                                                            AND SessionID = :sessionID 
                                                                            AND SubjectID = :subjectID";
                                                        $getMaxMarksQuery = $dbh->prepare($getMaxMarksSql);
                                                        $getMaxMarksQuery->bindParam(':classID', $eid, PDO::PARAM_STR);
                                                        $getMaxMarksQuery->bindParam(':examID', $examid, PDO::PARAM_STR);
                                                        $getMaxMarksQuery->bindParam(':sessionID', $sessionID, PDO::PARAM_STR);
                                                        $getMaxMarksQuery->bindParam(':subjectID', $subjectId, PDO::PARAM_STR);
                                                        $getMaxMarksQuery->execute();
                                                        $maxMarksData = $getMaxMarksQuery->fetch(PDO::FETCH_ASSOC);

                                                        $SubMaxMarks = ($maxMarksData) ? $maxMarksData['SubMaxMarks'] : 0;
                                                        $passingPercent = ($maxMarksData) ? $maxMarksData['PassingPercentage'] : $defaultPassMarks;

                                                        ?>
                                                        <td>
                                                            <input type="number" class="border border-secondary py-1"
                                                                min="0"
                                                                name="subMaxMarks[<?php echo $subjectId; ?>]"
                                                                value="<?php echo $SubMaxMarks; ?>"
                                                                <?php echo $disabledSub;
                                                                ?>
                                                                >
                                                        </td>
                                                        <td>
                                                            <input type="number" class="border border-secondary py-1"
                                                                min="0"
                                                                name="passMarks[<?php echo $subjectId; ?>]"
                                                                value="<?php echo $passingPercent;?>"
                                                                <?php echo $disabledPassMarks;?>
                                                                >
                                                        </td>
                                                    </tr>
                                                    <?php
                                                }
                                                ?>
                                            </tbody>
                                        </table>
                                    </div>
                                    <?php
                                    $examTypeSql = "SELECT ExamType FROM tblexamination WHERE ID = :examId";
                                    $examTypeQuery = $dbh->prepare($examTypeSql);
                                    $examTypeQuery->bindParam(':examId', $examid, PDO::PARAM_STR);
                                    $examTypeQuery->execute();
                                    $examTypeRow = $examTypeQuery->fetch(PDO::FETCH_ASSOC);

                                        // $examType = $examTypeRow['ExamType'];
                                    if($examTypeRow['ExamType'] !== 'Co-Curricular')
                                    {
                                    ?>
                                    <!-- OPTIONAL SUBJECTS -->
                                    <div class="mt-2 text-center">
                                        <?php
                                        // Query to show optional subjects
                                        $subjectSql = "SELECT ID, SubjectName, ClassName FROM tblsubjects WHERE FIND_IN_SET(:editid, ClassName) AND SubjectName IS NOT NULL AND IsDeleted = 0 AND SessionID = :sessionID AND IsCurricularSubject = 0 AND IsOptional = 1";
                                        $subjectQuery = $dbh->prepare($subjectSql);
                                        $subjectQuery->bindParam(':editid', $eid, PDO::PARAM_STR);
                                        $subjectQuery->bindParam(':sessionID', $sessionID, PDO::PARAM_STR);
                                        $subjectQuery->execute();
                                        $subjects = $subjectQuery->fetchAll(PDO::FETCH_ASSOC);

                                        foreach ($subjects as $subject) 
                                        {
                                            $subjectId = $subject['ID'];
                                        }
                                        

                                        // Fetch existing record from tblmaxmarks
                                        $getMaxMarksSql = "SELECT GradingSystem 
                                                            FROM tblmaxmarks 
                                                            WHERE ClassID = :classID 
                                                            AND GradingSystem = 1
                                                            AND ExamID = :examID 
                                                            AND SessionID = :sessionID 
                                                            AND SubjectID = :subjectID";
                                        $getMaxMarksQuery = $dbh->prepare($getMaxMarksSql);
                                        $getMaxMarksQuery->bindParam(':classID', $eid, PDO::PARAM_STR);
                                        $getMaxMarksQuery->bindParam(':examID', $examid, PDO::PARAM_STR);
                                        $getMaxMarksQuery->bindParam(':sessionID', $sessionID, PDO::PARAM_STR);
                                        $getMaxMarksQuery->bindParam(':subjectID', $subjectId, PDO::PARAM_STR);
                                        $getMaxMarksQuery->execute();
                                        $maxMarksData = $getMaxMarksQuery->fetch(PDO::FETCH_ASSOC);

                                        $checkedGrade = ($maxMarksData) ? 'checked' : '';
                                        $disabledGrade = ($published) ? 'disabled' : '';

                                        ?>
                                        <strong>OPTIONAL SUBJECTS</strong>
                                        <!-- Add the label and checkbox here -->
                                        <label for="optionalGrading" class="ml-2">Use Grading System:</label>
                                        <input type="checkbox" class="grading-checkbox" name="optionalGrading" id="optionalGrading" <?php echo $disabledGrade .' '. $checkedGrade; ?>>
                                    </div>
                                    <div class="table table-responsive border rounded mt-2 p-1" id="hide-table">
                                        <table class="table">
                                            <thead>
                                                <tr>
                                                    <th class="font-weight-bold">Subjects</th>
                                                    <th class="font-weight-bold">Max Marks</th>
                                                    <th class="font-weight-bold">Pass Marks (%)</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php


                                                foreach ($subjects as $subject) {
                                                    ?>
                                                    <tr>
                                                        <td><?php echo htmlentities($subject['SubjectName']);?></td>
                                                        <?php
                                                        $subjectId = $subject['ID'];
                                                        $disabledSub = ($published) ? 'disabled' : '';

                                                        // Fetch the Default Passing Marks
                                                        $passPercentID = 1; 
                                                        $defaultPassMarksSql = "SELECT DefaultPassMarks FROM tblpasspercent
                                                                                WHERE ID = :id";
                                                        $defaultPassMarksQuery = $dbh->prepare($defaultPassMarksSql);
                                                        $defaultPassMarksQuery->bindParam(':id', $passPercentID, PDO::PARAM_INT);
                                                        $defaultPassMarksQuery->execute();
                                                        $defaultPassMarks = $defaultPassMarksQuery->fetch(PDO::FETCH_COLUMN);


                                                        // Fetch existing record from tblmaxmarks
                                                        $getMaxMarksSql = "SELECT SubMaxMarks, PassingPercentage 
                                                                            FROM tblmaxmarks 
                                                                            WHERE ClassID = :classID 
                                                                            AND ExamID = :examID 
                                                                            AND SessionID = :sessionID 
                                                                            AND SubjectID = :subjectID";
                                                        $getMaxMarksQuery = $dbh->prepare($getMaxMarksSql);
                                                        $getMaxMarksQuery->bindParam(':classID', $eid, PDO::PARAM_STR);
                                                        $getMaxMarksQuery->bindParam(':examID', $examid, PDO::PARAM_STR);
                                                        $getMaxMarksQuery->bindParam(':sessionID', $sessionID, PDO::PARAM_STR);
                                                        $getMaxMarksQuery->bindParam(':subjectID', $subjectId, PDO::PARAM_STR);
                                                        $getMaxMarksQuery->execute();
                                                        $maxMarksData = $getMaxMarksQuery->fetch(PDO::FETCH_ASSOC);

                                                        $SubMaxMarks = ($maxMarksData) ? $maxMarksData['SubMaxMarks'] : 0;
                                                        $passingPercent = ($maxMarksData) ? $maxMarksData['PassingPercentage'] : $defaultPassMarks;

                                                        ?>
                                                        <td>
                                                            <input type="number" class="border border-secondary py-1"
                                                                min="0"
                                                                name="subMaxMarks[<?php echo $subjectId; ?>]"
                                                                value="<?php echo $SubMaxMarks; ?>"
                                                                <?php echo $disabledSub;
                                                                ?>
                                                                >
                                                        </td>
                                                        <td>
                                                            <input type="number" class="border border-secondary py-1"
                                                                min="0"
                                                                name="passMarks[<?php echo $subjectId; ?>]"
                                                                value="<?php echo $passingPercent;?>"
                                                                <?php echo $disabledPassMarks;?>
                                                                >
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
                                    if(!$published)
                                    {
                                    ?>
                                    <div class="d-flex justify-content-end pt-4">
                                        <button type="submit" class="btn btn-primary mr-2" name="submit" >Add</button>
                                    </div>
                                    <?php
                                    }
                                    ?>
                                </form>
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
<script src="./js/toggleGradingSystem.js"></script>
<!-- End custom js for this page -->
</body>
</html>
<?php
}
?>
