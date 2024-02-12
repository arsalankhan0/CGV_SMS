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
                    $eid = $_SESSION['sturecmsEMPid'];
                    $sql = "SELECT * FROM tblemployees WHERE ID=:eid";

                    $query = $dbh->prepare($sql);
                    $query->bindParam(':eid', $eid, PDO::PARAM_STR);
                    $query->execute();
                    $results = $query->fetchAll(PDO::FETCH_OBJ);

                    $cnt = 1;
                    if ($query->rowCount() > 0) {
                        foreach ($results as $row) { ?>
                            <p class="profile-name"><?php echo htmlentities($row->Name); ?></p>
                            <p class="designation"><?php echo htmlentities($row->Email); ?></p>
                            <?php $cnt = $cnt + 1;
                        }
                    } ?>
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
        // Check if the role is "Teaching"
        if ($row->Role == "Teaching") {
        ?>
        <li class="nav-item">
            <a class="nav-link" data-toggle="collapse" href="#students" aria-expanded="false" aria-controls="students">
                <span class="menu-title">Report Card</span>
                <i class="icon-book-open menu-icon"></i>
            </a>
            <div class="collapse" id="students">
                <ul class="nav flex-column sub-menu">
                    <li class="nav-item"> <a class="nav-link" href="create-marks.php"> Create Score </a></li>
                    <li class="nav-item"> <a class="nav-link" href="view-students-list.php"> View Score </a></li>
                </ul>
            </div>
        </li>
        <?php } ?>

        <li class="nav-item">
            <a class="nav-link" href="between-dates-reports.php">
                <span class="menu-title">Reports</span>
                <i class="icon-notebook menu-icon"></i>
            </a>
        </li>
    </ul>
</nav>
