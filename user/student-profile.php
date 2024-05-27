<?php
session_start();
error_reporting(0);
include('includes/dbconnection.php');
if (strlen($_SESSION['sturecmsstuid']==0)) 
{
  header('location:logout.php');
} 
else
{

  ?>
<!DOCTYPE html>
<html lang="en">
  <head>
   
    <title>TPS || View Students Profile</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- plugins:css -->
    <link rel="stylesheet" href="../admin/vendors/simple-line-icons/css/simple-line-icons.css">
    <link rel="stylesheet" href="../admin/vendors/flag-icon-css/css/flag-icon.min.css">
    <link rel="stylesheet" href="../admin/vendors/css/vendor.bundle.base.css">
    <!-- endinject -->
    <!-- Plugin css for this page -->
    <link rel="stylesheet" href="../admin/vendors/select2/select2.min.css">
    <link rel="stylesheet" href="../admin/vendors/select2-bootstrap-theme/select2-bootstrap.min.css">
    <!-- End plugin css for this page -->
    <!-- inject:css -->
    <!-- endinject -->
    <!-- Layout styles -->
    <link rel="stylesheet" href="../admin/css/style.css" />
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
              <h3 class="page-title"> View Students Profile </h3>
              <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                  <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                  <li class="breadcrumb-item active" aria-current="page"> View Students Profile</li>
                </ol>
              </nav>
            </div>
            <div class="row">
          
              <div class="col-12 grid-margin stretch-card">
                <div class="card">
                  <div class="card-body table table-responsive">
                    
                  <table class="profile-table">
                                        <?php
                                        $sid = $_SESSION['sturecmsstuid'];
                                        $sql = "SELECT 
                                                    tblstudent.StudentName,
                                                    tblstudent.StudentClass,
                                                    tblstudent.StudentSection,
                                                    tblstudent.Gender,
                                                    tblstudent.StuID,
                                                    tblstudent.FatherName,
                                                    tblstudent.ContactNumber,
                                                    tblstudent.Address,
                                                    tblstudent.Password,
                                                    tblstudent.DateofAdmission,
                                                    tblclass.ClassName,
                                                    tblsections.SectionName
                                                FROM 
                                                    tblstudent 
                                                JOIN 
                                                    tblclass ON tblclass.ID = tblstudent.StudentClass
                                                JOIN 
                                                    tblsections ON tblsections.ID = tblstudent.StudentSection
                                                WHERE 
                                                    tblstudent.StuID = :sid";
                                        $query = $dbh->prepare($sql);
                                        $query->bindParam(':sid', $sid, PDO::PARAM_STR);
                                        $query->execute();
                                        $results = $query->fetchAll(PDO::FETCH_OBJ);
                                        $cnt = 1;
                                        if ($query->rowCount() > 0) {
                                            foreach ($results as $row) {
                                        ?>
                                                <tr>
                                                    <th>Student Name</th>
                                                    <td><?php echo $row->StudentName; ?></td>
                                                </tr>
                                                <tr>
                                                    <th>Student Class</th>
                                                    <td><?php echo $row->ClassName; ?> <?php echo $row->SectionName; ?></td>
                                                </tr>
                                                <tr>
                                                    <th>Gender</th>
                                                    <td><?php echo $row->Gender; ?></td>
                                                </tr>
                                                <tr>
                                                    <th>Student ID</th>
                                                    <td><?php echo $row->StuID; ?></td>
                                                </tr>
                                                <tr>
                                                    <th>Father Name</th>
                                                    <td><?php echo $row->FatherName; ?></td>
                                                </tr>
                                                <tr>
                                                    <th>Contact Number</th>
                                                    <td><?php echo $row->ContactNumber; ?></td>
                                                </tr>
                                                <tr>
                                                    <th>Address</th>
                                                    <td><?php echo $row->Address; ?></td>
                                                </tr>
                                                <tr>
                                                    <th>Date of Admission</th>
                                                    <td colspan="3"><?php echo $row->DateofAdmission; ?></td>
                                                </tr>
                                              
                                        <?php
                                            }
                                        } ?>
                                    </table>
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
    <script src="../admin/vendors/js/vendor.bundle.base.js"></script>
    <!-- endinject -->
    <!-- Plugin js for this page -->
    <script src="../admin/vendors/select2/select2.min.js"></script>
    <script src="../admin/vendors/typeahead.js/typeahead.bundle.min.js"></script>
    <!-- End plugin js for this page -->
    <!-- inject:js -->
    <script src="../admin/js/off-canvas.js"></script>
    <script src="../admin/js/misc.js"></script>
    <!-- endinject -->
    <!-- Custom js for this page -->
    <script src="../admin/js/typeahead.js"></script>
    <script src="../admin/js/select2.js"></script>
    <!-- End custom js for this page -->
  </body>
</html><?php }  ?>