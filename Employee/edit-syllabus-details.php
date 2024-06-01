<?php
session_start();
error_reporting(0);
include('includes/dbconnection.php');

if (strlen($_SESSION['sturecmsEMPid']) == 0) {
    header('location:logout.php');
} else {
    $empID = $_SESSION['sturecmsEMPid'];
    $sql = "SELECT * FROM tblemployees WHERE ID=:empID";
    $query = $dbh->prepare($sql);
    $query->bindParam(':empID', $empID, PDO::PARAM_STR);
    $query->execute();
    $IsAccessible = $query->fetch(PDO::FETCH_ASSOC);

    // Check if the role is "Teaching"
    if ($IsAccessible['EmpType'] != "Teaching") 
    {
        echo "<h1>You have no permission to access this page!</h1>";
        exit;
    }

    $successAlert = false;
    $dangerAlert = false;
    $msg = "";
    $eid = $_GET['editid'];

    try 
    {
        if (isset($_POST['submit'])) {

            // Check if a file is uploaded
            if (!empty($_FILES['syllabus']['name'])) 
            {
                $syllabusName = $_FILES['syllabus']['name'];
                $syllabusTmpName = $_FILES['syllabus']['tmp_name'];
                $syllabusSize = $_FILES['syllabus']['size'];
                $syllabusError = $_FILES['syllabus']['error'];
                $fileNameCmps = explode(".", $syllabusName);
                $fileExtension = strtolower(end($fileNameCmps));

                // Check if file is a PDF and size limit is not exceeded
                $allowedExtensions = array("pdf");
                $maxFileSize = 20 * 1048576; // 20MB
                // File upload validation
                if (in_array($fileExtension, $allowedExtensions) && $syllabusSize <= $maxFileSize) {
                    $newFileName = "planner_" . time() . '.' . $fileExtension;
                    $uploadFileDir = '../admin/syllabus/';
                    $destPath = $uploadFileDir . $newFileName;

                    // Check if the file is a PDF
                    if (move_uploaded_file($syllabusTmpName, $destPath)) 
                    {
                        $fileName = basename($destPath);
                        $sql = "UPDATE tblsyllabus SET Syllabus=:syllabus WHERE ID=:eid";
                        $query = $dbh->prepare($sql);
                        $query->bindParam(':syllabus', $fileName, PDO::PARAM_STR);
                        $query->bindParam(':eid', $eid, PDO::PARAM_INT);
                        $query->execute();
                        $successAlert = true;
                        $msg = "Planner of the selected class has been updated successfully.";
                    } else {
                        $msg = "Failed to move uploaded file.";
                        $dangerAlert = true;
                    }
                } else {
                    $msg = "File must be a PDF and size must be less than 20MB.";
                    $dangerAlert = true;
                }
            } else {
                $msg = "Failed to upload Planner file.";
                $dangerAlert = true;
            }
        }
    } 
    catch (PDOException $e) 
    {
        $dangerAlert = true;
        $msg = "Ops! An error occurred while updating Planner.";
        echo "<script>console.error('Error:---> " . $e->getMessage() . "');</script>";
    }
    // Fetch the class name based on the class ID stored in tblsyllabus
    $className = "";
    $sql = "SELECT c.ClassName FROM tblclass c JOIN tblsyllabus s ON s.Class = c.ID WHERE s.ID = :editid";
    $query = $dbh->prepare($sql);
    $query->bindParam(':editid', $eid, PDO::PARAM_INT);
    $query->execute();
    $row = $query->fetch(PDO::FETCH_ASSOC);
    if ($row) 
    {
        $className = $row['ClassName'];
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>TPS || Update Planner</title>
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
                        <h3 class="page-title"> Update Planner </h3>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                                <li class="breadcrumb-item active" aria-current="page"> Update Planner</li>
                            </ol>
                        </nav>
                    </div>
                    <div class="row">
                        <div class="col-12 grid-margin stretch-card">
                            <div class="card">
                                <div class="card-body">
                                    <h4 class="card-title" style="text-align: center;">Update Planner of Class <?php echo htmlspecialchars($className); ?></h4>
                                    <!-- Dismissible Alert messages -->
                                    <?php if ($successAlert) { ?>
                                        <!-- Success -->
                                        <div id="success-alert" class="alert alert-success alert-dismissible" role="alert">
                                            <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                            <?php echo $msg; ?>
                                        </div>
                                    <?php } ?>
                                    <?php if ($dangerAlert) { ?>
                                        <!-- Danger -->
                                        <div id="danger-alert" class="alert alert-danger alert-dismissible" role="alert">
                                            <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                            <?php echo $msg; ?>
                                        </div>
                                    <?php } ?>

                                    <form class="forms-sample" id="form" method="post" enctype="multipart/form-data">
                                    <?php
                                        $eid=$_GET['editid'];
                                        $sql="SELECT * from tblsyllabus where ID=:eid";
                                        $query = $dbh -> prepare($sql);
                                        $query->bindParam(':eid',$eid,PDO::PARAM_STR);
                                        $query->execute();
                                        $results=$query->fetchAll(PDO::FETCH_OBJ);
                                        if($query->rowCount() > 0)
                                        {
                                            foreach($results as $row)
                                            {   ?>
                                                <div class="form-group">
                                                    <label for="syllabusInput">Upload Planner (PDF only)</label>
                                                    <div class="file-input-wrapper">
                                                        <input type="file" name="syllabus" class="form-control-file border-border-dark" id="syllabusInput" onchange="updateFileName(this)">
                                                        <span id="fileNameLabel"><?php
                                                            if (!empty($row->Syllabus)) {
                                                                $fileName = basename($row->Syllabus);
                                                            }
                                                            ?>
                                                        </span>
                                                    </div>
                                                    <p class="text-muted mt-2">PDF must be less than 20MB</p>
                                                </div>
                                            <?php
                                            }
                                        } 
                                        ?>
                                        <button type="button" class="btn btn-primary mr-2" data-toggle="modal" data-target="#confirmationModal">Update</button>

                                        <!-- Confirmation Modal (Update) -->
                                        <div class="modal fade" id="confirmationModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" data-backdrop="static" data-keyboard="false">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h4 class="modal-title" id="myModalLabel">Confirmation</h4>
                                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        Are you sure you want to update Planner of Class <?php echo htmlspecialchars($className); ?>?
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                                                        <button type="submit" class="btn btn-primary" id="submit" name="submit">Update</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
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
    <!-- End custom js for this page -->
    <script src="./js/dataBinding.js"></script>
    <script src="./js/manageAlert.js"></script>
    <script>

    // Function to set the value of the file input field
    function setFileInputValue(input, fileName) 
    {
        let file = new File([""], fileName, {type: "application/pdf"});
        let dataTransfer = new DataTransfer();
        dataTransfer.items.add(file);
        input.files = dataTransfer.files;
    }
    let input = document.getElementById('syllabusInput'); 
    let fileName = '<?php if (!empty($fileName)) echo $fileName; ?>'; 
    setFileInputValue(input, fileName); 

    </script>
</body>
</html>
<?php } ?>
