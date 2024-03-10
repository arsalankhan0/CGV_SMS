<?php
session_start();
error_reporting(0);
include('includes/dbconnection.php');
if (strlen($_SESSION['sturecmsstuid'] == 0)) 
{
    header('location:logout.php');
} 
else 
{
?>
    <!DOCTYPE html>
    <html lang="en">
    <head>

        <title>Student  Management System || View Notice</title>
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
                        <h3 class="page-title"> View Notice </h3>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                                <li class="breadcrumb-item active" aria-current="page"> View Notice</li>
                            </ol>
                        </nav>
                    </div>
                    <div class="row">
                        <div class="col-12 grid-margin stretch-card">
                            <div class="card">
                                <div class="card-body table table-responsive">
                                    <?php
                                    $stuclass = $_SESSION['stuclass'];
                                    $stusection = $_SESSION['stusection'];
                                    $sql = "SELECT tblclass.ID, tblclass.ClassName, tblclass.Section, tblnotice.NoticeTitle, tblnotice.CreationDate, tblnotice.ClassId, tblnotice.NoticeMsg, tblnotice.ID as nid 
                                            FROM tblnotice 
                                            JOIN tblclass ON tblclass.ID = tblnotice.ClassId 
                                            WHERE tblnotice.ClassId = :stuclass 
                                            AND tblnotice.IsDeleted = 0 
                                            AND tblclass.IsDeleted = 0
                                            AND FIND_IN_SET(:stusection, tblnotice.SectionID)
                                            ORDER BY tblnotice.CreationDate DESC";
                                    $query = $dbh->prepare($sql);
                                    $query->bindParam(':stuclass', $stuclass, PDO::PARAM_STR);
                                    $query->bindParam(':stusection', $stusection, PDO::PARAM_STR);
                                    $query->execute();
                                    $results = $query->fetchAll(PDO::FETCH_OBJ);
                                    $cnt = 1;
                                    if ($query->rowCount() > 0) {
                                        ?>
                                        <table class="table-notice">
                                            <thead>
                                                <tr>
                                                    <th colspan="4" class="text-center font-weight-bold bg-maroon">Notice</th>
                                                </tr>
                                                <tr class="bg-warning">
                                                    <th class="font-weight-bold">S.No</th>
                                                    <th class="font-weight-bold">Notice Announced Date</th>
                                                    <th class="font-weight-bold">Notice Title</th>
                                                    <th class="font-weight-bold">Message</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($results as $row) { ?>
                                                    <tr>
                                                        <td><?php echo $cnt; ?></td>
                                                        <td><?php echo date('j-F-Y', strtotime($row->CreationDate)); ?></td>
                                                        <td><?php echo $row->NoticeTitle; ?></td>
                                                        <td><?php echo $row->NoticeMsg; ?></td>
                                                    </tr>
                                                    <?php $cnt = $cnt + 1; ?>
                                                <?php } ?>
                                            </tbody>
                                        </table>
                                    <?php } else { ?>
                                        <table border="1" class="table table-bordered mg-b-0">
                                            <thead>
                                                <tr>
                                                    <th colspan="4" class="text-danger font-weight-bold text-center">No Notice Found</th>
                                                </tr>
                                            </thead>
                                        </table>
                                    <?php } ?>
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
    </html><?php } ?>
