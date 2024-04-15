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
  $successAlert = false;
  $dangerAlert = false;
  $msg = "";

  try
  {
    if(isset($_POST['submit']))
    {
      $pagedes=$_POST['pagedes'];
      $mobnum=$_POST['mobnum'];
      $email=$_POST['email'];
      $sql="UPDATE tblpage set PageDescription=:pagedes,Email=:email,MobileNumber=:mobnum where  PageType='contactus'";
      $query=$dbh->prepare($sql);
      $query->bindParam(':pagedes',$pagedes,PDO::PARAM_STR);
      $query->bindParam(':email',$email,PDO::PARAM_STR);
      $query->bindParam(':mobnum',$mobnum,PDO::PARAM_STR);
      $query->execute();

      $successAlert = true;
      $msg = "Contact Us has been updated successfully.";
    }
  }
  catch(PDOException $e)
  {
    $dangerAlert = true;
    $msg = "Ops! An error occurred while updating Contact Us.";
    echo "<script>console.error('Error:---> ".$e->getMessage()."');</script>";
  }

  ?>
<!DOCTYPE html>
<html lang="en">
  <head>
   
    <title>TPS || Update Contact Us</title>
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
     <?php include_once('includes/header.php');?>
      <!-- partial -->
      <div class="container-fluid page-body-wrapper">
        <!-- partial:partials/_sidebar.html -->
      <?php include_once('includes/sidebar.php');?>
        <!-- partial -->
        <div class="main-panel">
          <div class="content-wrapper">
            <div class="page-header">
              <h3 class="page-title"> Update Contact Us </h3>
              <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                  <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                  <li class="breadcrumb-item active" aria-current="page"> Update Contact Us</li>
                </ol>
              </nav>
            </div>
            <div class="row">
          
              <div class="col-12 grid-margin stretch-card">
                <div class="card">
                  <div class="card-body">
                    <h4 class="card-title" style="text-align: center;">Update Contact Us</h4>
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
                      $sql="SELECT * from  tblpage where PageType='contactus'";
                      $query = $dbh -> prepare($sql);
                      $query->execute();
                      $results=$query->fetchAll(PDO::FETCH_OBJ);
                      $cnt=1;
                      if($query->rowCount() > 0)
                      {
                      foreach($results as $row)
                      {               
                        ?>    
                      <div class="form-group">
                        <label for="exampleInputName1">Address:</label>
                        <textarea type="text" name="pagedes" class="form-control" required='true' rows="3"><?php  echo $row->PageDescription;?></textarea>
                      </div>
                      <div class="form-group">
                        <label for="exampleInputName1">Email:</label>
                        <input type="text" name="email" id="email" required="true" value="<?php  echo $row->Email;?>" class="form-control">
                      </div>
                      <div class="form-group">
                        <label for="exampleInputName1">Contact Number:</label>
                        <input type="text" name="mobnum" id="mobnum" required="true" value="<?php  echo $row->MobileNumber;?>" class="form-control" >
                      </div>
                      <?php $cnt=$cnt+1;}} ?>
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
                                Are you sure you want to update the content of Contact Us?
                              </div>
                              <div class="modal-footer">
                                <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-primary" name="submit">Update</button>
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
    <script src="./js/contact-num-validation.js"></script>
    <!-- End custom js for this page -->
  </body>
</html><?php }  ?>