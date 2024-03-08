<?php
session_start();
error_reporting(0);
include('includes/dbconnection.php');

// Define permissions array
$requiredPermissions = array(
    'edit-student-detail' => 'Students',
);

if (strlen($_SESSION['sturecmsEMPid']) == 0) 
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
    $requiredPermission = $requiredPermissions['edit-student-detail']; 

    $sqlPermissions = "SELECT * FROM tblpermissions WHERE RoleID=:employeeRole AND Name=:requiredPermission";
    $queryPermissions = $dbh->prepare($sqlPermissions);
    $queryPermissions->bindParam(':employeeRole', $employeeRole, PDO::PARAM_STR);
    $queryPermissions->bindParam(':requiredPermission', $requiredPermission, PDO::PARAM_STR);
    $queryPermissions->execute();
    $permissions = $queryPermissions->fetch(PDO::FETCH_ASSOC);

    if (!$permissions || $permissions['UpdatePermission'] != 1) 
    {
        echo "<h1>You have no permission to access this page!</h1>";
        exit;
    }

    $successAlert = false;
    $dangerAlert = false;
    $msg = "";
    try 
    {
        // Fetch current active session ID
        $activeSessionSql = "SELECT session_id FROM tblsessions WHERE is_active = 1 AND IsDeleted = 0";
        $activeSessionQuery = $dbh->prepare($activeSessionSql);
        $activeSessionQuery->execute();
        $activeSessionID = $activeSessionQuery->fetch(PDO::FETCH_COLUMN);

        if (isset($_POST['submit'])) 
        {
            $stuname = $_POST['stuname'];
            $stuemail = $_POST['stuemail'];
            $stuclass = $_POST['stuclass'];
            $stusection = $_POST['stusection'];
            $gender = $_POST['gender'];
            $dob = $_POST['dob'];
            $stuid = $_POST['stuid'];
            $fname = $_POST['fname'];
            $mname = $_POST['mname'];
            $connum = $_POST['connum'];
            $altconnum = $_POST['altconnum'];
            $address = $_POST['address'];
            $eid = $_GET['editid'];

                $sql = "UPDATE tblstudent SET 
                        StudentName=:stuname,
                        StudentEmail=:stuemail,
                        StudentClass=:stuclass,
                        StudentSection=:stusection,
                        Gender=:gender,
                        DOB=:dob,
                        StuID=:stuid,
                        FatherName=:fname,
                        MotherName=:mname,
                        ContactNumber=:connum,
                        AltenateNumber=:altconnum,
                        Address=:address 
                        WHERE ID=:eid";

            $query = $dbh->prepare($sql);
            $query->bindParam(':stuname', $stuname, PDO::PARAM_STR);
            $query->bindParam(':stuemail', $stuemail, PDO::PARAM_STR);
            $query->bindParam(':stuclass', $stuclass, PDO::PARAM_STR);
            $query->bindParam(':stusection', $stusection, PDO::PARAM_STR);
            $query->bindParam(':gender', $gender, PDO::PARAM_STR);
            $query->bindParam(':dob', $dob, PDO::PARAM_STR);
            $query->bindParam(':stuid', $stuid, PDO::PARAM_STR);
            $query->bindParam(':fname', $fname, PDO::PARAM_STR);
            $query->bindParam(':mname', $mname, PDO::PARAM_STR);
            $query->bindParam(':connum', $connum, PDO::PARAM_STR);
            $query->bindParam(':altconnum', $altconnum, PDO::PARAM_STR);
            $query->bindParam(':address', $address, PDO::PARAM_STR);
            $query->bindParam(':eid', $eid, PDO::PARAM_STR);
            $query->execute();
            $successAlert = true;
            $msg = "Student has been updated successfully.";
            
        }
        
    } 
    catch (PDOException $e) 
    {
        $dangerAlert = true;
        $msg = "Ops! Something went wrong.";
    }


