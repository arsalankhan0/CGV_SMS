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
    
?>
<!DOCTYPE html>
<html lang="en">
<head>

    <title>TPS || Set Active Session</title>
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
                    <h3 class="page-title"> Set Active Session </h3>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                            <li class="breadcrumb-item active" aria-current="page"> Set Active Session</li>
                        </ol>
                    </nav>
                </div>
                <div class="row">

                    <div class="col-12 grid-margin stretch-card">
                        <div class="card">
                            <div class="card-body">
                                <div class="d-sm-flex align-items-center mb-4">
                                    <h4 class="card-title mb-sm-0">Set Active Session</h4>
                                    <a href="#" class="text-dark ml-auto mb-3 mb-sm-0"> View all Sessions</a>
                                </div>
                                <div class="table-responsive border rounded p-1">
                                    <table class="table">
                                        <thead>
                                        <tr>
                                            <th class="font-weight-bold">S.No</th>
                                            <th class="font-weight-bold">Session Name</th>
                                            <th class="font-weight-bold">Status</th>
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
                                        $offset = ($pageno - 1) * $no_of_records_per_page;
                                        $ret = "SELECT session_id FROM tblsessions";
                                        $query1 = $dbh->prepare($ret);
                                        $query1->execute();
                                        $results1 = $query1->fetchAll(PDO::FETCH_OBJ);
                                        $total_rows = $query1->rowCount();
                                        $total_pages = ceil($total_rows / $no_of_records_per_page);
                                        $sql = "SELECT * from tblsessions WHERE IsDeleted = 0 LIMIT $offset, $no_of_records_per_page";
                                        $query = $dbh->prepare($sql);
                                        $query->execute();
                                        $results = $query->fetchAll(PDO::FETCH_OBJ);

                                        $cnt = 1;
                                        if ($query->rowCount() > 0) {
                                            foreach ($results as $row) {
                                                ?>
                                                <tr>
                                                    <td><?php echo htmlentities($cnt); ?></td>
                                                    <td><?php echo htmlentities($row->session_name); ?></td>
                                                    <td>
                                                        <?php
                                                        if ($row->is_active == 1) 
                                                        {
                                                            echo '<button class="btn btn-success btn-sm" disabled>Active</button>';
                                                        } 
                                                        else 
                                                        {
                                                            echo '<button class="btn btn-secondary btn-sm" name="setActive" onclick="setActive(' . $row->session_id . ')">Inactive</button>';
                                                        }
                                                        ?>
                                                    </td>
                                                </tr>
                                                <?php $cnt = $cnt + 1;
                                            }
                                        } ?>
                                        </tbody>
                                    </table>
                                </div>
                                <div align="left">
                                    <ul class="pagination">
                                        <li><a href="?pageno=1"><strong>First></strong></a></li>
                                        <li class="<?php if ($pageno <= 1) {
                                            echo 'disabled';
                                        } ?>">
                                            <a href="<?php if ($pageno <= 1) {
                                                echo '#';
                                            } else {
                                                echo "?pageno=" . ($pageno - 1);
                                            } ?>"><strong style="padding-left: 10px">Prev></strong></a>
                                        </li>
                                        <li class="<?php if ($pageno >= $total_pages) {
                                            echo 'disabled';
                                        } ?>">
                                            <a href="<?php if ($pageno >= $total_pages) {
                                                echo '#';
                                            } else {
                                                echo "?pageno=" . ($pageno + 1);
                                            } ?>"><strong style="padding-left: 10px">Next></strong></a>
                                        </li>
                                        <li><a href="?pageno=<?php echo $total_pages; ?>"><strong
                                                        style="padding-left: 10px">Last</strong></a></li>
                                    </ul>
                                </div>
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
<!-- Confirmation Modal -->
<div class="modal fade" id="confirmationModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="myModalLabel">Confirmation</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">
                Are you sure you want to set this session as active?
                Please note that this will reflect throughout the website!
            </div>
            <div class="modal-footer">
                <!-- <input type="hidden" id="sessionid"> -->
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                <!-- <button type="button" class="btn btn-primary" id="confirmButton">Yes</button> -->
                <button type="button" class="btn btn-primary" id="nextButton">Yes</button>
            </div>
        </div>
    </div>
