<?php
session_start();
error_reporting(0);
include('includes/dbconnection.php');
if (strlen($_SESSION['sturecmsEMPid']==0)) 
{
  header('location:logout.php');
} 
else
{

  ?>
<!DOCTYPE html>
<html lang="en">
  <head>
  
    <title>TPS || Dashboard</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- plugins:css -->
    <link rel="stylesheet" href="vendors/simple-line-icons/css/simple-line-icons.css">
    <link rel="stylesheet" href="vendors/flag-icon-css/css/flag-icon.min.css">
    <link rel="stylesheet" href="vendors/css/vendor.bundle.base.css">
    <!-- endinject -->
    <!-- Plugin css for this page -->
    <link rel="stylesheet" href="vendors/daterangepicker/daterangepicker.css">
    <link rel="stylesheet" href="vendors/chartist/chartist.min.css">
    <!-- End plugin css for this page -->
    <!-- inject:css -->
    <!-- endinject -->
    <!-- Layout styles -->
    <link rel="stylesheet" href="css/style.css">
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
            <div class="row">
              <div class="col-md-12 grid-margin">
                <div class="card">
                  <div class="card-body">
                    <div class="row">
                      <div class="col-md-12">
                        <div class="d-sm-flex align-items-baseline report-summary-header">
                          <h5 class="font-weight-semibold">Report Summary</h5> <span class="ml-auto">Updated Report</span> <button class="btn btn-icons border-0 p-2" onclick="location.reload();"><i class="icon-refresh"></i></button>
                        </div>
                      </div>
                    </div>
                    <div class="row report-inner-cards-wrapper">
                      <div class=" col-md-6 col-xl report-inner-card">
                        <div class="inner-card-text">
                          <?php 
                          // $sql1 ="SELECT ID from  tblclass";
                          $sql1 ="SELECT c.ID
                                  FROM tblemployees e 
                                  JOIN tblclass c ON FIND_IN_SET(c.ID, e.AssignedClasses) 
                                  WHERE e.ID = :empID 
                                  AND e.IsDeleted = 0 
                                  AND c.IsDeleted = 0";
                          $query1 = $dbh -> prepare($sql1);
                          $query1->bindParam(':empID', $_SESSION['sturecmsEMPid'], PDO::PARAM_INT);
                          $query1->execute();
                          $results1=$query1->fetchAll(PDO::FETCH_OBJ);
                          $totclass=$query1->rowCount();
                          ?>
                          <span class="report-title">Total Class</span>
                          <h4><?php echo htmlentities($totclass);?></h4>
                        </div>
                        <div class="inner-card-icon bg-success">
                          <i class="icon-rocket"></i>
                        </div>
                      </div>
                      <div class="col-md-6 col-xl report-inner-card">
                        <div class="inner-card-text">
                          <?php 
                          // $sql2 ="SELECT ID from  tblstudent";
                          $sql2 ="SELECT std.ID
                                  FROM tblemployees e 
                                  JOIN tblstudent std ON FIND_IN_SET(std.StudentClass, e.AssignedClasses) 
                                  AND FIND_IN_SET(std.StudentSection, e.AssignedSections)
                                  WHERE e.ID = :empID 
                                  AND e.IsDeleted = 0 
                                  AND std.IsDeleted = 0";
                          $query2 = $dbh -> prepare($sql2);
                          $query2->bindParam(':empID', $_SESSION['sturecmsEMPid'], PDO::PARAM_INT);
                          $query2->execute();
                          $results2=$query2->fetchAll(PDO::FETCH_OBJ);
                          $totstu=$query2->rowCount();
                          ?>
                          <span class="report-title">Total Students</span>
                          <h4><?php echo htmlentities($totstu);?></h4>
                        </div>
                        <div class="inner-card-icon bg-danger">
                          <i class="icon-user"></i>
                        </div>
                      </div>
                      
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
    <!-- container-scroller -->
    <!-- plugins:js -->
    <script src="vendors/js/vendor.bundle.base.js"></script>
    <!-- endinject -->
    <!-- Plugin js for this page -->
    <script src="vendors/chart.js/Chart.min.js"></script>
    <script src="vendors/moment/moment.min.js"></script>
    <script src="vendors/daterangepicker/daterangepicker.js"></script>
    <script src="vendors/chartist/chartist.min.js"></script>
    <!-- End plugin js for this page -->
    <!-- inject:js -->
    <script src="js/off-canvas.js"></script>
    <script src="js/misc.js"></script>
    <!-- endinject -->
    <!-- Custom js for this page -->
    <script src="js/dashboard.js"></script>
    <!-- End custom js for this page -->
  </body>
</html>
<?php 
}  
?>