<?php
session_start();
error_reporting(0);
include('includes/dbconnection.php');

$dangerAlert = false;
$msg = "";

try 
{
  if (isset($_POST['login'])) 
  {
      $stuid = $_POST['stuid'];
      $password = $_POST['password'];

      $sql = "SELECT StuID, ID, StudentClass, StudentSection, Password FROM tblstudent WHERE (StuID=:stuid) AND IsDeleted = 0";
      $query = $dbh->prepare($sql);
      $query->bindParam(':stuid', $stuid, PDO::PARAM_STR);
      $query->execute();
      $result = $query->fetch(PDO::FETCH_ASSOC);

      if ($result && password_verify($password, $result['Password'])) 
      {
          $_SESSION['sturecmsstuid'] = $result['StuID'];
          $_SESSION['sturecmsuid'] = $result['ID'];
          $_SESSION['stuclass'] = $result['StudentClass'];
          $_SESSION['stusection'] = $result['StudentSection'];

          if (!empty($_POST["remember"])) 
          {
              // COOKIES for username
              setcookie("user_login", $_POST["stuid"], time() + (30 * 24 * 60 * 60));
          } 
          else 
          {
              if (isset($_COOKIE["user_login"])) 
              {
                  setcookie("user_login", "");
              }
          }
          $_SESSION['login'] = $_POST['stuid'];
          echo "<script type='text/javascript'> document.location ='dashboard.php'; </script>";
      } 
      else 
      {
          $msg = "Invalid Credentials!";
          $dangerAlert = true;
      }
  }
} 
catch (PDOException $e) 
{
  $msg = "Ops! An error occurred.";
  $dangerAlert = true;
  echo "<script>console.error('Error:---> " . $e->getMessage() . "');</script>";
}
?>
<!DOCTYPE html>
<html lang="en">
  <head>
  
    <title>TPS || Student Login Page</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- plugins:css -->
    <link rel="stylesheet" href="../admin/vendors/simple-line-icons/css/simple-line-icons.css">
    <link rel="stylesheet" href="../admin/vendors/flag-icon-css/css/flag-icon.min.css">
    <link rel="stylesheet" href="../admin/vendors/css/vendor.bundle.base.css">
    <!-- endinject -->
    <!-- Plugin css for this page -->
    <!-- End plugin css for this page -->
    <!-- inject:css -->
    <!-- endinject -->
    <!-- Layout styles -->
    <link rel="stylesheet" href="../admin/css/style.css">
   
  </head>
  <body>
    <div class="container-scroller">
      <div class="container-fluid page-body-wrapper full-page-wrapper">
        <div class="content-wrapper d-flex align-items-center auth">
          <div class="row flex-grow">
            <div class="col-lg-4 mx-auto">
              <div class="auth-form-light text-left p-5">
                <div class="brand-logo">
                  <img src="../Main/img/logo1.png">
                </div>
                <h4>Tibetan Public School</h4>
                <h6 class="font-weight-light">Sign in to continue.</h6>
                <form class="pt-3" id="login" method="post" name="login">
                  <!-- Dismissible Alert messages -->
                  <?php 
                  if($dangerAlert)
                  { 
                  ?>
                      <!-- Danger -->
                      <div id="danger-alert" class="alert alert-danger alert-dismissible" role="alert">
                      <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                      <?php echo $msg; ?>
                      </div>
                  <?php
                  }?>
                  <div class="form-group">
                    <input type="text" class="form-control form-control-lg" placeholder="enter your student id or username" required="true" name="stuid" value="<?php if(isset($_COOKIE["user_login"])) { echo $_COOKIE["user_login"]; } ?>" >
                  </div>
                  <div class="form-group">
                    
                    <input type="password" class="form-control form-control-lg" placeholder="enter your password" name="password" required="true" value="<?php if(isset($_COOKIE["userpassword"])) { echo $_COOKIE["userpassword"]; } ?>">
                  </div>
                  <div class="mt-3">
                    <button class="btn btn-success btn-block loginbtn" name="login" type="submit">Login</button>
                  </div>
                  <div class="my-2 d-flex justify-content-between align-items-center">
                    <div class="form-check">
                      <label class="form-check-label text-muted">
                        <input type="checkbox" id="remember" class="form-check-input" name="remember" <?php if(isset($_COOKIE["user_login"])) { ?> checked <?php } ?> /> Keep me signed in </label>
                    </div>
                    <a href="forgot-password.php" class="auth-link text-black">Forgot password?</a>
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
    <script src="../admin/vendors/js/vendor.bundle.base.js"></script>
    <!-- endinject -->
    <!-- Plugin js for this page -->
    <!-- End plugin js for this page -->
    <!-- inject:js -->
    <script src="../admin/js/off-canvas.js"></script>
    <script src="../admin/js/misc.js"></script>
    <script src="../admin/js/manageAlert.js"></script>
    <!-- endinject -->
  </body>
</html>