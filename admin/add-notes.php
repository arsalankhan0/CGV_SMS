<?php
session_start();
error_reporting(0);
include('includes/dbconnection.php');

if (strlen($_SESSION['sturecmsaid'] == 0)) 
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
        if (isset($_POST['submit'])) 
        {
            $classId = filter_var($_POST['class'], FILTER_SANITIZE_STRING);
            $subjectId = filter_var($_POST['subject'], FILTER_SANITIZE_STRING);
            $title = filter_var($_POST['title'], FILTER_SANITIZE_STRING);

            // File upload validation
            if ($_FILES['notesPdf']['error'] === UPLOAD_ERR_OK) 
            {
                $fileTmpPath = $_FILES['notesPdf']['tmp_name'];
                $fileName = $_FILES['notesPdf']['name'];
                $fileSize = $_FILES['notesPdf']['size'];
                $fileType = $_FILES['notesPdf']['type'];
                $fileNameCmps = explode(".", $fileName);
                $fileExtension = strtolower(end($fileNameCmps));

                // Check if file is a PDF and size limit is not exceeded
                $allowedExtensions = array("pdf");
                $maxFileSize = 10485760; // 10MB
                if (in_array($fileExtension, $allowedExtensions) && $fileSize <= $maxFileSize) 
                {
                    $newFileName = "notes_" . time() . '.' . $fileExtension;
                    $uploadFileDir = 'notes/';
                    $destPath = $uploadFileDir . $newFileName;

                    if (move_uploaded_file($fileTmpPath, $destPath)) 
                    {
                        $sql = "INSERT INTO tblnotes (Title, Class, `Subject`, Notes) VALUES (:title, :classId, :subjectID, :notesFile)";
                        $query = $dbh->prepare($sql);
                        $query->bindParam(':title', $title, PDO::PARAM_STR);
                        $query->bindParam(':classId', $classId, PDO::PARAM_INT);
                        $query->bindParam(':subjectID', $subjectId, PDO::PARAM_INT);
                        $query->bindParam(':notesFile', $newFileName, PDO::PARAM_STR);
                        $query->execute();

                        $msg = "Notes has been uploaded successfully.";
                        $successAlert = true;
                    } 
                    else 
                    {
                        $msg = "Failed to move uploaded file.";
                        $dangerAlert = true;
                    }
                } 
                else 
                {
                    $msg = "File must be a PDF and size must be less than 10MB.";
                    $dangerAlert = true;
                }
            } 
            else 
            {
                $msg = "Failed to upload Notes file.";
                $dangerAlert = true;
            }
        }
    } 
    catch (PDOException $e) 
    {
        $msg = "Ops! An Error occurred.";
        $dangerAlert = true;
        echo "<script>console.error('Error:---> " . $e->getMessage() . "');</script>";
    }

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>TPS || Add Notes</title>
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
    <?php include_once('includes/header.php'); ?>
    <!-- partial -->
    <div class="container-fluid page-body-wrapper">
        <!-- partial:partials/_sidebar.html -->
        <?php include_once('includes/sidebar.php'); ?>
        <!-- partial -->
        <div class="main-panel">
            <div class="content-wrapper">
                <div class="page-header">
                    <h3 class="page-title"> Add Notes </h3>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                            <li class="breadcrumb-item active" aria-current="page"> Add Notes</li>
                        </ol>
                    </nav>
                </div>
                <div class="row">
                    <div class="col-12 grid-margin stretch-card">
                        <div class="card">
                            <div class="card-body">
                                <h4 class="card-title" style="text-align: center;">Add Notes</h4>
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
                                    <div class="form-group">
                                        <label for="title">Title</label>
                                        <input type="text" name="title" class="form-control" id="title" placeholder="Enter title" required='true'>
                                    </div>
                                    <div class="form-group">
                                        <label for="classDropdown">Select Class</label>
                                        <select name="class" class="form-control w-100" id="classDropdown">
                                            <option value="">Select Class</option>
                                            <?php
                                            $sql = "SELECT * FROM tblclass WHERE IsDeleted = 0";
                                            $query = $dbh->prepare($sql);
                                            $query->execute();

                                            if ($query->rowCount() > 0) {
                                                $classResults = $query->fetchAll(PDO::FETCH_ASSOC);

                                                foreach ($classResults as $class) {
                                                    $classNameWithSection = $class['ClassName'];
                                                    echo "<option value='" . htmlentities($class['ID']) . "'>" . htmlentities($classNameWithSection) . "</option>";
                                                }
                                            }
                                            ?>
                                        </select>
                                    </div>
                                    <?php
                                        // Fetching current active session.
                                        $sqlActiveSession = "SELECT session_id FROM tblsessions WHERE is_active = 1";
                                        $queryActiveSession = $dbh->prepare($sqlActiveSession);
                                        $queryActiveSession->execute();
                                        $activeSession = $queryActiveSession->fetch(PDO::FETCH_COLUMN);
                                        ?>
                                        <div class="form-group">
                                            <label for="subjectDropdown">Select Subject</label>
                                            <select name="subject" class="form-control w-100" id="subjectDropdown">
                                                <option value="">Select Subject</option>
                                                    <?php
                                                    // // Fetching main subjects
                                                    // $sqlMainSubjects = "SELECT ID, SubjectName 
                                                    //                     FROM tblsubjects
                                                    //                     WHERE IsDeleted = 0 AND SessionID = :activeSession AND IsOptional = 0 AND IsCurricularSubject = 0";
                                                    // $queryMainSubjects = $dbh->prepare($sqlMainSubjects);
                                                    // $queryMainSubjects->bindParam(':activeSession', $activeSession, PDO::PARAM_INT);
                                                    // $queryMainSubjects->execute();
                                                    // $mainSubjects = $queryMainSubjects->fetchAll(PDO::FETCH_ASSOC);

                                                    // foreach ($mainSubjects as $subject) {
                                                    //     echo "<option value='" . htmlentities($subject['ID']) . "'>" . htmlentities($subject['SubjectName']) . "</option>";
                                                    // }
                                                    ?>
                                            </select>
                                        </div>
                                    <div class="form-group">
                                        <label for="exampleInputName1">Upload Notes (PDF only)</label>
                                        <input type="file" name="notesPdf" class="form-control-file" accept=".pdf" required>
                                        <p class="text-muted mt-2">PDF must be less than 10MB</p>
                                    </div>
                                    <button type="submit" class="btn btn-primary mr-2" name="submit">Add</button>
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
<script src="../Main/js/resources.js"></script> 
<!-- End custom js for this page -->
</body>
</html>
<?php } ?>
