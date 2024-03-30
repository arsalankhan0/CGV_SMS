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

    // Get the active session ID
    $getSessionSql = "SELECT session_id FROM tblsessions WHERE is_active = 1 AND IsDeleted = 0";
    $sessionQuery = $dbh->prepare($getSessionSql);
    $sessionQuery->execute();
    $sessionID = $sessionQuery->fetchColumn();

    try 
    {
        if (isset($_POST['submit'])) 
        {
            $curricularName = filter_var($_POST['curricularName'], FILTER_SANITIZE_STRING);
            $classes = isset($_POST['classes']) ? $_POST['classes'] : [];

            if (empty($curricularName) || empty($classes)) 
            {
                $dangerAlert = true;
                $msg = "Please enter Curricular Name and select at least one class!";
            } 
            else 
            {
                // Fetch IDs of selected classes
                $selectedClassIds = [];
                foreach ($classes as $className) 
                {
                    $classSql = "SELECT ID FROM tblclass WHERE ClassName = :className";
                    $classQuery = $dbh->prepare($classSql);
                    $classQuery->bindParam(':className', $className, PDO::PARAM_STR);
                    $classQuery->execute();
                    $classId = $classQuery->fetchColumn();
                    if ($classId) 
                    {
                        $selectedClassIds[] = $classId;
                    }
                }

                $cName = implode(",", $selectedClassIds);
                $curricularSubject = 1; 

                // Insert curricular with comma-separated class IDs, active session ID
                $sql = "INSERT INTO tblsubjects (SubjectName, ClassName, SessionID, IsCurricularSubject) VALUES (:subjectName, :cName, :sessionID, :isCurricularSubject)";
                $query = $dbh->prepare($sql);
                $query->bindParam(':subjectName', $curricularName, PDO::PARAM_STR);
                $query->bindParam(':cName', $cName, PDO::PARAM_STR);
                $query->bindParam(':sessionID', $sessionID, PDO::PARAM_INT);
                $query->bindParam(':isCurricularSubject', $curricularSubject, PDO::PARAM_INT);
                $query->execute();
                $LastInsertId = $dbh->lastInsertId();

                if ($LastInsertId > 0) 
                {
                    $successAlert = true;
                    $msg = "Curricular has been created successfully!";
                } 
                else 
                {
                    $dangerAlert = true;
                    $msg = "Something went wrong! Please try again later.";
                }
            }
        }
    }
    catch (PDOException $e) 
    {
        $dangerAlert = true;
        $msg = "Ops! An error occurred.";
        echo "<script>console.error('Error:---> ".$e->getMessage()."');</script>";
    }
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <title>Tibetan Public School || Create Curricular</title>
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
            <?php include_once('includes/header.php');?>
            <!-- partial -->
            <div class="container-fluid page-body-wrapper">
            <!-- partial:partials/_sidebar.html -->
            <?php include_once('includes/sidebar.php');?>
            <!-- partial -->
            <div class="main-panel">
                <div class="content-wrapper">
                    <div class="page-header">
                        <h3 class="page-title"> Create Curricular </h3>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                                <li class="breadcrumb-item active" aria-current="page"> Create Curricular</li>
                            </ol>
                        </nav>
                    </div>
                    <div class="row">
                        <div class="col-12 grid-margin stretch-card">
                            <div class="card">
                                <div class="card-body">
                                    <h4 class="card-title" style="text-align: center;">Create Curricular</h4>
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
                                            <label for="exampleInputName1">Curricular Name</label>
                                            <input type="text" name="curricularName" value="" id="input-subject" class="form-control" required='true'>
                                        </div>

                                        <div class="form-group">
                                            <label for="exampleFormControlSelect2">Assign Classes</label>
                                            <select multiple="multiple" name="classes[]"
                                                    class="js-example-basic-multiple w-100" required="true">
                                                <?php
                                                $sql = "SELECT * FROM tblclass WHERE IsDeleted = 0";
                                                $query = $dbh->prepare($sql);
                                                $query->execute();
                                                $row = $query->fetch(PDO::FETCH_OBJ);

                                                if ($query->rowCount() > 0) 
                                                {
                                                    $classSql = "SELECT DISTINCT ClassName FROM tblclass WHERE IsDeleted = 0";
                                                    $classQuery = $dbh->prepare($classSql);
                                                    $classQuery->execute();
                                                    $classResults = $classQuery->fetchAll(PDO::FETCH_COLUMN);

                                                    foreach ($classResults as $className) 
                                                    {
                                                        echo "<option value='" . htmlentities($className) . "'>" . htmlentities($className) . "</option>";
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
                <?php include_once('includes/footer.php');?>
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
    <script src="./js/manageAlert.js"></script>
  </body>
</html><?php }  ?>