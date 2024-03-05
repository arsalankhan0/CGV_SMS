<?php
session_start();
// error_reporting(0);
include('includes/dbconnection.php');

if (strlen($_SESSION['sturecmsaid']) == 0) {
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

    if (isset($_POST['submit'])) 
    {
        try 
        {
            $name = filter_var($_POST['name'], FILTER_SANITIZE_STRING);
            $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
            $role = filter_var($_POST['role'], FILTER_SANITIZE_STRING);
            $empType = filter_var($_POST['empType'], FILTER_SANITIZE_STRING);
            $gender = filter_var($_POST['gender'], FILTER_SANITIZE_STRING);
            $dob = filter_var($_POST['dob'], FILTER_SANITIZE_STRING);
            $fathername = filter_var($_POST['fathername'], FILTER_SANITIZE_STRING);
            $contactnumber = filter_var($_POST['contactnumber'], FILTER_SANITIZE_STRING);
            $alternatenumber = filter_var($_POST['alternatenumber'], FILTER_SANITIZE_STRING);
            $address = filter_var($_POST['address'], FILTER_SANITIZE_STRING);
            $eid = filter_var($_GET['editid'], FILTER_SANITIZE_STRING);

            // Fetch assigned classes and subjects from form
            $assignedClasses = isset($_POST['assignedClasses']) ? implode(',', $_POST['assignedClasses']) : '';
            $assignedSubjects = isset($_POST['assignedSubjects']) ? implode(',', $_POST['assignedSubjects']) : '';

            $sql = "UPDATE tblemployees SET 
                    Name = :name, 
                    Email = :email, 
                    Role = :role,
                    EmpType = :empType, 
                    Gender = :gender, 
                    DOB = :dob, 
                    FatherName = :fathername, 
                    ContactNumber = :contactnumber, 
                    AlternateNumber = :alternatenumber, 
                    Address = :address,
                    AssignedClasses = :assignedClasses,
                    AssignedSubjects = :assignedSubjects
                    WHERE ID = :eid";

            $query = $dbh->prepare($sql);
            $query->bindParam(':name', $name, PDO::PARAM_STR);
            $query->bindParam(':email', $email, PDO::PARAM_STR);
            $query->bindParam(':role', $role, PDO::PARAM_STR);
            $query->bindParam(':empType', $empType, PDO::PARAM_STR);
            $query->bindParam(':gender', $gender, PDO::PARAM_STR);
            $query->bindParam(':dob', $dob, PDO::PARAM_STR);
            $query->bindParam(':fathername', $fathername, PDO::PARAM_STR);
            $query->bindParam(':contactnumber', $contactnumber, PDO::PARAM_STR);
            $query->bindParam(':alternatenumber', $alternatenumber, PDO::PARAM_STR);
            $query->bindParam(':address', $address, PDO::PARAM_STR);
            $query->bindParam(':assignedClasses', $assignedClasses, PDO::PARAM_STR);
            $query->bindParam(':assignedSubjects', $assignedSubjects, PDO::PARAM_STR);
            $query->bindParam(':eid', $eid, PDO::PARAM_STR);

            $query->execute();

            $successAlert = true;
            $msg = "Employee details have been updated successfully.";
        } 
        catch (PDOException $e) 
        {
            $dangerAlert = true;
            $msg = "Ops! An error occurred while updating the details.";
        }
    }
?>

<!DOCTYPE html>
<html lang="en">
    <head>

    <title>Student  Management System || Update Employee</title>
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
                <h3 class="page-title"> Update Employee </h3>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                    <li class="breadcrumb-item active" aria-current="page"> Update Employee</li>
                    </ol>
                </nav>
                </div>
                <div class="row">
            
                <div class="col-12 grid-margin stretch-card">
                    <div class="card">
                    <div class="card-body">
                        <h4 class="card-title" style="text-align: center;">Update Employee</h4>
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
                        <?php
                        $eid=$_GET['editid'];
                        $sql="SELECT * FROM tblemployees WHERE ID = :eid";
                        $query = $dbh -> prepare($sql);
                        $query->bindParam(':eid',$eid,PDO::PARAM_STR);
                        $query->execute();
                        $results=$query->fetchAll(PDO::FETCH_OBJ);
                        $cnt=1;
                        if($query->rowCount() > 0)
                        {
                        foreach($results as $row)
                        {           
                            $selectedClasses = explode(',', $row->AssignedClasses);
                            $selectedSubjects = explode(',', $row->AssignedSubjects);    
                            ?>
                            <div class="form-group">
                                <label for="exampleInputName1">Employee Name</label>
                                <input type="text" name="name" value="<?php echo htmlentities($row->Name); ?>" class="form-control" required='true'>
                            </div>
                                        <div class="form-group">
                                            <label for="exampleInputName1">Employee Email</label>
                                            <input type="text" name="email" value="<?php echo htmlentities($row->Email); ?>" class="form-control" required='true'>
                                        </div>


                                        <div class="form-group">
                                            <label for="exampleInputEmail3">Employee Type</label>
                                            <select name="empType" id="employeeRole" class="form-control" required='true'>
                                                <option>--Select--</option>
                                                <option value="Teaching" <?php echo ($row->EmpType == 'Teaching') ? 'selected' : ''; ?>>Teaching</option>
                                                <option value="Non-Teaching" <?php echo ($row->EmpType == 'Non-Teaching') ? 'selected' : ''; ?>>Non-Teaching</option>
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
                                                    $selected = in_array($class['ID'], $selectedClasses) ? 'selected' : '';
                                                    echo "<option value='" . htmlentities($class['ID']) . "' $selected>" . htmlentities($class['ClassName']) . "</option>";
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
                                                    $selected = in_array($subject['ID'], $selectedSubjects) ? 'selected' : '';
                                                    echo "<option value='" . htmlentities($subject['ID']) . "' $selected>" . htmlentities($subject['SubjectName']) . "</option>";
                                                }
                                                ?>
                                            </select>
                                        </div>



                                        <div class="form-group">
                                            <label for="exampleInputName1">Employee Role</label>
                                            <select name="role" id="employeeRole" class="form-control" required>
                                                <option value="">--Select Role--</option>

                                                <?php
                                                // Fetch roles from tblroles table
                                                $rolesQuery = "SELECT ID, RoleName FROM tblroles";
                                                $rolesStmt = $dbh->prepare($rolesQuery);
                                                $rolesStmt->execute();
                                                $rolesData = $rolesStmt->fetchAll(PDO::FETCH_ASSOC);

                                                foreach ($rolesData as $role) 
                                                {
                                                    $selected = ($role['ID'] == $row->Role) ? 'selected' : '';
                                                    echo '<option value="' . $role['ID'] . '" ' . $selected . '>' . $role['RoleName'] . '</option>';                                                }
                                                ?>
                                            </select>
                                        </div>
                                        
                                        <div class="form-group">
                                            <label for="exampleInputName1">Gender</label>
                                            <select name="gender" value="" class="form-control" required='true'>
                                                <option value="<?php echo htmlentities($row->Gender); ?>"><?php echo htmlentities($row->Gender); ?></option>
                                                <option value="Male">Male</option>
                                                <option value="Female">Female</option>
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label for="exampleInputName1">Date of Birth</label>
                                            <input type="date" name="dob" value="<?php echo htmlentities($row->DOB); ?>" class="form-control" required='true'>
                                        </div>
                                        <div class="form-group">
                                            <label for="exampleInputName1">Employee ID</label>
                                            <input type="text" name="empid" value="<?php echo htmlentities($row->EmpID); ?>" class="form-control" readonly='true'>
                                        </div>
                                        <div class="form-group">
                                            <label for="exampleInputName1">Father's Name</label>
                                            <input type="text" name="fathername" value="<?php echo htmlentities($row->FatherName); ?>" class="form-control" required='true'>
                                        </div>
                                        <div class="form-group">
                                            <label for="exampleInputName1">Contact Number</label>
                                            <input type="text" name="contactnumber" value="<?php echo htmlentities($row->ContactNumber); ?>" class="form-control" required='true' maxlength="10" pattern="[0-9]+">
                                        </div>
                                        <div class="form-group">
                                            <label for="exampleInputName1">Alternate Contact Number</label>
                                            <input type="text" name="alternatenumber" value="<?php echo htmlentities($row->AlternateNumber); ?>" class="form-control" required='true' maxlength="10" pattern="[0-9]+">
                                        </div>
                                        <div class="form-group">
                                            <label for="exampleInputName1">Address</label>
                                            <textarea name="address" class="form-control"
                                                        required='true'><?php echo htmlentities($row->Address); ?></textarea>
                                        </div>
                                        <div class="form-group">
                                            <label for="exampleInputName1">Username</label>
                                            <input type="text" name="username" value="<?php echo htmlentities($row->UserName); ?>" class="form-control" readonly='true'>
                                        </div>
                                        <div class="form-group">
                                            <label for="exampleInputName1">Password</label>
                                            <input type="Password" name="password" value="<?php echo htmlentities($row->Password); ?>" class="form-control" readonly='true'>
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
                                                    Are you sure you want to update this Employee?
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
    <script src="./js/showMoreInput.js"></script>
    <script src="./js/manageAlert.js"></script>
    <!-- End custom js for this page -->
    </body>
</html>
<?php 
}  
?>