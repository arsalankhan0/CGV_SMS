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

    // Fetch active session from tblsessions
    $activeSessionSql = "SELECT session_id FROM tblsessions WHERE is_active = 1 AND IsDeleted = 0";
    $activeSessionQuery = $dbh->prepare($activeSessionSql);
    $activeSessionQuery->execute();
    $activeSession = $activeSessionQuery->fetch(PDO::FETCH_COLUMN);
    try
    {  
      if(isset($_POST['submit']))
        {
          $stuname=$_POST['stuname'];
          $stuclass = $_POST['stuclass'];
          $stusection = $_POST['stusection'];
          $stuRollNo=$_POST['stuRollNo'];
          $gender=$_POST['gender'];
          $stuid=$_POST['stuid'];
          $fname=$_POST['fname'];
          $connum=$_POST['connum'];
          $address=$_POST['address'];
          $code=$_POST['code'];
          $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
          if(!empty($_POST['password'])) {
            $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
          } else {
              $password = NULL;
          }
          $ret="SELECT ID FROM tblstudent WHERE StuID=:stuid AND IsDeleted = 0";
          $query= $dbh -> prepare($ret);
          $query->bindParam(':stuid',$stuid,PDO::PARAM_STR);
          $query-> execute();
          $results = $query -> fetchAll(PDO::FETCH_OBJ);
          if($query -> rowCount() == 0)
          {
            if (strlen($connum) < 10)
            {
                $msg = "Contact Number must be at least 10 digits";
                $dangerAlert = true;
            }
            else
            {
              if(isset($_POST['password']) && !empty($_POST['password']))
              {
                  if (strlen($_POST['password']) < 8) 
                  {
                      $msg = "Password must be at least 8 characters long!";
                      $dangerAlert = true;
                  } 
                  else if(!preg_match('/[a-zA-Z]/', $_POST['password']))
                  {
                      $msg = "Password must contain at least one alphabetic character!";
                      $dangerAlert = true;
                  }
                  else if(!preg_match('/[0-9]/', $_POST['password']) || !preg_match('/[!@#$%^&*(),.?":{}|<>]/', $_POST['password']))
                  {
                      $msg = "Password must contain at least one number and one special character!";
                      $dangerAlert = true;
                  }
              }
              $sql = "INSERT INTO tblstudent(StudentName,StudentClass,StudentSection,RollNo,Gender,StuID,FatherName,ContactNumber,`Address`,CodeNumber,`Password`,SessionID) VALUES (:stuname,:stuclass,:stusection,:stuRollNo,:gender,:stuid,:fname,:connum,:address,:code,:password,:sessionID)";
              $query=$dbh->prepare($sql);
              $query->bindParam(':stuname',$stuname,PDO::PARAM_STR);
              $query->bindParam(':stuclass',$stuclass,PDO::PARAM_STR);
              $query->bindParam(':stusection',$stusection,PDO::PARAM_STR);
              $query->bindParam(':stuRollNo',$stuRollNo,PDO::PARAM_STR);
              $query->bindParam(':gender',$gender,PDO::PARAM_STR);
              $query->bindParam(':stuid',$stuid,PDO::PARAM_STR);
              $query->bindParam(':fname',$fname,PDO::PARAM_STR);
              $query->bindParam(':connum',$connum,PDO::PARAM_STR);
              $query->bindParam(':address',$address,PDO::PARAM_STR);
              $query->bindParam(':code',$code,PDO::PARAM_STR);
              $query->bindParam(':password',$password,PDO::PARAM_STR);
              $query->bindParam(':sessionID',$activeSession,PDO::PARAM_STR);
              $query->execute();
              $LastInsertId=$dbh->lastInsertId();
              if ($LastInsertId > 0) 
              {
                $successAlert = true;
                $msg = "Student has been added successfully.";
              }
              else
              {
                $dangerAlert = true;
                $msg = "Something went wrong! Please try again.";
              }
            }
          }
          else
          {
            $dangerAlert = true;
            $msg = "Student ID already exists! Please try again with different Student ID.";
          }
      }
    }
    catch(PDOException $e)
    {
      $dangerAlert = true;
      $msg = "Ops! An error occurred.";
      echo "<script>console.error('Error:---> ".$e->getMessage()."');</script>";
    }
  ?>
