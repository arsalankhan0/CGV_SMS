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
  $successAlert = false;
  $dangerAlert = false;
  $msg = "";

  try 
  {
    if (isset($_POST['confirmUpdate']))
    {
        $cname = filter_var($_POST['cname'], FILTER_SANITIZE_STRING);
        $sections = implode(',', $_POST['section']);
        $eid = $_GET['editid'];

        $sql = "update tblclass set ClassName=:cname,Section=:section where ID=:eid";
        $query = $dbh->prepare($sql);
        $query->bindParam(':cname', $cname, PDO::PARAM_STR);
        $query->bindParam(':section', $sections, PDO::PARAM_STR);
        $query->bindParam(':eid', $eid, PDO::PARAM_STR);

        $query->execute();

        $msg = "Class has been updated successfully.";
        $successAlert = true;
    }
  } 
  catch (PDOException $e) 
  {
      // error_log($e->getMessage()); //-->This is only for debugging purpose 
      $msg = "Ops! An error occurred.";
      $dangerAlert = true;
  }


?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <title>Student  Management System || Manage Class</title>
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
              <h3 class="page-title"> Manage Class </h3>
              <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                  <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                  <li class="breadcrumb-item active" aria-current="page"> Manage Class</li>
                </ol>
              </nav>
            </div>
            <div class="row">
          
              <div class="col-12 grid-margin stretch-card">
                <div class="card">
                  <div class="card-body">
                    <h4 class="card-title" style="text-align: center;">Manage Class</h4>
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
                    <form class="forms-sample" method="post">
                      
                      <?php
                        $eid=$_GET['editid'];
                        $sql="SELECT * from  tblclass where ID=$eid";
                        $query = $dbh -> prepare($sql);
                        $query->execute();
                        $results=$query->fetchAll(PDO::FETCH_OBJ);
                        if($query->rowCount() > 0)
                        {
                            foreach($results as $row)
                            {               
                          ?>  
                              <div class="form-group">
                                <label for="exampleInputName1">Class Name</label>
                                <input type="text" name="cname" value="<?php  echo htmlentities($row->ClassName);?>" class="form-control" required='true'>
                              </div>
                              <div class="form-group">
                                  <label for="exampleInputEmail3">Sections</label>
                                  <?php
                                  $selectedSections = explode(',', $row->Section);

                                  $sectionQuery = "SELECT * FROM tblsections";
                                  $sectionStmt = $dbh->query($sectionQuery);
                                  $allSections = $sectionStmt->fetchAll(PDO::FETCH_ASSOC);

                                  ?>
                                  <select name="section[]" class="js-example-basic-multiple w-100" required='true' multiple>
                                      <?php
                                      foreach ($allSections as $section) {
                                          $sectionID = $section['ID'];
                                          $sectionName = $section['SectionName'];
                                          $selected = in_array($sectionID, $selectedSections) ? 'selected' : '';
                                          echo "<option value='$sectionID' $selected>$sectionName</option>";
                                      }
                                      ?>
                                  </select>
                              </div><?php
                            }
                        } ?>
                      <button type="button" class="btn btn-primary mr-2" data-toggle="modal" data-target="#confirmationModal">Update</button>
                        <!-- Confirmation Modal (Update) -->
                        <div class="modal fade" id="confirmationModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" data-backdrop="static" data-keyboard="false">
                          <div class="modal-dialog">
                            <div class="modal-content">
                              <div class="modal-header">
                                <h4 class="modal-title" id="myModalLabel">Confirmation</h4>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                              </div>
                              <div class="modal-body">
                                Are you sure you want to update this Class?
                              </div>
                              <div class="modal-footer">
                                <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-primary" name="confirmUpdate">Update</button>
                              </div>
                            </div>
                          </div>
                        </div>
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