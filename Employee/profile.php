<?php
session_start();
error_reporting(0);
include('includes/dbconnection.php');

if (strlen($_SESSION['sturecmsEMPid']) == 0) {
    header('location:logout.php');
} else {
    $EMPid = $_SESSION['sturecmsEMPid'];

    if (isset($_POST['submit'])) {
        $Name = $_POST['name'];
        $mobno = $_POST['contactnumber'];
        $email = $_POST['email'];

        $sql = "UPDATE tblemployees SET Name=:name, ContactNumber=:contactnumber, Email=:email WHERE ID=:eid";
        $query = $dbh->prepare($sql);
        $query->bindParam(':name', $Name, PDO::PARAM_STR);
        $query->bindParam(':email', $email, PDO::PARAM_STR);
        $query->bindParam(':contactnumber', $mobno, PDO::PARAM_STR);
        $query->bindParam(':eid', $EMPid, PDO::PARAM_STR);
        $query->execute();

        echo '<script>alert("Your profile has been updated")</script>';
        echo "<script>window.location.href ='profile.php'</script>";
    }
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <title>Student Management System || Profile</title>
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
                        <h3 class="page-title"> Employee Profile </h3>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                                <li class="breadcrumb-item active" aria-current="page">Employee Profile</li>
                            </ol>
                        </nav>
                    </div>
                    <div class="row">
                        <div class="col-12 grid-margin stretch-card">
                            <div class="card">
                                <div class="card-body">
                                    <h4 class="card-title" style="text-align: center;">Employee Profile</h4>
                                    <form class="forms-sample" method="post">
                                        <?php
                                        $sql = "SELECT * FROM tblemployees WHERE ID = :eid";
                                        $query = $dbh->prepare($sql);
                                        $query->bindParam(':eid', $EMPid, PDO::PARAM_STR);
                                        $query->execute();
                                        $row = $query->fetch(PDO::FETCH_ASSOC);

                                        if ($row) { ?>
                                            <div class="form-group">
                                                <label for="exampleInputName1">Employee Name</label>
                                                <input type="text" name="name" value="<?php echo $row['Name']; ?>" class="form-control" required='true'>
                                            </div>
                                            <div class="form-group">
                                                <label for="exampleInputEmail3">User Name</label>
                                                <input type="text" name="username" value="<?php echo $row['UserName']; ?>" class="form-control" readonly="">
                                            </div>
                                            <div class="form-group">
                                                <label for="exampleInputPassword4">Contact Number</label>
                                                <input type="text" name="contactnumber" value="<?php echo $row['ContactNumber']; ?>" class="form-control" maxlength='10' required='true' pattern="[0-9]+">
                                            </div>
                                            <div class="form-group">
                                                <label for="exampleInputCity1">Email</label>
                                                <input type="email" name="email" value="<?php echo $row['Email']; ?>" class="form-control" required='true'>
                                            </div>
                                            <div class="form-group">
                                                <label for="exampleInputCity1">Employee Joining Date</label>
                                                <input type="text" name="" value="<?php echo $row['DateOfJoining']; ?>" readonly="" class="form-control">
                                            </div>
                                    <?php } ?>
                                    <button type="submit" class="btn btn-primary mr-2" name="submit">Update</button>
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
</body>

</html>
<?php
}
?>
