<?php
session_start();
error_reporting(0);
include('includes/dbconnection.php');

if (strlen($_SESSION['sturecmsaid']) == 0) {
    header('location:logout.php');
} 
else 
{
    $successAlert = false;
    $dangerAlert = false;
    $msg = "";
    try
    {
        if(isset($_POST['confirmDelete']))
        {
            $rid = intval($_POST['curricularID']);

            $sql = "UPDATE tblsubjects SET IsDeleted = 1 WHERE ID = :rid";
            $query = $dbh->prepare($sql);
            $query->bindParam(':rid', $rid, PDO::PARAM_STR);
            $query->execute();
            $successAlert = true;
            $msg = "Subject deleted successfully.";
        }
    }
    catch (PDOException $e) 
    {
        $dangerAlert = true;
        $msg = "Ops! An error occurred while deleting the subject!";
        echo "<script>console.error('Error:---> " . $e->getMessage() . "');</script>";
    }
        
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <title>Tibetan Public School || Manage Curricular</title>
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <!-- plugins:css -->
        <link rel="stylesheet" href="vendors/simple-line-icons/css/simple-line-icons.css">
        <link rel="stylesheet" href="vendors/flag-icon-css/css/flag-icon.min.css">
        <link rel="stylesheet" href="vendors/css/vendor.bundle.base.css">
        <!-- endinject -->
        <!-- Plugin css for this page -->
        <link rel="stylesheet" href="./vendors/daterangepicker/daterangepicker.css">
        <link rel="stylesheet" href="./vendors/chartist/chartist.min.css">
        <!-- End plugin css for this page -->
        <!-- inject:css -->
        <!-- endinject -->
        <!-- Layout styles -->
        <link rel="stylesheet" href="./css/style.css">
        <!-- End layout styles -->
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
                            <h3 class="page-title"> Manage Curricular </h3>
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb">
                                    <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                                    <li class="breadcrumb-item active" aria-current="page"> Manage Curricular</li>
                                </ol>
                            </nav>
                        </div>
                        <div class="row">
                            <div class="col-md-12 grid-margin stretch-card">
                                <div class="card">
                                    <div class="card-body">
                                        <div class="d-sm-flex align-items-center mb-4">
                                            <h4 class="card-title mb-sm-0">Manage Curricular</h4>
                                            <a href="#" class="text-dark ml-auto mb-3 mb-sm-0"> View all Curricular Activities</a>
                                        </div>
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
                                                        <th class="font-weight-bold">S.No</th>
                                                        <th class="font-weight-bold">Curricular Name</th>
                                                        <th class="font-weight-bold">Class Name</th>
                                                        <th class="font-weight-bold">Action</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php
                                                    if (isset($_GET['pageno'])) 
                                                    {
                                                        $pageno = $_GET['pageno'];
                                                    } 
                                                    else 
                                                    {
                                                        $pageno = 1;
                                                    }
                                                    // Get the active session ID
                                                    $getSessionSql = "SELECT session_id FROM tblsessions WHERE is_active = 1 AND IsDeleted = 0";
                                                    $sessionQuery = $dbh->prepare($getSessionSql);
                                                    $sessionQuery->execute();
                                                    $sessionID = $sessionQuery->fetchColumn();

                                                    // Formula for pagination
                                                    $no_of_records_per_page = 15;
                                                    $offset = ($pageno - 1) * $no_of_records_per_page;
                                                    $ret = "SELECT ID, SubjectName, ClassName FROM tblsubjects WHERE SessionID = :sessionID AND IsCurricularSubject = 1 AND IsDeleted = 0 LIMIT $offset, $no_of_records_per_page";
                                                    $query1 = $dbh->prepare($ret);
                                                    $query1->bindParam(':sessionID', $sessionID, PDO::PARAM_INT);
                                                    $query1->execute();
                                                    $results1 = $query1->fetchAll(PDO::FETCH_OBJ);
                                                    $total_rows = $query1->rowCount();
                                                    $total_pages = ceil($total_rows / $no_of_records_per_page);

                                                    $cnt = 1;
                                                    if ($query1->rowCount() > 0) {
                                                        foreach ($results1 as $row) {
                                                    ?>
                                                            <tr>
                                                                <td><?php echo htmlentities($cnt);?></td>
                                                                <td><?php echo htmlentities($row->SubjectName);?></td>
                                                                <td>
                                                                    <?php
                                                                    // Fetch class names based on IDs stored in ClassName column
                                                                    $classIds = explode(",", $row->ClassName);
                                                                    $classNames = [];

                                                                    foreach ($classIds as $classId) 
                                                                    {
                                                                        $classSql = "SELECT ClassName FROM tblclass WHERE ID = :classId AND IsDeleted = 0";
                                                                        $classQuery = $dbh->prepare($classSql);
                                                                        $classQuery->bindParam(':classId', $classId, PDO::PARAM_STR);
                                                                        $classQuery->execute();
                                                                        $className = $classQuery->fetchColumn();
                                                                        if ($className) 
                                                                        {
                                                                            $classNames[] = $className;
                                                                        }
                                                                        
                                                                    }
                                                                    if(empty($classNames))
                                                                    {
                                                                        echo "N.A";
                                                                    }
                                                                    echo implode(", ", $classNames);
                                                                    ?>
                                                                </td>
                                                                <td>
                                                                    <div>
                                                                        <a href="edit-curricular-details.php?editid=<?php echo htmlentities($row->ID);?>"><i class="icon-pencil"></i></a>
                                                                        || <a href="" onclick="setDeleteId(<?php echo ($row->ID);?>)" data-toggle="modal" data-target="#confirmationModal">
                                                                                <i class="icon-trash"></i>
                                                                            </a>
                                                                    </div>
                                                                </td>
                                                            </tr>
                                                    <?php $cnt = $cnt + 1;
                                                        }
                                                    }
                                                    ?>
                                                </tbody>
                                            </table>
                                        </div>
                                        <!-- **********Pagination********** -->
                                        <div align="left">
                                            <ul class="pagination" >
                                                <li><a href="?pageno=1"><strong>First></strong></a></li>
                                                <li class="<?php if($pageno <= 1){ echo 'disabled'; } ?>">
                                                    <a href="<?php if($pageno <= 1){ echo '#'; } else { echo "?pageno=".($pageno - 1); } ?>"><strong style="padding-left: 10px">Prev></strong></a>
                                                </li>
                                                <li class="<?php if($pageno >= $total_pages){ echo 'disabled'; } ?>">
                                                    <a href="<?php if($pageno >= $total_pages){ echo '#'; } else { echo "?pageno=".($pageno + 1); } ?>"><strong style="padding-left: 10px">Next></strong></a>
                                                </li>
                                                <li><a href="?pageno=<?php echo $total_pages; ?>"><strong style="padding-left: 10px">Last</strong></a></li>
                                            </ul>
                                        </div>
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
            <!-- Confirmation Modal (Delete) -->
            <div class="modal fade" id="confirmationModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" data-backdrop="static" data-keyboard="false">
                <div class="modal-dialog">
                    <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title" id="myModalLabel">Confirmation</h4>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    </div>
                    <div class="modal-body">
                        Are you sure you want to delete this Curricular Activity?
                    </div>
                    <div class="modal-footer">
                        <form id="deleteForm" action="" method="post">
                        <input type="hidden" name="curricularID" id="curricularID">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary" name="confirmDelete">Delete</button>
                        </form>
                    </div>
                    </div>
                </div>
            </div>
        <!-- container-scroller -->
        <!-- plugins:js -->
        <script src="vendors/js/vendor.bundle.base.js"></script>
        <!-- endinject -->
        <!-- Plugin js for this page -->
        <script src="./vendors/chart.js/Chart.min.js"></script>
        <script src="./vendors/moment/moment.min.js"></script>
        <script src="./vendors/daterangepicker/daterangepicker.js"></script>
        <script src="./vendors/chartist/chartist.min.js"></script>
        <!-- End plugin js for this page -->
        <!-- inject:js -->
        <script src="js/off-canvas.js"></script>
        <script src="js/misc.js"></script>
        <!-- endinject -->
        <!-- Custom js for this page -->
        <script src="./js/dashboard.js"></script>
        <script src="./js/manageAlert.js"></script>
        <!-- End custom js for this page -->
        <script>
            function setDeleteId(id) 
            {
                document.getElementById('curricularID').value = id;
            }
        </script>
    </body>
</html>
<?php

}  
?>