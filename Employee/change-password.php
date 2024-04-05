<?php
session_start();
error_reporting(0);
include('includes/dbconnection.php');

if (strlen($_SESSION['sturecmsEMPid'] == 0)) 
{
    header('location:logout.php');
} 
else 
{
  try
  {
    $msg = "";
    $dangerAlert = false;
    $successAlert = false;

    if (isset($_POST['submit'])) 
    {
        $EMPid = $_SESSION['sturecmsEMPid'];
        $currentPassword = $_POST['currentpassword'];
        $newPassword = $_POST['newpassword'];
        $confirmPassword = $_POST['confirmpassword'];

        $sql = "SELECT Password FROM tblemployees WHERE ID=:EMPid";
        $query = $dbh->prepare($sql);
        $query->bindParam(':EMPid', $EMPid, PDO::PARAM_STR);
        $query->execute();

        $result = $query->fetch(PDO::FETCH_ASSOC);

        if (strlen($_POST['newpassword']) < 8) 
        {
            $msg = "New password must be at least 8 characters long!";
            $dangerAlert = true;
        } 
        else if(!preg_match('/[a-zA-Z]/', $_POST['newpassword']))
        {
            $msg = "New password must contain at least one alphabetic character!";
            $dangerAlert = true;
        }
        else if(!preg_match('/[0-9]/', $_POST['newpassword']) || !preg_match('/[!@#$%^&*(),.?":{}|<>]/', $_POST['newpassword']))
        {
            $msg = "New password must contain at least one number and one special character!";
            $dangerAlert = true;
        }
        else if ($result && password_verify($currentPassword, $result['Password'])) 
        {
            if ($newPassword == $confirmPassword) 
            {
                $hashedNewPassword = password_hash($newPassword, PASSWORD_DEFAULT);

                $con = "UPDATE tblemployees SET Password=:newpassword WHERE ID=:EMPid";
                $chngpwd1 = $dbh->prepare($con);
                $chngpwd1->bindParam(':EMPid', $EMPid, PDO::PARAM_STR);
                $chngpwd1->bindParam(':newpassword', $hashedNewPassword, PDO::PARAM_STR);
                $chngpwd1->execute();

                $msg = "Your password changed successfully.";
                $successAlert = true;
            } 
            else 
            {
                $msg = "New Password and confirm password do not match!";
                $dangerAlert = true;
            }
        } 
        else 
        {
            $msg = "Your current password is wrong!";
            $dangerAlert = true;
        }
    }
  }
  catch(PDOException $e)
  {
    $msg = "Ops! An error occurred.";
    $dangerAlert = true;
    echo "<script>console.error('Error:---> ".$e->getMessage()."');</script>";
  }
?>
<!DOCTYPE html>
<html lang="en">
  <head>
  
    <title>TPS || Change Password</title>
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
              <h3 class="page-title"> Change Password </h3>
              <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                  <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                  <li class="breadcrumb-item active" aria-current="page">Change Password</li>
                </ol>
              </nav>
            </div>
            <div class="row">
          
              <div class="col-12 grid-margin stretch-card">
                <div class="card">
                  <div class="card-body">
                    <h4 class="card-title" style="text-align: center;">Change Password</h4>
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
                    <form class="forms-sample" name="changepassword" method="post">
                      
                      <div class="form-group">
                        <label for="exampleInputName1">Current Password</label>
                        <input type="password" name="currentpassword" id="currentpassword" class="form-control" required="true">
                      </div>
                      <div class="form-group">
                        <label for="exampleInputEmail3">New Password</label>
                        <input type="password" name="newpassword"  class="form-control" required="true">
                      </div>
                      <div class="form-group">
                        <label for="exampleInputPassword4">Confirm Password</label>
                        <input type="password" name="confirmpassword" id="confirmpassword" value=""  class="form-control" required="true">
                      </div>
                      
                      <button type="submit" class="btn btn-primary mr-2" name="submit">Change</button>
                    
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