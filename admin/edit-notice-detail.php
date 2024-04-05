<?php
session_start();
error_reporting(0);
include('includes/dbconnection.php');
if (strlen($_SESSION['sturecmsaid']) == 0) 
{
    header('location:logout.php');
} 
else 
{
  $msg = "";
  $successAlert = false;
  $dangerAlert = false;
  try
  {
    if (isset($_POST['submit'])) 
    {
        // Update notice logic
        $nottitle = $_POST['nottitle'];
        $classid = $_POST['classid'];
        $selectedSections = implode(',', $_POST['sections']); // Convert array to comma-separated string
        $notmsg = $_POST['notmsg'];
        $eid = $_GET['editid'];

        $sql = "UPDATE tblnotice SET NoticeTitle=:nottitle, ClassId=:classid, SectionID=:selectedSections, NoticeMsg=:notmsg WHERE ID=:eid";
        $query = $dbh->prepare($sql);
        $query->bindParam(':nottitle', $nottitle, PDO::PARAM_STR);
        $query->bindParam(':classid', $classid, PDO::PARAM_STR);
        $query->bindParam(':selectedSections', $selectedSections, PDO::PARAM_STR);
        $query->bindParam(':notmsg', $notmsg, PDO::PARAM_STR);
        $query->bindParam(':eid', $eid, PDO::PARAM_STR);
        $query->execute();
        $msg = "Notice has been updated successfully.";
        $successAlert = true;
    }
  }
  catch(PDOException $e)
  {
    $msg = "Ops! An error occurred!";
    $dangerAlert = true;
    echo "<script>console.error('Error:---> ".$e->getMessage()."');</script>";
  }
?>
    <!DOCTYPE html>
    <html lang="en">
    <head>

        <title>TPS || Update Notice</title>
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
        <link rel="stylesheet" href="css/style.css" />

    </head>
    <body>
        <div class="container-scroller">
            <!-- partial:partials/_navbar.html -->
            <?php include_once('includes/header.php'); ?>
            <!-- partial -->
            <div class="container-fluid page-body-wrapper">
                <!-- partial:partials/_sidebar.html -->
                <?php include_once('includes/sidebar.php'); ?>
                <!-- partial -->
                <div class="main-panel">
                    <div class="content-wrapper">
                        <div class="page-header">
                            <h3 class="page-title">Update Notice </h3>
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb">
                                    <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                                    <li class="breadcrumb-item active" aria-current="page"> Update Notice</li>
                                </ol>
                            </nav>
                        </div>
                        <div class="row">

                            <div class="col-12 grid-margin stretch-card">
                                <div class="card">
                                    <div class="card-body">
                                        <h4 class="card-title" style="text-align: center;">Update Notice</h4>
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
                                        <form class="forms-sample" method="post" enctype="multipart/form-data">
                                            <?php
                                            $eid = $_GET['editid'];
                                            $sql = "SELECT tblclass.ID, tblclass.ClassName, tblclass.Section, tblnotice.NoticeTitle, tblnotice.SectionID, tblnotice.CreationDate, tblnotice.ClassId, tblnotice.NoticeMsg, tblnotice.ID as nid 
                                                    FROM tblnotice 
                                                    JOIN tblclass ON tblclass.ID=tblnotice.ClassId 
                                                    WHERE tblnotice.ID=:eid";
                                            $query = $dbh->prepare($sql);
                                            $query->bindParam(':eid', $eid, PDO::PARAM_STR);
                                            $query->execute();
                                            $results = $query->fetchAll(PDO::FETCH_OBJ);
                                            if ($query->rowCount() > 0) {
                                                foreach ($results as $row) {

                                                  $selectedSections = explode(',', $row->SectionID);
                                                    ?>
                                                    <div class="form-group">
                                                        <label for="exampleInputName1">Notice Title</label>
                                                        <input type="text" name="nottitle" value="<?php echo htmlentities($row->NoticeTitle); ?>" class="form-control" required='true'>
                                                    </div>

                                                    <div class="form-group">
                                                        <label for="exampleInputEmail3">Notice For</label>
                                                        <select name="classid" id="classid" class="form-control">
                                                            <?php
                                                            // Get the selected class ID from tblnotice
                                                            $selectedClassId = $row->ClassId;

                                                            // Fetch classes
                                                            $sqlClasses = "SELECT * FROM tblclass";
                                                            $queryClasses = $dbh->prepare($sqlClasses);
                                                            $queryClasses->execute();
                                                            $resultClasses = $queryClasses->fetchAll(PDO::FETCH_OBJ);

                                                            foreach ($resultClasses as $class) 
                                                            {
                                                                $selected = ($class->ID == $selectedClassId) ? 'selected' : '';
                                                                ?>
                                                                <option value="<?php echo htmlentities($class->ID); ?>" <?php echo $selected; ?>>
                                                                    <?php echo htmlentities($class->ClassName); ?>
                                                                </option>
                                                                <?php
                                                            }
                                                            ?>
                                                        </select>
                                                    </div>


                                                    <div class="form-group">
                                                          <label for="exampleInputName1">Sections</label>
                                                          <select name="sections[]" id="sections" class="js-example-basic-multiple w-100" multiple required='true'>
                                                              <?php
                                                              // Fetch all sections
                                                              $sqlSections = "SELECT * FROM tblsections";
                                                              $querySections = $dbh->prepare($sqlSections);
                                                              $querySections->execute();
                                                              $resultSections = $querySections->fetchAll(PDO::FETCH_OBJ);

                                                              foreach ($resultSections as $section) 
                                                              {
                                                                  $sectionId = $section->ID;
                                                                  $sectionName = $section->SectionName;
                                                                  $selected = in_array($sectionId, $selectedSections) ? 'selected' : '';
                                                                  echo '<option value="' . $sectionId . '" ' . $selected . '>' . $sectionName . '</option>';
                                                              }
                                                              ?>
                                                          </select>
                                                      </div>

                                                    <div class="form-group">
                                                        <label for="exampleInputName1">Notice Message</label>
                                                        <textarea name="notmsg" class="form-control" required='true'><?php echo htmlentities($row->NoticeMsg); ?></textarea>
                                                    </div>
                                                    <?php
                                                }
                                            }
                                            ?>
                                            <button type="submit" class="btn btn-primary mr-2" name="submit">Update</button>

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
<?php } ?>
