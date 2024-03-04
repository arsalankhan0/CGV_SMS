<?php
session_start();
error_reporting(0);
include('includes/dbconnection.php');

if (strlen($_SESSION['sturecmsaid']==0)) 
{
    header('location:logout.php');
} 
else
{
    // Function to get all permissions
    function getAllPermissions()
    {
        return array(
            'Class',
            'Sections',
            'Subjects',
            'Students',
            'Examination',
            'Promotion'
        );
    }


    try
    {
        $successAlert = false;
        $dangerAlert = false;
        $msg = "";

        if (isset($_POST['submit'])) 
        {
            $roleName = $_POST['roleName'];

            // Check if the role with the same name already exists
            $checkRoleQuery = "SELECT ID FROM tblroles WHERE RoleName = :roleName";
            $checkRoleStmt = $dbh->prepare($checkRoleQuery);
            $checkRoleStmt->bindParam(':roleName', $roleName, PDO::PARAM_STR);
            $checkRoleStmt->execute();
            $existingRole = $checkRoleStmt->fetch(PDO::FETCH_ASSOC);

            if ($existingRole) 
            {
                $dangerAlert = true;
                $msg = "Role with the same name already exists!";
            } 
            else 
            {
                // Insert the new role
                $sql = "INSERT INTO tblroles (RoleName) VALUES (:roleName)";
                $query = $dbh->prepare($sql);
                $query->bindParam(':roleName', $roleName, PDO::PARAM_STR);
                $query->execute();

                $LastInsertId = $dbh->lastInsertId();

                if ($LastInsertId > 0) 
                {
                    // Insert default permissions for the new role
                    $permissions = getAllPermissions();

                    foreach ($permissions as $permission) 
                    {
                        $view = 1;
                        $create = 1;
                        $update = 1;
                        $delete = 1;

                        $insertPermissionQuery = "INSERT INTO tblpermissions (RoleID, `Name`, ReadPermission, CreatePermission, UpdatePermission, DeletePermission) 
                                                    VALUES (:roleID, :permissionName, :viewPermission, :createPermission, :updatePermission, :deletePermission)";
                        $insertPermissionStmt = $dbh->prepare($insertPermissionQuery);
                        $insertPermissionStmt->bindParam(':roleID', $LastInsertId, PDO::PARAM_INT);
                        $insertPermissionStmt->bindParam(':permissionName', $permission, PDO::PARAM_STR);
                        $insertPermissionStmt->bindParam(':viewPermission', $view, PDO::PARAM_INT);
                        $insertPermissionStmt->bindParam(':createPermission', $create, PDO::PARAM_INT);
                        $insertPermissionStmt->bindParam(':updatePermission', $update, PDO::PARAM_INT);
                        $insertPermissionStmt->bindParam(':deletePermission', $delete, PDO::PARAM_INT);
                        $insertPermissionStmt->execute();
                    }


                    $successAlert = true;
                    $msg = "Role has been added successfully";
                } 
                else 
                {
                    $dangerAlert = true;
                    $msg = "Something went wrong. Please try again later!";
                }
            }
        }
    }
    catch(PDOException $e)
    {
        $dangerAlert = true;
        $msg = "Ops! An error occurred.";
        echo "<script>console.error('Error:---> ".$e->getMessage()."');</script>";
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Student  Management System || Add Role</title>
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
                <h3 class="page-title"> Add Role </h3>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                    <li class="breadcrumb-item active" aria-current="page"> Add Role</li>
                    </ol>
                </nav>
                </div>
                <div class="row">
            
                <div class="col-12 grid-margin stretch-card">
                    <div class="card">
                    <div class="card-body">
                        <h4 class="card-title" style="text-align: center;">Add Role</h4>
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
                                <label for="exampleInputName1">Role Name</label>
                                <input type="text" name="roleName" value="" class="form-control" required='true'>
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
    <script src="./js/manageAlert.js"></script>
    <!-- End custom js for this page -->
  </body>
</html>
<?php 
}  
?>