<?php
session_start();
error_reporting(0);
include('includes/dbconnection.php');
if (strlen($_SESSION['sturecmsaid']==0)) 
{
  header('location:logout.php');
} 
else
{
  $successAlert = false;
  $dangerAlert = false;
  $msg = "";
  try
  {
    if(isset($_POST['confirmDelete']))
    {
        $rid = $_POST['classID'];

        $checkExaminationSql = "SELECT COUNT(*) FROM tblexamination WHERE ClassName = :rid";
        $checkExaminationQuery = $dbh->prepare($checkExaminationSql);
        $checkExaminationQuery->bindParam(':rid', $rid, PDO::PARAM_STR);
        $checkExaminationQuery->execute();
        $examinationRecordCount = $checkExaminationQuery->fetchColumn();

        $checkStudentSql = "SELECT COUNT(*) FROM tblstudent WHERE StudentClass = :rid";
        $checkStudentQuery = $dbh->prepare($checkStudentSql);
        $checkStudentQuery->bindParam(':rid', $rid, PDO::PARAM_STR);
        $checkStudentQuery->execute();
        $studentRecordCount = $checkStudentQuery->fetchColumn();

        $checkAssignedClassSql = "SELECT COUNT(*) FROM tblemployees WHERE FIND_IN_SET(:rid, AssignedClasses)";
        $checkAssignedClassQuery = $dbh->prepare($checkAssignedClassSql);
        $checkAssignedClassQuery->bindParam(':rid', $rid, PDO::PARAM_STR);
        $checkAssignedClassQuery->execute();
        $assignedRecordCount = $checkAssignedClassQuery->fetchColumn();

        if ($examinationRecordCount > 0 || $studentRecordCount > 0 || $assignedRecordCount > 0) 
        {
          $msg = "There are associated records with this class. Please remove the class from associated record first to delete this class";
          $dangerAlert = true;
        } 
        else 
        {
            $sql = "UPDATE tblclass SET IsDeleted = 1 WHERE ID = :rid";
            $query = $dbh->prepare($sql);
            $query->bindParam(':rid', $rid, PDO::PARAM_STR);
            $query->execute();
            
            $msg = "Class Deleted Successfully!";
            $successAlert = true;
        }
    }
  }
  catch(PDOException $e)
  {
    $msg = "Ops! An error occurred.";
    $dangerAlert = true;
  }

?>
<!DOCTYPE html>
<html lang="en">
  <head>
   
    <title>Student  Management System || Manage Class</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
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
              <h3 class="page-title"> Manage Class </h3>
              <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                  <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                  <li class="breadcrumb-item active" aria-current="page"> Manage Class</li>
                </ol>
              </nav>
            </div>
            <div class="row">
              <div class="col-md-12 grid-margin stretch-card">
                <div class="card">
                  <div class="card-body">
                    <div class="d-sm-flex align-items-center mb-4">
                      <h4 class="card-title mb-sm-0">Manage Class</h4>
                      <a href="#" class="text-dark ml-auto mb-3 mb-sm-0"> View all Classes</a>
                    </div>
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
                    <div class="table-responsive border rounded p-1">
                      <table class="table">
                    
                        <thead>
                          <tr>
                            <th class="font-weight-bold">S.No</th>
                            <th class="font-weight-bold">Class Name</th>
                            <th class="font-weight-bold">Sections</th>
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
                              $no_of_records_per_page =15;
                              $offset = ($pageno-1) * $no_of_records_per_page;
                              $ret = "SELECT ID FROM tblclass";
                              $query1 = $dbh -> prepare($ret);
                              $query1->execute();
                              $results1=$query1->fetchAll(PDO::FETCH_OBJ);
                              $total_rows=$query1->rowCount();
                              $total_pages = ceil($total_rows / $no_of_records_per_page);
                              $sql="SELECT * from tblclass WHERE IsDeleted = 0 LIMIT $offset, $no_of_records_per_page";
                              $query = $dbh -> prepare($sql);
                              $query->execute();
                              $results=$query->fetchAll(PDO::FETCH_OBJ);

                              $cnt=1;
                              if($query->rowCount() > 0)
                              {
                                foreach($results as $row)
                                {    
                                  // Fetch SectionName based on Section ID
                                  $sectionId = $row->Section;
                                  $sectionSql = "SELECT SectionName FROM tblsections WHERE ID = :sectionId";
                                  $sectionQuery = $dbh->prepare($sectionSql);
                                  $sectionQuery->bindParam(':sectionId', $sectionId, PDO::PARAM_STR);
                                  $sectionQuery->execute();
                                  $sectionRow = $sectionQuery->fetch(PDO::FETCH_ASSOC);
                                            ?>   
                                  <tr>
                                  
                                    <td><?php echo htmlentities($cnt);?></td>
                                    <td><?php  echo htmlentities($row->ClassName);?></td>
                                    <td><?php  
                                            // Fetch all SectionNames based on Section IDs
                                            $sectionIds = explode(',', $row->Section);
                                            $sectionNames = [];

                                            foreach ($sectionIds as $sectionId) 
                                            {
                                                $sectionSql = "SELECT SectionName FROM tblsections WHERE ID = :sectionId";
                                                $sectionQuery = $dbh->prepare($sectionSql);
                                                $sectionQuery->bindParam(':sectionId', $sectionId, PDO::PARAM_STR);
                                                $sectionQuery->execute();
                                                $sectionRow = $sectionQuery->fetch(PDO::FETCH_ASSOC);

                                                // Add SectionName to the array
                                                $sectionNames[] = htmlentities($sectionRow['SectionName']);
                                            }

                                            echo implode(', ', $sectionNames);?>
                                    </td>
                                    <td><?php  echo htmlentities($row->CreationDate);?></td>
                                    <td>
                                      <div><a href="edit-class-detail.php?editid=<?php echo htmlentities ($row->ID);?>"><i class="icon-pencil"></i></a>
                                                        || <a href="" onclick="setDeleteId(<?php echo ($row->ID);?>)" data-toggle="modal" data-target="#confirmationModal">
                                                                <i class="icon-trash"></i>
                                                            </a>
                                      </div>
                                    </td> 
                                  </tr>
                                  <?php $cnt=$cnt+1;
                                }
                              } ?>
                        </tbody>
                      </table>
                    </div>
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
    <!-- Confirmation Modal (Delete) -->
    <div class="modal fade" id="confirmationModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" data-backdrop="static" data-keyboard="false">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h4 class="modal-title" id="myModalLabel">Confirmation</h4>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
          </div>
          <div class="modal-body">
            Are you sure you want to delete this class?
          </div>
          <div class="modal-footer">
            <form id="deleteForm" action="" method="post">
              <input type="hidden" name="classID" id="classID">
              <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
              <button type="submit" class="btn btn-primary" name="confirmDelete">Delete</button>
            </form>
          </div>
        </div>
      </div>
    </div>


    <!-- container-scroller -->
    <!-- plugins:js -->
    <script src="vendors/js/vendor.bundle.base.js"></script>
    <script src="./js/bootstrap.js"></script>
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
    <script src="./js/manageAlert.js"></script>
    <!-- End custom js for this page -->
    <script>
        function setDeleteId(id) 
        {
            document.getElementById('classID').value = id;
        }
    </script>
  


  </body>
</html><?php }  ?>