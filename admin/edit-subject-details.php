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
    $eid = $_GET['editid'];

    try {
        if (isset($_POST['submit']) && isset($_GET['editid']) && !empty($_GET['editid'])) 
        {
            $cName = isset($_POST['classes']) ? implode(",", $_POST['classes']) : '';

            if (!empty($cName)) 
            {
                $sql = "UPDATE tblsubjects SET ClassName=:cName WHERE ID=:eid";
                $query = $dbh->prepare($sql);
                $query->bindParam(':cName', $cName, PDO::PARAM_STR);
                $query->bindParam(':eid', $eid, PDO::PARAM_STR);
                $query->execute();

                echo '<script>alert("Subjects has been updated")</script>';
            } 
            else 
            {
                echo '<script>alert("Please select at least one class")</script>';
            }
        }
    } 
    catch (PDOException $e) 
    {
        echo '<script>alert("Ops! An Error occurred.")</script>';
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>

    <title>Student Management System|| Manage Subjects</title>
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

                    $subjectNameSql = "SELECT SubjectName FROM tblsubjects WHERE ID = :eid";
                    $subjectNameQuery = $dbh->prepare($subjectNameSql);
                    $subjectNameQuery->bindParam(':eid', $eid, PDO::PARAM_STR);
                    $subjectNameQuery->execute();
                    $subjectNameRow = $subjectNameQuery->fetch(PDO::FETCH_OBJ);
                    $subjectName = $subjectNameRow->SubjectName;

                    if (isset($subjectName)) { ?>
                        <h3 class="page-title"> Manage Subject - Assign Classes for '<?php echo $subjectName; ?>'</h3>
                    <?php } else { ?>
                        <h3 class="page-title"> Manage Subject </h3>
                    <?php } ?>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                            <li class="breadcrumb-item active" aria-current="page"> Manage Subject</li>
                        </ol>
                    </nav>
                </div>
                <div class="row">
                    <div class="col-12 grid-margin stretch-card">
                        <div class="card">
                            <div class="card-body">
                                <h4 class="card-title" style="text-align: center;">Manage Subject</h4>
                                <form class="forms-sample" method="post">
                                    <div class="form-group">
                                        <label for="exampleFormControlSelect2">Assign Classes to <?php echo $subjectName; ?> subject</label>
                                        <select multiple="multiple" name="classes[]"
                                                class="js-example-basic-multiple w-100">
                                            <?php
                                            $eid = $_GET['editid'];
                                            $subjectClassesSql = "SELECT ClassName FROM tblsubjects WHERE ID = :eid";
                                            $subjectClassesQuery = $dbh->prepare($subjectClassesSql);
                                            $subjectClassesQuery->bindParam(':eid', $eid, PDO::PARAM_STR);
                                            $subjectClassesQuery->execute();
                                            $subjectClassesRow = $subjectClassesQuery->fetch(PDO::FETCH_OBJ);
                                            $selectedClasses = explode(",", $subjectClassesRow->ClassName);

                                            if ($query->rowCount() > 0) 
                                            {
                                                $classSql = "SELECT DISTINCT ClassName FROM tblclass";
                                                $classQuery = $dbh->prepare($classSql);
                                                $classQuery->execute();
                                                $classResults = $classQuery->fetchAll(PDO::FETCH_COLUMN);

                                                foreach ($classResults as $className) 
                                                {
                                                    $selected = in_array($className, $selectedClasses) ? 'selected' : '';
                                                    echo "<option value='" . htmlentities($className) . "' $selected>" . htmlentities($className) . "</option>";
                                                }
                                            }
                                            ?>
                                        </select>
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
<!-- End custom js for this page -->
</body>
</html>
<?php
}
?>
