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
    $successAlert = false;
    $dangerAlert = false;
    $msg = "";
    try 
    {
        if (isset($_POST['submit'])) 
        {
            $examName = filter_var($_POST['examName'], FILTER_SANITIZE_STRING);
            $classIDs = isset($_POST['classes']) ? $_POST['classes'] : [];

            if (empty($examName) || empty($classIDs)) 
            {
                $msg = "Please enter Exam Name and select at least one class";
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

                    $sql = "INSERT INTO tblexamination (ExamName, ClassName) VALUES (:examName, :classNames)";
                    $query = $dbh->prepare($sql);
                    $query->bindParam(':examName', $examName, PDO::PARAM_STR);
                    $query->bindParam(':classNames', $classNames, PDO::PARAM_STR);
                    $query->execute();

                    $msg = "Exam has been added successfully.";
                    $successAlert = true;
                }
            }
        }
    } 
    catch (PDOException $e) 
    {
        // error_log($e->getMessage()); //-->This is only for debugging purpose
        $msg = "Ops! An Error occurred.";
        $dangerAlert = true;
    }

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Student Management System|| Add Exam</title>
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
