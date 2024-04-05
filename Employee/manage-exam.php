<?php
session_start();
error_reporting(0);
include('includes/dbconnection.php');

// Define permissions array
$requiredPermissions = array(
    'manage-exam' => 'Examination',
);

if (strlen($_SESSION['sturecmsEMPid'])==0) 
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
    $requiredPermission = $requiredPermissions['manage-exam']; 

    $sqlPermissions = "SELECT * FROM tblpermissions WHERE RoleID=:employeeRole AND Name=:requiredPermission";
    $queryPermissions = $dbh->prepare($sqlPermissions);
    $queryPermissions->bindParam(':employeeRole', $employeeRole, PDO::PARAM_STR);
    $queryPermissions->bindParam(':requiredPermission', $requiredPermission, PDO::PARAM_STR);
    $queryPermissions->execute();
    $permissions = $queryPermissions->fetch(PDO::FETCH_ASSOC);

    if (!$permissions || $permissions['ReadPermission'] != 1) 
    {
        echo "<h1>You have no permission to access this page!</h1>";
        exit;
    }

    $successAlert = false;
    $dangerAlert = false;
    $msg = "";

    $examPublished = false;
    $resultPublished = false;

    // Get the active session ID
    $getSessionSql = "SELECT session_id FROM tblsessions WHERE is_active = 1 AND IsDeleted = 0";
    $sessionQuery = $dbh->prepare($getSessionSql);
    $sessionQuery->execute();
    $sessionID = $sessionQuery->fetchColumn();

    try
    {
        // Code for deletion
        if(isset($_POST['confirmDelete']))
        {
            $rid = intval($_POST['examID']);

            // Check if the exam is published
            $checkPublishedSql = "SELECT IsPublished FROM tblexamination WHERE ID = :examId AND IsDeleted = 0";
            $checkPublishedQuery = $dbh->prepare($checkPublishedSql);
            $checkPublishedQuery->bindParam(':examId', $rid, PDO::PARAM_STR);
            $checkPublishedQuery->execute();
            $examPublished = $checkPublishedQuery->fetch(PDO::FETCH_ASSOC);

            if ($examPublished && $examPublished['IsPublished'] == 1) 
            {
                $msg = "Cannot delete the published exam!";
                $dangerAlert = true;
            } 
            else 
            {
                // Check if there are records in tblreports for this exam
                $checkReportsSql = "SELECT COUNT(*) FROM tblreports WHERE ExamName = :examName";
                $checkReportsQuery = $dbh->prepare($checkReportsSql);
                $checkReportsQuery->bindParam(':examName', $rid, PDO::PARAM_STR);
                $checkReportsQuery->execute();
                $reportCount = $checkReportsQuery->fetchColumn();

                if ($reportCount > 0) 
                {
                    $msg = "Cannot delete exam as there are records associated with it!";
                    $dangerAlert = true;
                } 
                else 
                {
                    $sql = "UPDATE tblexamination SET IsDeleted = 1 WHERE ID = :rid";
                    $query = $dbh->prepare($sql);
                    $query->bindParam(':rid', $rid, PDO::PARAM_STR);
                    $query->execute();

                    $msg = "Exam deleted successfully.";
                    $successAlert = true;
                }
            }
        }
    }
    catch(PDOException $e)
    {
        $dangerAlert = true;
        $msg = "Ops! An error occurred while deleting the exam.";
        echo "<script>console.error('Error:---> " . $e->getMessage() . "');</script>";
    }

    try
    {
        if (isset($_POST['publish'])) 
        {
            // Get values from the submitted form
            $examId = $_POST['exam_id'];
        
            // Check if already published
            $checkPublishedSql = "SELECT IsPublished, session_id FROM tblexamination WHERE ID = :examId AND IsDeleted = 0";
            $checkPublishedQuery = $dbh->prepare($checkPublishedSql);
            $checkPublishedQuery->bindParam(':examId', $examId, PDO::PARAM_STR);
            $checkPublishedQuery->execute();
            $publish = $checkPublishedQuery->fetch(PDO::FETCH_ASSOC);
        
            if ($publish && $publish['IsPublished'] === 1 && $publish['session_id'] === $sessionID) 
            {
                $msg = "Exam Already published!";
                $dangerAlert = true;
            } 
            else 
            {
                // Update IsPublished
                $updateSql = "UPDATE tblexamination SET IsPublished = 1, session_id = :session_id WHERE ID = :examId";
                $updateQuery = $dbh->prepare($updateSql);
                $updateQuery->bindParam(':session_id', $sessionID, PDO::PARAM_STR);
                $updateQuery->bindParam(':examId', $examId, PDO::PARAM_STR);
                $updateQuery->execute();
        
                $msg = "Exam published successfully.";
                $successAlert = true;
            }
        }
    }
    catch(PDOException $e)
    {
        $dangerAlert = true;
        $msg = "Ops! An error occurred while publishing the exam.";
        echo "<script>console.error('Error:---> " . $e->getMessage() . "');</script>";
    }

    try
    {
        if (isset($_POST['publish_result'])) 
        {
            // Get values from the submitted form
            $examId = $_POST['exam_id'];

            // Check if result is already published
            $checkResultPublishedSql = "SELECT IsResultPublished FROM tblexamination WHERE ID = :examId AND IsDeleted = 0";
            $checkResultPublishedQuery = $dbh->prepare($checkResultPublishedSql);
            $checkResultPublishedQuery->bindParam(':examId', $examId, PDO::PARAM_STR);
            $checkResultPublishedQuery->execute();
            $resultPublished = $checkResultPublishedQuery->fetch(PDO::FETCH_ASSOC);

            if ($resultPublished && $resultPublished['IsResultPublished'] == 1) 
            {
                $msg = "Result Already Published!";
                $dangerAlert = true;
            } 
            else 
            {
                // Check if exam is published
                $checkPublishedSql = "SELECT IsPublished, session_id FROM tblexamination WHERE ID = :examId AND IsDeleted = 0";
                $checkPublishedQuery = $dbh->prepare($checkPublishedSql);
                $checkPublishedQuery->bindParam(':examId', $examId, PDO::PARAM_STR);
                $checkPublishedQuery->execute();
                $publish = $checkPublishedQuery->fetch(PDO::FETCH_ASSOC);

                if ($publish && $publish['IsPublished'] == 1 && $publish['session_id'] == $sessionID) 
                {
                    // Update IsResultPublished
                    $updatePublishResultSql = "UPDATE tblexamination SET IsResultPublished = 1, session_id = :session_id WHERE ID = :examId";
                    $updatePublishResultQuery = $dbh->prepare($updatePublishResultSql);
                    $updatePublishResultQuery->bindParam(':session_id', $sessionID, PDO::PARAM_STR);
                    $updatePublishResultQuery->bindParam(':examId', $examId, PDO::PARAM_STR);
                    $updatePublishResultQuery->execute();

                    $msg = "Result Published Successfully.";
                    $successAlert = true;
                } 
                else 
                {
                    $msg = "Please Publish the Exam first!";
                    $dangerAlert = true;
                }
            }
        }
    }
    catch(PDOException $e)
    {
        $dangerAlert = true;
        $msg = "Ops! An error occurred while publishing the result.";
    }


        // For Role
        $eid = $_SESSION['sturecmsEMPid'];
        $sql = "SELECT * FROM tblemployees WHERE ID=:eid";
        $query = $dbh->prepare($sql);
        $query->bindParam(':eid', $eid, PDO::PARAM_STR);
        $query->execute();
        $results = $query->fetchAll(PDO::FETCH_OBJ);
    
        $employeeRole = $results[0]->Role;
    
        // Fetch permissions for the logged-in user
        $sqlPermissions = "SELECT * FROM tblpermissions WHERE RoleID=:employeeRole";
        $queryPermissions = $dbh->prepare($sqlPermissions);
        $queryPermissions->bindParam(':employeeRole', $employeeRole, PDO::PARAM_STR);
        $queryPermissions->execute();
        $permissions = $queryPermissions->fetchAll(PDO::FETCH_OBJ);
    
        $employeePermissions = array();
    
        // Populate the $employeePermissions array with permission names
        foreach ($permissions as $permission) 
        {
            $employeePermissions[$permission->Name] = array(
                'UpdatePermission' => $permission->UpdatePermission,
                'DeletePermission' => $permission->DeletePermission,
            );
        }
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <title>TPS || Manage Exam</title>
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
                    <h3 class="page-title"> Manage Exam </h3>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                            <li class="breadcrumb-item active" aria-current="page"> Manage Exam</li>
                        </ol>
                    </nav>
                </div>
                <div class="row">
                    <div class="col-md-12 grid-margin stretch-card">
                        <div class="card">
                            <div class="card-body">
                                <div class="d-sm-flex align-items-center mb-4">
                                    <h4 class="card-title mb-sm-0">Manage Exam</h4>
                                    <a href="#" class="text-dark ml-auto mb-3 mb-sm-0"> View all Exams</a>
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
                                                <th class="font-weight-bold">Exam Name</th>
                                                <th class="font-weight-bold">Class Name</th>
                                                <th class="font-weight-bold">Creation Date</th>
                                                <?php 
                                                        // Check if the user has UpdatePermission or DeletePermission
                                                        if ($employeePermissions['Examination']['UpdatePermission'] == 1 || $employeePermissions['Examination']['DeletePermission'] == 1) 
                                                        { ?>

                                                        <th class="font-weight-bold">Action</th>
                                                        
                                                        <?php } ?>
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
                                                // Formula for pagination
                                                $no_of_records_per_page = 15;
                                                $offset = ($pageno-1) * $no_of_records_per_page;
                                                $ret = "SELECT ID FROM tblexamination";
                                                $query1 = $dbh -> prepare($ret);
                                                $query1->execute();
                                                $results1=$query1->fetchAll(PDO::FETCH_OBJ);
                                                $total_rows=$query1->rowCount();
                                                $total_pages = ceil($total_rows / $no_of_records_per_page);
                                                $sql = "SELECT * FROM tblexamination WHERE IsDeleted = 0 LIMIT $offset, $no_of_records_per_page";
                                                $query = $dbh -> prepare($sql);
                                                $query->execute();
                                                $results=$query->fetchAll(PDO::FETCH_OBJ);

                                                $cnt=1;
                                                if($query->rowCount() > 0)
                                                {
                                                    foreach($results as $row)
                                                    {
                                                        // Check if the exam is published
                                                        $checkPublishedSql = "SELECT IsPublished, IsResultPublished FROM tblexamination WHERE ID = :examId AND IsDeleted = 0";
                                                        $checkPublishedQuery = $dbh->prepare($checkPublishedSql);
                                                        $checkPublishedQuery->bindParam(':examId', $row->ID, PDO::PARAM_STR);
                                                        $checkPublishedQuery->execute();
                                                        $publishStatus = $checkPublishedQuery->fetch(PDO::FETCH_ASSOC);

                                                        if ($publishStatus) 
                                                        {
                                                            $examPublished = $publishStatus['IsPublished'] == 1;
                                                            $resultPublished = $publishStatus['IsResultPublished'] == 1;
                                                        }
                                                        ?>   
                                                        <tr>
                                                            <td><?php echo htmlentities($cnt);?></td>
                                                            <td><?php  echo htmlentities($row->ExamName);?></td>
                                                            <td>
                                                                <?php
                                                                    $classIds = explode(",", $row->ClassName);

                                                                    for ($i = 0; $i < count($classIds); $i++) 
                                                                    {
                                                                        $classId = $classIds[$i];
                                                                        $classSql = "SELECT ClassName, Section FROM tblclass WHERE ID = :classId AND IsDeleted = 0";
                                                                        $classQuery = $dbh->prepare($classSql);
                                                                        $classQuery->bindParam(':classId', $classId, PDO::PARAM_STR);
                                                                        $classQuery->execute();
                                                                    
                                                                        if ($classQuery) 
                                                                        {
                                                                            $classInfo = $classQuery->fetch(PDO::FETCH_ASSOC);
                                                                    
                                                                            if ($classInfo) 
                                                                            {
                                                                                echo htmlentities($classInfo['ClassName']);
                                                                    
                                                                                if ($i < count($classIds) - 1) 
                                                                                {
                                                                                    echo ", ";
                                                                                }
                                                                            } 
                                                                            else 
                                                                            {
                                                                                echo ""; 
                                                                            }
                                                                        } 
                                                                        else 
                                                                        {
                                                                            echo "Error fetching class"; 
                                                                        }
                                                                    }
                                                                    
                                                                ?>
                                                            </td>
                                                            <td><?php  echo htmlentities($row->CreationDate);?></td>

                                                                <?php 
                                                                // Check if the user has UpdatePermission or DeletePermission
                                                                if ($employeePermissions['Examination']['UpdatePermission'] == 1 || $employeePermissions['Examination']['DeletePermission'] == 1) 
                                                                { ?>
                                                                <td>
                                                                    <div>
                                                                    <?php 
                                                                        // Check if the user has UpdatePermission
                                                                        if ($employeePermissions['Examination']['UpdatePermission'] == 1) { ?>
                                                                            <a href="view-exam-detail.php?editid=<?php echo htmlentities ($row->ID);?>"><i class="icon-pencil"></i></a>
                                                                        <?php 
                                                                        } 
                                                                        if ($employeePermissions['Examination']['UpdatePermission'] == 1 && $employeePermissions['Examination']['DeletePermission'] == 1) 
                                                                        { ?>
                                                                        ||
                                                                        <?php
                                                                        }
                                                                        // Check if the user has DeletePermission
                                                                        if ($employeePermissions['Examination']['DeletePermission'] == 1) { ?>
                                                                            <a href="" onclick="setDeleteId(<?php echo ($row->ID);?>)" data-toggle="modal" data-target="#confirmationModal">
                                                                                    <i class="icon-trash"></i>
                                                                            </a>
                                                                        <?php } ?>
                                                                    </div>
                                                                </td> 
                                                                <?php }?>
                                                                


                                                                    <td>
                                                                        <form method="post" action="">
                                                                            <input type="hidden" name="exam_id" value="<?php echo htmlentities($row->ID); ?>">

                                                                            
                                                                            <!-- <?php
                                                                            if($examPublished)
                                                                            {
                                                                            ?>
                                                                                <button type="button" class="btn-sm btn-secondary text-muted font-weight-bold border-0" disabled>
                                                                                    Exam Published
                                                                                </button>
                                                                            <?php
                                                                            }
                                                                            else
                                                                            {
                                                                            ?>
                                                                                <button type="button" class="btn-sm btn-dark" data-toggle="modal" data-target="#confirmPublishModal_<?php echo $row->ID; ?>">
                                                                                    publish Exam
                                                                                </button>
                                                                            <?php   
                                                                            }
                                                                            if($resultPublished)
                                                                            {
                                                                            ?>
                                                                                <button type="button" class="btn-sm btn-secondary text-muted font-weight-bold border-0" disabled>
                                                                                    Result Published
                                                                                </button>
                                                                            <?php
                                                                            }
                                                                            else
                                                                            {
                                                                            ?>
                                                                                <button type="button" class="btn-sm btn-dark" data-toggle="modal" data-target="#confirmResultModal_<?php echo $row->ID; ?>">
                                                                                    Publish Result
                                                                                </button>
                                                                            <?php
                                                                            }
                                                                            ?> -->


                                                                            <!-- Confirmation Modal (Publish Exam) -->
                                                                            <div class="modal fade" id="confirmPublishModal_<?php echo $row->ID; ?>" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" data-backdrop="static" data-keyboard="false">
                                                                                <div class="modal-dialog">
                                                                                    <div class="modal-content">
                                                                                    <div class="modal-header">
                                                                                        <h4 class="modal-title" id="myModalLabel">Confirmation</h4>
                                                                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                                                                    </div>
                                                                                    <div class="modal-body">
                                                                                        Are you sure you want to Publish <strong><?php echo $row->ExamName; ?></strong> Exam ?
                                                                                    </div>
                                                                                    <div class="modal-footer">
                                                                                        <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                                                                                        <button type="submit" class="btn btn-primary" name="publish">Publish</button>
                                                                                    </div>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                            <!-- Confirmation Modal (Publish Result) -->
                                                                            <div class="modal fade" id="confirmResultModal_<?php echo $row->ID; ?>" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" data-backdrop="static" data-keyboard="false">
                                                                                <div class="modal-dialog">
                                                                                    <div class="modal-content">
                                                                                    <div class="modal-header">
                                                                                        <h4 class="modal-title" id="myModalLabel">Confirmation</h4>
                                                                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                                                                    </div>
                                                                                    <div class="modal-body">
                                                                                        Are you sure you want to Publish Result of <strong><?php echo $row->ExamName; ?></strong> ?
                                                                                    </div>
                                                                                    <div class="modal-footer">
                                                                                        <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                                                                                        <button type="submit" class="btn btn-primary" name="publish_result">Publish</button>
                                                                                    </div>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                        </form>  
                                                                    </td>        
                                                                    
                                                        </tr>
                                                        <?php $cnt=$cnt+1;
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
    <!-- container-scroller -->
        <!-- Confirmation Modal (Delete) -->
        <div class="modal fade" id="confirmationModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" data-backdrop="static" data-keyboard="false">
            <div class="modal-dialog">
                <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="myModalLabel">Confirmation</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body">
                    Are you sure you want to delete this record?
                </div>
                <div class="modal-footer">
                    <form id="deleteForm" action="" method="post">
                    <input type="hidden" name="examID" id="examID">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" name="confirmDelete">Delete</button>
                    </form>
                </div>
                </div>
            </div>
        </div>


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
            document.getElementById('examID').value = id;
        }
    </script>
</body>
</html>
<?php 
}  
?>