</div>
<!-- Confirmation Subject Import Modal -->
<div class="modal fade" id="importSubjectsModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="myModalLabel">Confirmation</h4>
            </div>
            <div class="modal-body">
                Do you want to import previous Subjects in this session?
            </div>
            <div class="modal-footer">
                <input type="hidden" id="sessionid">
                <button type="button" class="btn btn-default" id="noButton" data-dismiss="modal">No</button>
                <button type="button" class="btn btn-primary" id="confirmButton">Yes</button>
            </div>
        </div>
    </div>
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
<script>
   function setActive(sessionId) {
    // Get the first confirmation modal element
    var confirmationModal = $('#confirmationModal');

    // Get the second modal element
    var importSubjectsModal = $('#importSubjectsModal');

    // Get the input field for session ID in the second modal
    var sessionIdInput = $('#sessionid');

    // Set the session ID in the second modal
    sessionIdInput.val(sessionId);

    // Display the first confirmation modal
    confirmationModal.modal('show');

    // Handle confirmation in the first modal
    $('#nextButton').on('click', function() {
        // Hide the first confirmation modal
        confirmationModal.modal('hide');

        // Display the second modal
        importSubjectsModal.modal('show');
    });

    // Handle confirmation in the second modal
    $('#confirmButton').on('click', function() {
        let sessionId = sessionIdInput.val();

        // Redirect or perform any action you want
        window.location.href = 'manage-session.php?setActive=true&session_id=' + sessionId;

        // Hide the second modal
        importSubjectsModal.modal('hide');
    });
    
    // Handle confirmation in the second modal - No button
    $('#noButton').on('click', function() {
        let sessionId = sessionIdInput.val();

        // Redirect or perform any action you want
        window.location.href = 'manage-session.php?setActive=true&session_id=' + sessionId + '&importSubjects=false';

        // Hide the second modal
        importSubjectsModal.modal('hide');
    });
}

</script>

