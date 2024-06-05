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

    <title>TPS || View Final Results</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- plugins:css -->
    <link rel="stylesheet" href="vendors/simple-line-icons/css/simple-line-icons.css">
    <link rel="stylesheet" href="vendors/flag-icon-css/css/flag-icon.min.css">
    <link rel="stylesheet" href="vendors/css/vendor.bundle.base.css">
    <!-- endinject -->
    <!-- Plugin css for this page -->
    <link rel="stylesheet" href="./vendors/daterangepicker/daterangepicker.css">
    <link rel="stylesheet" href="./vendors/chartist/chartist.min.css">
    <!-- End plugin css for this page -->
    <!-- inject:css -->
    <!-- endinject -->
    <!-- Layout styles -->
    <link rel="stylesheet" href="./css/style.css">
    <!-- End layout styles -->
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
                    <h3 class="page-title">View Final Results</h3>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                            <li class="breadcrumb-item active" aria-current="page">View Final Results</li>
                        </ol>
                    </nav>
                </div>
                <div class="row">
                    <div class="col-md-12 grid-margin stretch-card">
                        <div class="card">
                            <div class="card-body">
                                <div class="d-sm-flex align-items-center mb-4">
                                    <h4 class="card-title mb-sm-0">View Final Results</h4>
                                </div>
                                
                                <!-- Filter this Form -->
                                <form method="post" class="mb-3">
                                    <div class="form-row">
                                        <?php
                                        $sql = "SELECT 
                                                        session_id, 
                                                        session_name,
                                                        NULL as class_id,
                                                        NULL as class_name,
                                                        NULL as section_id,
                                                        NULL as section_name
                                                    FROM tblsessions
                                                    WHERE IsDeleted = 0
                                                UNION ALL
                                                    SELECT 
                                                        NULL as session_id, 
                                                        NULL as session_name,
                                                        ID as class_id,
                                                        ClassName as class_name,
                                                        NULL as section_id,
                                                        NULL as section_name
                                                    FROM tblclass
                                                    WHERE IsDeleted = 0
                                                UNION ALL
                                                    SELECT 
                                                        NULL as session_id, 
                                                        NULL as session_name,
                                                        NULL as class_id,
                                                        NULL as class_name,
                                                        ID as section_id,
                                                        SectionName as section_name
                                                    FROM tblsections
                                                    WHERE IsDeleted = 0";
                                        $query = $dbh->prepare($sql);
                                        $query->execute();
                                        $data = $query->fetchAll(PDO::FETCH_ASSOC);
                                        ?>
                                        <!-- Select Session -->
                                        <div class="form-group col-md-4">
                                            <label for="session">Select Session:</label>
                                            <select name="session" id="session" class="form-control">
                                                <?php
                                                foreach ($data as $Session) 
                                                {
                                                    if ($Session['session_id'] !== null) {
                                                        echo "<option value='" . $Session['session_id'] . "'>" . $Session['session_name'] . "</option>";
                                                    }                                                
                                                }
                                                ?>
                                            </select>
                                            
                                        </div>
                                        <!-- Select Class -->
                                        <div class="form-group col-md-4">
                                            <label for="class">Select Class:</label>
                                            <select name="class" id="class" class="form-control">
                                                <?php
                                                    foreach ($data as $class) 
                                                    {
                                                        if($class['class_id'] !== null)
                                                        {
                                                            echo "<option value='" . $class['class_id'] . "'>" . $class['class_name'] . "</option>";
                                                        }
                                                    }
                                                ?>
                                            </select>
                                        </div>
                                        <!-- Select Section -->
                                        <div class="form-group col-md-4">
                                            <label for="section">Select Class:</label>
                                            <select name="section" id="section" class="form-control">
                                                <?php
                                                    foreach ($data as $section) 
                                                    {
                                                        if($section['section_id'] !== null)
                                                        {
                                                            echo "<option value='" . $section['section_id'] . "'>" . $section['section_name'] . "</option>";
                                                        }
                                                    }
                                                ?>
                                            </select>
                                        </div>

                                    </div>
                                    <button type="submit" name="filter" class="btn btn-primary">Search</button>
                                </form>
                                    <?php
                                    if (isset($_POST['filter'])) 
                                    {
                                        $selectedSession = $_POST['session'];
                                        $selectedClass = $_POST['class'];
                                        $selectedSection = $_POST['section'];

                                        $sqlFilteredReports = "SELECT DISTINCT 
                                                                    tr.StudentName AS StudentID, 
                                                                    tc.ID AS ClassID, 
                                                                    sec.ID AS SectionID, 
                                                                    ts.session_id AS SessionID, 
                                                                    s.StudentName,
                                                                    s.RollNo,
                                                                    tc.ClassName, 
                                                                    sec.SectionName, 
                                                                    ts.session_name AS ExamSession 
                                                                FROM tblreports tr
                                                                INNER JOIN tblstudent s ON tr.StudentName = s.ID
                                                                INNER JOIN tblclass tc ON tr.ClassName = tc.ID
                                                                INNER JOIN tblsessions ts ON tr.ExamSession = ts.session_id
                                                                INNER JOIN tblsections sec ON tr.SectionName = sec.ID
                                                                WHERE tr.ClassName = :class 
                                                                AND tr.SectionName = :selectedSection 
                                                                AND tr.ExamSession = :selectedSession 
                                                                AND tr.IsDeleted = 0
                                                                ORDER BY s.RollNo ASC";
                                        $queryFilteredReports = $dbh->prepare($sqlFilteredReports);
                                        $queryFilteredReports->bindParam(':class', $selectedClass, PDO::PARAM_STR);
                                        $queryFilteredReports->bindParam(':selectedSection', $selectedSection, PDO::PARAM_STR);
                                        $queryFilteredReports->bindParam(':selectedSession', $selectedSession, PDO::PARAM_STR);
                                        $queryFilteredReports->execute();
                                        $filteredReports = $queryFilteredReports->fetchAll(PDO::FETCH_ASSOC);

                                        
                                        if (!empty($filteredReports)) 
                                        {
                                            $filteredClassName = $filteredReports[0]['ClassName'];
                                            $filteredSectionName = $filteredReports[0]['SectionName'];
                                            $filteredSessionName = $filteredReports[0]['ExamSession'];

                                            // Display message indicating the filtered results
                                            echo "<div class='d-flex flex-md-row flex-column justify-content-between align-items-center'>";
                                            echo "<strong class=''>Showing results for <span class='text-dark'>Class: " . htmlspecialchars($filteredClassName) . "</span>, <span class='text-dark'>Section: " . htmlspecialchars($filteredSectionName) . "</span>, <span class='text-dark'>Session: " . htmlspecialchars($filteredSessionName) . "</span></strong>";
                                            echo "<button class='btn btn-info' onclick='printAllReports()'>Print All</button>";
                                            echo "</div>";
                                            echo "<div class='table-responsive border rounded p-1 mt-4'>";
                                            echo "<table class='table'>";
                                            echo "<thead>";
                                            echo "<tr>";
                                            echo "<th class='font-weight-bold'>S.No</th>";
                                            echo "<th class='font-weight-bold'>Student Name</th>";
                                            echo "<th class='font-weight-bold'>Roll No</th>";
                                            echo "<th class='font-weight-bold'>Action</th>";
                                            echo "</tr>";
                                            echo "</thead>";
                                            echo "<tbody>";
                                            
                                            $cnt = 1;
                                            foreach ($filteredReports as $report) 
                                            {   
                                                echo "<tr>";
                                                echo "<td>" . htmlentities($cnt) . "</td>";
                                                echo "<td>". htmlentities($report['StudentName']) ."</td>";
                                                echo "<td>". htmlentities($report['RollNo']) ."</td>";
                                                echo "<td>";
                                                echo "<div>";
                                                echo "<button class='btn btn-info' onclick='printReportDetails(\"view-report-details.php?className=" . urlencode(base64_encode($report['ClassID'])) . "&studentName=" . urlencode(base64_encode($report['StudentID'])) . "&examSession=" . urlencode(base64_encode($report['SessionID'])) . "\")'>Print</button>";
                                                echo "</div>";
                                                echo "</td>";
                                                echo "</tr>";
                                                $cnt = $cnt + 1;
                                            }

                                            echo "</tbody>";
                                            echo "</table>";
                                            echo "</div>";
                                        } 
                                        else 
                                        {
                                            echo "<strong>No Record found!</strong>";
                                        }
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
<script src="./vendors/chart.js/Chart.min.js"></script>
<script src="./vendors/moment/moment.min.js"></script>
<script src="./vendors/daterangepicker/daterangepicker.js"></script>
<script src="./vendors/chartist/chartist.min.js"></script>
<!-- End plugin js for this page -->
<!-- inject:js -->
<script src="js/off-canvas.js"></script>
<script src="js/misc.js"></script>
<!-- endinject -->
<!-- Custom js for this page -->
<script src="./js/dashboard.js"></script>
<script>
    function printReportDetails(url) {
        var newWindow = window.open(url, '_blank');
        newWindow.print();
    }
    function printAllReports() {
        <?php
        foreach ($filteredReports as $report) {
            echo "printReportDetails(\"print-all-reports.php?className=" . urlencode(base64_encode($report['ClassID'])) .  "&SecName=" . urlencode(base64_encode($report['SectionID'])) . "&examSession=" . urlencode(base64_encode($report['SessionID'])) . "\");";
        }
        ?>
    }
</script>
<!-- End custom js for this page -->
</body>
</html>
<?php
}
?>
