<?php
session_start();
error_reporting(0);
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
    
    // Get the active session ID
    $getSessionSql = "SELECT session_id FROM tblsessions WHERE is_active = 1 AND IsDeleted = 0";
    $sessionQuery = $dbh->prepare($getSessionSql);
    $sessionQuery->execute();
    $sessionID = $sessionQuery->fetchColumn();

    // Check if already published
    $checkPublishedSql = "SELECT IsPublished, session_id FROM tblexamination WHERE ID = :examId 
                        AND IsPublished = 1
                        AND session_id = :session_id
                        AND IsDeleted = 0";
    $checkPublishedQuery = $dbh->prepare($checkPublishedSql);
    $checkPublishedQuery->bindParam(':examId', $eid, PDO::PARAM_STR);
    $checkPublishedQuery->bindParam(':session_id', $sessionID, PDO::PARAM_STR);
    $checkPublishedQuery->execute();
    $published = $checkPublishedQuery->fetch(PDO::FETCH_ASSOC);

    try 
    {
        if (isset($_POST['submit']) && isset($_GET['editid']) && !empty($_GET['editid'])) {

            $selectedClasses = isset($_POST['classes']) ? $_POST['classes'] : [];

            if (!empty($selectedClasses)) 
            {
                    $selectedClassesImploded = implode(",", $selectedClasses);
                    $examType = isset($_POST['examType']) ? $_POST['examType'] : '';
                    $durationFrom = isset($_POST['durationFrom']) ? $_POST['durationFrom'] : '';
                    $durationTo = isset($_POST['durationTo']) ? $_POST['durationTo'] : '';

                    $sql = "UPDATE tblexamination 
                            SET ClassName = :cName, ExamType = :examType, DurationFrom = :durationFrom, DurationTo = :durationTo 
                            WHERE ID = :eid";
                    $query = $dbh->prepare($sql);
                    $query->bindParam(':cName', $selectedClassesImploded, PDO::PARAM_STR);
                    $query->bindParam(':examType', $examType, PDO::PARAM_STR);
                    $query->bindParam(':durationFrom', $durationFrom, PDO::PARAM_STR);
                    $query->bindParam(':durationTo', $durationTo, PDO::PARAM_STR);
                    $query->bindParam(':eid', $_GET['editid'], PDO::PARAM_STR);
                    $query->execute();

                    $successAlert = true;
                    $msg = "Classes has been updated successfully.";
            } 
            else 
            {
                $dangerAlert = true;
                $msg = "Please select at least one class!";
            }
        }
    } 
    catch (PDOException $e) 
    {
        $dangerAlert = true;
        $msg = "Ops! And error occurred.";
        echo "<script>console.error('Error:---> " . $e->getMessage() . "');</script>";
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>

    <title>TPS || Manage Classes</title>
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
                    $eid = $_GET['editid'];

                    $examNameSql = "SELECT ExamName FROM tblexamination WHERE ID = :eid";
                    $examNameQuery = $dbh->prepare($examNameSql);
                    $examNameQuery->bindParam(':eid', $eid, PDO::PARAM_STR);
                    $examNameQuery->execute();
                    $examNameRow = $examNameQuery->fetch(PDO::FETCH_OBJ);
                    $examName = $examNameRow->ExamName;

                    $disabledInput = ($published) ? 'disabled' : '';

                    if (isset($examName)) { ?>
                        <h3 class="page-title"> Manage Classes - Select Classes for '<?php echo $examName; ?>'</h3>
                    <?php } else { ?>
                        <h3 class="page-title"> Manage Classes </h3>
                    <?php } ?>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                            <li class="breadcrumb-item active" aria-current="page"> Manage Classes</li>
                        </ol>
                    </nav>
                </div>
                <div class="row">
                    <div class="col-12 grid-margin stretch-card">
                        <div class="card">
                            <div class="card-body">
                                <h4 class="card-title" style="text-align: center;">Manage Classes</h4>
                                <form class="forms-sample" method="post">
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
                                    <div class="form-group">
                                        <label for="exampleFormControlSelect2">Select Classes for <?php echo $examName; ?> exam</label>
                                        <select multiple="multiple" name="classes[]"
                                                class="js-example-basic-multiple w-100" <?php echo $disabledInput; ?>>
                                            <?php
                                            $eid = $_GET['editid'];
                                            $examClassesSql = "SELECT ClassName, ExamType FROM tblexamination WHERE ID = :eid AND IsDeleted = 0";
                                            $examClassesQuery = $dbh->prepare($examClassesSql);
                                            $examClassesQuery->bindParam(':eid', $eid, PDO::PARAM_STR);
                                            $examClassesQuery->execute();
                                            $examClassesRow = $examClassesQuery->fetch(PDO::FETCH_OBJ);
                                            $selectedClasses = explode(",", $examClassesRow->ClassName);

                                            if ($query->rowCount() > 0) 
                                            {
                                                $classSql = "SELECT ID, ClassName, Section FROM tblclass WHERE IsDeleted = 0";
                                                $classQuery = $dbh->prepare($classSql);
                                                $classQuery->execute();
                                                $classResults = $classQuery->fetchAll(PDO::FETCH_ASSOC);

                                                foreach ($classResults as $class) 
                                                {
                                                    $selected = in_array($class['ID'], $selectedClasses) ? 'selected' : '';
                                                    echo "<option value='" . htmlentities($class['ID']) . "' $selected>" . htmlentities($class['ClassName']) . "</option>";
                                                }
                                            }
                                            ?>
                                        </select>
                                    </div>

                                    <div class="form-group">
                                        <label>Exam Types</label>
                                        <div class="radio-group d-flex flex-wrap justify-content-start">
                                            <?php
                                            $selectedExamType = isset($examClassesRow->ExamType) ? $examClassesRow->ExamType : "";

                                            $examTypeLabels = [
                                                "Formative" => "formative",
                                                "Summative" => "summative"
                                            ];

                                            foreach ($examTypeLabels as $examTypeLabel => $examTypeId) {
                                                $checked = ($selectedExamType === $examTypeLabel) ? 'checked' : '';
                                                echo "<div class='form-check mr-4'>
                                                        <label class='form-check-label' for='$examTypeId'>
                                                            $examTypeLabel
                                                            <input class='form-check-input' type='radio' name='examType' value='$examTypeLabel' id='$examTypeId' $disabledInput $checked>
                                                        </label>
                                                    </div>";
                                            }
                                            ?>
                                        </div>
                                        <div class="form-group">
                                            <label for="durationFrom">Duration</label>
                                            <div class="d-flex justify-content-between flex-column flex-md-row align-items-center">
                                                <input type="date" name="durationFrom" class="form-control mr-2" id="durationFrom" value="<?php echo isset($examClassesRow->DurationFrom) ? $examClassesRow->DurationFrom : ''; ?>" <?php echo $disabledInput; ?> required>
                                                <span>to</span>
                                                <input type="date" name="durationTo" class="form-control ml-2" id="durationTo" value="<?php echo isset($examClassesRow->DurationTo) ? $examClassesRow->DurationTo : ''; ?>" <?php echo $disabledInput; ?> required>
                                            </div>
                                        </div>
                                    </div>  

                                    <button type="submit" class="btn btn-primary mr-2" name="submit">Update
                                    </button>
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