</body>
</html>
<?php
        // Get the active session ID
        $getSessionSql = "SELECT session_id FROM tblsessions WHERE is_active = 1 AND IsDeleted = 0";
        $sessionQuery = $dbh->prepare($getSessionSql);
        $sessionQuery->execute();
        $currentSessionID = $sessionQuery->fetchColumn();

            if (isset($_GET['setActive']) && isset($_GET['session_id'])) 
            {
                $session_id = $_GET['session_id'];
            
                // if (isset($_GET['importSubjects']) && $_GET['importSubjects'] == 'false') 
                // {
                //     try
                //     {
                //         $dbh->beginTransaction();

                //         // Set all sessions to inactive
                //         $updateInactive = "UPDATE tblsessions SET is_active = 0";
                //         $queryInactive = $dbh->prepare($updateInactive);
                //         $queryInactive->execute();
                    
                //         // Set the selected session to active
                //         $updateActive = "UPDATE tblsessions SET is_active = 1 WHERE session_id = :session_id";
                //         $queryActive = $dbh->prepare($updateActive);
                //         $queryActive->bindParam(':session_id', $session_id, PDO::PARAM_INT);
                //         $queryActive->execute();
                    
                //         // Reset the published exam, result, and session_id to 0 in tblexamination
                //         $resetPublishedSql = "UPDATE tblexamination SET IsPublished = 0, IsResultPublished = 0, session_id = 0 WHERE session_id = :activeSession";
                //         $resetPublished = $dbh->prepare($resetPublishedSql);
                //         $resetPublished->bindParam(':activeSession', $currentSessionID, PDO::PARAM_INT);
                //         $resetPublished->execute();
                        
                //         $dbh->commit();
                //     }
                //     catch(PDOException $e)
                //     {
                //         $dbh->rollBack();
                //         echo "Error: " . $e->getMessage();
                //     }
                //     finally
                //     {
                //         echo "<script>window.location.href ='manage-session.php'</script>";
                //     }
                // } 

                if (isset($_GET['importSubjects']) && $_GET['importSubjects'] == 'false') 
                {
                    try 
                    {
                        $dbh->beginTransaction();
                
                        // Set all sessions to inactive
                        $updateInactive = "UPDATE tblsessions SET is_active = 0";
                        $queryInactive = $dbh->prepare($updateInactive);
                        $queryInactive->execute();
                
                        // Set the selected session to active
                        $updateActive = "UPDATE tblsessions SET is_active = 1 WHERE session_id = :session_id";
                        $queryActive = $dbh->prepare($updateActive);
                        $queryActive->bindParam(':session_id', $session_id, PDO::PARAM_INT);
                        $queryActive->execute();
                
                        // Reset the published exam, result, and session_id to 0 in tblexamination
                        $resetPublishedSql = "UPDATE tblexamination SET IsPublished = 0, IsResultPublished = 0, session_id = 0 WHERE session_id = :activeSession";
                        $resetPublished = $dbh->prepare($resetPublishedSql);
                        $resetPublished->bindParam(':activeSession', $currentSessionID, PDO::PARAM_INT);
                        $resetPublished->execute();
                
                        // Select Subjects from tblsubjects
                        $fetchSubjectsSql = "SELECT ID FROM tblsubjects WHERE IsDeleted = 0";
                        $fetchSubjectsQuery = $dbh->prepare($fetchSubjectsSql);
                        $fetchSubjectsQuery->execute();
                        $subjectIDs = $fetchSubjectsQuery->fetchAll(PDO::FETCH_COLUMN);

                        foreach ($subjectIDs as $subjectID) 
                        {
                            // Check if subject ID exists in tblsubjecthistory for the new active session
                            $checkSubjectHistorySql = "SELECT COUNT(*) FROM tblsubjecthistory WHERE SessionID = :newSessionID AND SubjectID = :subjectID";
                            $checkSubjectHistory = $dbh->prepare($checkSubjectHistorySql);
                            $checkSubjectHistory->bindParam(':newSessionID', $session_id, PDO::PARAM_INT);
                            $checkSubjectHistory->bindParam(':subjectID', $subjectID, PDO::PARAM_INT);
                            $checkSubjectHistory->execute();
                            $subjectHistoryCount = $checkSubjectHistory->fetchColumn();

                            if ($subjectHistoryCount > 0) 
                            {
                                // Update session IDs of subjects to the new active session ID
                                $updateSubSession = "UPDATE tblsubjects SET SessionID = :newSessionID WHERE ID = :subjectID";
                                $querySubSession = $dbh->prepare($updateSubSession);
                                $querySubSession->bindParam(':newSessionID', $session_id, PDO::PARAM_INT);
                                $querySubSession->bindParam(':subjectID', $subjectID, PDO::PARAM_INT);
                                $querySubSession->execute();
                            }
                        }               
                        $dbh->commit();
                    } 
                    catch (PDOException $e) 
                    {
                        $dbh->rollBack();
                        echo "Error: " . $e->getMessage();
                    } 
                    finally 
                    {
                        echo "<script>window.location.href ='manage-session.php'</script>";
                    }
                }                
                else 
                {
                    $dbh->beginTransaction();
                    try 
                    {   
                        // Insert subjects of the current session into tblsubjecthistory if they don't already exist
                        $insertHistorySql = "INSERT INTO tblsubjecthistory (SubjectID, SessionID) 
                                            SELECT ID, SessionID FROM tblsubjects 
                                            WHERE SessionID = :currentSessionID 
                                            AND NOT EXISTS (
                                                SELECT 1 FROM tblsubjecthistory 
                                                WHERE tblsubjecthistory.SubjectID = tblsubjects.ID 
                                                AND tblsubjecthistory.SessionID = tblsubjects.SessionID
                                            )";
                        $queryHistory = $dbh->prepare($insertHistorySql);
                        $queryHistory->bindParam(':currentSessionID', $currentSessionID, PDO::PARAM_INT);
                        $queryHistory->execute();
       
                        // Update session IDs of subjects to the new active session ID
                        $updateSubSession = "UPDATE tblsubjects SET SessionID = :newSessionID WHERE SessionID = :currentSessionID";
                        $querySubSession = $dbh->prepare($updateSubSession);
                        $querySubSession->bindParam(':newSessionID', $session_id, PDO::PARAM_INT);
                        $querySubSession->bindParam(':currentSessionID', $currentSessionID, PDO::PARAM_INT);
                        $querySubSession->execute();
       
                        // Set all sessions to inactive except the selected one
                        $updateSessions = "UPDATE tblsessions SET is_active = CASE WHEN session_id = :session_id THEN 1 ELSE 0 END";
                        $querySessions = $dbh->prepare($updateSessions);
                        $querySessions->bindParam(':session_id', $session_id, PDO::PARAM_INT);
                        $querySessions->execute();
       
                        // Reset the published exam, result, and session_id to 0 in tblexamination
                        $resetPublishedSql = "UPDATE tblexamination SET IsPublished = 0, IsResultPublished = 0, session_id = 0 WHERE session_id = :activeSession";
                        $resetPublished = $dbh->prepare($resetPublishedSql);
                        $resetPublished->bindParam(':activeSession', $currentSessionID, PDO::PARAM_INT);
                        $resetPublished->execute();

                        $dbh->commit();
                    } 
                    catch (PDOException $e) 
                    {
                        $dbh->rollBack();
                        echo "Error: " . $e->getMessage();
                    }
                    finally
                    {
                        echo "<script>window.location.href ='manage-session.php'</script>";
                    }
                } 
            }
}
?>
