<?php
session_start();
error_reporting(0);
include('includes/dbconnection.php');

if (strlen($_SESSION['sturecmsaid'] == 0)) {
    header('location:logout.php');
} else {
    $successAlert = false;
    $dangerAlert = false;
    $msg = "";
    $sessionData = [];

    // Get session ID from URL
    if (isset($_GET['session_id'])) {
        $session_id = $_GET['session_id'];
        
        // Fetch existing session data
        $sql = "SELECT * FROM tblsessions WHERE session_id = :session_id AND IsDeleted = 0";
        $query = $dbh->prepare($sql);
        $query->bindParam(':session_id', $session_id, PDO::PARAM_INT);
        $query->execute();
        $sessionData = $query->fetch(PDO::FETCH_OBJ);
        
        if (!$sessionData) {
            header('location:manage-session.php');
            exit();
        }
    } else {
        header('location:manage-session.php');
        exit();
    }

    if (isset($_POST['update'])) {
        $sessionName = filter_input(INPUT_POST, 'sName', FILTER_SANITIZE_STRING);
        $startDate = filter_input(INPUT_POST, 'startDate', FILTER_SANITIZE_STRING);
        $endDate = filter_input(INPUT_POST, 'endDate', FILTER_SANITIZE_STRING);

        if (empty($sessionName) || empty($startDate) || empty($endDate)) {
            $dangerAlert = true;
            $msg = "Please fill in all fields!";
        } else {
            // Check for duplicate session (excluding current session)
            $checkSessionSql = "SELECT * FROM tblsessions 
                              WHERE session_name = :sessionName 
                              AND start_date = :startDate 
                              AND end_date = :endDate 
                              AND session_id != :session_id
                              AND IsDeleted = 0";
            $checkSessionQuery = $dbh->prepare($checkSessionSql);
            $checkSessionQuery->bindParam(':sessionName', $sessionName, PDO::PARAM_STR);
            $checkSessionQuery->bindParam(':startDate', $startDate, PDO::PARAM_STR);
            $checkSessionQuery->bindParam(':endDate', $endDate, PDO::PARAM_STR);
            $checkSessionQuery->bindParam(':session_id', $session_id, PDO::PARAM_INT);
            $checkSessionQuery->execute();

            if ($checkSessionQuery->rowCount() > 0) {
                $dangerAlert = true;
                $msg = "Duplicate entry found! The session with the same details already exists.";
            } else {
                try {
                    $updateSql = "UPDATE tblsessions 
                                SET session_name = :sessionName, 
                                    start_date = :startDate, 
                                    end_date = :endDate 
                                WHERE session_id = :session_id";
                    $query = $dbh->prepare($updateSql);
                    $query->bindParam(':sessionName', $sessionName, PDO::PARAM_STR);
                    $query->bindParam(':startDate', $startDate, PDO::PARAM_STR);
                    $query->bindParam(':endDate', $endDate, PDO::PARAM_STR);
                    $query->bindParam(':session_id', $session_id, PDO::PARAM_INT);
                    $query->execute();

                    if ($query->rowCount() > 0) {
                        $successAlert = true;
                        $msg = "Session updated successfully.";
                    } else {
                        $dangerAlert = true;
                        $msg = "No changes made to the session.";
                    }
                } catch (PDOException $e) {
                    $dangerAlert = true;
                    $msg = "Error updating session: " . $e->getMessage();
                }
            }
        }
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>TPS || Edit Session</title>
    <!-- Same header content as add-session.php -->
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
    <?php include_once('includes/header.php'); ?>
    <!-- partial -->
    <div class="container-fluid page-body-wrapper">
        <!-- partial:partials/_sidebar.html -->
        <?php include_once('includes/sidebar.php'); ?>
        <!-- partial -->
        <div class="main-panel">
            <div class="content-wrapper">
                <div class="page-header">
                    <h3 class="page-title">Edit Session</h3>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Edit Session</li>
                        </ol>
                    </nav>
                </div>
                <div class="row">
                    <div class="col-12 grid-margin stretch-card">
                        <div class="card">
                            <div class="card-body">
                                <h4 class="card-title" style="text-align: center;">Edit Session</h4>
                                <form class="forms-sample" method="post">
                                    <!-- Alert messages -->
                                    <?php if ($successAlert): ?>
                                        <div class="alert alert-success alert-dismissible">
                                            <button type="button" class="close" data-dismiss="alert">&times;</button>
                                            <?php echo $msg; ?>
                                        </div>
                                    <?php endif; ?>
                                    <?php if ($dangerAlert): ?>
                                        <div class="alert alert-danger alert-dismissible">
                                            <button type="button" class="close" data-dismiss="alert">&times;</button>
                                            <?php echo $msg; ?>
                                        </div>
                                    <?php endif; ?>

                                    <div class="form-group">
                                        <label for="sName">Session Name</label>
                                        <input type="text" name="sName" class="form-control" 
                                               value="<?php echo htmlentities($sessionData->session_name); ?>" 
                                               required>
                                    </div>
                                    <div class="form-group">
                                        <label for="startDate">Start Date</label>
                                        <input type="date" name="startDate" class="form-control" 
                                               value="<?php echo htmlentities($sessionData->start_date); ?>">
                                    </div>
                                    <div class="form-group">
                                        <label for="endDate">End Date</label>
                                        <input type="date" name="endDate" class="form-control" 
                                               value="<?php echo htmlentities($sessionData->end_date); ?>">
                                    </div>
                                    <button type="submit" class="btn btn-primary mr-2" name="update">Update</button>
                                </form>
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
    </div>
</div>
<!-- Same scripts as add-session.php -->
<script src="vendors/js/vendor.bundle.base.js"></script>
<script src="vendors/select2/select2.min.js"></script>
<script src="vendors/typeahead.js/typeahead.bundle.min.js"></script>
<script src="js/off-canvas.js"></script>
<script src="js/misc.js"></script>
</body>
</html>
<?php 
}
?> 