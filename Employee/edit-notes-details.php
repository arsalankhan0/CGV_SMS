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
                if (!empty($_FILES['notes']['name'])) 
                {
                    $notesName = $_FILES['notes']['name'];
                    $notesTmpName = $_FILES['notes']['tmp_name'];
                    $notesSize = $_FILES['notes']['size'];
                    $notesError = $_FILES['notes']['error'];
                    $fileNameCmps = explode(".", $notesName);
                    $fileExtension = strtolower(end($fileNameCmps));

                    // Check if file is a PDF and size limit is not exceeded
                    $allowedExtensions = array("pdf");
                    $maxFileSize = 10485760; // 10MB

                    $newFileName = "notes_" . time() . '.' . $fileExtension;
                    $uploadFileDir = '../admin/notes/';
                    $destPath = $uploadFileDir . $newFileName;
                    $fileName = basename($destPath);
                    
                    $title = filter_var($_POST['title'], FILTER_SANITIZE_STRING);
                    $class = filter_var($_POST['class'], FILTER_SANITIZE_STRING);
                    $subject = filter_var($_POST['subject'], FILTER_SANITIZE_STRING);
                    $existingPDF = $_POST['existing_notes'];
                    
                    // Fetch the existing file name from the database
                    $sqlFetchFileName = "SELECT Notes FROM tblnotes WHERE ID = :eid";
                    $queryFetchFileName = $dbh->prepare($sqlFetchFileName);
                    $queryFetchFileName->bindParam(':eid', $eid, PDO::PARAM_INT);
                    $queryFetchFileName->execute();
                    $rowFileName = $queryFetchFileName->fetch(PDO::FETCH_ASSOC);
                    $existingFileName = $rowFileName['Notes'];

                    // Check if the selected file name is different from the existing file name
                    if ($existingPDF !== $existingFileName) 
                    {
                        // File upload validation
                        if (in_array($fileExtension, $allowedExtensions) && $notesSize <= $maxFileSize) 
                        {
                            // Check if the file is a PDF
                            if (move_uploaded_file($notesTmpName, $destPath)) 
                            {
                                $sql = "UPDATE tblnotes SET Title=:title, Class=:class, `Subject`=:subjectName, Notes=:notes WHERE ID=:eid";
                                $query = $dbh->prepare($sql);
                                $query->bindParam(':title', $title, PDO::PARAM_STR);
                                $query->bindParam(':class', $class, PDO::PARAM_INT);
                                $query->bindParam(':subjectName', $subject, PDO::PARAM_INT);
                                $query->bindParam(':notes', $fileName, PDO::PARAM_STR);
                                $query->bindParam(':eid', $eid, PDO::PARAM_INT);
                                $query->execute();
                                $successAlert = true;
                                $msg = "Notes has been updated successfully.";
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
                        $sql = "UPDATE tblnotes SET Title=:title, Class=:class, `Subject`=:subjectName WHERE ID=:eid";
                        $query = $dbh->prepare($sql);
                        $query->bindParam(':title', $title, PDO::PARAM_STR);
                        $query->bindParam(':class', $class, PDO::PARAM_INT);
                        $query->bindParam(':subjectName', $subject, PDO::PARAM_INT);
                        $query->bindParam(':eid', $eid, PDO::PARAM_INT);
                        $query->execute();
                        $successAlert = true;
                        $msg = "Fields of the selected Notes has been updated successfully.";
                    }
                } 
                else 
                {
                    $msg = "Failed to upload notes file.";
                    $dangerAlert = true;
                }
            }
        } 
        catch (PDOException $e) 
        {
            $dangerAlert = true;
            $msg = "Ops! An error occurred while updating notes.";
            echo "<script>console.error('Error:---> " . $e->getMessage() . "');</script>";
        }
        // Fetch the class name based on the class ID stored in tblnotes
        $className = "";
        $sql = "SELECT c.ClassName FROM tblclass c JOIN tblnotes s ON s.Class = c.ID WHERE s.ID = :editid";
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
        <title>TPS || Update notes</title>
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
                            <h3 class="page-title"> Update Notes </h3>
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb">
                                    <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                                    <li class="breadcrumb-item active" aria-current="page"> Update Notes</li>
                                </ol>
                            </nav>
                        </div>
                        <div class="row">
                            <div class="col-12 grid-margin stretch-card">
                                <div class="card">
                                    <div class="card-body">
                                        <h4 class="card-title" style="text-align: center;">Update Notes</h4>
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

                                        <?php
                                            $eid = $_GET['editid'];
                                            $sql="SELECT * from tblnotes where ID=:eid";
                                            $query = $dbh -> prepare($sql);
                                            $query->bindParam(':eid',$eid,PDO::PARAM_STR);
                                            $query->execute();
                                            $results=$query->fetchAll(PDO::FETCH_OBJ);
                                            
                                            // Fetching current active session.
                                            $sqlActiveSession = "SELECT session_id FROM tblsessions WHERE is_active = 1";
                                            $queryActiveSession = $dbh->prepare($sqlActiveSession);
                                            $queryActiveSession->execute();
                                            $activeSession = $queryActiveSession->fetch(PDO::FETCH_COLUMN);
                                            
                                            // Fetching Classes
                                            $sqlClasses = "SELECT c.ID, c.ClassName 
                                            FROM tblemployees e 
                                            JOIN tblclass c ON FIND_IN_SET(c.ID, e.AssignedClasses) 
                                            WHERE e.ID = :empID 
                                            AND e.IsDeleted = 0 
                                            AND c.IsDeleted = 0";
                                            $queryClasses = $dbh->prepare($sqlClasses);
                                            $queryClasses->bindParam(':empID', $_SESSION['sturecmsEMPid'], PDO::PARAM_INT);
                                            $queryClasses->execute();
                                            $classResults = $queryClasses->fetchAll(PDO::FETCH_ASSOC);
                                            
                                            // Fetching main Subjects
                                            $sqlMainSubjects = "SELECT sub.ID, sub.SubjectName 
                                                                FROM tblemployees emp 
                                                                JOIN tblsubjects sub ON FIND_IN_SET(sub.ID, emp.AssignedSubjects) 
                                                                WHERE emp.ID = :empID
                                                                AND sub.SessionID = :activeSession
                                                                AND sub.IsOptional = 0
                                                                AND IsCurricularSubject = 0
                                                                AND emp.IsDeleted = 0 
                                                                AND sub.IsDeleted = 0";
                                            $queryMainSubjects = $dbh->prepare($sqlMainSubjects);
                                            $queryMainSubjects->bindParam(':activeSession', $activeSession, PDO::PARAM_INT);
                                            $queryMainSubjects->bindParam(':empID', $_SESSION['sturecmsEMPid'], PDO::PARAM_INT);
                                            $queryMainSubjects->execute();
                                            $mainSubjects = $queryMainSubjects->fetchAll(PDO::FETCH_ASSOC);
                                            
                                            $classDefault = "";
                                            $subjectDefault = "";
                                            
                                            foreach($results as $row) {
                                                $classDefault = $row->Class;
                                                $subjectDefault = $row->Subject;
                                                ?>

                                            <form class="forms-sample" id="form" method="post" enctype="multipart/form-data">
                                                <div class="form-group">
                                                    <label for="title">Title</label>
                                                    <input type="text" name="title" class="form-control" id="title" placeholder="Enter title" required='true' value="<?php echo htmlentities($row->Title); ?>">
                                                </div>
                                                <div class="form-group">
                                                    <label for="exampleFormControlSelect2">Select Class</label>
                                                    <select name="class" class="form-control w-100">
                                                        <?php
                                                        foreach ($classResults as $class) {
                                                            $classNameWithSection = $class['ClassName'];
                                                            $selected = ($class['ID'] == $classDefault) ? 'selected' : '';
                                                            echo "<option value='" . htmlentities($class['ID']) . "' $selected>" . htmlentities($classNameWithSection) . "</option>";
                                                        }
                                                        ?>
                                                    </select>
                                                </div>
                                                <div class="form-group">
                                                    <label for="subject">Select Subject</label>
                                                    <select name="subject" class="form-control w-100" id="subject">
                                                        <?php
                                                        foreach ($mainSubjects as $subject) {
                                                            $selected = ($subject['ID'] == $subjectDefault) ? 'selected' : ''; // Check if subject ID matches default value
                                                            echo "<option value='" . htmlentities($subject['ID']) . "' $selected>" . htmlentities($subject['SubjectName']) . "</option>";
                                                        }
                                                        ?>
                                                    </select>
                                                </div>
                                                <div class="form-group">
                                                    <label for="notesInput">Upload notes (PDF only)</label>
                                                    <div class="file-input-wrapper">
                                                        <input type="file" name="notes" class="form-control-file border-border-dark" id="notesInput" onchange="updateFileName(this)">
                                                        <span id="fileNameLabel"><?php
                                                            if (!empty($row->Notes)) {
                                                                $fileName = basename($row->Notes);
                                                            }
                                                            ?>
                                                        </span>
                                                    </div>
                                                    <p class="text-muted mt-2">PDF must be less than 10MB</p>
                                                </div>
                                                <input type="hidden" name="existing_notes" id="existingNotes" value="<?php echo (!empty($row->Notes)) ? htmlentities($row->Notes) : ''; ?>">
                                                
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
                                                                Are you sure you want to update notes?
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                                                                <button type="submit" class="btn btn-primary" id="submit" name="submit">Update</button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </form>
                                            <?php 
                                            } 
                                        ?>
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
        let input = document.getElementById('notesInput'); 
        let fileName = '<?php if (!empty($fileName)) echo $fileName; ?>'; 
        setFileInputValue(input, fileName); 

        function updateFileName(input) 
        {
            if (input.files.length > 0) 
            {
                var fileName = input.files[0].name;
                document.getElementById('existingNotes').value = fileName;
            }
        }
        </script>
    </body>
    </html>
    <?php } ?>
