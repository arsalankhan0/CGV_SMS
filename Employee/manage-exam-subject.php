<?php
session_start();
// error_reporting(0);
include('includes/dbconnection.php');

if (strlen($_SESSION['sturecmsEMPid']) == 0) 
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

    try {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') 
        {
            if (isset($_POST['submit'])) 
            {
                foreach ($_POST['theory'] as $subjectId => $theoryMaxMarks) 
                {
                    // Set default values for disabled fields
                    $theoryMaxMarks = isset($_POST['theory'][$subjectId]) ? $_POST['theory'][$subjectId] : 0;
                    $practicalMaxMarks = isset($_POST['practical'][$subjectId]) ? $_POST['practical'][$subjectId] : 0;
                    $vivaMaxMarks = isset($_POST['viva'][$subjectId]) ? $_POST['viva'][$subjectId] : 0;
                    $passMarks = $_POST['passMarks'][$subjectId];

                    // Check if the entry already exists
                    $checkDuplicateSql = "SELECT * FROM tblmaxmarks WHERE ClassID = :classID AND ExamID = :examID AND SessionID = :sessionID AND SubjectID = :subjectID";
                    $checkDuplicateQuery = $dbh->prepare($checkDuplicateSql);
                    $checkDuplicateQuery->bindParam(':classID', $eid, PDO::PARAM_STR);
                    $checkDuplicateQuery->bindParam(':examID', $examid, PDO::PARAM_STR);
                    $checkDuplicateQuery->bindParam(':sessionID', $sessionID, PDO::PARAM_STR);
                    $checkDuplicateQuery->bindParam(':subjectID', $subjectId, PDO::PARAM_STR);
                    $checkDuplicateQuery->execute();

                    if ($checkDuplicateQuery->rowCount() > 0) 
                    {
                        $insertFlag = false;
                        $updateMaxMarksSql = "UPDATE tblmaxmarks 
                                                SET TheoryMaxMarks = :theoryMaxMarks, 
                                                    PracticalMaxMarks = :practicalMaxMarks, 
                                                    VivaMaxMarks = :vivaMaxMarks,
                                                    PassingPercentage = :passPercent
                                                WHERE ClassID = :classID 
                                                AND ExamID = :examID 
                                                AND SessionID = :sessionID 
                                                AND SubjectID = :subjectID";
                        $updateMaxMarksQuery = $dbh->prepare($updateMaxMarksSql);
                        $updateMaxMarksQuery->bindParam(':classID', $eid, PDO::PARAM_STR);
                        $updateMaxMarksQuery->bindParam(':examID', $examid, PDO::PARAM_STR);
                        $updateMaxMarksQuery->bindParam(':sessionID', $sessionID, PDO::PARAM_STR);
                        $updateMaxMarksQuery->bindParam(':subjectID', $subjectId, PDO::PARAM_STR);
                        $updateMaxMarksQuery->bindParam(':theoryMaxMarks', $theoryMaxMarks, PDO::PARAM_STR);
                        $updateMaxMarksQuery->bindParam(':practicalMaxMarks', $practicalMaxMarks, PDO::PARAM_STR);
                        $updateMaxMarksQuery->bindParam(':vivaMaxMarks', $vivaMaxMarks, PDO::PARAM_STR);
                        $updateMaxMarksQuery->bindParam(':passPercent', $passMarks, PDO::PARAM_INT);
                        $updateMaxMarksQuery->execute();
                    }
                }
                if (!$insertFlag) 
                {
                    $successAlert = true;
                    $msg = "Max Marks assigned successfully.";
                }

                if ($insertFlag) 
                {
                    foreach ($_POST['theory'] as $subjectId => $theoryMaxMarks) 
                    {
                        // Fetch subject details
                        $subjectDetailsSql = "SELECT * FROM tblsubjects WHERE ID = :subjectId";
                        $subjectDetailsQuery = $dbh->prepare($subjectDetailsSql);
                        $subjectDetailsQuery->bindParam(':subjectId', $subjectId, PDO::PARAM_STR);
                        $subjectDetailsQuery->execute();
                        $subjectDetails = $subjectDetailsQuery->fetch(PDO::FETCH_ASSOC);

                        // Insert into tblmaxmarks table
                        $insertMaxMarksSql = "INSERT INTO tblmaxmarks (ClassID, ExamID, SessionID, SubjectID, TheoryMaxMarks, PracticalMaxMarks, VivaMaxMarks, PassingPercentage) 
                                        VALUES (:classID, :examID, :sessionID, :subjectID, :theoryMaxMarks, :practicalMaxMarks, :vivaMaxMarks, :passPercent)";
                        $insertMaxMarksQuery = $dbh->prepare($insertMaxMarksSql);
                        $insertMaxMarksQuery->bindParam(':classID', $eid, PDO::PARAM_STR);
                        $insertMaxMarksQuery->bindParam(':examID', $examid, PDO::PARAM_STR);
                        $insertMaxMarksQuery->bindParam(':sessionID', $sessionID, PDO::PARAM_STR);
                        $insertMaxMarksQuery->bindParam(':subjectID', $subjectId, PDO::PARAM_STR);
                        $insertMaxMarksQuery->bindParam(':theoryMaxMarks', $theoryMaxMarks, PDO::PARAM_STR);
                        $insertMaxMarksQuery->bindParam(':practicalMaxMarks', $practicalMaxMarks, PDO::PARAM_STR);
                        $insertMaxMarksQuery->bindParam(':vivaMaxMarks', $vivaMaxMarks, PDO::PARAM_STR);
                        $insertMaxMarksQuery->bindParam(':passPercent', $passMarks, PDO::PARAM_INT);
                        $insertMaxMarksQuery->execute();



                    }
                    $successAlert = true;
                    $msg = "Max Marks assigned successfully.";
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
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Student Management System || Assign Max Marks</title>
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
                                <h4 class="card-title" style="text-align: center;">Assign Max Marks</h4>
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
                                    <div class="table-responsive border rounded p-1">
                                        <table class="table">
                                            <thead>
                                                <tr>
                                                    <th class="font-weight-bold">Subjects</th>
                                                    <th class="font-weight-bold">Max Marks (Theory)</th>
                                                    <th class="font-weight-bold">Max Marks (Practical)</th>
                                                    <th class="font-weight-bold">Max Marks (Viva)</th>
                                                    <th class="font-weight-bold">Pass Marks (%)</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                $subjectSql = "SELECT * FROM tblsubjects WHERE FIND_IN_SET(:editid, ClassName) AND SubjectName IS NOT NULL AND IsDeleted = 0 AND SessionID = :sessionID";
                                                $subjectQuery = $dbh->prepare($subjectSql);
                                                $subjectQuery->bindParam(':editid', $eid, PDO::PARAM_STR);
                                                $subjectQuery->bindParam(':sessionID', $sessionID, PDO::PARAM_STR);
                                                $subjectQuery->execute();
                                                $subjects = $subjectQuery->fetchAll(PDO::FETCH_ASSOC);

                                                foreach ($subjects as $subject) {
                                                    ?>
                                                    <tr>
                                                        <td><?php echo htmlentities($subject['SubjectName']); ?></td>
                                                        <?php
                                                        $subjectTypes = explode(",", strtolower($subject['SubjectType']));
                                                        $subjectId = $subject['ID'];
                                                        $disabledTheory = (!in_array('theory', $subjectTypes) || $published) ? 'disabled' : '';
                                                        $disabledPractical = (!in_array('practical', $subjectTypes) || $published) ? 'disabled' : '';
                                                        $disabledViva = (!in_array('viva', $subjectTypes) || $published) ? 'disabled' : '';
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
                                                        $getMaxMarksSql = "SELECT TheoryMaxMarks, PracticalMaxMarks, VivaMaxMarks, PassingPercentage 
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

                                                        $theoryMaxMarks = ($maxMarksData) ? $maxMarksData['TheoryMaxMarks'] : 0;
                                                        $practicalMaxMarks = ($maxMarksData) ? $maxMarksData['PracticalMaxMarks'] : 0;
                                                        $vivaMaxMarks = ($maxMarksData) ? $maxMarksData['VivaMaxMarks'] : 0;
                                                        $passingPercent = ($maxMarksData) ? $maxMarksData['PassingPercentage'] : $defaultPassMarks;

                                                        ?>
                                                        <td>
                                                            <input type="number" class="border border-secondary py-1"
                                                                name="theory[<?php echo $subjectId; ?>]"
                                                                value="<?php echo $theoryMaxMarks; ?>"
                                                                <?php echo $disabledTheory;
                                                                ?>
                                                                >
                                                        </td>
                                                        <td>
                                                            <input type="number" class="border border-secondary py-1"
                                                                name="practical[<?php echo $subjectId; ?>]"
                                                                value="<?php echo $practicalMaxMarks; ?>"
                                                                <?php echo $disabledPractical; 
                                                                ?>
                                                                >
                                                        </td>
                                                        <td>
                                                            <input type="number" class="border border-secondary py-1"
                                                                name="viva[<?php echo $subjectId; ?>]"
                                                                value="<?php echo $vivaMaxMarks; ?>"
                                                                <?php echo $disabledViva; 
                                                                ?>
                                                                >
                                                        </td>
                                                        <td>
                                                            <input type="number" class="border border-secondary py-1"
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
                                    <div class="d-flex justify-content-end pt-4">
                                        <button type="submit" class="btn btn-primary mr-2" name="submit">Add</button>
                                    </div>
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
<!-- End custom js for this page -->
</body>
</html>
<?php
}
?>
