<?php
session_start();
error_reporting(0);
include('includes/dbconnection.php');

// Define permissions array
$requiredPermissions = array(
    'add-exam' => 'Examination',
);

if (strlen($_SESSION['sturecmsEMPid'] == 0)) 
{
    header('location:logout.php');
} 
else 
{
    // Check if the employee has the required permission for this file
    $eid = $_SESSION['sturecmsEMPid'];
    $sql = "SELECT * FROM tblemployees WHERE ID=:eid";
    $query = $dbh->prepare($sql);
    $query->bindParam(':eid', $eid, PDO::PARAM_STR);
    $query->execute();
    $results = $query->fetch(PDO::FETCH_ASSOC);

    $employeeRole = $results['Role'];
    $requiredPermission = $requiredPermissions['add-exam']; 

    $sqlPermissions = "SELECT * FROM tblpermissions WHERE RoleID=:employeeRole AND Name=:requiredPermission";
    $queryPermissions = $dbh->prepare($sqlPermissions);
    $queryPermissions->bindParam(':employeeRole', $employeeRole, PDO::PARAM_STR);
    $queryPermissions->bindParam(':requiredPermission', $requiredPermission, PDO::PARAM_STR);
    $queryPermissions->execute();
    $permissions = $queryPermissions->fetch(PDO::FETCH_ASSOC);

    if (!$permissions || $permissions['CreatePermission'] != 1) 
    {
        echo "<h1>You have no permission to access this page!</h1>";
        exit;
    }

    $successAlert = false;
    $dangerAlert = false;
    $msg = "";
    try 
    {
        if (isset($_POST['submit'])) 
        {
            $examName = filter_var($_POST['examName'], FILTER_SANITIZE_STRING);
            $classIDs = isset($_POST['classes']) ? $_POST['classes'] : [];
            $examType = isset($_POST['examType']) ? $_POST['examType'] : '';
            $durationFrom = isset($_POST['durationFrom']) ? $_POST['durationFrom'] : '';
            $durationTo = isset($_POST['durationTo']) ? $_POST['durationTo'] : '';


            if (empty($examName) || empty($classIDs) || empty($examType)) 
            {
                $msg = "Please fill up all the fields!";
                $dangerAlert = true;
            } 
            else 
            {
                $checkSql = "SELECT ID FROM tblexamination WHERE IsDeleted = 0 AND ExamName = :examName";
                $checkQuery = $dbh->prepare($checkSql);
                $checkQuery->bindParam(':examName', $examName, PDO::PARAM_STR);
                $checkQuery->execute();
                $examId = $checkQuery->fetchColumn();

                if ($examId > 0) 
                {
                    $msg = "Exam already exists! Please update the existing exam.";
                    $dangerAlert = true;
                } 
                else 
                {
                    $classNames = implode(",", $classIDs);

                    $sql = "INSERT INTO tblexamination (ExamName, ClassName, ExamType, DurationFrom, DurationTo) 
                            VALUES (:examName, :classNames, :examType, :durationFrom, :durationTo)";
                    $query = $dbh->prepare($sql);
                    $query->bindParam(':examName', $examName, PDO::PARAM_STR);
                    $query->bindParam(':classNames', $classNames, PDO::PARAM_STR);
                    $query->bindParam(':examType', $examType, PDO::PARAM_STR);
                    $query->bindParam(':durationFrom', $durationFrom, PDO::PARAM_STR);
                    $query->bindParam(':durationTo', $durationTo, PDO::PARAM_STR);
                    $query->execute();

                    $msg = "Exam has been added successfully.";
                    $successAlert = true;
                }
            }
        }
    } 
    catch (PDOException $e) 
    {
        $msg = "Ops! An Error occurred.";
        $dangerAlert = true;
        echo "<script>console.error('Error:---> " . $e->getMessage() . "');</script>";
    }

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>TPS || Add Exam</title>
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
                    <h3 class="page-title"> Add Exam </h3>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                            <li class="breadcrumb-item active" aria-current="page"> Add Exam</li>
                        </ol>
                    </nav>
                </div>
                <div class="row">
                    <div class="col-12 grid-margin stretch-card">
                        <div class="card">
                            <div class="card-body">
                                <h4 class="card-title" style="text-align: center;">Add Exam</h4>
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
                                    <div class="form-group">
                                        <label for="exampleInputName1">Exam Name</label>
                                        <input type="text" name="examName" value="" class="form-control" required='true'>
                                    </div>
                                    <div class="form-group">
                                        <label for="exampleFormControlSelect2">Select Classes for exam</label>
                                        <select multiple="multiple" name="classes[]"
                                                class="js-example-basic-multiple w-100">
                                            <?php
                                            $sql = "SELECT * FROM tblclass WHERE IsDeleted = 0";
                                            $query = $dbh->prepare($sql);
                                            $query->execute();

                                            if ($query->rowCount() > 0) {
                                                $classResults = $query->fetchAll(PDO::FETCH_ASSOC);

                                                foreach ($classResults as $class) {
                                                    $classNameWithSection = $class['ClassName'];
                                                    echo "<option value='" . htmlentities($class['ID']) . "'>" . htmlentities($classNameWithSection) . "</option>";                                                
                                                }
                                            }
                                            ?>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                    <label>Exam Types</label>
                                        <div class="radio-group d-flex flex-wrap justify-content-start">
                                            <div class="form-check mr-4 mb-2">
                                                <label class="form-check-label" for="formative">
                                                    Formative Assessment
                                                    <input class="form-check-input" type="radio" name="examType" value="Formative" id="formative">
                                                </label>
                                            </div>
                                            <div class="form-check mr-4 mb-2">
                                                <label class="form-check-label" for="summative">
                                                    Summative Assessment
                                                    <input class="form-check-input" type="radio" name="examType" value="Summative" id="summative">
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="durationFrom">Duration</label>
                                        <div class="d-flex justify-content-between flex-column flex-md-row align-items-center">
                                            <input type="date" name="durationFrom" class="form-control mr-2" id="durationFrom" required>
                                            <span>to</span>
                                            <input type="date" name="durationTo" class="form-control ml-2" id="durationTo" required>
                                        </div>
                                    </div>
                                    <button type="submit" class="btn btn-primary mr-2" name="submit">Add</button>
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
<?php } ?>
