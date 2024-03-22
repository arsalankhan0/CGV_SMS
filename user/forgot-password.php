<?php
session_start();
error_reporting(0);
include('includes/dbconnection.php');

$msg = "";
$dangerAlert = false;
$successAlert = false;
try
{
  if(isset($_POST['submit']))
  {
      $email = $_POST['email'];
      $mobile = $_POST['mobile'];
      $newpassword = $_POST['newpassword'];
      $confirmpassword = $_POST['confirmpassword'];

      $sql = "SELECT StudentEmail FROM tblstudent WHERE StudentEmail=:email and ContactNumber=:mobile";
      $query = $dbh->prepare($sql);
      $query->bindParam(':email', $email, PDO::PARAM_STR);
      $query->bindParam(':mobile', $mobile, PDO::PARAM_STR);
      $query->execute();
      $results = $query->fetchAll(PDO::FETCH_OBJ);

      if ($query->rowCount() > 0)
      {
        $hashedPassword = password_hash($newpassword, PASSWORD_DEFAULT);

        if (strlen($newpassword) < 8 || !preg_match('/[a-zA-Z]/', $newpassword) || !preg_match('/[0-9]/', $newpassword) || !preg_match('/[!@#$%^&*(),.?":{}|<>]/', $newpassword) || $newpassword != $confirmpassword) 
        {
            $msg = "Invalid Password! Ensure it meets the criteria.";
            $dangerAlert = true;
        } 
        else 
        {
          $con = "UPDATE tblstudent SET Password=:newpassword WHERE StudentEmail=:email AND ContactNumber=:mobile";
          $chngpwd1 = $dbh->prepare($con);
          $chngpwd1->bindParam(':email', $email, PDO::PARAM_STR);
          $chngpwd1->bindParam(':mobile', $mobile, PDO::PARAM_STR);
          $chngpwd1->bindParam(':newpassword', $hashedPassword, PDO::PARAM_STR);
          $resultStudent = $chngpwd1->execute();

          if ($resultStudent) 
          {
              $msg = "Your Password changed Successfully.";
              $successAlert = true;
          } 
          else 
          {
              $msg = "Failed to update password!";
              $dangerAlert = true;
          }
        }
      }
      else
      {
        $msg = "Invalid Email or Mobile number!";
        $dangerAlert = true;
      }
  }
}
catch (PDOException $e) 
{
  $msg = "Ops! An error occurred while updating the password.";
  $dangerAlert = true;
  echo "<script>console.error('Error:---> " . $e->getMessage() . "');</script>";
}

?>
<!DOCTYPE html>
<html lang="en">
  <head>
  
    <title>Student  Management System || Student Forgot Password</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- plugins:css -->
    <link rel="stylesheet" href="vendors/simple-line-icons/css/simple-line-icons.css">
    <link rel="stylesheet" href="vendors/flag-icon-css/css/flag-icon.min.css">
    <link rel="stylesheet" href="vendors/css/vendor.bundle.base.css">
    <!-- endinject -->
    <!-- Plugin css for this page -->
    <!-- End plugin css for this page -->
    <!-- inject:css -->
    <!-- endinject -->
    <!-- Layout styles -->
    <link rel="stylesheet" href="css/style.css">
  </head>
  <body>
    <div class="container-scroller">
      <div class="container-fluid page-body-wrapper full-page-wrapper">
        <div class="content-wrapper d-flex align-items-center auth">
          <div class="row flex-grow">
            <div class="col-lg-4 mx-auto">
              <div class="auth-form-light text-left p-5">
                <div class="brand-logo">
                  <img src="images/logo.svg"> SMS
                </div>
                <h4>RECOVER PASSWORD</h4>
                <h6 class="font-weight-light">Enter your email address and mobile number to reset password!</h6>
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
                <form class="pt-3" id="login" method="post" name="login">
                  <div class="form-group">
                    <input type="email" class="form-control form-control-lg" placeholder="Email Address" required="true" name="email">
                  </div>
                  <div class="form-group">
                    
                     <input type="text" class="form-control form-control-lg"  name="mobile" placeholder="Mobile Number" required="true" maxlength="10" pattern="[0-9]+">
                  </div>
                  <div class="form-group">
                   
                    <input class="form-control form-control-lg" type="password" name="newpassword" placeholder="New Password" required="true"/>
                  </div>
                  <div class="form-group">
                    
                   <input class="form-control form-control-lg" type="password" name="confirmpassword" placeholder="Confirm Password" required="true" />
                  </div>
                  <div class="mt-3">
                    <button class="btn btn-success btn-block loginbtn" name="submit" type="submit">Reset</button>
                  </div>
                  <div class="my-2 d-flex justify-content-between align-items-center">
                    
                    <a href="login.php" class="auth-link text-black">signin</a>
                  </div>
                  <div class="mb-2">
                    <a href="../index.php" class="btn btn-block btn-facebook auth-form-btn">
                      <i class="icon-social-home mr-2"></i>Back Home </a>
                  </div>
                  
                </form>
              </div>
            </div>
          </div>
        </div>
        <!-- content-wrapper ends -->
      </div>
      <!-- page-body-wrapper ends -->
    </div>
    <!-- container-scroller -->
    <!-- plugins:js -->
    <script src="vendors/js/vendor.bundle.base.js"></script>
    <!-- endinject -->
    <!-- Plugin js for this page -->
    <!-- End plugin js for this page -->
    <!-- inject:js -->
    <script src="js/off-canvas.js"></script>
    <script src="js/misc.js"></script>
    <script src="../admin/js/manageAlert.js"></script>
    <!-- endinject -->
  </body>
</html>