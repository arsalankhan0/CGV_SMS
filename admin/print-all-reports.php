<?php
session_start();
// error_reporting(0);
include('includes/dbconnection.php');

if (!isset($_SESSION['sturecmsaid']) || empty($_SESSION['sturecmsaid'])) 
{
    header('location:logout.php');
} 
else 
{

    if (isset($_GET['className']) && isset($_GET['examName']) && isset($_GET['examSession'])) 
    {
        
        $className = urldecode($_GET['className']);
        $examName = urldecode($_GET['examName']);
        $examSession = urldecode($_GET['examSession']);
    
        // Fetch all students and their reports based on the specified criteria
        $sqlReports = "SELECT * FROM tblreports WHERE ClassName = :className AND ExamName = :examName AND ExamSession = :examSession AND IsDeleted = 0";
        $stmtReports = $dbh->prepare($sqlReports);
        $stmtReports->bindParam(':className', $className, PDO::PARAM_STR);
        $stmtReports->bindParam(':examName', $examName, PDO::PARAM_STR);
        $stmtReports->bindParam(':examSession', $examSession, PDO::PARAM_STR);
        $stmtReports->execute();
        $allReports = $stmtReports->fetchAll(PDO::FETCH_ASSOC);

        // Initialize variables for totals
        $theoryMaxMarksTotal = 0;
        $theoryObtMarksTotal = 0;
        $pracMaxMarksTotal = 0;
        $pracObtMarksTotal = 0;
        $vivaMaxMarksTotal = 0;
        $vivaObtMarksTotal = 0;

        if (!$allReports) 
        {
            echo "<script>alert('No data found for the selected criteria.');</script>";
        }
        ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Student Management System || Student Reports</title>
    <link rel="stylesheet" href="vendors/simple-line-icons/css/simple-line-icons.css">
    <link rel="stylesheet" href="vendors/flag-icon-css/css/flag-icon.min.css">
    <link rel="stylesheet" href="vendors/css/vendor.bundle.base.css">
    <link rel="stylesheet" href="vendors/select2/select2.min.css">
    <link rel="stylesheet" href="vendors/select2-bootstrap-theme/select2-bootstrap.min.css">
    <link rel="stylesheet" href="css/style.css" />
</head>
<body>
    <div class="container-scroller">
        <div class="container-fluid page-body-wrapper d-flex flex-column">
            <?php
                $groupedReports = [];

                foreach ($allReports as $report) {
                    // Assuming StudentName is the key to group by
                    $studentName = $report['StudentName'];

                    if (!isset($groupedReports[$studentName])) {
                        $groupedReports[$studentName] = [];
                    }

                    $groupedReports[$studentName][] = $report;
                }

                foreach ($groupedReports as $studentName => $studentReports) {
                    // Fetch student details
                    $studentDetailsSql = "SELECT * FROM tblstudent WHERE ID = :studentID AND IsDeleted = 0";
                    $studentDetailsQuery = $dbh->prepare($studentDetailsSql);
                    $studentDetailsQuery->bindParam(':studentID', $studentReports[0]['StudentName'], PDO::PARAM_INT);
                    $studentDetailsQuery->execute();
                    $studentDetails = $studentDetailsQuery->fetch(PDO::FETCH_ASSOC);

                    // Fetch Class Details
                    $studentClassSql = "SELECT ClassName FROM tblclass WHERE ID = :classID AND IsDeleted = 0";
                    $studentClassQuery = $dbh->prepare($studentClassSql);
                    $studentClassQuery->bindParam(':classID', $studentDetails['StudentClass'], PDO::PARAM_INT);
                    $studentClassQuery->execute();
                    $studentClass = $studentClassQuery->fetch(PDO::FETCH_COLUMN);
            ?>
                <div class="card">
                    <div class="card-body" id="report-card">
                        <h4 class="card-title" style="text-align: center;">Student Report of
                            <strong><?php
                                    $sql = "SELECT * FROM tblexamination WHERE ID = :examID AND IsDeleted = 0";
                                    $query = $dbh->prepare($sql);
                                    $query->bindParam(':examID', $_GET['examName'], PDO::PARAM_INT);
                                    $query->execute();
                                    $examinations = $query->fetchAll(PDO::FETCH_ASSOC);
                                    
                                    foreach ($examinations as $exam) 
                                    {
                                        echo htmlentities($exam['ExamName']);
                                    }
                                    ?>
                            </strong>
                        </h4>

                        <div class="d-flex flex-column">
                            <table class="table table-bordered col-md-6">
                                <tbody>
                                    <tr>
                                        <td>Student Name:</td>
                                        <td><?php echo htmlentities($studentDetails['StudentName']); ?></td>
                                    </tr>
                                    <tr>
                                        <td>Roll No:</td>
                                        <td><?php echo htmlentities($studentDetails['RollNo']); ?></td>
                                    </tr>
                                    <tr>
                                        <td>Class:</td>
                                        <td><?php echo htmlentities($studentClass); ?></td>
                                    </tr>
                                    <tr>
                                        <td>Section:</td>
                                        <td><?php echo htmlentities($studentDetails['StudentSection']); ?></td>
                                    </tr>
                                </tbody>
                            </table>

                            <table class="table table-bordered">
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
                                    foreach ($studentReports as $report) 
                                    {
                                        
                                    $subjectID = $report['Subjects'];
                                    $sqlSubjectsName = "SELECT * FROM tblsubjects WHERE ID = :subjectID AND IsDeleted = 0";
                                    $querySubjectsName = $dbh->prepare($sqlSubjectsName);
                                    $querySubjectsName->bindParam(':subjectID', $subjectID, PDO::PARAM_INT);
                                    $querySubjectsName->execute();
                                    $subjectName = $querySubjectsName->fetch(PDO::FETCH_ASSOC);

                                    // Adding individual subject marks
                                    $theoryMaxMarksTotal += $report['TheoryMaxMarks'];
                                    $theoryObtMarksTotal += $report['TheoryMarksObtained'];
                                    $pracMaxMarksTotal += $report['PracticalMaxMarks'];
                                    $pracObtMarksTotal += $report['PracticalMarksObtained'];
                                    $vivaMaxMarksTotal += $report['VivaMaxMarks'];
                                    $vivaObtMarksTotal += $report['VivaMarksObtained'];
                            
                                    // Calculate grand total and total max marks
                                    $grandTotal = $theoryObtMarksTotal + $pracObtMarksTotal + $vivaObtMarksTotal;
                                    $totalMaxMarks = $theoryMaxMarksTotal + $pracMaxMarksTotal + $vivaMaxMarksTotal;
                                    
                                    // Calculate percentage
                                    $percentage = ($grandTotal / $totalMaxMarks) * 100;
                                    
                                    
                                    
                                    ?>
                                    <tr>
                                        <td><?php echo htmlentities($subjectName['SubjectName']); ?></td>
                                        <td><?php echo htmlentities($report['TheoryMaxMarks']); ?></td>
                                        <td><?php echo htmlentities($report['TheoryMarksObtained']); ?></td>
                                        <td><?php echo htmlentities($report['PracticalMaxMarks']); ?></td>
                                        <td><?php echo htmlentities($report['PracticalMarksObtained']); ?></td>
                                        <td><?php echo htmlentities($report['VivaMaxMarks']); ?></td>
                                        <td><?php echo htmlentities($report['VivaMarksObtained']); ?></td> 
                                    </tr>
                                    <?php
                                    }
                                    ?>
                                    <tr class=" table-secondary">
                                        <td class="font-weight-bold">TOTAL</td>
                                        <td id="th-max-marks"><?php echo $theoryMaxMarksTotal; ?></td>
                                        <td id="th-obt-marks"><?php echo $theoryObtMarksTotal; ?></td>
                                        <td id="prac-max-marks"><?php echo $pracMaxMarksTotal; ?></td>
                                        <td id="prac-obt-marks"><?php echo $pracObtMarksTotal; ?></td>
                                        <td id="viva-max-marks"><?php echo $vivaMaxMarksTotal; ?></td>
                                        <td id="viva-obt-marks"><?php echo $vivaObtMarksTotal; ?></td>
                                    </tr>

                                    <tr>
                                        <td colspan="2"></td>
                                        <td class="font-weight-bold">TOTAL MAX MARKS</td>
                                        <td class="font-weight-bold">TOTAL OBTAINED MARKS</td>
                                        <td class="font-weight-bold">PERCENTAGE</td>
                                    </tr>
                                    <tr class=" table-secondary">
                                        <td class="font-weight-bold" colspan="2">GRAND TOTAL</td>
                                        <td id="total-max-marks"><?php echo $totalMaxMarks; ?></td>
                                        <td id="total-obt-marks"><?php echo $grandTotal; ?></td>
                                        <td id="percentage"><?php echo number_format($percentage, 2) . "%"; ?></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            <?php
            }
            ?>
        </div>
    </div>
    <script src="vendors/js/vendor.bundle.base.js"></script>
    <script src="vendors/select2/select2.min.js"></script>
    <script src="vendors/typeahead.js/typeahead.bundle.min.js"></script>
    <script src="js/off-canvas.js"></script>
    <script src="js/misc.js"></script>
    <script src="js/typeahead.js"></script>
    <script src="js/select2.js"></script>
    <script src="./js/resultGeneration.js"></script>
    <script src="./js/printReportCard.js"></script>
</body>
</html>
<?php
    } 
    else 
    {
        echo "<script>alert('Invalid Request');</script>";
    }
}
?>
