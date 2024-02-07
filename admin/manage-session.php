<?php
session_start();
error_reporting(0);
include('includes/dbconnection.php');

if (strlen($_SESSION['sturecmsaid'] == 0)) 
{
    header('location:logout.php');
} 
else 
{
    if (isset($_GET['delid'])) 
    {
        $session_id = $_GET['delid'];
    
        // Here we are checking if the session to be deleted is active
        $checkActive = "SELECT is_active FROM tblsessions WHERE session_id = :session_id";
        $queryCheckActive = $dbh->prepare($checkActive);
        $queryCheckActive->bindParam(':session_id', $session_id, PDO::PARAM_INT);
        $queryCheckActive->execute();
        $isActiveResult = $queryCheckActive->fetch(PDO::FETCH_ASSOC);
    
        // Here we are checking for active session
        if ($isActiveResult['is_active'] == 1) 
        {
            echo "<script>alert('Cannot delete the active session. Set another session as active first.');</script>";
        } 
        else 
        {
            $deleteSession = "UPDATE tblsessions SET IsDeleted = 1 WHERE session_id = :session_id";
            $queryDelete = $dbh->prepare($deleteSession);
            $queryDelete->bindParam(':session_id', $session_id, PDO::PARAM_INT);
            $queryDelete->execute();
    
            echo "<script>alert('Session deleted successfully.');</script>";
            echo "<script>window.location.href ='manage-session.php'</script>";
        }
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>

    <title>Student  Management System || Set Active Session</title>
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
    <?php include_once('includes/header.php'); ?>
    <!-- partial -->
    <div class="container-fluid page-body-wrapper">
        <!-- partial:partials/_sidebar.html -->
        <?php include_once('includes/sidebar.php'); ?>
        <!-- partial -->
        <div class="main-panel">
            <div class="content-wrapper">
                <div class="page-header">
                    <h3 class="page-title"> Set Active Session </h3>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                            <li class="breadcrumb-item active" aria-current="page"> Set Active Session</li>
                        </ol>
                    </nav>
                </div>
                <div class="row">

                    <div class="col-12 grid-margin stretch-card">
                        <div class="card">
                            <div class="card-body">
                                <div class="d-sm-flex align-items-center mb-4">
                                    <h4 class="card-title mb-sm-0">Set Active Session</h4>
                                    <a href="#" class="text-dark ml-auto mb-3 mb-sm-0"> View all Sessions</a>
                                </div>
                                <div class="table-responsive border rounded p-1">
                                    <table class="table">
                                        <thead>
                                        <tr>
                                            <th class="font-weight-bold">S.No</th>
                                            <th class="font-weight-bold">Session Name</th>
                                            <th class="font-weight-bold">Status</th>
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
                                        // Formula for pagination
                                        $no_of_records_per_page = 15;
                                        $offset = ($pageno - 1) * $no_of_records_per_page;
                                        $ret = "SELECT session_id FROM tblsessions";
                                        $query1 = $dbh->prepare($ret);
                                        $query1->execute();
                                        $results1 = $query1->fetchAll(PDO::FETCH_OBJ);
                                        $total_rows = $query1->rowCount();
                                        $total_pages = ceil($total_rows / $no_of_records_per_page);
                                        $sql = "SELECT * from tblsessions WHERE IsDeleted = 0 LIMIT $offset, $no_of_records_per_page";
                                        $query = $dbh->prepare($sql);
                                        $query->execute();
                                        $results = $query->fetchAll(PDO::FETCH_OBJ);

                                        $cnt = 1;
                                        if ($query->rowCount() > 0) {
                                            foreach ($results as $row) {
                                                ?>
                                                <tr>
                                                    <td><?php echo htmlentities($cnt); ?></td>
                                                    <td><?php echo htmlentities($row->session_name); ?></td>
                                                    <td>
                                                        <?php
                                                        if ($row->is_active == 1) 
                                                        {
                                                            echo '<button class="btn btn-success btn-sm" disabled>Active</button>';
                                                        } 
                                                        else 
                                                        {
                                                            echo '<button class="btn btn-secondary btn-sm" name="setActive" onclick="setActive(' . $row->session_id . ')">Inactive</button>';
                                                        }
                                                        ?>
                                                    </td>
                                                    <td>
                                                        <a href="manage-session.php?delid=<?php echo ($row->session_id); ?>"
                                                            onclick="return confirm('Do you really want to Delete ?');">
                                                            <i class="icon-trash"></i></a></div>
                                                    </td>
                                                </tr>
                                                <?php $cnt = $cnt + 1;
                                            }
                                        } ?>
                                        </tbody>
                                    </table>
                                </div>
                                <div align="left">
                                    <ul class="pagination">
                                        <li><a href="?pageno=1"><strong>First></strong></a></li>
                                        <li class="<?php if ($pageno <= 1) {
                                            echo 'disabled';
                                        } ?>">
                                            <a href="<?php if ($pageno <= 1) {
                                                echo '#';
                                            } else {
                                                echo "?pageno=" . ($pageno - 1);
                                            } ?>"><strong style="padding-left: 10px">Prev></strong></a>
                                        </li>
                                        <li class="<?php if ($pageno >= $total_pages) {
                                            echo 'disabled';
                                        } ?>">
                                            <a href="<?php if ($pageno >= $total_pages) {
                                                echo '#';
                                            } else {
                                                echo "?pageno=" . ($pageno + 1);
                                            } ?>"><strong style="padding-left: 10px">Next></strong></a>
                                        </li>
                                        <li><a href="?pageno=<?php echo $total_pages; ?>"><strong
                                                        style="padding-left: 10px">Last</strong></a></li>
                                    </ul>
                                </div>
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
<script>
    function setActive(sessionId) 
    {
        let confirmation = confirm('Do you really want to set this session as active?');
        if (confirmation) 
        {
            window.location.href = 'manage-session.php?setActive=true&session_id=' + sessionId;
        }
    }
</script>
</body>
</html>
<?php
    if (isset($_GET['setActive']) && isset($_GET['session_id'])) 
    {
        $session_id = $_GET['session_id'];

        // Here we are setting all sessions to inactive
        $updateInactive = "UPDATE tblsessions SET is_active = 0";
        $queryInactive = $dbh->prepare($updateInactive);
        $queryInactive->execute();

        // And here we Set the selected session to active
        $updateActive = "UPDATE tblsessions SET is_active = 1 WHERE session_id = :session_id";
        $queryActive = $dbh->prepare($updateActive);
        $queryActive->bindParam(':session_id', $session_id, PDO::PARAM_INT);
        $queryActive->execute();
        // echo "<script>alert('Active session set successfully.');</script>";
        echo "<script>window.location.href ='manage-session.php'</script>";
    }
}
?>
