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


    if (isset($_SESSION['classIDs']) && isset($_SESSION['examName'])) 
    {
            // Fetch students
    $classIDs = unserialize($_SESSION['classIDs']);
    $sql = "SELECT * FROM tblstudent WHERE StudentClass IN (" . implode(",", $classIDs) . ") AND IsDeleted = 0";
    $query = $dbh->prepare($sql);
    $query->execute();
    $students = $query->fetchAll(PDO::FETCH_ASSOC);

    // Fetch assigned subjects for the teacher
    $teacherID = $_SESSION['sturecmsEMPid'];
    $assignedSubjectsSql = "SELECT AssignedSubjects FROM tblemployees WHERE ID = :teacherID AND IsDeleted = 0";
    $assignedSubjectsQuery = $dbh->prepare($assignedSubjectsSql);
    $assignedSubjectsQuery->bindParam(':teacherID', $teacherID, PDO::PARAM_INT);
    $assignedSubjectsQuery->execute();
    $assignedSubjects = $assignedSubjectsQuery->fetchColumn();

    // Get the active session ID
    $getSessionSql = "SELECT session_id FROM tblsessions WHERE is_active = 1 AND IsDeleted = 0";
    $sessionQuery = $dbh->prepare($getSessionSql);
    $sessionQuery->execute();
    $sessionID = $sessionQuery->fetchColumn();

    if (!empty($assignedSubjects)) 
    {
        $assignedSubjectsIDs = explode(',', $assignedSubjects);

        // Fetch subjects for the selected class
        $subjectSql = "SELECT * FROM tblsubjects WHERE ID IN (" . implode(",", $assignedSubjectsIDs) . ") AND SessionID = $sessionID AND IsDeleted = 0";
        $subjectQuery = $dbh->prepare($subjectSql);
        $subjectQuery->execute();
        $subjects = $subjectQuery->fetchAll(PDO::FETCH_ASSOC);

        // Function to check if a specific type is present in the comma-separated list
        function isSubjectType($type, $subject) 
        {
            $types = explode(',', $subject['SubjectType']);
            return in_array($type, $types);
        }
        // Function to check if the max marks are assigned by the admin
        function getMaxMarks($classID, $examID, $sessionID, $subjectID, $type) 
        {
            global $dbh;
            
            $sql = "SELECT * FROM tblmaxmarks 
                    WHERE ClassID = :classID 
                    AND ExamID = :examID 
                    AND SessionID = :sessionID 
                    AND SubjectID = :subjectID";
            
            $query = $dbh->prepare($sql);
            $query->bindParam(':classID', $classID, PDO::PARAM_INT);
            $query->bindParam(':examID', $examID, PDO::PARAM_INT);
            $query->bindParam(':sessionID', $sessionID, PDO::PARAM_INT);
            $query->bindParam(':subjectID', $subjectID, PDO::PARAM_INT);
            
            $query->execute();
            $result = $query->fetch(PDO::FETCH_ASSOC);
            
            if ($result && $result[$type . 'MaxMarks'] > 0) {
                return $result[$type . 'MaxMarks'];
            }
            
            return null;
        }
    } 
    else 
    {
        echo '<script>alert("No subjects assigned to the teacher.")</script>';
    }
        if (isset($_POST['submit'])) 
        {
            try 
            {
                $examID = $_SESSION['examName'];
    
                foreach ($students as $student) 
                {
                    foreach ($subjects as $subject) 
                    {
                        // Check for duplicate entry
                        $checkDuplicateSql = "SELECT COUNT(*) as count FROM tblreports 
                                                WHERE ExamSession = :sessionID 
                                                AND ClassName = :classID 
                                                AND ExamName = :examID 
                                                AND StudentName = :studentID 
                                                AND Subjects = :subjectID
                                                AND IsDeleted = 0";
    
                        $checkDuplicateQuery = $dbh->prepare($checkDuplicateSql);
                        $checkDuplicateQuery->bindParam(':sessionID', $sessionID, PDO::PARAM_INT);
                        $checkDuplicateQuery->bindParam(':classID', $student['StudentClass'], PDO::PARAM_INT);
                        $checkDuplicateQuery->bindParam(':examID', $examID, PDO::PARAM_INT);
                        $checkDuplicateQuery->bindParam(':studentID', $student['ID'], PDO::PARAM_INT);
                        $checkDuplicateQuery->bindParam(':subjectID', $subject['ID'], PDO::PARAM_INT);
    
                        $checkDuplicateQuery->execute();
                        $duplicateCount = $checkDuplicateQuery->fetchColumn();

                        if ($duplicateCount > 0) 
                        {
                            echo '<script>alert("Duplicate entry found!")</script>';
                            echo "<script>window.location.href ='create-marks-p2.php'</script>";
                            exit;
                        }
    
                        // If no duplicate, proceed to insert data
                        $theoryMaxMarks = isset($_POST['theoryMaxMarks'][$student['ID']][$subject['ID']]) ? $_POST['theoryMaxMarks'][$student['ID']][$subject['ID']] : 0;
                        $theoryMarksObtained = isset($_POST['theoryMarksObtained'][$student['ID']][$subject['ID']]) ? $_POST['theoryMarksObtained'][$student['ID']][$subject['ID']] : 0;
    
                        $practicalMaxMarks = isset($_POST['practicalMaxMarks'][$student['ID']][$subject['ID']]) ? $_POST['practicalMaxMarks'][$student['ID']][$subject['ID']] : 0;
                        $practicalMarksObtained = isset($_POST['practicalMarksObtained'][$student['ID']][$subject['ID']]) ? $_POST['practicalMarksObtained'][$student['ID']][$subject['ID']] : 0;
    
                        $vivaMaxMarks = isset($_POST['vivaMaxMarks'][$student['ID']][$subject['ID']]) ? $_POST['vivaMaxMarks'][$student['ID']][$subject['ID']] : 0;
                        $vivaMarksObtained = isset($_POST['vivaMarksObtained'][$student['ID']][$subject['ID']]) ? $_POST['vivaMarksObtained'][$student['ID']][$subject['ID']] : 0;
    
                        $studentID = $student['ID'];
                        $subjectID = $subject['ID'];
    
                        // Insert data into tblreports table
                        $insertSql = "INSERT INTO tblreports (ExamSession, ClassName, ExamName, StudentName, Subjects, TheoryMaxMarks, TheoryMarksObtained, PracticalMaxMarks, PracticalMarksObtained, VivaMaxMarks, VivaMarksObtained)
                                        VALUES (:sessionID, :classID, :examID, :studentID, :subjectID, :theoryMaxMarks, :theoryMarksObtained, :practicalMaxMarks, :practicalMarksObtained, :vivaMaxMarks, :vivaMarksObtained)";
    
                        $insertQuery = $dbh->prepare($insertSql);
                        $insertQuery->bindParam(':sessionID', $sessionID, PDO::PARAM_INT);
                        $insertQuery->bindParam(':classID', $student['StudentClass'], PDO::PARAM_INT);
                        $insertQuery->bindParam(':examID', $examID, PDO::PARAM_INT);
                        $insertQuery->bindParam(':studentID', $studentID, PDO::PARAM_INT);
                        $insertQuery->bindParam(':subjectID', $subjectID, PDO::PARAM_INT);
                        $insertQuery->bindParam(':theoryMaxMarks', $theoryMaxMarks, PDO::PARAM_INT);
                        $insertQuery->bindParam(':theoryMarksObtained', $theoryMarksObtained, PDO::PARAM_INT);
                        $insertQuery->bindParam(':practicalMaxMarks', $practicalMaxMarks, PDO::PARAM_INT);
                        $insertQuery->bindParam(':practicalMarksObtained', $practicalMarksObtained, PDO::PARAM_INT);
                        $insertQuery->bindParam(':vivaMaxMarks', $vivaMaxMarks, PDO::PARAM_INT);
                        $insertQuery->bindParam(':vivaMarksObtained', $vivaMarksObtained, PDO::PARAM_INT);
    
                        $insertQuery->execute();
                    }
                }
    
                echo '<script>alert("Marks assigned successfully.")</script>';
            } 
            catch (PDOException $e) 
            {
                echo '<script>alert("Ops! An error occurred.")</script>';
            }
        }
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
                                <h4 class="card-title" style="text-align: center;">Create Student Report For <strong><?php
                                    $sql = "SELECT * FROM tblexamination WHERE ID = " . $_SESSION['examName'] . " AND IsDeleted = 0";
                                    $query = $dbh->prepare($sql);
                                    $query->execute();
                                    $examinations = $query->fetchAll(PDO::FETCH_ASSOC);

                                    foreach ($examinations as $exam) {
                                        echo htmlentities($exam['ExamName']);
                                    }
                                    ?></strong></h4>

                                <form class="forms-sample" method="post">
                                    
                                    <div class="table-responsive">
                                        <table class="table text-center table-bordered">
                                                <tr>
                                                    <th rowspan="3">Student Name</th>
                                                    <?php foreach ($subjects as $subject) { ?>
                                                        <th colspan="6" class="text-center font-weight-bold" style="font-size: 20px; letter-spacing: 2px;"><?php echo htmlentities($subject['SubjectName']); ?></th>
                                                    <?php } ?>
                                                </tr>
                                                <tr>
                                                    <?php foreach ($subjects as $subject) 
                                                    { ?>
                                                        <th colspan="2">Theory</th>
                                                        <th colspan="2">Practical</th>
                                                        <th colspan="2">Viva</th>
                                                    <?php } ?>
                                                </tr>
                                                <tr>
                                                    <?php foreach ($subjects as $subject) { ?>
                                                        <td>Max Marks</td>
                                                        <td>Marks Obtained</td>
                                                        <td>Max Marks</td>
                                                        <td>Marks Obtained</td>
                                                        <td>Max Marks</td>
                                                        <td>Marks Obtained</td>
                                                    <?php } ?>
                                                </tr>
                                            <tbody>
                                            <?php foreach ($students as $student) 
                                            { ?>
                                                <tr>
                                                    <td class="font-weight-bold"><?php echo htmlentities($student['StudentName']); ?></td>
                                                    <?php foreach ($subjects as $subject) 
                                                    { ?>
                                                        <?php
                                                            $theoryMaxMarks = getMaxMarks($student['StudentClass'], $_SESSION['examName'], $sessionID, $subject['ID'], 'Theory');
                                                            $practicalMaxMarks = getMaxMarks($student['StudentClass'], $_SESSION['examName'], $sessionID, $subject['ID'], 'Practical');
                                                            $vivaMaxMarks = getMaxMarks($student['StudentClass'], $_SESSION['examName'], $sessionID, $subject['ID'], 'Viva');
                                                        ?>
                                                            <?php if ($theoryMaxMarks !== null || $practicalMaxMarks !== null || $vivaMaxMarks !== null) 
                                                            { ?>
                                                                <td>
                                                                    <input type='number' class='border border-secondary' name="theoryMaxMarks[<?php echo $student['ID']; ?>][<?php echo $subject['ID']; ?>]" 
                                                                        <?php echo (isSubjectType('theory', $subject)) ? '' : 'disabled'; ?>
                                                                        value="<?php echo $theoryMaxMarks; ?>">
                                                                </td>
                                                                <td>
                                                                    <input type='number' class='border border-secondary' name="theoryMarksObtained[<?php echo $student['ID']; ?>][<?php echo $subject['ID']; ?>]" 
                                                                        <?php echo (isSubjectType('theory', $subject)) ? '' : 'disabled'; ?>>
                                                                </td>
                                                                <td>
                                                                    <input type='number' class='border border-secondary' name="practicalMaxMarks[<?php echo $student['ID']; ?>][<?php echo $subject['ID']; ?>]" 
                                                                        <?php echo (isSubjectType('practical', $subject)) ? '' : 'disabled'; ?>
                                                                        value="<?php echo $practicalMaxMarks; ?>">
                                                                </td>
                                                                <td>
                                                                    <input type='number' class='border border-secondary' name="practicalMarksObtained[<?php echo $student['ID']; ?>][<?php echo $subject['ID']; ?>]" 
                                                                        <?php echo (isSubjectType('practical', $subject)) ? '' : 'disabled'; ?>>
                                                                </td>
                                                                <td>
                                                                    <input type='number' class='border border-secondary' name="vivaMaxMarks[<?php echo $student['ID']; ?>][<?php echo $subject['ID']; ?>]" 
                                                                        <?php echo (isSubjectType('viva', $subject)) ? '' : 'disabled'; ?>
                                                                        value="<?php echo $vivaMaxMarks; ?>">
                                                                </td>
                                                                <td>
                                                                    <input type='number' class='border border-secondary' name="vivaMarksObtained[<?php echo $student['ID']; ?>][<?php echo $subject['ID']; ?>]" 
                                                                        <?php echo (isSubjectType('viva', $subject)) ? '' : 'disabled'; ?>>
                                                                </td>
                                                            <?php 
                                                            } 
                                                            else
                                                            {
                                                            ?>
                                                            <td>
                                                                <input type='number' class='border border-secondary' name="theoryMaxMarks[<?php echo $student['ID']; ?>][<?php echo $subject['ID']; ?>]" 
                                                                        <?php echo (isSubjectType('theory', $subject)) ? '' : 'disabled'; ?>
                                                                        >
                                                                </td>
                                                                <td>
                                                                    <input type='number' class='border border-secondary' name="theoryMarksObtained[<?php echo $student['ID']; ?>][<?php echo $subject['ID']; ?>]" 
                                                                        <?php echo (isSubjectType('theory', $subject)) ? '' : 'disabled'; ?>>
                                                                </td>
                                                                <td>
                                                                    <input type='number' class='border border-secondary' name="practicalMaxMarks[<?php echo $student['ID']; ?>][<?php echo $subject['ID']; ?>]" 
                                                                        <?php echo (isSubjectType('practical', $subject)) ? '' : 'disabled'; ?>
                                                                        >
                                                                </td>
                                                                <td>
                                                                    <input type='number' class='border border-secondary' name="practicalMarksObtained[<?php echo $student['ID']; ?>][<?php echo $subject['ID']; ?>]" 
                                                                        <?php echo (isSubjectType('practical', $subject)) ? '' : 'disabled'; ?>>
                                                                </td>
                                                                <td>
                                                                    <input type='number' class='border border-secondary' name="vivaMaxMarks[<?php echo $student['ID']; ?>][<?php echo $subject['ID']; ?>]" 
                                                                        <?php echo (isSubjectType('viva', $subject)) ? '' : 'disabled'; ?>
                                                                        >
                                                                </td>
                                                                <td>
                                                                    <input type='number' class='border border-secondary' name="vivaMarksObtained[<?php echo $student['ID']; ?>][<?php echo $subject['ID']; ?>]" 
                                                                        <?php echo (isSubjectType('viva', $subject)) ? '' : 'disabled'; ?>>
                                                                </td>
                                                            <?php
                                                                }
                                                    } ?>
                                                </tr>
                                                <?php 
                                            } 
                                            ?>
                                            </tbody>
                                        </table>
                                    </div>
                                    <div class="pt-3">
                                        <button type="submit" class="btn btn-primary mr-2" name="submit">Assign Marks</button>
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
<!-- End custom js for this page -->
</body>
</html>
<?php } ?>
