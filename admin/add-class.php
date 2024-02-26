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
    try
    {
      $successAlert = false;
      $dangerAlert = false;
      $msg = "";

      if (isset($_POST['submit'])) 
      {
          $cname = $_POST['cname'];
          $sections = implode(',', $_POST['section']);

          $activeSessionQuery = "SELECT session_id FROM tblsessions WHERE is_active = 1";
          $activeSessionStmt = $dbh->query($activeSessionQuery);
          $activeSessionResult = $activeSessionStmt->fetch(PDO::FETCH_ASSOC);
          $activeSessionId = $activeSessionResult['session_id'];

          $sql = "INSERT INTO tblclass (ClassName, Section, SessionID) VALUES (:cname, :sections, :session_id)";
          $query = $dbh->prepare($sql);
          $query->bindParam(':cname', $cname, PDO::PARAM_STR);
          $query->bindParam(':sections', $sections, PDO::PARAM_STR);
          $query->bindParam(':session_id', $activeSessionId, PDO::PARAM_INT);
          $query->execute();

          $LastInsertId = $dbh->lastInsertId();

          if ($LastInsertId > 0) 
          {
              $successAlert = true;
              $msg = "Class has been added successfully";
          } 
          else 
          {
              $dangerAlert = true;
              $msg = "Something went wrong. Please try again later!";
          }
      }
    }
    catch(PDOException $e)
    {
      $dangerAlert = true;
      $msg = "Ops! An error occurred.";
      // error_log($e->getMessage()); //-->This is only for debugging purpose  
    }
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <title>Student  Management System || Add Class</title>
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
              <h3 class="page-title"> Add Class </h3>
              <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                  <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                  <li class="breadcrumb-item active" aria-current="page"> Add Class</li>
                </ol>
              </nav>
            </div>
            <div class="row">
          
              <div class="col-12 grid-margin stretch-card">
                <div class="card">
                  <div class="card-body">
                    <h4 class="card-title" style="text-align: center;">Add Class</h4>
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
                        <label for="exampleInputName1">Class Name</label>
                        <input type="text" name="cname" value="" class="form-control" required='true'>
                      </div>
                      <div class="form-group">
                          <label for="exampleInputEmail3">Sections</label>
                          <select name="section[]" multiple="multiple" class="js-example-basic-multiple w-100" required='true'>
                              <option value="" disabled>Choose Sections</option>
                              <?php
                              // Fetch sections from the database
                              $sectionQuery = $dbh->query("SELECT ID, SectionName FROM tblsections");
                              $sections = $sectionQuery->fetchAll(PDO::FETCH_ASSOC);

                              foreach ($sections as $section) 
                              {
                                  ?>
                                  <option value="<?php echo $section['ID']; ?>"><?php echo $section['SectionName']; ?></option>
                              <?php } ?>
                          </select>
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