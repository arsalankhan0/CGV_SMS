<?php
session_start();
error_reporting(0);
include('includes/dbconnection.php');

if (!isset($_SESSION['sturecmsaid']) || strlen($_SESSION['sturecmsaid']) == 0) 
{
    header('location:logout.php');
} 
else 
{
    $successAlert = false;
    $dangerAlert = false;
    $msg = "";

    // Get the active session ID
    $getSessionSql = "SELECT session_id FROM tblsessions WHERE is_active = 1 AND IsDeleted = 0";
    $sessionQuery = $dbh->prepare($getSessionSql);
    $sessionQuery->execute();
    $sessionID = $sessionQuery->fetchColumn();

    try
    {
        $dbh->beginTransaction();

        if (isset($_POST['submit'])) 
        {
            $name = filter_var($_POST['name'], FILTER_SANITIZE_STRING);
            $email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
            $role = filter_var($_POST['role'], FILTER_SANITIZE_STRING);
            $empType = filter_var($_POST['empType'], FILTER_SANITIZE_STRING);
            $gender = filter_var($_POST['gender'], FILTER_SANITIZE_STRING);
            $dob = filter_var($_POST['dob'], FILTER_SANITIZE_STRING);
            $empid = filter_var($_POST['empid'], FILTER_SANITIZE_STRING);
            $fathername = filter_var($_POST['fathername'], FILTER_SANITIZE_STRING);
            $contactnumber = filter_var($_POST['contactnumber'], FILTER_SANITIZE_STRING);
            $alternatenumber = filter_var($_POST['alternatenumber'], FILTER_SANITIZE_STRING);
            $address = filter_var($_POST['address'], FILTER_SANITIZE_STRING);
            $username = filter_var($_POST['username'], FILTER_SANITIZE_STRING);
            $password = password_hash(filter_var($_POST['password'], FILTER_SANITIZE_STRING), PASSWORD_DEFAULT);
            $image = $_FILES["image"]["name"];

            $ret = "SELECT UserName FROM tblemployees WHERE UserName = :username OR EmpID = :empid";
            $query = $dbh->prepare($ret);
            $query->bindParam(':username', $username, PDO::PARAM_STR);
            $query->bindParam(':empid', $empid, PDO::PARAM_STR);
            $query->execute();
            $results = $query->fetchAll(PDO::FETCH_OBJ);

            if ($query->rowCount() == 0) 
            {
                $extension = strtolower(pathinfo($image, PATHINFO_EXTENSION));
                $allowed_extensions = array("jpg", "jpeg", "png", "gif");

                if (!in_array($extension, $allowed_extensions)) 
                {
                    $dangerAlert = true;
                    $msg = "Image has Invalid format! Only jpg / jpeg / png / gif format are allowed.";
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
                    $image = md5($image) . time() . '.' . $extension;
                    move_uploaded_file($_FILES["image"]["tmp_name"], "images/" . $image);

                    if ($role === 'Teaching') 
                    {
                        $selectedClasses = implode(',', $_POST['assignedClasses']);
                        $selectedSubjects = implode(',', $_POST['assignedSubjects']);
                    } 
                    else 
                    {
                        $selectedClasses = '';
                        $selectedSubjects = '';
                    }

                    $sql = "INSERT INTO tblemployees(Name, Email, Role, EmpType, AssignedClasses, AssignedSubjects, Gender, DOB, EmpID, FatherName, ContactNumber, AlternateNumber, Address, UserName, Password, Image) VALUES (:name, :email, :role, :empType, :assignedClasses, :assignedSubjects, :gender, :dob, :empid, :fathername, :contactnumber, :alternatenumber, :address, :username, :password, :image)";
                    $query = $dbh->prepare($sql);
                    $query->bindParam(':name', $name, PDO::PARAM_STR);
                    $query->bindParam(':email', $email, PDO::PARAM_STR);
                    $query->bindParam(':role', $role, PDO::PARAM_STR);
                    $query->bindParam(':role', $role, PDO::PARAM_STR);
                    $query->bindParam(':empType', $empType, PDO::PARAM_INT);
                    $query->bindParam(':assignedClasses', $selectedClasses, PDO::PARAM_STR);
                    $query->bindParam(':assignedSubjects', $selectedSubjects, PDO::PARAM_STR);
                    $query->bindParam(':gender', $gender, PDO::PARAM_STR);
                    $query->bindParam(':dob', $dob, PDO::PARAM_STR);
                    $query->bindParam(':empid', $empid, PDO::PARAM_STR);
                    $query->bindParam(':fathername', $fathername, PDO::PARAM_STR);
                    $query->bindParam(':contactnumber', $contactnumber, PDO::PARAM_STR);
                    $query->bindParam(':alternatenumber', $alternatenumber, PDO::PARAM_STR);
                    $query->bindParam(':address', $address, PDO::PARAM_STR);
                    $query->bindParam(':username', $username, PDO::PARAM_STR);
                    $query->bindParam(':password', $password, PDO::PARAM_STR);
                    $query->bindParam(':image', $image, PDO::PARAM_STR);
                    $query->execute();

                    $lastInsertId = $dbh->lastInsertId();

                    if ($lastInsertId > 0) 
                    {
                        $successAlert = true;
                        $msg = "Employee has been added successfully.";

                        $dbh->commit();
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
                $msg = "Username or Employee ID already exists! Please try with different Username or Employee ID.";
            }
        }
    }
    catch (PDOException $e) 
    {
        $dbh->rollBack();
        $dangerAlert = true;
        $msg = "Ops! An error occurred.";
        echo "<script>console.error('Error:---> ".$e->getMessage()."');</script>";
    }
?>
<!DOCTYPE html>
<html lang="en">
  <head>
  
    <title>Student  Management System || Add Employees</title>
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
                    <h3 class="page-title"> Add Employees </h3>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                        <li class="breadcrumb-item active" aria-current="page"> Add Employees</li>
                        </ol>
                    </nav>
                </div>
                <div class="row">
                    <div class="col-12 grid-margin stretch-card">
                        <div class="card">
                            <div class="card-body">
                                <h4 class="card-title" style="text-align: center;">Add Employees</h4>
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

                                <form class="forms-sample" method="post" enctype="multipart/form-data">
                                    <div class="form-group">
                                        <label for="empName">Employee Name</label>
                                        <input type="text" name="name" id="empName" class="form-control" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="empEmail">Employee Email</label>
                                        <input type="email" name="email" id="empEmail" class="form-control" required>
                                    </div>

                                    <div class="form-group">
                                        <label for="employeeRole">Employee Type</label>
                                        <select name="empType" id="employeeRole" class="form-control" required>
                                            <option value="">--Select--</option>
                                            <option value="Teaching">Teaching</option>
                                            <option value="Non-Teaching">Non-Teaching</option>
                                        </select>
                                    </div>
                                    <div class="form-group" id="assignClassesSection">
                                        <label for="exampleInputName1">Assign Classes</label>
                                        <select name="assignedClasses[]" multiple="multiple" class="js-example-basic-multiple w-100">
                                            <?php
                                            // Fetch options for classes from tblclass
                                            $classSql = "SELECT ID, ClassName FROM tblclass WHERE IsDeleted = 0";
                                            $classQuery = $dbh->prepare($classSql);
                                            $classQuery->execute();
                                            $classResults = $classQuery->fetchAll(PDO::FETCH_ASSOC);

                                            foreach ($classResults as $class) {
                                                echo "<option value='" . htmlentities($class['ID']) . "'>" . htmlentities($class['ClassName']) . "</option>";
                                            }
                                            ?>
                                        </select>
                                    </div>
                                    <div class="form-group" id="assignSubjectsSection">
                                        <label for="exampleInputName1">Assign Subjects</label>
                                        <select name="assignedSubjects[]" multiple="multiple" class="js-example-basic-multiple w-100">
                                            <?php
                                            // Fetch options for subjects from tblsubjects
                                            $subjectSql = "SELECT ID, SubjectName FROM tblsubjects WHERE IsDeleted = 0 AND SessionID = :sessionID";
                                            $subjectQuery = $dbh->prepare($subjectSql);
                                            $subjectQuery->bindParam(':sessionID', $sessionID, PDO::PARAM_INT);
                                            $subjectQuery->execute();
                                            $subjectResults = $subjectQuery->fetchAll(PDO::FETCH_ASSOC);

                                            foreach ($subjectResults as $subject) {
                                                echo "<option value='" . htmlentities($subject['ID']) . "'>" . htmlentities($subject['SubjectName']) . "</option>";
                                            }
                                            ?>
                                        </select>
                                    </div>


                                    <div class="form-group">
                                        <label for="role">Role</label>
                                        <select name="role" id="role" class="form-control" required>
                                            <option value="">--Select--</option>

                                            <?php
                                            // Fetch roles from tblroles table
                                            $rolesQuery = "SELECT ID, RoleName FROM tblroles";
                                            $rolesStmt = $dbh->prepare($rolesQuery);
                                            $rolesStmt->execute();
                                            $rolesData = $rolesStmt->fetchAll(PDO::FETCH_ASSOC);

                                            foreach ($rolesData as $role) 
                                            {
                                                echo '<option value="' . $role['ID'] . '">' . $role['RoleName'] . '</option>';
                                            }
                                            ?>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label for="exampleInputName1">Gender</label>
                                        <select name="gender" class="form-control" required>
                                            <option value="">Choose Gender</option>
                                            <option value="Male">Male</option>
                                            <option value="Female">Female</option>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label for="exampleInputName1">Date of Birth</label>
                                        <input type="date" name="dob" class="form-control" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="exampleInputName1">Employee ID</label>
                                        <input type="text" name="empid" class="form-control" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="exampleInputName1">Employee Photo</label>
                                        <input type="file" name="image" class="form-control" required>
                                    </div>
                                    <h3>Parents/Guardian's details</h3>
                                    <div class="form-group">
                                        <label for="exampleInputName1">Guardian's/Father's Name</label>
                                        <input type="text" name="fathername" class="form-control" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="exampleInputName1">Contact Number</label>
                                        <input type="text" name="contactnumber" class="form-control" required maxlength="10" pattern="[0-9]+">
                                    </div>
                                    <div class="form-group">
                                        <label for="exampleInputName1">Alternate Contact Number</label>
                                        <input type="text" name="alternatenumber" class="form-control" required maxlength="10" pattern="[0-9]+">
                                    </div>
                                    <div class="form-group">
                                        <label for="exampleInputName1">Address</label>
                                        <textarea name="address" class="form-control" required></textarea>
                                    </div>
                                    <h3>Login details</h3>
                                    <div class="form-group">
                                        <label for="exampleInputName1">User Name</label>
                                        <input type="text" name="username" class="form-control" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="exampleInputName1">Password</label>
                                        <input type="password" name="password" class="form-control" required>
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
    <script src="./js/showMoreInput.js"></script>
    <script src="./js/manageAlert.js"></script>
    <!-- End custom js for this page -->
  </body>
</html><?php }  ?>