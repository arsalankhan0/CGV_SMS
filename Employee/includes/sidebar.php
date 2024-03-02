<nav class="sidebar sidebar-offcanvas" id="sidebar">
    <ul class="nav">
        <li class="nav-item nav-profile">
            <a href="#" class="nav-link">
                <div class="profile-image">
                    <img class="img-xs rounded-circle" src="images/faces/face8.jpg" alt="profile image">
                    <div class="dot-indicator bg-success"></div>
                </div>
                <div class="text-wrapper">
                    <?php
                    error_reporting(0);
                    
                    $eid = $_SESSION['sturecmsEMPid'];
                    $sql = "SELECT * FROM tblemployees WHERE ID=:eid";
                    $query = $dbh->prepare($sql);
                    $query->bindParam(':eid', $eid, PDO::PARAM_STR);
                    $query->execute();
                    $results = $query->fetch(PDO::FETCH_ASSOC);
                    ?>

                    <p class="profile-name"><?php echo htmlentities($results['Name']); ?></p>
                    <p class="designation"><?php echo htmlentities($results['Email']); ?></p>
                    
                    <?php
                    $employeeRole = $results['Role'];

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
                            'ReadPermission' => $permission->ReadPermission,
                            'CreatePermission' => $permission->CreatePermission,
                            'UpdatePermission' => $permission->UpdatePermission,
                            'DeletePermission' => $permission->DeletePermission,
                        );
                    }

                    //An array of navigation items, Sub Items and their corresponding required permissions
                    $navItems = array(
                        'Class' => array(
                            'Class' => array(
                                'CreatePermission' => 'Add Class',
                                'ReadPermission' => 'Manage Class'
                            )
                        ),
                        'Sections' => array(
                            'Sections' => array(
                                'CreatePermission' => 'Add Section',
                                'ReadPermission' => 'Manage Section'
                            )
                        ),
                        'Subjects' => array(
                            'Subjects' => array(
                                'CreatePermission' => 'Create Subjects',
                                'ReadPermission' => 'Manage Subjects'
                            )
                        ),
                        'Students' => array(
                            'Students' => array(
                                'CreatePermission' => 'Add Students',
                                'ReadPermission' => 'Manage Students'
                            )
                        ),
                        'Examination' => array(
                            'Examination' => array(
                                'CreatePermission' => 'Add Exam',
                                'ReadPermission' => 'Manage Exam'
                            )
                        ),
                        'Promotion' => array(
                            'Promotion' => array(
                                'UpdatePermission' => 'Promote Students'
                            )
                        ),
                    );
                    
                    ?>
                </div>
            </a>
        </li>

        <li class="nav-item nav-category">
            <span class="nav-link">Dashboard</span>
        </li>
        
        <li class="nav-item">
            <a class="nav-link" href="dashboard.php">
                <span class="menu-title">Dashboard</span>
                <i class="icon-screen-desktop menu-icon"></i>
            </a>
        </li>
        
        <?php
        foreach ($navItems as $itemName => $itemPermissions) 
        {
            $hasPermission = false;
        
            foreach ($itemPermissions as $permission => $subItems) 
            {
                if (isset($employeePermissions[$permission]) && is_array($employeePermissions[$permission])) 
                {
                    foreach ($subItems as $subPermission => $subItemName) 
                    {
                        if (isset($employeePermissions[$permission][$subPermission]) && $employeePermissions[$permission][$subPermission] == 1) 
                        {
                            $hasPermission = true;
                            break;
                        }
                    }
                }
            }
        
            if ($hasPermission || empty($itemPermissions)) 
            {
                echo '<li class="nav-item">
                        <a class="nav-link" data-toggle="collapse" href="#ui-' . str_replace(' ', '-', strtolower($itemName)) . '" aria-expanded="false" aria-controls="ui-' . str_replace(' ', '-', strtolower($itemName)) . '">
                            <span class="menu-title">' . $itemName . '</span>
                            <i class="icon-layers menu-icon"></i>
                        </a>';
        
                // Check if there are any sub-menu items for this navigation item
                if (!empty($itemPermissions)) 
                {
                    echo '<div class="collapse" id="ui-' . str_replace(' ', '-', strtolower($itemName)) . '">
                            <ul class="nav flex-column sub-menu">';
        
                    foreach ($itemPermissions as $permission => $subItems) 
                    {
                        foreach ($subItems as $subPermission => $subItemName) 
                        {
                            if ($employeePermissions[$permission][$subPermission] == 1) 
                            {
                                echo '<li class="nav-item"><a class="nav-link" href="' . str_replace(' ', '-', strtolower($subItemName)) . '.php">' . $subItemName . '</a></li>';
                            }
                        }
                    }
        
                    echo '</ul></div>';
                }
        
                echo '</li>';
            }
        }

        // Check if the role is "Teaching"
        if ($results['EmpType'] == "Teaching") 
        {
        ?>
        <li class="nav-item">
            <a class="nav-link" data-toggle="collapse" href="#students" aria-expanded="false" aria-controls="students">
                <span class="menu-title">Report Card</span>
                <i class="icon-book-open menu-icon"></i>
            </a>
            <div class="collapse" id="students">
                <ul class="nav flex-column sub-menu">
                    <li class="nav-item"> <a class="nav-link" href="create-marks.php"> Add Score </a></li>
                    <li class="nav-item"> <a class="nav-link" href="view-students-list.php"> View Score </a></li>
                </ul>
            </div>
        </li>
        <?php 
        } 
        ?>
    </ul>
</nav>
