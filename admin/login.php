<?php
session_start();
// error_reporting(0);
include('includes/dbconnection.php');


$dangerAlert = false;
$msg = "";
if(isset($_POST['login'])) 
{

  try
  {
    $role = $_POST['role'];
    if($role === "admin")
    {
        $username=$_POST['username'];
        $password=md5($_POST['password']);
        $sql ="SELECT ID FROM tbladmin WHERE UserName=:username AND Password=:password";
        $query=$dbh->prepare($sql);
        $query-> bindParam(':username', $username, PDO::PARAM_STR);
        $query-> bindParam(':password', $password, PDO::PARAM_STR);
        $query-> execute();
        $results=$query->fetchAll(PDO::FETCH_OBJ);
        if($query->rowCount() > 0)
        {
            foreach ($results as $result) 
            {
              $_SESSION['sturecmsaid']=$result->ID;
            }

            if(!empty($_POST["remember"])) 
            {
              //COOKIES for username
              setcookie ("user_login",$_POST["username"],time()+ (10 * 365 * 24 * 60 * 60));
              //COOKIES for password
              setcookie ("userpassword",$_POST["password"],time()+ (10 * 365 * 24 * 60 * 60));
              //COOKIES for role
              setcookie ("role",$role,time()+(10 * 365 * 24 * 60 * 60));
            } 
            else 
            {
              if(isset($_COOKIE["user_login"])) 
              {
                setcookie ("user_login","");
                setcookie ("role", "");
                if(isset($_COOKIE["userpassword"])) 
                {
                  setcookie ("userpassword","");
                }
              }
            }
            $_SESSION['login']=$_POST['username'];
            echo "<script type='text/javascript'> document.location ='dashboard.php'; </script>";
        } 
        else
        {
            $msg = "Invalid Credentials!";
            $dangerAlert = true;
            
        }
    }
    else
    {
      $username = $_POST['username'];
      $password = $_POST['password'];
  
      $sql = "SELECT ID, Password FROM tblemployees WHERE UserName=:username AND IsDeleted = 0";
      $query = $dbh->prepare($sql);
      $query->bindParam(':username', $username, PDO::PARAM_STR);
      $query->execute();
  
      $result = $query->fetch(PDO::FETCH_ASSOC);
  
      if ($result && password_verify($password, $result['Password'])) 
      {
          $_SESSION['sturecmsEMPid'] = $result['ID'];
  
          if (!empty($_POST["remember"])) 
          {
              setcookie("user_login", $username, time() + (10 * 365 * 24 * 60 * 60));
              setcookie("userpassword", $password, time() + (10 * 365 * 24 * 60 * 60));
              setcookie ("role",$role,time()+(10 * 365 * 24 * 60 * 60));
          } 
          else 
          {
              setcookie("user_login", "");
              setcookie("userpassword", "");
              setcookie ("role", "");
          }
  
          $_SESSION['login'] = $username;
          echo "<script type='text/javascript'> document.location ='../Employee/dashboard.php'; </script>";
          exit;
      } 
      else 
      {
          $msg = "Invalid Credentials!";
          $dangerAlert = true;
      }
    }
  }
  catch(PDOException $e)
  {
    $msg = "Ops! An Error occurred.";
    $dangerAlert = true;
  }
}

?>
<!DOCTYPE html>
<html lang="en">
  <head>
  
    <title>Student  Management System || Login Page</title>
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
                  <img src="images/logo.svg">
                </div>
                <h4>Hello! let's get started</h4>
                <h6 class="font-weight-light">Sign in to continue.</h6>
                <form class="pt-0" id="login" method="post" name="login">
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
                <div class="d-flex justify-content-end mb-3">
                  <div>
                    <label for="admin" class="px-1">Admin</label><input type="radio" id="admin" name="role" value="admin" <?php if(!isset($_COOKIE["role"]) || $_COOKIE["role"] == "admin") { ?> checked <?php } ?>/>
                    <label for="emp" class="px-1">Employee</label><input type="radio" id="emp" name="role" value="employee" <?php if(isset($_COOKIE["role"]) && $_COOKIE["role"] == "employee") { ?> checked <?php } ?> />
                  </div>
                </div>

                  <div class="form-group">
                    <input type="text" class="form-control form-control-lg" placeholder="enter your username" required="true" name="username" value="<?php if(isset($_COOKIE["user_login"])) { echo $_COOKIE["user_login"]; } ?>" >
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
    <script src="vendors/js/vendor.bundle.base.js"></script>
    <!-- endinject -->
    <!-- Plugin js for this page -->
    <!-- End plugin js for this page -->
    <!-- inject:js -->
    <script src="js/off-canvas.js"></script>
    <script src="js/misc.js"></script>
    <script src="./js/manageAlert.js"></script>
    <!-- endinject -->
  </body>
</html>