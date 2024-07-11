<?php
session_start();
error_reporting(0);
include ('includes/dbconnection.php');

if (strlen($_SESSION['sturecmsaid'] == 0)) {
    header('location:logout.php');
} else {
    $successAlert = false;
    $dangerAlert = false;
    $msg = "";

    try {
        if (isset($_POST['submit'])) {
            $title = filter_var($_POST['title'], FILTER_SANITIZE_STRING);
            $desc = filter_var($_POST['desc'], FILTER_SANITIZE_STRING);

            $image = $_FILES['image'];
            $imageName = basename($image['name']);
            $targetDir = "../Main/img/LatestUpdates/";
            $targetFile = $targetDir . $imageName;
            $imageFileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));
            $url = "LatestUpdates/" . $imageName;
            $size = 2 * 1024 * 1024;  //2MB 

            // Validations
            $check = getimagesize($image['tmp_name']);
            if ($check === false) {
                $msg = "File is not an image.";
                $dangerAlert = true;
            } elseif ($image['size'] > $size) {
                $msg = "Sorry, your file is too large.";
                $dangerAlert = true;
            } elseif (!in_array($imageFileType, ['jpg', 'png', 'jpeg'])) {
                $msg = "Sorry, only JPG, JPEG & PNG files are allowed.";
                $dangerAlert = true;
            } else {
                if (move_uploaded_file($image['tmp_name'], $targetFile)) {
                    $sql = "INSERT INTO tbllatestupdates (title, description, image) VALUES (:title, :description, :image)";
                    $query = $dbh->prepare($sql);
                    $query->bindParam(':title', $title, PDO::PARAM_STR);
                    $query->bindParam(':description', $desc, PDO::PARAM_STR);
                    $query->bindParam(':image', $url, PDO::PARAM_STR);
                    $query->execute();

                    $msg = "Added successfully.";
                    $successAlert = true;
                } else {
                    $msg = "Sorry, there was an error uploading your file.";
                    $dangerAlert = true;
                }
            }
        }
    } catch (PDOException $e) {
        $msg = "Ops! An error occurred.";
        $dangerAlert = true;
        echo "<script>console.error('Error: " . $e->getMessage() . "');</script>";
    }
    ?>

    <!DOCTYPE html>
    <html lang="en">

    <head>
        <title>TPS || Add Updates</title>
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="stylesheet" href="vendors/simple-line-icons/css/simple-line-icons.css">
        <link rel="stylesheet" href="vendors/flag-icon-css/css/flag-icon.min.css">
        <link rel="stylesheet" href="vendors/css/vendor.bundle.base.css">
        <link rel="stylesheet" href="vendors/select2/select2.min.css">
        <link rel="stylesheet" href="vendors/select2-bootstrap-theme/select2-bootstrap.min.css">
        <link rel="stylesheet" href="css/style.css" />
    </head>

    <body>
        <div class="container-scroller">
            <?php include_once ('includes/header.php'); ?>
            <div class="container-fluid page-body-wrapper">
                <?php include_once ('includes/sidebar.php'); ?>
                <div class="main-panel">
                    <div class="content-wrapper">
                        <div class="page-header">
                            <h3 class="page-title"> Add Updates </h3>
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb">
                                    <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                                    <li class="breadcrumb-item active" aria-current="page"> Add Updates</li>
                                </ol>
                            </nav>
                        </div>
                        <div class="row">
                            <div class="col-12 grid-margin stretch-card">
                                <div class="card">
                                    <div class="card-body">
                                        <h4 class="card-title" style="text-align: center;">Add Updates</h4>
                                        <?php
                                        if ($successAlert) {
                                            echo '<div id="success-alert" class="alert alert-success alert-dismissible" role="alert">
                                                <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>' . $msg . '</div>';
                                        }
                                        if ($dangerAlert) {
                                            echo '<div id="danger-alert" class="alert alert-danger alert-dismissible" role="alert">
                                                <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>' . $msg . '</div>';
                                        }
                                        ?>
                                        <form class="forms-sample" method="post" enctype="multipart/form-data">
                                            <div class="form-group">
                                                <label for="title">Title</label>
                                                <input type="text" name="title" id="title" value="" class="form-control"
                                                    required>
                                            </div>
                                            <div class="form-group">
                                                <label for="desc">Description</label>
                                                <textarea name="desc" id="desc" rows="3" class="form-control"
                                                    required></textarea>
                                            </div>
                                            <div class="form-group">
                                                <label for="image">Image</label>
                                                <input type="file" name="image" class="form-control-file" id="image" required>
                                                <p class="text-muted mt-2">Image must be less than 2MB</p>
                                            </div>
                                            <button type="submit" class="btn btn-primary mr-2" name="submit">Add</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php include_once ('includes/footer.php'); ?>
                </div>
            </div>
        </div>
        <script src="vendors/js/vendor.bundle.base.js"></script>
        <script src="vendors/select2/select2.min.js"></script>
        <script src="vendors/typeahead.js/typeahead.bundle.min.js"></script>
        <script src="js/off-canvas.js"></script>
        <script src="js/misc.js"></script>
        <script src="js/typeahead.js"></script>
        <script src="js/select2.js"></script>
        <script src="./js/manageAlert.js"></script>
    </body>

    </html>
<?php } ?>