<?php
session_start();
// error_reporting(0);
include('includes/dbconnection.php');

if (strlen($_SESSION['sturecmsEMPid'] == 0)) 
{
    header('location:logout.php');
} 
else 
{
    if (isset($_SESSION['classIDs']) && isset($_SESSION['examName']) && isset($_SESSION['sessionYear']) && isset($_SESSION['studentName'])) 
    {
        try 
        {
            if (isset($_POST['submit'])) 
            {
                
            }

        } 
        catch (PDOException $e) 
        {
            echo '<script>alert("Ops! An Error occurred.")</script>';
            // error_log($e->getMessage()); //-->This is only for debugging purposes
        }

        // Get the filtered Session of studentName. 
        $studentID = filter_var($_SESSION['studentName'], FILTER_VALIDATE_INT);

        // Fetch student details
        $sqlStudent = "SELECT * FROM tblstudent WHERE ID = :studentID AND IsDeleted = 0";
        $queryStudent = $dbh->prepare($sqlStudent);
        $queryStudent->bindParam(':studentID', $studentID, PDO::PARAM_INT);
        $queryStudent->execute();
        $studentDetails = $queryStudent->fetch(PDO::FETCH_ASSOC);

        // Fetch student Class
        $stdClassID = $studentDetails['StudentClass'];
        $sqlStudentClass = "SELECT * FROM tblclass WHERE ID = :stdClassID AND IsDeleted = 0";
        $queryStudentClass = $dbh->prepare($sqlStudentClass);
        $queryStudentClass->bindParam(':stdClassID', $stdClassID, PDO::PARAM_INT);
        $queryStudentClass->execute();
        $studentClass = $queryStudentClass->fetch(PDO::FETCH_ASSOC);

        // Fetch subjects
        $sqlSubjects = "SELECT * FROM tblsubjects WHERE IsDeleted = 0";
        $querySubjects = $dbh->prepare($sqlSubjects);
        $querySubjects->execute();
        $subjects = $querySubjects->fetchAll(PDO::FETCH_ASSOC);
    } 
    else 
    {
        header("Location:create-marks.php");
    }

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <title>Student Management System || Create Student Report</title>
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
        <?php 
        include_once('includes/header.php'); 
        ?>
        <!-- partial -->
        <div class="container-fluid page-body-wrapper">
            <!-- partial:partials/_sidebar.html -->
            <?php include_once('includes/sidebar.php'); ?>
            <!-- partial -->
            <div class="main-panel">
                <div class="content-wrapper">
                    <div class="page-header">
                        <h3 class="page-title"> Create Student Report </h3>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                                <li class="breadcrumb-item active" aria-current="page"> Create Student Report </li>
                            </ol>
                        </nav>
                    </div>
                    <div class="row">
                        <div class="col-12 grid-margin stretch-card">
                            <div class="card">
                                <div class="card-body">
                                    <h4 class="card-title" style="text-align: center;">Create Student Report For
                                        <strong><?php
                                            $sql = "SELECT * FROM tblexamination WHERE ID = " . $_SESSION['examName'] . " AND IsDeleted = 0";
                                            $query = $dbh->prepare($sql);
                                            $query->execute();
                                            $examinations = $query->fetchAll(PDO::FETCH_ASSOC);
                                            foreach ($examinations as $exam) 
                                            {
                                                echo htmlentities($exam['ExamName']);
                                            }
                                            ?>
                                        </strong>
                                    </h4>
                                    <?php 
                                    if (isset($studentDetails))
                                    {
                                        ?>
                                        <div class="col-md-6">
                                            <h4>Student Details</h4>
                                            <table class="table table-bordered">
                                                <tbody>
                                                    <tr>
                                                        <td>Student Name:</td>
                                                        <td><?php echo htmlentities($studentDetails['StudentName']); ?></td>
                                                    </tr>
                                                    <tr>
                                                        <td>Roll No:</td>
                                                        <td><?php 
                                                        // echo htmlentities($studentDetails['RollNo']); 
                                                        ?></td>
                                                    </tr>
                                                    <tr>
                                                        <td>Class:</td>
                                                        <td><?php echo htmlentities($studentClass['ClassName']); ?></td>
                                                    </tr>
                                                    <tr>
                                                        <td>Section:</td>
                                                        <td><?php echo htmlentities($studentClass['Section']); ?></td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    <?php 
                                    }
                                    ?>

                                    <?php 
                                    if (isset($subjects)) 
                                    { 
                                        ?>
                                        <div>
                                            <h4>Report Card</h4>
                                            <table class="table table-responsive table-bordered">
                                                <thead>
                                                    <tr>
                                                        <th></th>
                                                        <th colspan="2" class="text-center font-weight-bold">THEORY</th>
                                                        <th colspan="2" class="text-center font-weight-bold">PRACTICAL</th>
                                                        <th colspan="2" class="text-center font-weight-bold">VIVA</th>
                                                    </tr>
                                                    <tr>
                                                        <th class="font-weight-bold">Subjects</th>
                                                        <th class="font-weight-bold">Max Marks</th>
                                                        <th class="font-weight-bold">Marks Obtained</th>
                                                        <th class="font-weight-bold">Max Marks</th>
                                                        <th class="font-weight-bold">Marks Obtained</th>
                                                        <th class="font-weight-bold">Max Marks</th>
                                                        <th class="font-weight-bold">Marks Obtained</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php 
                                                    foreach ($subjects as $subject)
                                                    {
                                                        ?>
                                                        <tr class="">
                                                            <td><?php echo htmlentities($subject['SubjectName']); ?></td>
                                                            <td> <input type="number" id="th-max-assign" value="0" class="border-0" min="0"></td>
                                                            <td> <input type="number" id="th-obt-assign" value="0" class="border-0" min="0"></td>
                                                            <td><input type="number" id="prac-max-assign" value="0" class="border-0" min="0"></td>
                                                            <td> <input type="number" id="prac-obt-assign" value="0" class="border-0" min="0"></td>
                                                            <td> <input type="number" id="viva-max-assign" value="0" class="border-0" min="0"></td>
                                                            <td> <input type="number" id="viva-obt-assign" value="0" class="border-0" min="0"></td>
                                                        </tr>
                                                    <?php 
                                                    }
                                                    ?>
                                                    <tr>
                                                        <td class="font-weight-bold">TOTAL</td>
                                                        <td id="th-max-marks"></td>
                                                        <td id="th-obt-marks"></td>
                                                        <td id="prac-max-marks"></td>
                                                        <td id="prac-obt-marks"></td>
                                                        <td id="viva-max-marks"></td>
                                                        <td id="viva-obt-marks"></td>
                                                    </tr>
                                                    <tr>
                                                    
                                                        <td colspan="2"></td>
                                                        <td class="font-weight-bold">TOTAL MAX MARKS</td>
                                                        <td class="font-weight-bold">TOTAL OBTAINED MARKS</td>
                                                        <td class="font-weight-bold">PERCENTAGE</td>
                                                    </tr>
                                                    <tr>
                                                        <td class="font-weight-bold" colspan="2">GRAND TOTAL</td>
                                                        <td id="total-max-marks"></td>
                                                        <td id="total-obt-marks"></td>
                                                        <td id="percentage"></td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    <?php
                                    }
                                    ?>
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
    <!-- End custom js for this page -->
</body>

</html>
<?php
}
?>
