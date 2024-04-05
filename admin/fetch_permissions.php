<?php
include('includes/dbconnection.php');
error_reporting(0);


if (isset($_POST['roleID'])) 
{
    $roleID = $_POST['roleID'];

    // Fetch role name
    $checkRoleQuery = "SELECT RoleName FROM tblroles WHERE ID = :roleID";
    $checkRoleStmt = $dbh->prepare($checkRoleQuery);
    $checkRoleStmt->bindParam(':roleID', $roleID, PDO::PARAM_STR);
    $checkRoleStmt->execute();
    $RoleName = $checkRoleStmt->fetch(PDO::FETCH_ASSOC);

    // Fetch current permissions for the selected role
    $fetchPermissionsQuery = "SELECT `Name`, ReadPermission, CreatePermission, UpdatePermission, DeletePermission
                                FROM tblpermissions
                                WHERE RoleID = :roleID";
    $fetchPermissionsStmt = $dbh->prepare($fetchPermissionsQuery);
    $fetchPermissionsStmt->bindParam(':roleID', $roleID, PDO::PARAM_INT);
    $fetchPermissionsStmt->execute();
    $currentPermissions = $fetchPermissionsStmt->fetchAll(PDO::FETCH_ASSOC);

    echo '<h3 class="text-center">Select Permissions for ' . $RoleName["RoleName"] . '</h3>
            <form method="post" action="">
            <div class="table table-responsive">
            <input type="hidden" name="roleID" value="' . $roleID . '">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th class="font-weight-bolder">Permission Name</th>
                        <th class="font-weight-bolder">View</th>
                        <th class="font-weight-bolder">Create</th>
                        <th class="font-weight-bolder">Update</th>
                        <th class="font-weight-bolder">Delete</th>
                    </tr>
                </thead>
                <tbody>';

    $permissions = array(
        'Class',
        'Sections',
        'Subjects',
        'Students',
        'Examination',
        'Promotion'
    );

    foreach ($permissions as $permission) 
    {
        $viewChecked = getPermissionValue($currentPermissions, $permission, 'ReadPermission');
        $createChecked = getPermissionValue($currentPermissions, $permission, 'CreatePermission');
        $updateChecked = getPermissionValue($currentPermissions, $permission, 'UpdatePermission');
        $deleteChecked = getPermissionValue($currentPermissions, $permission, 'DeletePermission');

        // Disable Create, Update, and Delete for Promotion by default
        $createDisabled = ($permission === 'Promotion') ? 'disabled' : '';
        $deleteDisabled = ($permission === 'Promotion') ? 'disabled' : '';

        echo '<tr>
                <td>' . $permission . '</td>
                <td><input type="checkbox" name="permissions[' . $permission . '][view]" class="select2-checkbox" ' . $viewChecked . '></td>
                <td><input type="checkbox" name="permissions[' . $permission . '][create]" class="select2-checkbox" ' . $createChecked . ' ' . $createDisabled . '></td>
                <td><input type="checkbox" name="permissions[' . $permission . '][update]" class="select2-checkbox" ' . $updateChecked . '></td>
                <td><input type="checkbox" name="permissions[' . $permission . '][delete]" class="select2-checkbox" ' . $deleteChecked . ' ' . $deleteDisabled . '></td>
            </tr>';
    }

    echo '</tbody></table></div>
            <div class="d-flex justify-content-end mt-3">
                <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#confirmationModal">Add Permissions</button>
            </div>

            <!-- Confirmation Modal (assign permissions) -->
            <div class="modal fade" id="confirmationModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" data-backdrop="static" data-keyboard="false">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h4 class="modal-title" id="myModalLabel">Confirmation</h4>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        </div>
                        <div class="modal-body">
                            Are you sure you want to assign selected Permissions to <strong>' . $RoleName["RoleName"] . '</strong>?
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary" name="assignPermissions">Add Permissions</button>
                        </div>
                    </div>
                </div>
            </div>
        </form>';
} 
else 
{
    echo "Invalid request!";
}

// Function to check if the permission is selected in the database
function getPermissionValue($permissions, $permissionName, $columnName)
{
    foreach ($permissions as $permission) 
    {
        if ($permission['Name'] === $permissionName && $permission[$columnName] == 1) 
        {
            return 'checked';
        }
    }
    return '';
}

?>
