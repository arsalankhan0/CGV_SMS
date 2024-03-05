<?php
session_start();
error_reporting(0);
include('includes/dbconnection.php');

// Define permissions array
$requiredPermissions = array(
    'manage-section' => 'Sections',
);


if (strlen($_SESSION['sturecmsEMPid']==0)) 
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
    $requiredPermission = $requiredPermissions['manage-section']; 

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
    try
    {
        if(isset($_POST['confirmDelete']))
        {
            $rid = $_POST['sectionID'];

            $checkSectionSql = "SELECT COUNT(*) FROM tblclass WHERE FIND_IN_SET(:rid, Section)";
            $checkSectionQuery = $dbh->prepare($checkSectionSql);
            $checkSectionQuery->bindParam(':rid', $rid, PDO::PARAM_STR);
            $checkSectionQuery->execute();
            $SectionCount = $checkSectionQuery->fetchColumn();

            if ($SectionCount > 0) 
            {
                $msg = "There are associated records with this Section! Please remove the Section from associated record first to delete this Section.";
                $dangerAlert = true;
            } 
            else 
            {
                $sql = "UPDATE tblsections SET IsDeleted = 1 WHERE ID = :rid";
                $query = $dbh->prepare($sql);
                $query->bindParam(':rid', $rid, PDO::PARAM_STR);
                $query->execute();
                
                $msg = "Section Deleted Successfully!";
                $successAlert = true;
            }
        }
    }
    catch(PDOException $e)
    {
        $msg = "Ops! An error occurred while deleting section.";
        $dangerAlert = true;
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
    <title>Student  Management System || Manage Sections</title>
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
                <h3 class="page-title"> Manage Sections </h3>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                    <li class="breadcrumb-item active" aria-current="page"> Manage Sections</li>
                    </ol>
                </nav>
                </div>
                <div class="row">
                <div class="col-md-12 grid-margin stretch-card">
                    <div class="card">
                    <div class="card-body">
                        <div class="d-sm-flex align-items-center mb-4">
                        <h4 class="card-title mb-sm-0">Manage Sections</h4>
                        <a href="#" class="text-dark ml-auto mb-3 mb-sm-0"> View all Sections</a>
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
                                <th class="font-weight-bold">Section Name</th>
                                <th class="font-weight-bold">Creation Date</th>
                                <?php 
                                // Check if the user has UpdatePermission or DeletePermission
                                if ($employeePermissions['Sections']['UpdatePermission'] == 1 || $employeePermissions['Sections']['DeletePermission'] == 1) 
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
                                $no_of_records_per_page =15;
                                $offset = ($pageno-1) * $no_of_records_per_page;
                                $ret = "SELECT ID FROM tblsections";
                                $query1 = $dbh -> prepare($ret);
                                $query1->execute();
                                $results1=$query1->fetchAll(PDO::FETCH_OBJ);
                                $total_rows=$query1->rowCount();
                                $total_pages = ceil($total_rows / $no_of_records_per_page);
                                $sql="SELECT * from tblsections WHERE IsDeleted = 0 LIMIT $offset, $no_of_records_per_page";
                                $query = $dbh -> prepare($sql);
                                $query->execute();
                                $results=$query->fetchAll(PDO::FETCH_OBJ);

                                $cnt=1;
                                if($query->rowCount() > 0)
                                {
                                    foreach($results as $row)
                                    {               ?>   
                                    <tr>
                                    
                                        <td><?php echo htmlentities($cnt);?></td>
                                        <td><?php  echo htmlentities($row->SectionName);?></td>
                                        <td><?php  echo htmlentities($row->CreationDate);?></td>
                                        <?php 
                                        // Check if the user has UpdatePermission or DeletePermission
                                        if ($employeePermissions['Sections']['UpdatePermission'] == 1 || $employeePermissions['Sections']['DeletePermission'] == 1) 
                                        { ?>
                                        <td>
                                            <div>
                                            <?php 
                                                // Check if the user has UpdatePermission
                                                if ($employeePermissions['Sections']['UpdatePermission'] == 1) { ?>
                                                    <a href="edit-section.php?editid=<?php echo htmlentities ($row->ID);?>"><i class="icon-pencil"></i></a>
                                                <?php 
                                                } 
                                                if ($employeePermissions['Sections']['UpdatePermission'] == 1 && $employeePermissions['Sections']['DeletePermission'] == 1) 
                                                { ?>
                                                ||
                                                <?php
                                                }
                                                // Check if the user has DeletePermission
                                                if ($employeePermissions['Sections']['DeletePermission'] == 1) { ?>
                                                    <a href="" onclick="setDeleteId(<?php echo ($row->ID);?>)" data-toggle="modal" data-target="#confirmationModal">
                                                        <i class="icon-trash"></i>
                                                    </a>
                                                <?php } ?>
                                            </div>
                                        </td> 
                                        <?php }?>
                                    </tr>
                                    <?php $cnt=$cnt+1;
                                    }
                                } else {?>
                                    <tr><td colspan="4"><h3 class="text-center">No Record Found</h3></td></tr>
                                    <?php } ?>
                            </tbody>
                        </table>
                        </div>
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
                Are you sure you want to delete this Section?
            </div>
            <div class="modal-footer">
                <form id="deleteForm" action="" method="post">
                <input type="hidden" name="sectionID" id="sectionID">
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
    <script src="./js/bootstrap.js"></script>
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
            document.getElementById('sectionID').value = id;
        }
    </script>
</body>
</html>
<?php 
}  
?>