<?php
session_start();
error_reporting(0);
include('../../includes/dbconnection.php');

// Fetch active session from tblsessions
$activeSessionSql = "SELECT session_id FROM tblsessions WHERE is_active = 1 AND IsDeleted = 0";
$activeSessionQuery = $dbh->prepare($activeSessionSql);
$activeSessionQuery->execute();
$activeSession = $activeSessionQuery->fetch(PDO::FETCH_COLUMN);

// Get the selected session ID from the AJAX request
$sessionId = $_GET['session_id'] ?? $activeSession;

// Initialize employeePermissions array
$employeePermissions = [];

if (!isset($_SESSION['sturecmsaid'])) 
{
    // Fetch assigned classes and sections for the current employee session
    $sqlAssignedClassesSections = "SELECT AssignedClasses, AssignedSections FROM tblemployees WHERE ID = ?";
    $queryAssignedClassesSections = $dbh->prepare($sqlAssignedClassesSections);
    $queryAssignedClassesSections->execute([$_SESSION['sturecmsEMPid']]);
    $assignedClassesSectionsRow = $queryAssignedClassesSections->fetch(PDO::FETCH_ASSOC);
    $assignedClasses = $assignedClassesSectionsRow['AssignedClasses'];
    $assignedSections = $assignedClassesSectionsRow['AssignedSections'];

    // Fetch permissions for the logged-in user
    $eid = $_SESSION['sturecmsEMPid'];
    $sql = "SELECT Role FROM tblemployees WHERE ID=:eid";
    $query = $dbh->prepare($sql);
    $query->bindParam(':eid', $eid, PDO::PARAM_STR);
    $query->execute();
    $results = $query->fetch(PDO::FETCH_OBJ);
    $employeeRole = $results->Role;

    $sqlPermissions = "SELECT * FROM tblpermissions WHERE RoleID=:employeeRole";
    $queryPermissions = $dbh->prepare($sqlPermissions);
    $queryPermissions->bindParam(':employeeRole', $employeeRole, PDO::PARAM_STR);
    $queryPermissions->execute();
    $permissions = $queryPermissions->fetchAll(PDO::FETCH_OBJ);

    foreach ($permissions as $permission) {
        $employeePermissions[$permission->Name] = [
            'UpdatePermission' => $permission->UpdatePermission,
            'DeletePermission' => $permission->DeletePermission,
        ];
    }
}

// SQL query for fetching student records
$studentSql = "SELECT 
    tblstudent.ID,
    tblstudent.StuID, 
    tblstudent.StudentName, 
    tblstudent.StudentClass, 
    tblstudent.StudentSection, 
    tblstudent.DateofAdmission,
    tblstudent.SessionID,
    tblclass.ClassName,
    tblclass.Section as ClassSection,
    NULL as HistoricalClass,
    NULL as HistoricalSection
FROM tblstudent
JOIN tblclass ON tblstudent.StudentClass = tblclass.ID
WHERE tblstudent.SessionID = :sessionId AND tblstudent.IsDeleted = 0";

if (isset($assignedClasses)) {
    $studentSql .= " AND tblstudent.StudentClass IN ($assignedClasses)";
}

if (isset($assignedSections)) {
    $studentSql .= " AND tblstudent.StudentSection IN ($assignedSections)";
}

$studentSql .= " UNION 
SELECT 
    tblstudenthistory.ID as ID,
    tblstudent.StuID as StuID, 
    tblstudent.StudentName, 
    tblstudenthistory.ClassID as StudentClass, 
    tblstudenthistory.Section as StudentSection,
    tblstudent.DateofAdmission,
    tblstudenthistory.SessionID as SessionID,
    tblclass.ClassName,
    tblclass.Section as ClassSection,
    tblstudenthistory.ClassID as HistoricalClass,
    tblstudenthistory.Section as HistoricalSection
FROM tblstudenthistory
JOIN tblclass ON tblstudenthistory.ClassID = tblclass.ID
JOIN tblstudent ON tblstudenthistory.StudentID = tblstudent.ID
WHERE tblstudenthistory.SessionID = :sessionId AND tblstudenthistory.IsDeleted = 0";

if (isset($assignedClasses)) {
    $studentSql .= " AND tblstudenthistory.ClassID IN ($assignedClasses)";
}

if (isset($assignedSections)) {
    $studentSql .= " AND tblstudenthistory.Section IN ($assignedSections)";
}

$studentQuery = $dbh->prepare($studentSql);
$studentQuery->bindParam(':sessionId', $sessionId, PDO::PARAM_STR);
$studentQuery->execute();
$students = $studentQuery->fetchAll(PDO::FETCH_OBJ);

