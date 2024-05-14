<?php
session_start();
error_reporting(0);
include('includes/dbconnection.php');

// Fetch active session from tblsessions
$activeSessionSql = "SELECT session_id FROM tblsessions WHERE is_active = 1 AND IsDeleted = 0";
$activeSessionQuery = $dbh->prepare($activeSessionSql);
$activeSessionQuery->execute();
$activeSession = $activeSessionQuery->fetch(PDO::FETCH_COLUMN);

// Get the selected session ID from the AJAX request
$sessionId = $_GET['session_id'];

// Fetch assigned classes and sections for the current employee session
$sqlAssignedClassesSections = "SELECT AssignedClasses, AssignedSections FROM tblemployees WHERE ID = ?";
$queryAssignedClassesSections = $dbh->prepare($sqlAssignedClassesSections);
$queryAssignedClassesSections->execute([$_SESSION['sturecmsEMPid']]);
$assignedClassesSectionsRow = $queryAssignedClassesSections->fetch(PDO::FETCH_ASSOC);
$assignedClasses = $assignedClassesSectionsRow['AssignedClasses'];
$assignedSections = $assignedClassesSectionsRow['AssignedSections'];

// Fetch records from tblstudent based on assigned classes and sections
$studentSql = "SELECT 
    tblstudent.ID,
    tblstudent.StuID, 
    tblstudent.StudentName, 
    tblstudent.StudentEmail,
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

// If assigned classes exist, filter students based on those classes
if ($assignedClasses) {
    $studentSql .= " AND tblstudent.StudentClass IN ($assignedClasses)";
}

// If assigned sections exist, filter students based on those sections
if ($assignedSections) {
    $studentSql .= " AND tblstudent.StudentSection IN ($assignedSections)";
}

// Fetch records from tblstudenthistory based on assigned classes and sections
$studentSql .= " UNION 
SELECT 
    tblstudenthistory.ID as ID,
    tblstudent.StuID as StuID, 
    tblstudent.StudentName, 
    tblstudent.StudentEmail,
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

// If assigned classes exist, filter students based on those classes
if ($assignedClasses) {
    $studentSql .= " AND tblstudenthistory.ClassID IN ($assignedClasses)";
}

// If assigned sections exist, filter students based on those sections
if ($assignedSections) {
    $studentSql .= " AND tblstudenthistory.Section IN ($assignedSections)";
}

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

// For Role
$eid = $_SESSION['sturecmsEMPid'];
$sql = "SELECT * FROM tblemployees WHERE ID=:eid";
$query = $dbh->prepare($sql);
$query->bindParam(':eid', $eid, PDO::PARAM_STR);
$query->execute();
$results = $query->fetchAll(PDO::FETCH_OBJ);

$employeeRole = $results[0]->Role;

// Fetch permissions for the logged-in user
$sqlPermissions = "SELECT * FROM tblpermissions WHERE RoleID=:employeeRole";
$queryPermissions = $dbh->prepare($sqlPermissions);
$queryPermissions->bindParam(':employeeRole', $employeeRole, PDO::PARAM_STR);
$queryPermissions->execute();
$permissions = $queryPermissions->fetchAll(PDO::FETCH_OBJ);

$employeePermissions = array();

// Populate the $employeePermissions array with permission names
foreach ($permissions as $permission) 
{
    $employeePermissions[$permission->Name] = array(
        'UpdatePermission' => $permission->UpdatePermission,
        'DeletePermission' => $permission->DeletePermission,
    );
}
?>

