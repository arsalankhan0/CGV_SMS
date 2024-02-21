<?php
session_start();
// error_reporting(0);
include('includes/dbconnection.php');
if (strlen($_SESSION['sturecmsaid'])==0) 
{
    header('location:logout.php');
} 
else
{
    // Get the active session ID
    $getSessionSql = "SELECT session_id FROM tblsessions WHERE is_active = 1 AND IsDeleted = 0";
    $sessionQuery = $dbh->prepare($getSessionSql);
    $sessionQuery->execute();
    $sessionID = $sessionQuery->fetchColumn();

    // Code for deletion
    if(isset($_GET['delid']))
    {
        $rid=intval($_GET['delid']);
        $sql = "UPDATE tblexamination SET IsDeleted = 1 WHERE ID = :rid";
        $query=$dbh->prepare($sql);
        $query->bindParam(':rid',$rid,PDO::PARAM_STR);
        $query->execute();
        echo "<script>alert('Data deleted');</script>"; 
        echo "<script>window.location.href = 'manage-exam.php'</script>";     
    }
    if (isset($_POST['publish'])) 
    {
        // Get values from the submitted form
        $examId = $_POST['exam_id'];
    
        // Check if already published
        $checkPublishedSql = "SELECT IsPublished, session_id FROM tblexamination WHERE ID = :examId AND IsDeleted = 0";
        $checkPublishedQuery = $dbh->prepare($checkPublishedSql);
        $checkPublishedQuery->bindParam(':examId', $examId, PDO::PARAM_STR);
        $checkPublishedQuery->execute();
        $publish = $checkPublishedQuery->fetch(PDO::FETCH_ASSOC);
    
        if ($publish && $publish['IsPublished'] === 1 && $publish['session_id'] === $sessionID) 
        {
            echo "<script>alert('Exam Already published');</script>";
            echo "<script>window.location.href = 'manage-exam.php'</script>";
        } 
        else 
        {
            // Update IsPublished
            $updateSql = "UPDATE tblexamination SET IsPublished = 1, session_id = :session_id WHERE ID = :examId";
            $updateQuery = $dbh->prepare($updateSql);
            $updateQuery->bindParam(':session_id', $sessionID, PDO::PARAM_STR);
            $updateQuery->bindParam(':examId', $examId, PDO::PARAM_STR);
            $updateQuery->execute();
    
            echo "<script>alert('Exam Published Successfully');</script>";
            echo "<script>window.location.href = 'manage-exam.php'</script>";
        }
    }
    if (isset($_POST['publish_result'])) 
    {
        // Get values from the submitted form
        $examId = $_POST['exam_id'];

        // Check if result is already published
        $checkResultPublishedSql = "SELECT IsResultPublished FROM tblexamination WHERE ID = :examId AND IsDeleted = 0";
        $checkResultPublishedQuery = $dbh->prepare($checkResultPublishedSql);
        $checkResultPublishedQuery->bindParam(':examId', $examId, PDO::PARAM_STR);
        $checkResultPublishedQuery->execute();
        $resultPublished = $checkResultPublishedQuery->fetch(PDO::FETCH_ASSOC);

        if ($resultPublished && $resultPublished['IsResultPublished'] == 1) 
        {
            echo "<script>alert('Result Already Published');</script>";
            echo "<script>window.location.href = 'manage-exam.php'</script>";
        } 
        else 
        {
            // Check if exam is published
            $checkPublishedSql = "SELECT IsPublished, session_id FROM tblexamination WHERE ID = :examId AND IsDeleted = 0";
            $checkPublishedQuery = $dbh->prepare($checkPublishedSql);
            $checkPublishedQuery->bindParam(':examId', $examId, PDO::PARAM_STR);
            $checkPublishedQuery->execute();
            $publish = $checkPublishedQuery->fetch(PDO::FETCH_ASSOC);

            if ($publish && $publish['IsPublished'] == 1 && $publish['session_id'] == $sessionID) 
            {
                // Update IsResultPublished
                $updatePublishResultSql = "UPDATE tblexamination SET IsResultPublished = 1, session_id = :session_id WHERE ID = :examId";
                $updatePublishResultQuery = $dbh->prepare($updatePublishResultSql);
                $updatePublishResultQuery->bindParam(':session_id', $sessionID, PDO::PARAM_STR);
                $updatePublishResultQuery->bindParam(':examId', $examId, PDO::PARAM_STR);
                $updatePublishResultQuery->execute();

                echo "<script>alert('Result Published Successfully');</script>";
                echo "<script>window.location.href = 'manage-exam.php'</script>";
            } 
            else 
            {
                echo "<script>alert('Please Publish the exam first!');</script>";
                echo "<script>window.location.href = 'manage-exam.php'</script>";
            }
        }
    }
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <title>Student  Management System|||Manage Exam</title>
        <!-- plugins:css -->
        <link rel="stylesheet" href="vendors/simple-line-icons/css/simple-line-icons.css">
        <link rel="stylesheet" href="vendors/flag-icon-css/css/flag-icon.min.css">
        <link rel="stylesheet" href="vendors/css/vendor.bundle.base.css">
        <!-- endinject -->
        <!-- Plugin css for this page -->
        <link rel="stylesheet" href="./vendors/daterangepicker/daterangepicker.css">
        <link rel="stylesheet" href="./vendors/chartist/chartist.min.css">
        <!-- End plugin css for this page -->
        <!-- inject:css -->
        <!-- endinject -->
        <!-- Layout styles -->
        <link rel="stylesheet" href="./css/style.css">
        <!-- End layout styles -->
    
    </head>
<body>
    <div class="container-scroller">
        <!-- partial:partials/_navbar.html -->
        <?php include_once('includes/header.php');?>
        <!-- partial -->
      <div class="container-fluid page-body-wrapper">
        <!-- partial:partials/_sidebar.html -->
        <?php include_once('includes/sidebar.php');?>
        <!-- partial -->
        <div class="main-panel">
            <div class="content-wrapper">
                <div class="page-header">
                    <h3 class="page-title"> Manage Exam </h3>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                            <li class="breadcrumb-item active" aria-current="page"> Manage Exam</li>
                        </ol>
                    </nav>
                </div>
                <div class="row">
                    <div class="col-md-12 grid-margin stretch-card">
                        <div class="card">
                            <div class="card-body">
                                <div class="d-sm-flex align-items-center mb-4">
                                    <h4 class="card-title mb-sm-0">Manage Exam</h4>
                                    <a href="#" class="text-dark ml-auto mb-3 mb-sm-0"> View all Exams</a>
                                </div>
                                <div class="table-responsive border rounded p-1">
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th class="font-weight-bold">S.No</th>
                                                <th class="font-weight-bold">Exam Name</th>
                                                <th class="font-weight-bold">Class Name</th>
                                                <th class="font-weight-bold">Creation Date</th>
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
                                                $sql = "SELECT * FROM tblexamination WHERE IsDeleted = 0 LIMIT $offset, $no_of_records_per_page";
                                                $query = $dbh -> prepare($sql);
                                                $query->execute();
                                                $results=$query->fetchAll(PDO::FETCH_OBJ);

                                                $cnt=1;
                                                if($query->rowCount() > 0)
                                                {
                                                    foreach($results as $row)
                                                    {
                                                        ?>   
                                                        <tr>
                                                            <td><?php echo htmlentities($cnt);?></td>
                                                            <td><?php  echo htmlentities($row->ExamName);?></td>
                                                            <td>
                                                                <?php
                                                                    $classIds = explode(",", $row->ClassName);

                                                                    for ($i = 0; $i < count($classIds); $i++) 
                                                                    {
                                                                        $classId = $classIds[$i];
                                                                        $classSql = "SELECT ClassName, Section FROM tblclass WHERE ID = :classId AND IsDeleted = 0";
                                                                        $classQuery = $dbh->prepare($classSql);
                                                                        $classQuery->bindParam(':classId', $classId, PDO::PARAM_STR);
                                                                        $classQuery->execute();
                                                                    
                                                                        if ($classQuery) 
                                                                        {
                                                                            $classInfo = $classQuery->fetch(PDO::FETCH_ASSOC);
                                                                    
                                                                            if ($classInfo) 
                                                                            {
                                                                                echo htmlentities($classInfo['ClassName']);
                                                                    
                                                                                if ($i < count($classIds) - 1) 
                                                                                {
                                                                                    echo ", ";
                                                                                }
                                                                            } 
                                                                            else 
                                                                            {
                                                                                echo ""; 
                                                                            }
                                                                        } 
                                                                        else 
                                                                        {
                                                                            echo "Error fetching class"; 
                                                                        }
                                                                    }
                                                                    
                                                                ?>
                                                            </td>
                                                            <td><?php  echo htmlentities($row->CreationDate);?></td>
                                                            <td>
                                                                <div><a href="view-exam-detail.php?editid=<?php echo htmlentities ($row->ID);?>"><i class="icon-eye"></i></a>
                                                                            || <a href="manage-exam.php?delid=<?php echo ($row->ID);?>" onclick="return confirm('Do you really want to Delete ?');"> <i class="icon-trash"></i></a>
                                                                        </div>
                                                                    </td> 
                                                                    <td>
                                                                        <form method="post" action="">
                                                                            <input type="hidden" name="exam_id" value="<?php echo htmlentities($row->ID); ?>">
                                                                            <button type="submit" name="publish" class="btn-sm btn-dark">Publish</button>
                                                                            <button type="submit" name="publish_result" class="btn-sm btn-dark">Publish Result</button>
                                                                        </form>  
                                                                    </td>                                                                      
                                                                    
                                                        </tr>
                                                        <?php $cnt=$cnt+1;
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
            <?php include_once('includes/footer.php');?>
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
    <script src="./vendors/chart.js/Chart.min.js"></script>
    <script src="./vendors/moment/moment.min.js"></script>
    <script src="./vendors/daterangepicker/daterangepicker.js"></script>
    <script src="./vendors/chartist/chartist.min.js"></script>
    <!-- End plugin js for this page -->
    <!-- inject:js -->
    <script src="js/off-canvas.js"></script>
    <script src="js/misc.js"></script>
    <!-- endinject -->
    <!-- Custom js for this page -->
    <script src="./js/dashboard.js"></script>
    <!-- End custom js for this page -->
</body>
</html>
<?php 
}  
?>