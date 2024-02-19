<?php
include('includes/dbconnection.php');

// Get the selected session ID from the AJAX request
$sessionId = $_GET['session_id'];

// Fetch records from tblstudent
$studentSql = "SELECT 
    tblstudent.ID,
    tblstudent.StuID, 
    tblstudent.StudentName, 
    tblstudent.StudentEmail,
    tblstudent.StudentClass, 
    tblstudent.StudentSection, 
    tblstudent.DateofAdmission,
    tblclass.ClassName,
    tblclass.Section as ClassSection,
    NULL as HistoricalClass,
    NULL as HistoricalSection
FROM tblstudent
JOIN tblclass ON tblstudent.StudentClass = tblclass.ID
WHERE tblstudent.SessionID = :sessionId AND tblstudent.IsDeleted = 0
";

// Fetch records from tblstudenthistory
$studentSql .= " UNION 
SELECT 
    tblstudenthistory.ID as ID,
    tblstudent.StuID as StuID, 
    tblstudent.StudentName, 
    tblstudent.StudentEmail,
    tblstudenthistory.ClassID as StudentClass, 
    tblstudenthistory.Section as StudentSection,
    tblstudent.DateofAdmission,
    tblclass.ClassName,
    tblclass.Section as ClassSection,
    tblstudenthistory.ClassID as HistoricalClass,
    tblstudenthistory.Section as HistoricalSection
FROM tblstudenthistory
JOIN tblclass ON tblstudenthistory.ClassID = tblclass.ID
JOIN tblstudent ON tblstudenthistory.StudentID = tblstudent.ID
WHERE tblstudenthistory.SessionID = :sessionId
";

$studentQuery = $dbh->prepare($studentSql);
$studentQuery->bindParam(':sessionId', $sessionId, PDO::PARAM_STR);
$studentQuery->execute();
$students = $studentQuery->fetchAll(PDO::FETCH_OBJ);

// Pagination logic
if (isset($_GET['pageno'])) {
    $pageno = $_GET['pageno'];
} else {
    $pageno = 1;
}

$no_of_records_per_page = 15;
$total_pages = ceil(count($students) / $no_of_records_per_page);
$offset = ($pageno - 1) * $no_of_records_per_page;

// Fetch students with pagination
$studentSql .= " LIMIT :offset, :no_of_records_per_page";

$studentQuery = $dbh->prepare($studentSql);
$studentQuery->bindParam(':sessionId', $sessionId, PDO::PARAM_STR);
$studentQuery->bindParam(':offset', $offset, PDO::PARAM_INT);
$studentQuery->bindParam(':no_of_records_per_page', $no_of_records_per_page, PDO::PARAM_INT);
$studentQuery->execute();
$students = $studentQuery->fetchAll(PDO::FETCH_OBJ);

// Function to get class name by ID
function getClassName($classID) 
{
    global $dbh;

    $classSql = "SELECT ClassName FROM tblclass WHERE ID = :classID";
    $classQuery = $dbh->prepare($classSql);
    $classQuery->bindParam(':classID', $classID, PDO::PARAM_INT);
    $classQuery->execute();
    $className = $classQuery->fetchColumn();

    return $className ? $className : "N/A";
}
?>
<div class="table-responsive border rounded p-1">
    <table class="table">
        <thead>
            <tr>
                <th class="font-weight-bold">S.No</th>
                <th class="font-weight-bold">Student ID</th>
                <th class="font-weight-bold">Student Class</th>
                <th class="font-weight-bold">Student Name</th>
                <th class="font-weight-bold">Student Email</th>
                <th class="font-weight-bold">Admission Date</th>
                <th class="font-weight-bold">Action</th>
            </tr>
        </thead>
        <tbody>
            <?php
            if ($studentQuery->rowCount() > 0)
            {
                $cnt = $offset + 1;
                foreach ($students as $student) 
                {
                    ?>
                    <tr>
                        <td><?php echo htmlentities($cnt); ?></td>
                        <td><?php echo htmlentities($student->StuID); ?></td>
                        <td>
                            <?php
                            $displayClass = $student->HistoricalClass ? getClassName($student->HistoricalClass) : getClassName($student->StudentClass);
                            $displaySection = $student->HistoricalSection ? $student->HistoricalSection : $student->StudentSection;
                            echo htmlentities($displayClass . " " . $displaySection);
                            ?>
                        </td>
                        <td><?php echo htmlentities($student->StudentName); ?></td>
                        <td><?php echo htmlentities($student->StudentEmail); ?></td>
                        <td><?php echo htmlentities($student->DateofAdmission); ?></td>
                        <td>
                            <div>
                                <a href="edit-student-detail.php?editid=<?php echo htmlentities($student->ID); ?>"><i class="icon-eye"></i></a>
                                || <a href="manage-students.php?delid=<?php echo ($student->ID); ?>" onclick="return confirm('Do you really want to Delete ?');"> <i class="icon-trash"></i></a>
                            </div>
                        </td>
                    </tr>
                    <?php
                    $cnt = $cnt + 1;
                }
            } 
            else 
            {
                echo "<tr><td colspan='7'> <h3 class='text-center'>No Record found</h4></td></tr>";
            }
            ?>
        </tbody>
    </table>
</div>
<div align="left">
    <ul class="pagination">
        <li><a href="?pageno=1"><strong>First></strong></a></li>
        <li class="<?php if ($pageno <= 1) { echo 'disabled'; } ?>">
            <a href="<?php if ($pageno <= 1) { echo '#'; } else { echo "?pageno=" . ($pageno - 1); } ?>"><strong style="padding-left: 10px">Prev></strong></a>
        </li>
        <li class="<?php if ($pageno >= $total_pages) { echo 'disabled'; } ?>">
            <a href="<?php if ($pageno >= $total_pages) { echo '#'; } else { echo "?pageno=" . ($pageno + 1); } ?>"><strong style="padding-left: 10px">Next></strong></a>
        </li>
        <li><a href="?pageno=<?php echo $total_pages; ?>"><strong style="padding-left: 10px">Last</strong></a></li>
    </ul>
</div>
