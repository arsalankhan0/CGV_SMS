<?php
session_start();
error_reporting(0);
include('includes/dbconnection.php');

// Define permissions array
$requiredPermissions = array(
  'add-students' => 'Students',
);

  if (strlen($_SESSION['sturecmsEMPid']==0)) 
  {
    header('location:logout.php');
  } 
  else
  {

    // Check if the employee has the required permission for this file
    $eid = $_SESSION['sturecmsEMPid'];
    $sql = "SELECT * FROM tblemployees WHERE ID=:eid";
    $query = $dbh->prepare($sql);
    $query->bindParam(':eid', $eid, PDO::PARAM_STR);
    $query->execute();
    $results = $query->fetch(PDO::FETCH_ASSOC);

    $employeeRole = $results['Role'];
    $requiredPermission = $requiredPermissions['add-students']; 

    $sqlPermissions = "SELECT * FROM tblpermissions WHERE RoleID=:employeeRole AND Name=:requiredPermission";
    $queryPermissions = $dbh->prepare($sqlPermissions);
    $queryPermissions->bindParam(':employeeRole', $employeeRole, PDO::PARAM_STR);
    $queryPermissions->bindParam(':requiredPermission', $requiredPermission, PDO::PARAM_STR);
    $queryPermissions->execute();
    $permissions = $queryPermissions->fetch(PDO::FETCH_ASSOC);

    if (!$permissions || $permissions['CreatePermission'] != 1) 
    {
        echo "<h1>You have no permission to access this page!</h1>";
        exit;
    }

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
          $stuemail=$_POST['stuemail'];
          $stuclass = $_POST['stuclass'];
          $stusection = $_POST['stusection'];
          $stuRollNo=$_POST['stuRollNo'];
          $gender=$_POST['gender'];
          $dob=$_POST['dob'];
          $stuid=$_POST['stuid'];
          $fname=$_POST['fname'];
          $mname=$_POST['mname'];
          $connum=$_POST['connum'];
          $address=$_POST['address'];
          $uname=$_POST['uname'];
          $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
          $ret="SELECT UserName FROM tblstudent WHERE UserName=:uname || StuID=:stuid AND IsDeleted = 0";
          $query= $dbh -> prepare($ret);
          $query->bindParam(':uname',$uname,PDO::PARAM_STR);
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
            else if (strlen($_POST['password']) < 8) 
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
            else
            {
              $sql = "INSERT INTO tblstudent(StudentName,StudentEmail,StudentClass,StudentSection,RollNo,Gender,DOB,StuID,FatherName,MotherName,ContactNumber,`Address`,UserName,`Password`,SessionID) VALUES (:stuname,:stuemail,:stuclass,:stusection,:stuRollNo,:gender,:dob,:stuid,:fname,:mname,:connum,:address,:uname,:password,:sessionID)";
              $query=$dbh->prepare($sql);
              $query->bindParam(':stuname',$stuname,PDO::PARAM_STR);
              $query->bindParam(':stuemail',$stuemail,PDO::PARAM_STR);
              $query->bindParam(':stuclass',$stuclass,PDO::PARAM_STR);
              $query->bindParam(':stusection',$stusection,PDO::PARAM_STR);
              $query->bindParam(':stuRollNo',$stuRollNo,PDO::PARAM_STR);
              $query->bindParam(':gender',$gender,PDO::PARAM_STR);
              $query->bindParam(':dob',$dob,PDO::PARAM_STR);
              $query->bindParam(':stuid',$stuid,PDO::PARAM_STR);
              $query->bindParam(':fname',$fname,PDO::PARAM_STR);
              $query->bindParam(':mname',$mname,PDO::PARAM_STR);
              $query->bindParam(':connum',$connum,PDO::PARAM_STR);
              $query->bindParam(':address',$address,PDO::PARAM_STR);
              $query->bindParam(':uname',$uname,PDO::PARAM_STR);
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
            $msg = "Username or Student ID already exists! Please try again with different Username or Student ID.";
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
                        <label for="exampleInputName1">Student Name</label>
                        <input type="text" name="stuname" value="" class="form-control" required='true'>
                      </div>
                      <div class="form-group">
                        <label for="exampleInputName1">Student Email</label>
                        <input type="text" name="stuemail" value="" class="form-control" required='true'>
                      </div>
                      <div class="form-group">
                          <label for="exampleInputEmail3">Student Class</label>
                          <select name="stuclass" id="stuclass" class="form-control" required='true'>
                            <option value="">--Select--</option>
                            <?php
                                // Ensure $empSessionId is properly sanitized
                                $empSessionId = $_SESSION['sturecmsEMPid'];

                                // Fetch assigned classes for the current employee session
                                $sqlAssignedClasses = "SELECT AssignedClasses FROM tblemployees WHERE ID = ?";
                                $queryAssignedClasses = $dbh->prepare($sqlAssignedClasses);
                                $queryAssignedClasses->execute([$empSessionId]);
                                $assignedClassesRow = $queryAssignedClasses->fetch(PDO::FETCH_ASSOC);

                                if ($assignedClassesRow && isset($assignedClassesRow['AssignedClasses'])) 
                                {
                                    $assignedClasses = $assignedClassesRow['AssignedClasses'];
                                    // If no assigned classes found, show error or default message
                                    if (!$assignedClasses) 
                                    {
                                        echo "<option value=''>No classes assigned</option>";
                                    } 
                                    else 
                                    {
                                        // Fetch only those classes that are assigned to the teacher
                                        $assignedClassIds = explode(',', $assignedClasses);
                                        $placeholders = implode(',', array_fill(0, count($assignedClassIds), '?'));

                                        $sql2 = "SELECT * FROM tblclass WHERE ID IN ($placeholders)";
                                        $query2 = $dbh->prepare($sql2);
                                        $query2->execute($assignedClassIds);
                                        $result2 = $query2->fetchAll(PDO::FETCH_OBJ);

                                        // Display the options
                                        foreach ($result2 as $row1) 
                                        {
                                            $classId = htmlentities($row1->ID);
                                            $className = htmlentities($row1->ClassName);
                                            echo "<option value='$classId'>$className</option>";
                                        }
                                    }
                                } 
                                else 
                                {
                                    echo "<option value=''>No classes assigned</option>";
                                }
                            ?>
                        </select>
                      </div>

                      <div class="form-group">
                          <label for="exampleInputEmail3">Student Section</label>
                          <select name="stusection" id="stusection" class="form-control" required='true'>
                          <option value="">--Select--</option>
                          <?php
                            // Fetch assigned sections for the current employee session
                            $sqlAssignedSections = "SELECT AssignedSections FROM tblemployees WHERE ID = ?";
                            $queryAssignedSections = $dbh->prepare($sqlAssignedSections);
                            $queryAssignedSections->execute([$empSessionId]);
                            $assignedSectionsRow = $queryAssignedSections->fetch(PDO::FETCH_ASSOC);
                            $assignedSections = $assignedSectionsRow['AssignedSections'];

                            // If no assigned sections found, show error or default message
                            if (!$assignedSections) {
                                echo "<option value=''>No sections assigned</option>";
                            } else {
                                // Fetch only those sections that are assigned to the teacher
                                $sectionSql = "SELECT ID, SectionName FROM tblsections WHERE ID IN ($assignedSections)";
                                $sectionQuery = $dbh->prepare($sectionSql);
                                $sectionQuery->execute();

                                // Display the options
                                while ($sectionRow = $sectionQuery->fetch(PDO::FETCH_ASSOC)) {
                                    $sectionId = htmlentities($sectionRow['ID']);
                                    $sectionName = htmlentities($sectionRow['SectionName']);
                                    echo "<option value='$sectionId'>$sectionName</option>";
                                }
                            }
                            ?>
                          </select>
                      </div>

                      <div class="form-group">
                        <label for="exampleInputName1">Student Roll No</label>
                        <input type="number" name="stuRollNo" value="" class="form-control" min="0" required='true'>
                      </div>
                      <div class="form-group">
                        <label for="exampleInputName1">Gender</label>
                        <select name="gender" value="" class="form-control" required='true'>
                          <option value="">Choose Gender</option>
                          <option value="Male">Male</option>
                          <option value="Female">Female</option>
                        </select>
                      </div>
                      <div class="form-group">
                        <label for="exampleInputName1">Date of Birth</label>
                        <input type="date" name="dob" value="" class="form-control" required='true'>
                      </div>
                      <div class="form-group">
                        <label for="exampleInputName1">Student ID</label>
                        <input type="text" id="stuid" name="stuid" class="form-control" required>
                        <div id="stuidAvailability" class="text-danger"></div>
                      </div>
                      <h3>Parents/Guardian's details</h3>
                      <div class="form-group">
                        <label for="exampleInputName1">Father's Name</label>
                        <input type="text" name="fname" value="" class="form-control" required='true'>
                      </div>
                      <div class="form-group">
                        <label for="exampleInputName1">Mother's Name</label>
                        <input type="text" name="mname" value="" class="form-control" required='true'>
                      </div>
                      <div class="form-group">
                        <label for="exampleInputName1">Contact Number</label>
                        <input type="text" name="connum" value="" class="form-control" required='true' maxlength="10" pattern="[0-9]+">
                      </div>
                      <div class="form-group">
                        <label for="exampleInputName1">Address</label>
                        <textarea name="address" class="form-control" required='true'></textarea>
                      </div>
                        <h3>Login details</h3>
                      <div class="form-group">
                        <label for="uname">User Name</label>
                        <input type="text" id="uname" name="uname" class="form-control" required>
                        <div id="usernameAvailability" class="text-danger"></div>
                      </div>
                      <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" name="password" id="password" class="form-control" required onkeyup="validatePassword()">
                        <p id="passwordValidationMessage" class="text-danger"></p>
                        
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
    <script src="js/select2.js"></script>
    <script src="./js/manageAlert.js"></script>
    <script src="../admin/js/validatePassword.js"></script>
    <script src="../admin/js/studentAvailability.js"></script>
    <!-- End custom js for this page -->
  </body>
</html><?php }  ?>