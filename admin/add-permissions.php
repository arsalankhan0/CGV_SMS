<?php
session_start();
// error_reporting(0);
include('includes/dbconnection.php');

if (strlen($_SESSION['sturecmsaid']==0)) 
{
    header('location:logout.php');
} 
else
{
    $successAlert = false;
    $dangerAlert = false;
    $msg = "";

    if ($_SERVER['REQUEST_METHOD'] == 'POST') 
    {
        try
        {
            $roleID = $_POST['roleID'];

            if (isset($_POST['assignPermissions'])) 
            { 
                // $permissionsArray = $_POST['permissions'] ?? [];
                
                $checkExistingQuery = "SELECT * FROM tblpermissions WHERE RoleID = :roleID AND `Name` = :permissionName";
                $checkExistingStmt = $dbh->prepare($checkExistingQuery);
                $checkExistingStmt->bindParam(':roleID', $roleID, PDO::PARAM_INT);

                // Loop through submitted permissions and insert into tblpermissions
                foreach ($_POST['permissions'] as $permissionName => $permissionValues) 
                {
                    $checkExistingStmt->bindParam(':permissionName', $permissionName, PDO::PARAM_STR);
                    $checkExistingStmt->execute();

                    // Data already exists, perform an update
                    if ($checkExistingStmt->rowCount() > 0) 
                    {   
                        // echo "<script>";
                        // foreach($permissionValues as $permissionKey => $permissionValue)
                        // {
                        //     echo "alert('" . $permissionName . ": " . $permissionKey . " => " . $permissionValue . "');";
                        // }
                        // echo "</script>";

                        $view = isset($permissionValues['view']) ? 1 : 0;
                        $create = isset($permissionValues['create']) ? 1 : 0;
                        $update = isset($permissionValues['update']) ? 1 : 0;
                        $delete = isset($permissionValues['delete']) ? 1 : 0;

                        $updatePermissionQuery = "UPDATE tblpermissions SET 
                                                    ReadPermission = :viewPermission,
                                                    CreatePermission = :createPermission,
                                                    UpdatePermission = :updatePermission,
                                                    DeletePermission = :deletePermission
                                                WHERE RoleID = :roleID AND `Name` = :permissionName";
                        $updatePermissionStmt = $dbh->prepare($updatePermissionQuery);
                        $updatePermissionStmt->bindParam(':roleID', $roleID, PDO::PARAM_INT);
                        $updatePermissionStmt->bindParam(':permissionName', $permissionName, PDO::PARAM_STR);
                        $updatePermissionStmt->bindParam(':viewPermission', $view, PDO::PARAM_INT);
                        $updatePermissionStmt->bindParam(':createPermission', $create, PDO::PARAM_INT);
                        $updatePermissionStmt->bindParam(':updatePermission', $update, PDO::PARAM_INT);
                        $updatePermissionStmt->bindParam(':deletePermission', $delete, PDO::PARAM_INT);
                        $updatePermissionStmt->execute();

                        $successAlert = true;
                        $msg = "Permission updated successfully.";
                    }
                    else
                    {
                        $view = isset($permissionValues['view']) ? 1 : 0;
                        $create = isset($permissionValues['create']) ? 1 : 0;
                        $update = isset($permissionValues['update']) ? 1 : 0;
                        $delete = isset($permissionValues['delete']) ? 1 : 0;

                        $insertPermissionQuery = "INSERT INTO tblpermissions (RoleID, `Name`, ReadPermission, CreatePermission, UpdatePermission, DeletePermission) 
                                                    VALUES (:roleID, :permissionName, :viewPermission, :createPermission, :updatePermission, :deletePermission)";
                        $insertPermissionStmt = $dbh->prepare($insertPermissionQuery);
                        $insertPermissionStmt->bindParam(':roleID', $roleID, PDO::PARAM_INT);
                        $insertPermissionStmt->bindParam(':permissionName', $permissionName, PDO::PARAM_STR);
                        $insertPermissionStmt->bindParam(':viewPermission', $view, PDO::PARAM_INT);
                        $insertPermissionStmt->bindParam(':createPermission', $create, PDO::PARAM_INT);
                        $insertPermissionStmt->bindParam(':updatePermission', $update, PDO::PARAM_INT);
                        $insertPermissionStmt->bindParam(':deletePermission', $delete, PDO::PARAM_INT);
                        $insertPermissionStmt->execute();

                        $successAlert = true;
                        $msg = "Permission added successfully.";
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
    }
    
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>TPS || Assign Permissions</title>
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
        <?php include_once('includes/sidebar.php');?>
            <!-- partial -->
            <div class="main-panel">
            <div class="content-wrapper">
                <div class="page-header">
                <h3 class="page-title"> Assign Permissions </h3>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                    <li class="breadcrumb-item active" aria-current="page"> Assign Permissions</li>
                    </ol>
                </nav>
                </div>
                <div class="row">
            
                <div class="col-12 grid-margin stretch-card">
                    <div class="card">
                    <div class="card-body">
                        <h4 class="card-title" style="text-align: center;">Assign Permissions</h4>
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
                                <label for="roleSelect">Select Role</label>
                                <select class="form-control" id="roleSelect" name="roleID">
                                    <?php
                                    // Fetch roles from tblroles
                                    $roleQuery = "SELECT ID, RoleName FROM tblroles WHERE IsDeleted = 0";
                                    $roleStmt = $dbh->prepare($roleQuery);
                                    $roleStmt->execute();
                                    while ($row = $roleStmt->fetch(PDO::FETCH_ASSOC)) {
                                        echo "<option value='{$row['ID']}'>{$row['RoleName']}</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                            <!-- Button to fetch permissions -->
                            <button type="button" class="btn btn-primary mr-2" id="fetchPermissions">Assign Permissions</button>
                        </form>
                        <!-- Table to display permissions -->
                        <div id="permissionsTable" class="mt-3"></div>
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
    <script>

        document.addEventListener('DOMContentLoaded', function () {

            // Fetch permissions on clicking the button
            document.getElementById('fetchPermissions').addEventListener('click', function () {
                let roleID = document.getElementById('roleSelect').value;

                fetch('fetch_permissions.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'roleID=' + encodeURIComponent(roleID),
                })
                .then(response => response.text())
                .then(data => {
                    document.getElementById('permissionsTable').innerHTML = data;
                })
                .catch(error => console.error('Error fetching permissions:', error));
            });
        });

    </script>
    <!-- End custom js for this page -->
  </body>
</html>
<?php 
}  
?>