<div class="table-responsive border rounded p-1">
    <table class="table">
        <!-- Table headers -->
        <thead>
            <tr>
                <!-- Table headings -->
                <th class="font-weight-bold">S.No</th>
                <th class="font-weight-bold">Student ID</th>
                <th class="font-weight-bold">Student Class</th>
                <th class="font-weight-bold">Student Section</th>
                <th class="font-weight-bold">Student Name</th>
                <th class="font-weight-bold">Student Email</th>
                <th class="font-weight-bold">Admission Date</th>
                <!-- Actions column -->
                <?php 
                // Check if the user has UpdatePermission or DeletePermission
                if ($employeePermissions['Students']['UpdatePermission'] == 1 || $employeePermissions['Students']['DeletePermission'] == 1) 
                { ?>

                <th class="font-weight-bold">Action</th>
                                                        
                <?php } ?>
            </tr>
        </thead>
        <tbody>
            <!-- Loop through fetched students and display them in rows -->
            <?php
            if ($studentQuery->rowCount() > 0)
            {
                $cnt = $offset + 1;
                foreach ($students as $student) 
                {
                    ?>
                    <tr>
                        <!-- Display student details -->
                        <td><?php echo htmlentities($cnt); ?></td>
                        <td><?php echo htmlentities($student->StuID); ?></td>
                        <td>
                            <?php
                            // Get the class name
                            $displayClass = $student->HistoricalClass ? getClassName($student->HistoricalClass) : getClassName($student->StudentClass);
                            echo htmlentities($displayClass);
                            ?>
                        </td>
                        <td>
                            <?php
                            // Get the section name
                            $displaySection = $student->HistoricalSection ? getSectionName($student->HistoricalSection) : getSectionName($student->StudentSection);
                            echo htmlentities($displaySection);
                            ?>
                        </td>
                        <td><?php echo htmlentities($student->StudentName); ?></td>
                        <td><?php echo htmlentities($student->StudentEmail); ?></td>
                        <td><?php echo htmlentities($student->DateofAdmission); ?></td>
                        <!-- Action buttons -->
                        <?php 
                        // Check if the user has UpdatePermission or DeletePermission
                        if ($employeePermissions['Students']['UpdatePermission'] == 1 || $employeePermissions['Students']['DeletePermission'] == 1) 
                        { ?>
                        <td>
                            <div>
                            <?php 
                                // Check if the user has UpdatePermission
                                if ($employeePermissions['Students']['UpdatePermission'] == 1) 
                                {
                                    if ($student->SessionID != $activeSession) 
                                    { 
                                        ?>
                                        <!-- View/Edit student detail link -->
                                        <a href="edit-student-detail.php?editid=<?php echo htmlentities($student->ID); ?>&source=<?php echo htmlentities($student->HistoricalClass ? 'history' : 'current'); ?>"><i class="icon-eye"></i></a>
                                    <?php 
                                    } 
                                    else 
                                    { 
                                        ?>
                                        <!-- Edit student detail link -->
                                        <a href="edit-student-detail.php?editid=<?php echo htmlentities($student->ID); ?>&source=<?php echo htmlentities($student->HistoricalClass ? 'history' : 'current'); ?>"><i class="icon-pencil"></i></a>
                                    <?php 
                                    } 
                                } 
                                // Check if the user has both UpdatePermission and DeletePermission
                                if ($employeePermissions['Students']['UpdatePermission'] == 1 && $employeePermissions['Students']['DeletePermission'] == 1) 
                                { ?>
                                <!-- Separator -->
                                ||
                                <?php
                                }
                                // Check if the user has DeletePermission
                                if ($employeePermissions['Students']['DeletePermission'] == 1) { ?>
                                    <!-- Delete student link -->
                                    <a href="" onclick="setDeleteId(<?php echo ($student->ID);?>)" data-toggle="modal" data-target="#confirmationModal">
                                        <i class="icon-trash"></i>
                                    </a>
                                <?php } ?>
                            </div>
                        </td> 
                        <?php }?>
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

<!-- Pagination -->
<div align="left">
    <ul class="pagination">
        <!-- First page -->
        <li><a href="?pageno=1"><strong>First></strong></a></li>
        <!-- Previous page -->
        <li class="<?php if ($pageno <= 1) { echo 'disabled'; } ?>">
            <a href="<?php if ($pageno <= 1) { echo '#'; } else { echo "?pageno=" . ($pageno - 1); } ?>"><strong style="padding-left: 10px">Prev></strong></a>
        </li>
        <!-- Next page -->
        <li class="<?php if ($pageno >= $total_pages) { echo 'disabled'; } ?>">
            <a href="<?php if ($pageno >= $total_pages) { echo '#'; } else { echo "?pageno=" . ($pageno + 1); } ?>"><strong style="padding-left: 10px">Next></strong></a>
        </li>
        <!-- Last page -->
        <li><a href="?pageno=<?php echo $total_pages; ?>"><strong style="padding-left: 10px">Last</strong></a></li>
    </ul>
</div>

<!-- Confirmation Modal (Delete) -->
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
// Function to get section name by ID
function getSectionName($sectionID)
{
    global $dbh;

    $sectionSql = "SELECT SectionName FROM tblsections WHERE ID = :sectionID AND IsDeleted = 0";
    $sectionQuery = $dbh->prepare($sectionSql);
    $sectionQuery->bindParam(':sectionID', $sectionID, PDO::PARAM_INT);
    $sectionQuery->execute();
    $sectionName = $sectionQuery->fetchColumn();

    return $sectionName ? $sectionName : "N/A";
}
?>
    