$pageno = $_GET['pageno'] ?? 1;
$no_of_records_per_page = 15;
$total_pages = ceil(count($students) / $no_of_records_per_page);
$offset = ($pageno - 1) * $no_of_records_per_page;

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
    return $classQuery->fetchColumn() ?: "N/A";
}

// Function to get section name by ID
function getSectionName($sectionID) 
{
    global $dbh;
    $sectionSql = "SELECT SectionName FROM tblsections WHERE ID = :sectionID AND IsDeleted = 0";
    $sectionQuery = $dbh->prepare($sectionSql);
    $sectionQuery->bindParam(':sectionID', $sectionID, PDO::PARAM_INT);
    $sectionQuery->execute();
    return $sectionQuery->fetchColumn() ?: "N/A";
}
?>

<div class="table-responsive border rounded p-1">
    <table class="table">
        <thead>
            <tr>
                <th class="font-weight-bold">S.No</th>
                <th class="font-weight-bold">Student ID</th>
                <th class="font-weight-bold">Student Name</th>
                <th class="font-weight-bold">Student Class</th>
                <th class="font-weight-bold">Student Section</th>
                <th class="font-weight-bold">Admission Date</th>
                <?php 
                if (isset($employeePermissions['Students']) && 
                    ($employeePermissions['Students']['UpdatePermission'] == 1 || 
                    $employeePermissions['Students']['DeletePermission'] == 1) || 
                    isset($_SESSION['sturecmsaid'])) { ?>
                <th class="font-weight-bold">Action</th>
                <?php } ?>
            </tr>
        </thead>
        <tbody>
            <?php
            if ($studentQuery->rowCount() > 0) {
                $cnt = $offset + 1;
                foreach ($students as $student) { ?>
                    <tr>
                        <td><?php echo htmlentities($cnt); ?></td>
                        <td><?php echo htmlentities($student->StuID); ?></td>
                        <td><?php echo htmlentities($student->StudentName); ?></td>
                        <td><?php echo htmlentities(getClassName($student->HistoricalClass ?: $student->StudentClass)); ?></td>
                        <td><?php echo htmlentities(getSectionName($student->HistoricalSection ?: $student->StudentSection)); ?></td>
                        <td><?php echo htmlentities($student->DateofAdmission); ?></td>
                        <?php 
                        if (isset($employeePermissions['Students']) && 
                            ($employeePermissions['Students']['UpdatePermission'] == 1 || 
                            $employeePermissions['Students']['DeletePermission'] == 1) || 
                            isset($_SESSION['sturecmsaid'])) { ?>
                        <td>
                            <div>
                                <?php if (isset($employeePermissions['Students']) && ($employeePermissions['Students']['UpdatePermission'] == 1) || isset($_SESSION['sturecmsaid'])) { ?>
                                    <a href="edit-student-detail.php?editid=<?php echo htmlentities($student->ID); ?>&source=<?php echo htmlentities($student->HistoricalClass ? 'history' : 'current'); ?>">
                                        <i class="icon-pencil"></i>
                                    </a>
                                <?php } ?>
                                <?php if (isset($employeePermissions['Students']) && ($employeePermissions['Students']['UpdatePermission'] == 1 && $employeePermissions['Students']['DeletePermission'] == 1) || isset($_SESSION['sturecmsaid'])) { ?>
                                    ||
                                <?php } ?>
                                <?php if (isset($employeePermissions['Students']) && ($employeePermissions['Students']['DeletePermission'] == 1) || isset($_SESSION['sturecmsaid'])) { ?>
                                    <a href="" onclick="setDeleteId(<?php echo ($student->ID);?>)" data-toggle="modal" data-target="#confirmationModal">
                                        <i class="icon-trash"></i>
                                    </a>
                                <?php } ?>
                            </div>
                        </td>
                        <?php } ?>
                    </tr>
                    <?php $cnt = $cnt + 1; ?>
                <?php } ?>
            <?php } else { ?>
                <tr><td colspan='7'><h3 class='text-center'>No Record found</h4></td></tr>
            <?php } ?>
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

<div class="modal fade" id="confirmationModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="myModalLabel">Confirmation</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">
                Are you sure you want to delete this Student?
            </div>
            <div class="modal-footer">
                <form id="deleteForm" action="" method="post">
                    <input type="hidden" name="studentID" id="studentID">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" name="confirmDelete">Delete</button>
                </form>
            </div>
        </div>
    </div>
</div>
<?php
?>