<!DOCTYPE html>
<html lang="en">
  <head>
  
    <title>TPS || Add Students</title>
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
              <h3 class="page-title"> Add Students </h3>
              <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                  <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                  <li class="breadcrumb-item active" aria-current="page"> Add Students</li>
                </ol>
              </nav>
            </div>
            <div class="row">
          
              <div class="col-12 grid-margin stretch-card">
                <div class="card">
                  <div class="card-body">
                    <h4 class="card-title" style="text-align: center;">Add Students</h4>
                    <form class="forms-sample" method="post" enctype="multipart/form-data">

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
                            <label for="stuname">Student Name</label><span class="text-danger mx-1">*</span>
                            <input type="text" id="stuname" name="stuname" value="" class="form-control" required='true'>
                            <div class="text-danger"></div>
                        </div>

                        <div class="form-group">
                            <label for="gender">Gender</label><span class="text-danger mx-1">*</span>
                            <select id="gender" name="gender" value="" class="form-control" required='true'>
                                <option value="">Choose Gender</option>
                                <option value="Male">Male</option>
                                <option value="Female">Female</option>
                            </select>
                            <div class="text-danger"></div>
                        </div>
                      <div class="form-group">
                          <label for="stuclass">Student Class</label><span class="text-danger mx-1">*</span>
                          <select name="stuclass" id="stuclass" class="form-control" required='true'>
                              <option value="">Select Class</option>
                              <?php
                              $sql2 = "SELECT * FROM tblclass";
                              $query2 = $dbh->prepare($sql2);
                              $query2->execute();
                              $result2 = $query2->fetchAll(PDO::FETCH_OBJ);

                              foreach ($result2 as $row1) 
                              {
                                  $classId = htmlentities($row1->ID);
                                  $className = htmlentities($row1->ClassName);
                              ?>
                                  <option value="<?php echo $classId; ?>"><?php echo $className; ?></option>
                              <?php
                              }
                              ?>
                          </select>
                          <div class="text-danger"></div>
                      </div>

                      <div class="form-group">
                          <label for="stusection">Student Section</label><span class="text-danger mx-1">*</span>
                          <select name="stusection" id="stusection" class="form-control" required='true'>
                          <?php
                             // Fetch sections from the database
                            $sectionSql = "SELECT ID, SectionName FROM tblsections";
                            $sectionQuery = $dbh->prepare($sectionSql);
                            $sectionQuery->execute();

                            while ($sectionRow = $sectionQuery->fetch(PDO::FETCH_ASSOC)) 
                            {
                              echo "<option value='" . htmlentities($sectionRow['ID']) . "'>" . htmlentities($sectionRow['SectionName']) . "</option>";
                            }
                            ?>
                          </select>
                          <div class="text-danger"></div>
                      </div>

                      <div class="form-group">
                            <label for="stuRollNo">Student Roll No</label><span class="text-danger mx-1">*</span>
                            <input type="number" id="stuRollNo" name="stuRollNo" value="" class="form-control" min="0" required='true'>
                            <div class="text-danger"></div>
                        </div>

                        <div class="form-group">
                            <label for="fname">Father's/Guardian's Name</label><span class="text-danger mx-1">*</span>
                            <input type="text" id="fname" name="fname" value="" class="form-control" required='true'>
                            <div class="text-danger"></div>
                        </div>

                        <div class="form-group">
                            <label for="connum">Contact Number</label><span class="text-danger mx-1">*</span>
                            <input type="tel" id="connum" name="connum" value="" class="form-control" required='true' maxlength="10" pattern="[0-9]+">
                            <div class="text-danger"></div>
                        </div>

                        <div class="form-group">
                            <label for="address">Address</label><span class="text-danger mx-1">*</span>
                            <textarea id="address" name="address" class="form-control" required='true'></textarea>
                            <div class="text-danger"></div>
                        </div>
                        <div class="form-group">
                            <label for="code">Code Number</label><span class="text-danger mx-1">*</span>
                            <input type="text" id="code" name="code" class="form-control" required='true'></input>
                            <div class="text-danger"></div>
                        </div>

                        <div class="form-group">
                            <label for="stuid">Student ID</label><span class="text-danger mx-1">*</span>
                            <input type="text" id="stuid" name="stuid" class="form-control" required>
                            <div id="stuidAvailability" class="text-danger"></div>
                        </div>

                        <div class="form-group">
                            <label for="password">Password</label>
                            <input type="password" id="password" name="password" class="form-control">
                            <div id="passwordValidationMessage" class="text-danger"></div>
                            <p class="text-muted mb-0 mt-2">
                                Password must:
                                <ul class="text-muted">
                                    <li>Be at least 8 characters long</li>
                                    <li>Contain at least one alphabetic character</li>
                                    <li>Contain at least one number and one special character</li>
                                </ul>
                            </p>
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
    <script src="./js/manageAlert.js"></script>
    <script src="../Employee/js/studentValidation.js"></script>
    <!-- End custom js for this page -->

  </body>
</html><?php }  ?>