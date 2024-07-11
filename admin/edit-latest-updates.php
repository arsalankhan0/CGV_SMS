<?php
session_start();
error_reporting(0);
include ('includes/dbconnection.php');

if (strlen($_SESSION['sturecmsaid']) == 0) {
    header('location:logout.php');
} else {
    $successAlert = false;
    $dangerAlert = false;
    $msg = "";
    $eid = $_GET['editid'];

    function getData($dbh, $id)
    {
        $sql = "SELECT * from tbllatestupdates where ID=:id";
        $query = $dbh->prepare($sql);
        $query->bindParam(':id', $id, PDO::PARAM_STR);
        $query->execute();
        $results = $query->fetchAll(PDO::FETCH_ASSOC);

        if ($query->rowCount() > 0) {
            return $results;
        }
        return false;
    }

    try {
        if (isset($_POST['submit'])) {
            $title = filter_var($_POST['title'], FILTER_SANITIZE_STRING);
            $desc = filter_var($_POST['desc'], FILTER_SANITIZE_STRING);

            $image = $_FILES['image'];
            $imageName = basename($image['name']);
            $targetDir = "../Main/img/LatestUpdates/";
            $targetFile = $targetDir . $imageName;
            $imageFileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));
            $url = "";
            $size = 2 * 1024 * 1024;  //2MB 


            $result = getData($dbh, $eid);

            if (empty($imageName)) {
                foreach ($result as $row) {
                    $url = $row['image'];
                }
                $sql = "UPDATE tbllatestupdates SET title = :title, description = :desc, image = :image WHERE ID = :eid";
                $query = $dbh->prepare($sql);
                $query->bindParam(':title', $title, PDO::PARAM_STR);
                $query->bindParam(':desc', $desc, PDO::PARAM_STR);
                $query->bindParam(':image', $url, PDO::PARAM_STR);
                $query->bindParam(':eid', $eid, PDO::PARAM_INT);
                $query->execute();

                $msg = "Updated successfully.";
                $successAlert = true;

            } else {
                $url = "LatestUpdates/" . $imageName;

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
                        $sql = "UPDATE tbllatestupdates SET title = :title, description = :desc, image = :image WHERE ID = :eid";
                        $query = $dbh->prepare($sql);
                        $query->bindParam(':title', $title, PDO::PARAM_STR);
                        $query->bindParam(':desc', $desc, PDO::PARAM_STR);
                        $query->bindParam(':image', $url, PDO::PARAM_STR);
                        $query->bindParam(':eid', $eid, PDO::PARAM_INT);
                        $query->execute();

                        $msg = "Updated successfully.";
                        $successAlert = true;
                    } else {
                        $msg = "Sorry, there was an error uploading your file.";
                        $dangerAlert = true;
                    }
                }
            }

        }
    } catch (PDOException $e) {
        $msg = "Oops! An error occurred while updating the details";
        $dangerAlert = true;
        echo "<script>console.error('Error: " . $e->getMessage() . "');</script>";
    }
    ?>
    <!DOCTYPE html>
    <html lang="en">

    <head>
        <title>TPS || Edit Latest Updates</title>
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
            <?php include_once ('includes/header.php'); ?>
            <!-- partial -->
            <div class="container-fluid page-body-wrapper">
                <!-- partial:partials/_sidebar.html -->
                <?php include_once ('includes/sidebar.php'); ?>
                <!-- partial -->
                <div class="main-panel">
                    <div class="content-wrapper">
                        <div class="page-header">
                            <h3 class="page-title"> Edit Latest Updates </h3>
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb">
                                    <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                                    <li class="breadcrumb-item active" aria-current="page"> Edit Latest Updates</li>
                                </ol>
                            </nav>
                        </div>
                        <div class="row">
                            <div class="col-12 grid-margin stretch-card">
                                <div class="card">
                                    <div class="card-body">
                                        <h4 class="card-title" style="text-align: center;">Edit Latest Updates</h4>
                                        <!-- Dismissible Alert messages -->
                                        <?php if ($successAlert) { ?>
                                            <!-- Success -->
                                            <div id="success-alert" class="alert alert-success alert-dismissible" role="alert">
                                                <button type="button" class="close" data-dismiss="alert"
                                                    aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                                <?php echo $msg; ?>
                                            </div>
                                        <?php } ?>
                                        <?php if ($dangerAlert) { ?>
                                            <!-- Danger -->
                                            <div id="danger-alert" class="alert alert-danger alert-dismissible" role="alert">
                                                <button type="button" class="close" data-dismiss="alert"
                                                    aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                                <?php echo $msg; ?>
                                            </div>
                                        <?php } ?>

                                        <form class="forms-sample" id="form" method="post" enctype="multipart/form-data">
                                            <?php

                                            $results = getData($dbh, $eid);
                                            foreach ($results as $row) { ?>
                                                <div class="form-group">
                                                    <label for="title">Title</label>
                                                    <input type="text" name="title" id="title"
                                                        value="<?php echo htmlentities($row['title']); ?>" class="form-control"
                                                        placeholder="Enter Title" required>
                                                </div>
                                                <div class="form-group">
                                                    <label for="desc">Description</label>
                                                    <textarea name="desc" id="desc" rows="3" class="form-control"
                                                        placeholder="Enter Description"
                                                        required><?php echo htmlentities($row['description']); ?></textarea>
                                                </div>
                                                <div class="form-group">
                                                    <label for="imageInput">Update Image</label>
                                                    <input type="file" name="image" class="form-control-file" id="imageInput"
                                                        onchange="updateFileName(this)">
                                                    <span
                                                        id="fileNameLabel"><?php echo !empty($row['image']) ? basename($row['image']) : ''; ?></span>
                                                </div>
                                                <p class="text-muted mt-2">Image must be less than 2MB</p>
                                                <?php
                                            }
                                            ?>
                                            <button type="button" class="btn btn-primary mr-2" data-toggle="modal"
                                                data-target="#confirmationModal">Update</button>

                                            <!-- Confirmation Modal (Update) -->
                                            <div class="modal fade" id="confirmationModal" tabindex="-1" role="dialog"
                                                aria-labelledby="myModalLabel" aria-hidden="true" data-backdrop="static"
                                                data-keyboard="false">
                                                <div class="modal-dialog">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h4 class="modal-title" id="myModalLabel">Confirmation</h4>
                                                            <button type="button" class="close" data-dismiss="modal"
                                                                aria-label="Close"><span
                                                                    aria-hidden="true">&times;</span></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            Are you sure you want to update this latest update?
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-default"
                                                                data-dismiss="modal">Cancel</button>
                                                            <button type="submit" class="btn btn-primary" id="submit"
                                                                name="submit">Update</button>
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
                    <?php include_once ('includes/footer.php'); ?>
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
            function updateFileName(input) {
                var fileName = input.files[0].name;
                document.getElementById('fileNameLabel').innerText = fileName;
            }

            function setFileInputValue(input, fileName) {
                let file = new File([""], fileName, { type: "application/pdf" });
                let dataTransfer = new DataTransfer();
                dataTransfer.items.add(file);
                input.files = dataTransfer.files;
            }

            let input = document.getElementById('imageInput');
            let fileName = '<?php echo !empty($fileName) ? $fileName : ''; ?>';
            setFileInputValue(input, fileName);
        </script>
    </body>

    </html>
<?php } ?>