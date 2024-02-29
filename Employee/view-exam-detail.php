<?php
session_start();
error_reporting(0);
include('includes/dbconnection.php');

if (strlen($_SESSION['sturecmsEMPid']) == 0) 
{
    header('location:logout.php');
} 
else 
{
    $successAlert = false;
    $dangerAlert = false;
    $msg = false;

    $eid = $_GET['editid'];

    try
    {
        // Code for deletion
        if(isset($_POST['confirmDelete']))
        {
            $rid = intval($_POST['deleteID']);
            $classIdToDelete = $_POST['classID']; // Get the specific class ID to delete

            // Fetch the current class names
            $fetchSql = "SELECT ClassName FROM tblexamination WHERE ID = :rid";
            $fetchQuery = $dbh->prepare($fetchSql);
            $fetchQuery->bindParam(':rid', $rid, PDO::PARAM_STR);
            $fetchQuery->execute();
            $fetchResult = $fetchQuery->fetch(PDO::FETCH_ASSOC);

            if ($fetchResult) 
            {
                $classNames = explode(",", $fetchResult['ClassName']);
                $classNames = array_diff($classNames, array($classIdToDelete));
                $newClassName = implode(",", $classNames);

                // Update the ClassName column to remove the specified class ID
                $updateSql = "UPDATE tblexamination SET ClassName = :newClassName WHERE ID = :rid";
                $updateQuery = $dbh->prepare($updateSql);
                $updateQuery->bindParam(':newClassName', $newClassName, PDO::PARAM_STR);
                $updateQuery->bindParam(':rid', $rid, PDO::PARAM_STR);
                $updateQuery->execute();

                // echo "<script>alert('Class deleted');</script>";
                // echo "<script>window.location.href = 'view-exam-detail.php?editid=$eid'</script>";
                $successAlert = true;
                $msg = "Class deleted successfully.";
            }
        }
    }
    catch(PDOException $e)
    {
        $dangerAlert = true;
        $msg = "Ops! Something went wrong.";
    }
    
?>

<!DOCTYPE html>
<html lang="en">
<head>

    <title>Student Management System|| View Classes</title>
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
                    <?php
                    $eid = $_GET['editid'];

                    $examNameSql = "SELECT ExamName FROM tblexamination WHERE ID = :eid";
                    $examNameQuery = $dbh->prepare($examNameSql);
                    $examNameQuery->bindParam(':eid', $eid, PDO::PARAM_STR);
                    $examNameQuery->execute();
                    $examNameRow = $examNameQuery->fetch(PDO::FETCH_OBJ);
                    $examName = $examNameRow->ExamName;

                    if (isset($examName)) { ?>
                        <h3 class="page-title"> View Classes for '<?php echo $examName; ?>' Exam</h3>
                    <?php } else { ?>
                        <h3 class="page-title"> View Classes </h3>
                    <?php } ?>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                            <li class="breadcrumb-item active" aria-current="page"> View Classes</li>
                        </ol>
                    </nav>
                </div>
                <div class="row">
                    <div class="col-12 grid-margin stretch-card">
                        <div class="card">
                            <div class="card-body">
                                <h4 class="card-title" style="text-align: center;">View Classes</h4>
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

                                <a href="add-more-classes.php?editid=<?php echo $eid;?>">Manage Classes <i class="icon-plus"></i></a>
                                <div class="table-responsive border rounded p-1">
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th class="font-weight-bold">S.No</th>
                                                <th class="font-weight-bold">Classes</th>
                                                <th class="font-weight-bold">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                                if (isset($_GET['pageno'])) 
                                                {
                                                    $pageno = $_GET['pageno'];
                                                } 
                                                else 
                                                {
                                                    $pageno = 1;
                                                }
                                                // Formula for pagination
                                                $no_of_records_per_page = 15;
                                                $offset = ($pageno-1) * $no_of_records_per_page;
                                                $ret = "SELECT ID FROM tblexamination";
                                                $query1 = $dbh -> prepare($ret);
                                                $query1->execute();
                                                $results1=$query1->fetchAll(PDO::FETCH_OBJ);
                                                $total_rows=$query1->rowCount();
                                                $total_pages = ceil($total_rows / $no_of_records_per_page);
                                                $sql = "SELECT * FROM tblexamination WHERE ID = $eid AND IsDeleted = 0 LIMIT $offset, $no_of_records_per_page";
                                                $query = $dbh -> prepare($sql);
                                                $query->execute();
                                                $results=$query->fetchAll(PDO::FETCH_OBJ);

                                                $cnt=1;
                                                if ($query->rowCount() > 0) {
                                                    foreach ($results as $row) {
                                                        $classIds = explode(",", $row->ClassName);
                                                        foreach ($classIds as $classId) {
                                                            $classSql = "SELECT ID, ClassName, Section FROM tblclass WHERE ID = :classId AND IsDeleted = 0";
                                                            $classQuery = $dbh->prepare($classSql);
                                                            $classQuery->bindParam(':classId', $classId, PDO::PARAM_STR);
                                                            $classQuery->execute();
                                                
                                                            if ($classQuery) {
                                                                $classInfo = $classQuery->fetch(PDO::FETCH_ASSOC);
                                                
                                                                if ($classInfo) {
                                                                    ?>
                                                                    <tr>
                                                                        <td><?php echo htmlentities($cnt); ?></td>
                                                                        <td><?php echo htmlentities($classInfo['ClassName']); ?></td>
                                                                        <td>
                                                                            <a href="manage-exam-subject.php?editid=<?php echo htmlentities($classInfo['ID']); ?>&examid=<?php echo htmlentities($row->ID); ?>"><i class="icon-pencil"></i></a>
                                                                            || 
                                                                            
                                                                            <a href="#" onclick="setDeleteId(<?php echo $row->ID; ?>, '<?php echo htmlentities($classId); ?>')" data-toggle="modal" data-target="#confirmationModal">
                                                                                <i class="icon-trash"></i>
                                                                            </a>
                                                                        </td>
                                                                        
                                                                    </tr>
                                                                    <?php
                                                                    $cnt++;
                                                                } else {
                                                                    echo "";
                                                                }
                                                            } else {
                                                                echo "Error fetching class";
                                                            }
                                                        }
                                                    }
                                                }
                                                ?>
                                        </tbody>
                                    </table>
                                </div>
                                <!-- **********Pagination********** -->
                                <div align="left">
                                    <ul class="pagination" >
                                        <li><a href="?pageno=1"><strong>First></strong></a></li>
                                        <li class="<?php if($pageno <= 1){ echo 'disabled'; } ?>">
                                            <a href="<?php if($pageno <= 1){ echo '#'; } else { echo "?pageno=".($pageno - 1); } ?>"><strong style="padding-left: 10px">Prev></strong></a>
                                        </li>
                                        <li class="<?php if($pageno >= $total_pages){ echo 'disabled'; } ?>">
                                            <a href="<?php if($pageno >= $total_pages){ echo '#'; } else { echo "?pageno=".($pageno + 1); } ?>"><strong style="padding-left: 10px">Next></strong></a>
                                        </li>
                                        <li><a href="?pageno=<?php echo $total_pages; ?>"><strong style="padding-left: 10px">Last</strong></a></li>
                                    </ul>
                                </div>
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
        <!-- Confirmation Modal (Delete) -->
        <div class="modal fade" id="confirmationModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" data-backdrop="static" data-keyboard="false">
            <div class="modal-dialog">
                <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="myModalLabel">Confirmation</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body">
                    Are you sure you want to delete this Class?
                </div>
                <div class="modal-footer">
                    <form id="deleteForm" action="" method="post">
                    <input type="hidden" name="deleteID" id="examID">
                    <input type="hidden" name="classID" id="classID">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" name="confirmDelete">Delete</button>
                    </form>
                </div>
                </div>
            </div>
        </div>


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
    <script>
        function setDeleteId(id, classId) 
        {
            document.getElementById('examID').value = id;
            document.getElementById('classID').value = classId;
        }
    </script>
</body>
</html>
<?php
}
?>
