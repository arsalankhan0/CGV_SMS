<?php
session_start();
include('includes/dbconnection.php');

if (strlen($_SESSION['sturecmsaid']) == 0) 
{
    header('location:logout.php');
} 
else 
{
    $eid = $_GET['editid'];
    $examid = $_GET['examid'];

    // Get the active session ID
    $getSessionSql = "SELECT session_id FROM tblsessions WHERE is_active = 1 AND IsDeleted = 0";
    $sessionQuery = $dbh->prepare($getSessionSql);
    $sessionQuery->execute();
    $sessionID = $sessionQuery->fetchColumn();

    try
    {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') 
        {
            if(isset($_POST['submit']))
            {
                foreach ($_POST['theory'] as $subjectId => $theoryMaxMarks) 
                {
                    // Fetch subject details
                    $subjectDetailsSql = "SELECT * FROM tblsubjects WHERE ID = :subjectId";
                    $subjectDetailsQuery = $dbh->prepare($subjectDetailsSql);
                    $subjectDetailsQuery->bindParam(':subjectId', $subjectId, PDO::PARAM_STR);
                    $subjectDetailsQuery->execute();
                    $subjectDetails = $subjectDetailsQuery->fetch(PDO::FETCH_ASSOC);

                    $insertReportSql = "INSERT INTO tblreports (ExamSession, ClassName, ExamName, Subjects, TheoryMaxMarks, PracticalMaxMarks, VivaMaxMarks) 
                                        VALUES (:examSession, :className, :examName, :subjects, :theoryMaxMarks, :practicalMaxMarks, :vivaMaxMarks)";
                    $insertReportQuery = $dbh->prepare($insertReportSql);
                    $insertReportQuery->bindParam(':examSession', $sessionID, PDO::PARAM_STR);
                    $insertReportQuery->bindParam(':className', $eid, PDO::PARAM_STR);
                    $insertReportQuery->bindParam(':examName', $examid, PDO::PARAM_STR);
                    $insertReportQuery->bindParam(':subjects', $subjectId, PDO::PARAM_STR);

                    // Set default values for disabled fields
                    $theoryMaxMarks = isset($_POST['theory'][$subjectId]) ? $_POST['theory'][$subjectId] : 0;
                    $practicalMaxMarks = isset($_POST['practical'][$subjectId]) ? $_POST['practical'][$subjectId] : 0;
                    $vivaMaxMarks = isset($_POST['viva'][$subjectId]) ? $_POST['viva'][$subjectId] : 0;

                    $insertReportQuery->bindParam(':theoryMaxMarks', $theoryMaxMarks, PDO::PARAM_STR);
                    $insertReportQuery->bindParam(':practicalMaxMarks', $practicalMaxMarks, PDO::PARAM_STR);
                    $insertReportQuery->bindParam(':vivaMaxMarks', $vivaMaxMarks, PDO::PARAM_STR);

                    $insertReportQuery->execute();
                }

                echo "<script>alert('Published Successfully');</script>";
                echo "<script>window.location.href = 'manage-exam-subject.php'</script>";
                exit();
            }
        }
    }
    catch(PDOException $e)
    {
        echo "<script>alert('Ops! Something went wrong.');</script>";
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
</head>
<body>
<div class="container-scroller">
    <!-- partial:partials/_navbar.html -->
    <?php
    include_once('includes/header.php');
    ?>
    <!-- partial -->
    <div class="container-fluid page-body-wrapper">
        <!-- partial:partials/_sidebar.html -->
        <?php include_once('includes/sidebar.php'); ?>
        <!-- partial -->
        <div class="main-panel">
            <div class="content-wrapper">
                <div class="page-header">
                    <?php
                    // $eid = $_GET['editid'];
                    // $examid = $_GET['examid'];

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
                                    <div class="table-responsive border rounded p-1">
                                        <table class="table">
                                            <thead>
                                                <tr>
                                                    <th class="font-weight-bold">Subjects</th>
                                                    <th class="font-weight-bold">Max Marks (Theory)</th>
                                                    <th class="font-weight-bold">Max Marks (Practical)</th>
                                                    <th class="font-weight-bold">Max Marks (Viva)</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                // Fetch subjects based on the editid
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
                                                        $disabledTheory = !in_array('theory', $subjectTypes) ? 'disabled' : '';
                                                        $disabledPractical = !in_array('practical', $subjectTypes) ? 'disabled' : '';
                                                        $disabledViva = !in_array('viva', $subjectTypes) ? 'disabled' : '';
                                                        ?>
                                                            <td><input type="number" class="border border-secondary py-1"
                                                                    name="theory[<?php echo $subjectId; ?>]"
                                                                    <?php echo $disabledTheory; ?>></td>
                                                            <td><input type="number" class="border border-secondary py-1"
                                                                    name="practical[<?php echo $subjectId; ?>]"
                                                                    <?php echo $disabledPractical; ?>></td>
                                                            <td><input type="number" class="border border-secondary py-1"
                                                                    name="viva[<?php echo $subjectId; ?>]"
                                                                    <?php echo $disabledViva; ?>></td>
                                                    </tr>
                                                    <?php
                                                }
                                                ?>
                                            </tbody>
                                        </table>
                                    </div>
                                    <div class="d-flex justify-content-end pt-4">
                                        <button type="submit" class="btn btn-primary mr-2" name="submit">Publish
                                        </button>
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
<!-- End custom js for this page -->
</body>
</html>
<?php
}
?>
