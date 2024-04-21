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
    $successAlert = false;
    $dangerAlert = false;
    $msg = "";

    try 
    {
        if (isset($_POST['submit'])) 
        {
            $nottitle = $_POST['nottitle'];
            $notmsg = $_POST['notmsg'];

            // Check if a file is uploaded
            if (!empty($_FILES['attachment']['name'])) 
            {
                $attachmentName = $_FILES['attachment']['name'];
                $attachmentTmpName = $_FILES['attachment']['tmp_name'];
                $attachmentSize = $_FILES['attachment']['size'];
                $attachmentError = $_FILES['attachment']['error'];

                // File upload validation
                if ($attachmentError === UPLOAD_ERR_OK) 
                {
                    // Get file extension
                    $fileExt = strtolower(pathinfo($attachmentName, PATHINFO_EXTENSION));

                    // Check if the file is a PDF
                    if ($fileExt === 'pdf') 
                    {
                        // Check file size
                        $maxSizeAllowed = 2 * 1024 * 1024; // 2 MB

                        if ($attachmentSize > $maxSizeAllowed) 
                        {
                            $dangerAlert = true;
                            $msg = "Error! Attachment size exceeds the limit of 2MB.";
                        } 
                        else 
                        {
                            // Move the uploaded file to the desired directory
                            $uploadPath = 'attachments/';
                            $attachmentPath = $uploadPath . $attachmentName;
                            move_uploaded_file($attachmentTmpName, $attachmentPath);

                            // Insert data into the database
                            $sql = "INSERT INTO tblpublicnotice (NoticeTitle, NoticeMessage, Attachment) VALUES (:nottitle, :notmsg, :attachment)";
                            $query = $dbh->prepare($sql);
                            $query->bindParam(':nottitle', $nottitle, PDO::PARAM_STR);
                            $query->bindParam(':notmsg', $notmsg, PDO::PARAM_STR);
                            $query->bindParam(':attachment', $attachmentPath, PDO::PARAM_STR);
                            $query->execute();

                            $LastInsertId = $dbh->lastInsertId();
                            if ($LastInsertId > 0) 
                            {
                                $successAlert = true;
                                $msg = "Notice has been added successfully.";
                            } 
                            else 
                            {
                                $dangerAlert = true;
                                $msg = "Something went wrong! Please try again.";
                            }
                        }
                    } 
                    else 
                    {
                        $dangerAlert = true;
                        $msg = "Only PDF files are allowed.";
                    }
                } 
                else 
                {
                    $dangerAlert = true;
                    $msg = "Error uploading file.";
                }
            } 
            else 
            {
                // Insert data into the database without attachment
                $sql = "INSERT INTO tblpublicnotice (NoticeTitle, NoticeMessage) VALUES (:nottitle, :notmsg)";
                $query = $dbh->prepare($sql);
                $query->bindParam(':nottitle', $nottitle, PDO::PARAM_STR);
                $query->bindParam(':notmsg', $notmsg, PDO::PARAM_STR);
                $query->execute();

                $LastInsertId = $dbh->lastInsertId();
                if ($LastInsertId > 0) 
                {
                    $successAlert = true;
                    $msg = "Notice has been added successfully.";
                } 
                else 
                {
                    $dangerAlert = true;
                    $msg = "Something went wrong! Please try again.";
                }
            }
        }
    } 
    catch (PDOException $e) 
    {
        $dangerAlert = true;
        $msg = "Ops! An error occurred while adding public notice.";
        echo "<script>console.error('Error:---> " . $e->getMessage() . "');</script>";
    }

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>TPS || Add Notice</title>
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
                    <h3 class="page-title">Add Notice</h3>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Add Notice</li>
                        </ol>
                    </nav>
                </div>
                <div class="row">
                    <div class="col-12 grid-margin stretch-card">
                        <div class="card">
                            <div class="card-body">
                                <h4 class="card-title" style="text-align: center;">Add Notice</h4>
                                <!-- Dismissible Alert messages -->
                                <?php
                                if ($successAlert) {
                                    ?>
                                    <!-- Success -->
                                    <div id="success-alert" class="alert alert-success alert-dismissible" role="alert">
                                        <button type="button" class="close" data-dismiss="alert"
                                                aria-label="Close"><span
                                                    aria-hidden="true">&times;</span></button>
                                        <?php echo $msg; ?>
                                    </div>
                                    <?php
                                }
                                if ($dangerAlert) {
                                    ?>
                                    <!-- Danger -->
                                    <div id="danger-alert" class="alert alert-danger alert-dismissible" role="alert">
                                        <button type="button" class="close" data-dismiss="alert"
                                                aria-label="Close"><span
                                                    aria-hidden="true">&times;</span></button>
                                        <?php echo $msg; ?>
                                    </div>
                                    <?php
                                }
                                ?>

                                <form class="forms-sample" method="post" enctype="multipart/form-data">
                                    <div class="form-group">
                                        <label for="exampleInputName1">Notice Title</label>
                                        <input type="text" name="nottitle" value="" class="form-control"
                                                required='true'>
                                    </div>
                                    <div class="form-group">
                                        <label for="exampleInputName1">Notice Message</label>
                                        <textarea name="notmsg" value="" class="form-control"
                                                  required='true'></textarea>
                                    </div>
                                    <div class="form-group">
                                        <label for="attachment">Attachment (PDF only)</label>
                                        <input type="file" name="attachment" class="form-control-file">
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
<!-- End custom js for this page -->
</body>
</html>
<?php } ?>
