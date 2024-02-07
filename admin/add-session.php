<?php
session_start();
error_reporting(0);
include('includes/dbconnection.php');

if (strlen($_SESSION['sturecmsaid'] == 0)) 
{
    header('location:logout.php');
} 
else 
{
    if (isset($_POST['submit'])) 
    {
        $sessionName = filter_input(INPUT_POST, 'sName', FILTER_SANITIZE_STRING);
        $startDate = filter_input(INPUT_POST, 'startDate', FILTER_SANITIZE_STRING);
        $endDate = filter_input(INPUT_POST, 'endDate', FILTER_SANITIZE_STRING);       

        if (empty($sessionName) || empty($startDate) || empty($endDate)) 
        {
            echo '<script>alert("Please fill in all fields.");</script>';
        } 
        else 
        {
            $checkSessionSql = "SELECT * FROM tblsessions WHERE session_name = :sessionName AND start_date = :startDate AND end_date = :endDate AND IsDeleted = 0";
            $checkSessionQuery = $dbh->prepare($checkSessionSql);
            $checkSessionQuery->bindParam(':sessionName', $sessionName, PDO::PARAM_STR);
            $checkSessionQuery->bindParam(':startDate', $startDate, PDO::PARAM_STR);
            $checkSessionQuery->bindParam(':endDate', $endDate, PDO::PARAM_STR);
            $checkSessionQuery->execute();

            if ($checkSessionQuery->rowCount() > 0) 
            {
                echo '<script>alert("Duplicate entry!. The session with the same start date and end date already exists.");</script>';
            } 
            else 
            {
                try 
                {        
                    $sql = "INSERT INTO tblsessions (session_name, start_date, end_date) VALUES (:sessionName, :startDate, :endDate)";
                    $query = $dbh->prepare($sql);
                    $query->bindParam(':sessionName', $sessionName, PDO::PARAM_STR);
                    $query->bindParam(':startDate', $startDate, PDO::PARAM_STR);
                    $query->bindParam(':endDate', $endDate, PDO::PARAM_STR);
                    $query->execute();

                    $lastInsertId = $dbh->lastInsertId();
                    if ($lastInsertId > 0) 
                    {
                        echo '<script>alert("Session created successfully.");</script>';
                        echo "<script>window.location.href ='add-session.php'</script>";
                    } 
                    else 
                    {
                        echo '<script>alert("Unable to create session!");</script>';
                    }
                } 
                catch (PDOException $e) 
                {
                    echo '<script>alert("Error: ' . $e->getMessage() . '");</script>';
                }
            }
        }
    }
?>
<!DOCTYPE html>
<html lang="en">
  <head>
   
    <title>Student  Management System || Create Session</title>
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
              <h3 class="page-title"> Create Session </h3>
              <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                  <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                  <li class="breadcrumb-item active" aria-current="page"> Create Session</li>
                </ol>
              </nav>
            </div>
            <div class="row">
          
              <div class="col-12 grid-margin stretch-card">
                <div class="card">
                  <div class="card-body">
                    <h4 class="card-title" style="text-align: center;">Create Session</h4>
                    <form class="forms-sample" method="post">
                      
                      <div class="form-group">
                        <label for="exampleInputName1">Session Name</label>
                        <input type="text" name="sName" value="" class="form-control" placeholder="eg: 2021-2022" required='true'>
                      </div>
                      <div class="form-group">
                        <label for="exampleInputEmail3">Start Date</label>
                        <input type="date" name="startDate" class="form-control">
                      </div>
                      <div class="form-group">
                        <label for="exampleInputEmail3">End Date</label>
                        <input type="date" name="endDate" class="form-control">
                      </div>
                      <button type="submit" class="btn btn-primary mr-2" name="submit">Create</button>
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
    <!-- End custom js for this page -->
  </body>
</html>
<?php 
}  
?>