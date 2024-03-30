<?php
session_start();
error_reporting(0);
include('includes/dbconnection.php');

if (strlen($_SESSION['sturecmsEMPid'] == 0)) 
{
    header('location:logout.php');
} 
else 
{
    $eid = $_SESSION['sturecmsEMPid'];
    $sql = "SELECT * FROM tblemployees WHERE ID=:eid AND IsDeleted = 0";
    $query = $dbh->prepare($sql);
    $query->bindParam(':eid', $eid, PDO::PARAM_STR);
    $query->execute();
    $employeeData = $query->fetch(PDO::FETCH_ASSOC);
    
    // Check if the role is not "Teaching" or if there are no assigned subjects
    if ($employeeData['EmpType'] != "Teaching") 
    {
        echo "<h1>You have no permission to access this page!</h1>";
        exit;
    }
    // Fetch assigned subjects for the employee
    $assignedSubjects = explode(',', $employeeData['AssignedSubjects']);
    
    // Check if any assigned subject is co-curricular
    $coCurricularAssigned = false;
    
    if (!empty($assignedSubjects)) 
    {
        // Fetch subjects from tblsubjects
        $sqlSubjects = "SELECT * FROM tblsubjects WHERE ID IN (" . implode(",", $assignedSubjects) . ") AND IsCurricularSubject = 1 AND IsDeleted = 0";
        $querySubjects = $dbh->prepare($sqlSubjects);
        $querySubjects->execute();
        $subjects = $querySubjects->fetchAll(PDO::FETCH_ASSOC);
    
        // Check if any co-curricular subject is assigned
        foreach ($subjects as $subject) 
        {
            if ($subject['IsCurricularSubject'] == 1) 
            {
                $coCurricularAssigned = true;
                break;
            }
        }
    }
    // Check if no co-curricular subject is assigned
    if (!$coCurricularAssigned) 
    {
        echo "<h1>You have no permission to access this page!</h1>";
        exit;
    }
    
        
    $dangerAlert = false;
    $msg = "";
    try 
    {
        // Get the active session ID
        $getSessionSql = "SELECT session_id, session_name FROM tblsessions WHERE is_active = 1 AND IsDeleted = 0";
        $sessionQuery = $dbh->prepare($getSessionSql);
        $sessionQuery->execute();
        $session = $sessionQuery->fetchAll();
        
        foreach ($session as $row) 
        {
            $sessionId = $row['session_id'];
            $sessionName = $row['session_name'];
            break;
        }

        if (isset($_POST['submit'])) 
        {
            $examName = filter_var($_POST['exam'], FILTER_SANITIZE_STRING);
            $sectionID = $_POST['sections'];
            $classID = $_POST['classes'];

            if (empty($classID) || empty($sectionID)) 
            {
                $msg = "Please select at least one option in all fields!";
                $dangerAlert = true;
            } 
            else 
            {
                $_SESSION['sessionYear'] = $sessionId;
                $_SESSION['SectionIDs'] = serialize($sectionID);
                $_SESSION['classIDs'] = serialize($classID);

                echo "<script>window.location.href ='create-coCurricular-marks.php'</script>";
            }
        }
    } 
    catch (PDOException $e) 
    {
        $msg = "Ops! An error occurred.";
        $dangerAlert = true;
        echo "<script>console.error('Error:---> " . $e->getMessage() . "');</script>";
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Tibetan Public School || Create Student Report</title>
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
                                <h4 class="card-title" style="text-align: center;">Create Co-Curricular Report of Academic Session <?php echo '('.$sessionName.')'; ?></h4>
                                <!-- Dismissible Alert message -->
                                <?php 
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
                                <form class="forms-sample" method="post">

                                    <div class="form-group">
                                        <label for="exampleFormControlSelect2">Select Class</label>
                                        <?php
                                        $assignedClassSql = "SELECT AssignedClasses FROM tblemployees WHERE ID = :empID AND IsDeleted = 0";
                                        $assignedClassQuery = $dbh->prepare($assignedClassSql);
                                        $assignedClassQuery->bindParam(':empID', $_SESSION['sturecmsEMPid'], PDO::PARAM_INT);
                                        $assignedClassQuery->execute();
                                        $assignedClasses = $assignedClassQuery->fetchColumn();

                                        if (!empty($assignedClasses)) 
                                        {
                                            $assignedClassesArray = explode(',', $assignedClasses);
                                            $inClause = implode(',', array_fill(0, count($assignedClassesArray), '?'));
    
                                            $sql = "SELECT * FROM tblclass WHERE ID IN ($inClause) AND IsDeleted = 0";
                                            $query = $dbh->prepare($sql);
                                            $query->execute($assignedClassesArray);
    
                                            if ($query->rowCount() > 0) 
                                            {
                                                echo '<select name="classes" class="form-control">';
                                                $classResults = $query->fetchAll(PDO::FETCH_ASSOC);
    
                                                foreach ($classResults as $class) 
                                                {
                                                    echo "<option value='" . htmlentities($class['ID']) . "'>" . htmlentities($class['ClassName']) . "</option>";
                                                }
    
                                                echo '</select>';
                                            } 
                                            else 
                                            {
                                                echo '<p>No class assigned.</p>';
                                            }
                                        } 
                                        else 
                                        {
                                            echo '<p>No class assigned.</p>';
                                        }
                                        ?>
                                    </div>
                                    <div class="form-group">
                                        <label for="exampleFormControlSelect2">Select Section</label>
                                        <?php
                                        $assignedSectionSql = "SELECT AssignedSections FROM tblemployees WHERE ID = :empID AND IsDeleted = 0";
                                        $assignedSectionQuery = $dbh->prepare($assignedSectionSql);
                                        $assignedSectionQuery->bindParam(':empID', $_SESSION['sturecmsEMPid'], PDO::PARAM_INT);
                                        $assignedSectionQuery->execute();
                                        $assignedSections = $assignedSectionQuery->fetchColumn();

                                        if (!empty($assignedSections)) 
                                        {
                                            $assignedSectionsArray = explode(',', $assignedSections);
                                            $inClause = implode(',', array_fill(0, count($assignedSectionsArray), '?'));
    
                                            $sql = "SELECT ID, SectionName FROM tblsections WHERE ID IN ($inClause) AND IsDeleted = 0";
                                            $query = $dbh->prepare($sql);
                                            $query->execute($assignedSectionsArray);
    
                                            if ($query->rowCount() > 0) 
                                            {
                                                echo '<select name="sections" class="form-control">';
                                                $sectionResults = $query->fetchAll(PDO::FETCH_ASSOC);
    
                                                foreach ($sectionResults as $section) 
                                                {
                                                    echo "<option value='" . htmlentities($section['ID']) . "'>" . htmlentities($section['SectionName']) . "</option>";
                                                }
    
                                                echo '</select>';
                                            } 
                                            else 
                                            {
                                                echo '<p>No Section assigned.</p>';
                                            }
                                        } 
                                        else 
                                        {
                                            echo '<p>No Section assigned.</p>';
                                        }
                                        ?>
                                    </div>
                                    <button type="submit" class="btn btn-primary mr-2" name="submit">Next</button>
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
<script src="./js/manageAlert.js"></script>
<!-- End custom js for this page -->
</body>
</html>
<?php 
} 
?>
