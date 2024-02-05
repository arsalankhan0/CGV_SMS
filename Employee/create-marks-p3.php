<?php
session_start();
error_reporting(0);
include('includes/dbconnection.php');

if (!isset($_SESSION['sturecmsEMPid']) || empty($_SESSION['sturecmsEMPid']))
{
    header('location:logout.php');
} 
else 
{
    if (isset($_SESSION['classIDs']) && isset($_SESSION['examName']) && isset($_SESSION['sessionYear']) && isset($_SESSION['studentName'])) 
    {
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

        try 
        {
            if (isset($_POST['submit'])) 
            {
                // Extracting form data
                $examSession = $_SESSION['sessionYear'];
                $className = $studentClass['ID']; 
                $examName = $_SESSION['examName'];
                $studentName = $studentDetails['ID']; 

                // Extracting data for each subject
                $subjectsData = [];
                foreach ($subjects as $subject) 
                {
                    $subjectName = $subject['SubjectName'];
                    $theoryMaxMarks = $_POST['th-max-marks'][$subjectName];
                    $theoryMarksObtained = $_POST['th-obt-marks'][$subjectName];
                    $practicalMaxMarks = $_POST['prac-max-marks'][$subjectName];
                    $practicalMarksObtained = $_POST['prac-obt-marks'][$subjectName];
                    $vivaMaxMarks = $_POST['viva-max-marks'][$subjectName];
                    $vivaMarksObtained = $_POST['viva-obt-marks'][$subjectName];

                    $subjectsData[$subjectName] = [
                        'theoryMaxMarks' => $theoryMaxMarks,
                        'theoryMarksObtained' => $theoryMarksObtained,
                        'practicalMaxMarks' => $practicalMaxMarks,
                        'practicalMarksObtained' => $practicalMarksObtained,
                        'vivaMaxMarks' => $vivaMaxMarks,
                        'vivaMarksObtained' => $vivaMarksObtained,
                    ];
                }

                // Checking for duplicate entry
                $sqlDuplicate = "SELECT * FROM tblreports WHERE ExamSession = :examSession AND ClassName = :className AND ExamName = :examName AND StudentName = :studentName AND IsDeleted = 0";
                $stmtDuplicate = $dbh->prepare($sqlDuplicate);
                $stmtDuplicate->bindParam(':examSession', $examSession, PDO::PARAM_STR);
                $stmtDuplicate->bindParam(':className', $className, PDO::PARAM_INT);
                $stmtDuplicate->bindParam(':examName', $examName, PDO::PARAM_STR);
                $stmtDuplicate->bindParam(':studentName', $studentName, PDO::PARAM_INT);
                $stmtDuplicate->execute();
                $duplicateEntry = $stmtDuplicate->fetch(PDO::FETCH_ASSOC);

                if ($duplicateEntry) 
                {
                    echo "<script>alert('Duplicate entry found.');</script>";
                } 
                else
                {
                    // Inserting data 
                    $sqlInsert = "INSERT INTO tblreports (ExamSession, ClassName, ExamName, StudentName, Subjects, TheoryMaxMarks, TheoryMarksObtained, PracticalMaxMarks, PracticalMarksObtained, VivaMaxMarks, VivaMarksObtained) VALUES (:examSession, :className, :examName, :studentName, :subjects, :theoryMaxMarks, :theoryMarksObtained, :practicalMaxMarks, :practicalMarksObtained, :vivaMaxMarks, :vivaMarksObtained)";
                    $stmtInsert = $dbh->prepare($sqlInsert);

                    foreach ($subjectsData as $subjectName => $subjectData) 
                    {
                        $stmtInsert->bindParam(':examSession', $examSession, PDO::PARAM_STR);
                        $stmtInsert->bindParam(':className', $className, PDO::PARAM_INT);
                        $stmtInsert->bindParam(':examName', $examName, PDO::PARAM_STR);
                        $stmtInsert->bindParam(':studentName', $studentName, PDO::PARAM_INT);
                        $stmtInsert->bindParam(':subjects', $subjectName, PDO::PARAM_STR);
                        $stmtInsert->bindParam(':theoryMaxMarks', $subjectData['theoryMaxMarks'], PDO::PARAM_INT);
                        $stmtInsert->bindParam(':theoryMarksObtained', $subjectData['theoryMarksObtained'], PDO::PARAM_INT);
                        $stmtInsert->bindParam(':practicalMaxMarks', $subjectData['practicalMaxMarks'], PDO::PARAM_INT);
                        $stmtInsert->bindParam(':practicalMarksObtained', $subjectData['practicalMarksObtained'], PDO::PARAM_INT);
                        $stmtInsert->bindParam(':vivaMaxMarks', $subjectData['vivaMaxMarks'], PDO::PARAM_INT);
                        $stmtInsert->bindParam(':vivaMarksObtained', $subjectData['vivaMarksObtained'], PDO::PARAM_INT);

                        $stmtInsert->execute();
                    }
                    echo "<script>alert('Data Inserted Successfully!'); window.location.href='create-marks-p2.php';</script>";
                }
            }
        } 
        catch (PDOException $e) 
        {
            echo '<script>alert("Ops! An Error occurred.")</script>';
            // error_log($e->getMessage()); //-->This is only for debugging purposes
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
                                        </strong>- Session <?php echo $_SESSION['sessionYear']; ?>
                                    </h4>

                                    <?php 
                                    if (isset($subjects)) 
                                    { 
                                        ?>
                                        <div class="d-flex flex-column">
                                            <?php 
                                            if (isset($studentDetails))
                                            {
                                            ?>
                                            <table class="table table-bordered">
                                                <tbody>
                                                    <tr>
                                                        <td>Student Name:</td>
                                                        <td><?php echo htmlentities($studentDetails['StudentName']); ?></td>
                                                    </tr>
                                                    <tr>
                                                        <td>Roll No:</td>
                                                        <td><?php 
                                                        echo htmlentities($studentDetails['RollNo']); 
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
                                            <?php 
                                            }
                                            ?>
                                            <!-- <h4>Report Card</h4> -->
                                            <form method="post">

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
                                                                <td> <input type="number" id="th-max-assign" name="th-max-marks[<?php echo $subject['SubjectName']; ?>]" value="0" class="border-0" min="0"></td>
                                                                <td> <input type="number" id="th-obt-assign" name="th-obt-marks[<?php echo $subject['SubjectName']; ?>]" value="0" class="border-0" min="0"></td>
                                                                <td><input type="number" id="prac-max-assign" name="prac-max-marks[<?php echo $subject['SubjectName']; ?>]" value="0" class="border-0" min="0"></td>
                                                                <td> <input type="number" id="prac-obt-assign" name="prac-obt-marks[<?php echo $subject['SubjectName']; ?>]" value="0" class="border-0" min="0"></td>
                                                                <td> <input type="number" id="viva-max-assign" name="viva-max-marks[<?php echo $subject['SubjectName']; ?>]" value="0" class="border-0" min="0"></td>
                                                                <td> <input type="number" id="viva-obt-assign" name="viva-obt-marks[<?php echo $subject['SubjectName']; ?>]" value="0" class="border-0" min="0"></td>
                                                            </tr>
                                                        <?php 
                                                        }
                                                        ?>
                                                        <tr class=" table-secondary">
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
                                                            <td rowspan="2" colspan="2"> <button class="btn btn-primary" onclick="generateResult(event)">Generate Result</button></td>
                                                        </tr>
                                                        <tr class=" table-secondary">
                                                            <td class="font-weight-bold" colspan="2">GRAND TOTAL</td>
                                                            <td id="total-max-marks"></td>
                                                            <td id="total-obt-marks"></td>
                                                            <td id="percentage"></td>
                                                        </tr>
                                                        
                                                    </tbody>
                                                </table>
                                                <div class="d-flex justify-content-center mt-3">
                                                    <button class="btn btn-success" name="submit" type="submit">Submit</button>
                                                </div>
                                            </form>
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
    <script src="./js/resultGeneration.js"></script>
    <!-- End custom js for this page -->
</body>

</html>
<?php
}
?>