?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Student Management System || Update Students</title>
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
    <link rel="stylesheet" href="css/style.css"/>
</head>
<body>
<div class="container-scroller">
    <!-- partial:partials/_navbar.html -->
    <?php include_once('includes/header.php'); ?>
    <!-- partial -->
    <div class="container-fluid page-body-wrapper">
        <!-- partial:partials/_sidebar.html -->
        <?php include_once('includes/sidebar.php'); ?>
        <!-- partial -->
        <div class="main-panel">
            <div class="content-wrapper">
                <div class="page-header">
                    <h3 class="page-title"> Update Students </h3>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                            <li class="breadcrumb-item active" aria-current="page"> Update Students</li>
                        </ol>
                    </nav>
                </div>
                <div class="row">
                    <div class="col-12 grid-margin stretch-card">
                        <div class="card">
                            <div class="card-body">
                                <h4 class="card-title" style="text-align: center;">Update Students</h4>
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
                                    $eid = $_GET['editid'];
                                    if ($_GET['source'] == 'history') 
                                    {
                                        $sql = "SELECT tblstudent.StudentName,tblstudent.StudentEmail,RollNo,tblstudenthistory.ClassID as StudentClass, tblstudenthistory.Section as StudentSection ,tblstudent.Gender,tblstudent.DOB,tblstudent.StuID,tblstudent.FatherName,tblstudent.MotherName,tblstudent.ContactNumber,tblstudent.AltenateNumber,tblstudent.Address,tblstudent.UserName,tblstudent.Password,tblstudent.Image,tblstudent.DateofAdmission,tblstudenthistory.SessionID,tblclass.ClassName,tblclass.Section, tblclass.IsDeleted from tblstudenthistory JOIN tblstudent ON tblstudenthistory.StudentID = tblstudent.ID join tblclass on tblclass.ID=tblstudenthistory.ClassID where tblstudenthistory.ID=:eid AND tblstudenthistory.IsDeleted = 0";
                                    } 
                                    else 
                                    {
                                        $sql = "SELECT tblstudent.ID as ID, tblstudent.StudentName,tblstudent.StudentEmail,RollNo,tblstudent.StudentClass, tblstudent.StudentSection ,tblstudent.Gender,tblstudent.DOB,tblstudent.StuID,tblstudent.FatherName,tblstudent.MotherName,tblstudent.ContactNumber,tblstudent.AltenateNumber,tblstudent.Address,tblstudent.UserName,tblstudent.Password,tblstudent.Image,tblstudent.DateofAdmission,tblstudent.SessionID,tblclass.ClassName,tblclass.Section, tblclass.ID as ClassID, tblclass.IsDeleted from tblstudent join tblclass on tblclass.ID=tblstudent.StudentClass where tblstudent.ID=:eid AND tblstudent.IsDeleted = 0";
                                    }

                                    $query = $dbh->prepare($sql);
                                    $query->bindParam(':eid', $eid, PDO::PARAM_STR);
                                    $query->execute();
                                    $results = $query->fetchAll(PDO::FETCH_OBJ);
                                    $cnt = 1;
                                    if ($query->rowCount() > 0) {
                                        foreach ($results as $row) {
                                            ?>
                                            <div class="form-group">
                                                <label for="exampleInputName1">Student Name</label>
                                                <input type="text" name="stuname"
                                                        value="<?php echo htmlentities($row->StudentName); ?>"
                                                        class="form-control" required='true'
                                                    <?php if ($row->SessionID != $activeSessionID) echo 'readonly'; ?>
                                                >
                                            </div>
                                            <div class="form-group">
                                                <label for="exampleInputName1">Student Email</label>
                                                <input type="text" name="stuemail"
                                                        value="<?php echo htmlentities($row->StudentEmail); ?>"
                                                        class="form-control" required='true'
                                                    <?php if ($row->SessionID != $activeSessionID) echo 'readonly'; ?>
                                                >
                                            </div>
                                            <div class="form-group">
                                                <label for="exampleInputEmail3">Student Class</label>
                                                <select name="stuclass" id="stuclass" class="form-control"
                                                        required='true'
                                                    <?php if ($row->SessionID != $activeSessionID) echo 'disabled'; ?>
                                                >
                                                    <?php
                                                    if ($row->IsDeleted === 0) {
                                                        ?>
                                                        <option
                                                                value="<?php echo htmlentities($row->ClassID); ?>"><?php echo htmlentities($row->ClassName); ?></option>
                                                        <?php
                                                    } else {
                                                        ?>
                                                        <option value="<?php echo htmlentities($row->ClassID); ?>"
                                                                selected>
                                                                Select
                                                        </option>
                                                        <?php
                                                    }
                                                    $sql2 = "SELECT * from tblclass WHERE IsDeleted = 0";
                                                    $query2 = $dbh->prepare($sql2);
                                                    $query2->execute();
                                                    $result2 = $query2->fetchAll(PDO::FETCH_OBJ);

                                                    foreach ($result2 as $row1) {
                                                        ?>
                                                        <option
                                                                value="<?php echo htmlentities($row1->ID); ?>"><?php echo htmlentities($row1->ClassName); ?></option>
                                                        <?php
                                                    }
                                                    ?>
                                                </select>
                                            </div>
                                            <div class="form-group">
                                                <label for="exampleInputEmail3">Student Section</label>
                                                <select name="stusection" id="stusection" class="form-control" required='true'
                                                <?php if ($row->SessionID != $activeSessionID) echo 'disabled'; ?>
                                                >
                                                <?php
                                                    // Fetch sections from the database
                                                    $sectionSql = "SELECT ID, SectionName FROM tblsections WHERE IsDeleted = 0";
                                                    $sectionQuery = $dbh->prepare($sectionSql);
                                                    $sectionQuery->execute();

                                                    while ($sectionRow = $sectionQuery->fetch(PDO::FETCH_ASSOC)) {
                                                        $selected = ($sectionRow['ID'] == $row->StudentSection) ? 'selected' : '';
                                                        echo "<option value='" . htmlentities($sectionRow['ID']) . "' $selected>" . htmlentities($sectionRow['SectionName']) . "</option>";
                                                    }
                                                ?>
                                                </select>
                                            </div>
                                            <div class="form-group">
                                                <label for="exampleInputName1">Student Roll No</label>
                                                <input type="number" name="stuRollNo"
                                                        value="<?php echo htmlentities($row->RollNo); ?>" min="0"
                                                        class="form-control" required='true'
                                                    <?php if ($row->SessionID != $activeSessionID) echo 'readonly'; ?>
                                                >
                                            </div>
                                            <div class="form-group">
                                                <label for="exampleInputName1">Gender</label>
                                                <select name="gender" value="" class="form-control" required='true'
                                                    <?php if ($row->SessionID != $activeSessionID) echo 'disabled'; ?>
                                                >
                                                    <option
                                                            value="<?php echo htmlentities($row->Gender); ?>"><?php echo htmlentities($row->Gender); ?></option>
                                                    <option value="Male">Male</option>
                                                    <option value="Female">Female</option>
                                                </select>
                                            </div>
                                            <div class="form-group">
                                                <label for="exampleInputName1">Date of Birth</label>
                                                <input type="date" name="dob"
                                                        value="<?php echo htmlentities($row->DOB); ?>"
                                                        class="form-control" required='true'
                                                    <?php if ($row->SessionID != $activeSessionID) echo 'readonly'; ?>
                                                >
                                            </div>

                                            <div class="form-group">
                                                <label for="exampleInputName1">Student ID</label>
                                                <input type="text" name="stuid"
                                                        value="<?php echo htmlentities($row->StuID); ?>"
                                                        class="form-control" readonly='true'
                                                    <?php if ($row->SessionID != $activeSessionID) echo 'readonly'; ?>
                                                >
                                            </div>
                                            <div class="form-group">
                                                <label for="exampleInputName1">Student Photo</label>
                                                <img src="images/<?php echo $row->Image; ?>" width="100" height="100"
                                                        value="<?php echo $row->Image; ?>"><?php if ($row->SessionID === $activeSessionID) echo '<a href="changeimage.php?editid=' . $row->ID . '"> &nbsp; Edit Image</a>' ?>
                                            </div>
                                            <h3>Parents/Guardian's details</h3>
                                            <div class="form-group">
                                                <label for="exampleInputName1">Father's Name</label>
                                                <input type="text" name="fname"
                                                        value="<?php echo htmlentities($row->FatherName); ?>"
                                                        class="form-control" required='true'
                                                    <?php if ($row->SessionID != $activeSessionID) echo 'readonly'; ?>
                                                >
                                            </div>
                                            <div class="form-group">
                                                <label for="exampleInputName1">Mother's Name</label>
                                                <input type="text" name="mname"
                                                        value="<?php echo htmlentities($row->MotherName); ?>"
                                                        class="form-control" required='true'
                                                    <?php if ($row->SessionID != $activeSessionID) echo 'readonly'; ?>
                                                >
                                            </div>
                                            <div class="form-group">
                                                <label for="exampleInputName1">Contact Number</label>
                                                <input type="text" name="connum"
                                                        value="<?php echo htmlentities($row->ContactNumber); ?>"
                                                        class="form-control" required='true' maxlength="10"
                                                        pattern="[0-9]+"
                                                    <?php if ($row->SessionID != $activeSessionID) echo 'readonly'; ?>
                                                >
                                            </div>
                                            <div class="form-group">
                                                <label for="exampleInputName1">Alternate Contact Number</label>
                                                <input type="text" name="altconnum"
                                                        value="<?php echo htmlentities($row->AltenateNumber); ?>"
                                                        class="form-control" required='true' maxlength="10"
                                                        pattern="[0-9]+"
                                                    <?php if ($row->SessionID != $activeSessionID) echo 'readonly'; ?>
                                                >
                                            </div>
                                            <div class="form-group">
                                                <label for="exampleInputName1">Address</label>
                                                <textarea name="address" class="form-control" required='true'
                                                    <?php if ($row->SessionID != $activeSessionID) echo 'readonly'; ?>><?php echo htmlentities($row->Address); ?></textarea>
                                            </div>
                                            <h3>Login details</h3>
                                            <div class="form-group">
                                                <label for="exampleInputName1">User Name</label>
                                                <input type="text" name="uname"
                                                        value="<?php echo htmlentities($row->UserName); ?>"
                                                        class="form-control" readonly='true'>
                                            </div>
                                            <div class="form-group">
                                                <label for="exampleInputName1">Password</label>
                                                <input type="Password" name="password"
                                                        value="<?php echo htmlentities($row->Password); ?>"
                                                        class="form-control" readonly='true'>
                                            </div><?php echo ($row->SessionID != $activeSessionID) ? '' : '<button type="button" class="btn btn-primary mr-2" data-toggle="modal" data-target="#confirmationModal">Update</button>' ?>

                                        <?php }
                                    } ?>
                                <!-- Confirmation Modal (Update) -->
                                <div class="modal fade" id="confirmationModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" data-backdrop="static" data-keyboard="false">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                        <div class="modal-header">
                                            <h4 class="modal-title" id="myModalLabel">Confirmation</h4>
                                            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                        </div>
                                        <div class="modal-body">
                                            Are you sure you want to update this Student?
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
            <?php include_once('includes/footer.php'); ?>
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
<script src="./js/SectionsForStudent.js"></script>
<script src="./js/manageAlert.js"></script>
<!-- End custom js for this page -->
</body>
</html><?php } ?>