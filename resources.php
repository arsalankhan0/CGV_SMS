<?php 
    session_start();
    error_reporting(0);
    include('./includes/dbconnection.php');
?>
<!DOCTYPE html>
<html lang="zxx" class="no-js">
<head>
    <!-- Mobile Specific Meta -->
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <!-- Favicon-->
    <link rel="shortcut icon" href="./Main/img/favicon.png">
    <!-- Author Meta -->
    <meta name="author" content="Tibetan Public School">
    <!-- Meta Description -->
    <meta name="description" content="Notes, Syllabus, Resources">
    <!-- Meta Keyword -->
    <meta name="keywords" content="Tibetan Public School, Notes, Resources, Syllabus">
    <!-- meta character set -->
    <meta charset="UTF-8">
    <!-- Site Title -->
    <title>TPS - Resources</title>

    <link href="https://fonts.googleapis.com/css?family=Poppins:100,200,400,300,500,600,700" rel="stylesheet"> 
        <!--
        CSS
        ============================================= -->
        <link rel="stylesheet" href="./Main/css/linearicons.css">
        <link rel="stylesheet" href="./Main/css/font-awesome.min.css">
        <link rel="stylesheet" href="./Main/css/bootstrap.css">
        <link rel="stylesheet" href="./Main/css/magnific-popup.css">
        <link rel="stylesheet" href="./Main/css/nice-select.css">                            
        <link rel="stylesheet" href="./Main/css/animate.min.css">
        <link rel="stylesheet" href="./Main/css/owl.carousel.css">            
        <link rel="stylesheet" href="./Main/css/jquery-ui.css">            
        <link rel="stylesheet" href="./Main/css/main.css">
        <style>
            table 
            { 
                table-layout:fixed;
            }
            td 
            { 
                overflow: hidden; 
                text-overflow: ellipsis; 
                word-wrap: break-word;
            }
        </style>
    </head>
    <body>  
        <?php include_once('./includes/header.php'); ?>

        <section id="resources" class="about-section section-gap mt-80">
            <div class="container">
                <div class="section-title text-center">
                    <h2>Resources</h2>
                </div>
                <div class="row">
                    <div class="col-lg-3">
                        <ul class="resource-menu">
                            <li><a href="#syllabus">Syllabus</a></li>
                            <li><a href="#notes">Notes</a></li>
                        </ul>
                    </div>
                    <div class="col-lg-9">
                        <!-- Syllabus -->
                        <?php
                            $sql = "SELECT s.*, c.ClassName 
                            FROM tblsyllabus s 
                            JOIN tblclass c ON s.Class = c.ID";
                            $query = $dbh->prepare($sql);
                            $query->execute();
                            $rows = $query->fetchAll(PDO::FETCH_ASSOC);

                            if ($rows) 
                            {
                                ?>
                                <div id="syllabus" class="resource-content">
                                    <h3>Syllabus List</h3>
                                    <div class="syllabus-list mt-4">
                                        <div class="">
                                            <table class="table w-100">
                                                <thead>
                                                    <tr>
                                                        <th scope="col">S.No</th>
                                                        <th scope="col">Class</th>
                                                        <th scope="col">Title</th>
                                                        <th scope="col">Action</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php
                                                    $count = 1;
                                                    foreach ($rows as $row) 
                                                    {
                                                        ?>
                                                        <tr>
                                                            <td><?php echo $count++; ?></td>
                                                            <td><?php echo $row['ClassName']; ?></td>
                                                            <td><?php echo $row['Syllabus']; ?></td>
                                                            <td><a href="admin/syllabus/<?php echo $row['Syllabus']; ?>" target="_blank">View</a></td>
                                                        </tr>
                                                        <?php
                                                    }
                                                    ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                                <?php
                            } 
                            else 
                            {
                                echo "<div id='syllabus' class='resource-content'><h3>Syllabus List</h3>No records found</div>";
                            }
                        ?>
                        <!-- Notes -->
                        <?php
                            // Fetching current active session.
                            $sqlActiveSession = "SELECT session_id FROM tblsessions WHERE is_active = 1";
                            $queryActiveSession = $dbh->prepare($sqlActiveSession);
                            $queryActiveSession->execute();
                            $activeSession = $queryActiveSession->fetch(PDO::FETCH_COLUMN);

                            // Query to fetch optional subjects
                            $sqlOptionalSubjects = "SELECT ID, SubjectName 
                                                    FROM tblsubjects
                                                    WHERE IsDeleted = 0 AND SessionID = :activeSession AND IsOptional = 1";
                            $queryOptionalSubjects = $dbh->prepare($sqlOptionalSubjects);
                            $queryOptionalSubjects->bindParam(':activeSession', $activeSession, PDO::PARAM_INT);
                            $queryOptionalSubjects->execute();
                            $optionalSubjects = $queryOptionalSubjects->fetchAll(PDO::FETCH_ASSOC);
                        ?>
                        <div id="notes" class="resource-content">
                            <h3>Notes</h3>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="classDropdown">Select Class</label>
                                        <select class="form-control" id="classDropdown">
                                            <option value="">Select Class</option>
                                            <?php
                                            $sqlClass = "SELECT * FROM tblclass WHERE IsDeleted = 0";
                                            $queryClass = $dbh->prepare($sqlClass);
                                            $queryClass->execute();
                                            $classes = $queryClass->fetchAll(PDO::FETCH_ASSOC);
                                            foreach ($classes as $class) {
                                                echo "<option value='" . htmlentities($class['ID']) . "'>" . htmlentities($class['ClassName']) . "</option>";
                                            }
                                            ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="subjectDropdown">Select Subject</label>
                                        <select class="form-control" id="subjectDropdown">
                                            <option value="">Select Subject</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="notes-list mt-3">
                                <h4>Notes List</h4>
                                <div class="table-responsive">
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th scope="col">S.No</th>
                                                <th scope="col">Title</th>
                                                <th scope="col">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody id="notesTableBody">

                                        </tbody>
                                    </table>
                                </div>
                            </div>

                        </div>

                    </div>
                </div>
            </div>
        </section>

        <!-- Footer -->
        <?php include_once('./includes/footer.php');?>

        <script src="./Main/js/vendor/jquery-2.2.4.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
        <script src="./Main/js/vendor/bootstrap.min.js"></script>            
        <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBhOdIF3Y9382fqJYt5I_sswSrEw5eihAA"></script>
        <script src="./Main/js/easing.min.js"></script>            
        <script src="./Main/js/hoverIntent.js"></script>
        <script src="./Main/js/superfish.min.js"></script>    
        <script src="./Main/js/jquery.ajaxchimp.min.js"></script>
        <script src="./Main/js/jquery.magnific-popup.min.js"></script>    
        <script src="./Main/js/jquery.tabs.min.js"></script>                        
        <script src="./Main/js/jquery.nice-select.min.js"></script>    
        <script src="./Main/js/owl.carousel.min.js"></script>                                    
        <script src="./Main/js/mail-script.js"></script>    
        <script src="./Main/js/main.js"></script>    
        <script src="./Main/js/resources.js"></script>    
    </body>
</html>
