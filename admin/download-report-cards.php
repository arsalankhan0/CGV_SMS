<?php
session_start();
error_reporting(0);
include('includes/dbconnection.php');

if (!isset($_SESSION['sturecmsaid']) || empty($_SESSION['sturecmsaid'])) {
    header('location:logout.php');
    exit;
}

// Fetch all sessions for dropdown
$sessionSql = "SELECT session_id, session_name FROM tblsessions WHERE IsDeleted = 0 ORDER BY session_id DESC";
$sessionQuery = $dbh->prepare($sessionSql);
$sessionQuery->execute();
$sessions = $sessionQuery->fetchAll(PDO::FETCH_ASSOC);

$selectedSession = null;
$selectedSessionName = null;
$encodedSession = null;

if (isset($_GET['session_id']) && !empty($_GET['session_id'])) {
    $selectedSession = $_GET['session_id'];
    $encodedSession = urlencode(base64_encode($selectedSession));

    $snq = $dbh->prepare("SELECT session_name FROM tblsessions WHERE session_id = :sid");
    $snq->bindParam(':sid', $selectedSession, PDO::PARAM_STR);
    $snq->execute();
    $selectedSessionName = $snq->fetchColumn();

    // Fetch distinct classes that have report data for this session
    $classSql = "SELECT DISTINCT tc.ID, tc.ClassName 
                 FROM tblreports tr 
                 INNER JOIN tblclass tc ON tr.ClassName = tc.ID 
                 WHERE tr.ExamSession = :sessionId AND tr.IsDeleted = 0 AND tc.IsDeleted = 0";
    $classQuery = $dbh->prepare($classSql);
    $classQuery->bindParam(':sessionId', $selectedSession, PDO::PARAM_STR);
    $classQuery->execute();
    $classes = $classQuery->fetchAll(PDO::FETCH_ASSOC);

    // Custom sorting for classes
    function getClassSortKey($className) {
        $name = strtolower(trim($className));
        if ($name === 'nursery') return 0;
        if ($name === 'lkg') return 1;
        if ($name === 'ukg') return 2;
        preg_match('/^(\d+)/', $name, $matches);
        if (!empty($matches[1])) return 3 + (int)$matches[1];
        return 999;
    }

    usort($classes, function($a, $b) {
        return getClassSortKey($a['ClassName']) - getClassSortKey($b['ClassName']);
    });
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <title>TPS || Download Final Report Cards</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Download final report cards for all students by session.">
    <link rel="stylesheet" href="vendors/simple-line-icons/css/simple-line-icons.css">
    <link rel="stylesheet" href="vendors/flag-icon-css/css/flag-icon.min.css">
    <link rel="stylesheet" href="vendors/css/vendor.bundle.base.css">
    <link rel="stylesheet" href="./css/style.css">
    <style>
        .session-filter-card {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 2px 16px rgba(0, 0, 0, 0.08);
            padding: 24px 28px;
            margin-bottom: 18px;
        }

        .session-filter-card .form-control {
            border-radius: 8px;
            border: 1.5px solid #ddd;
            font-size: 1rem;
            height: 46px;
        }

        .btn-filter {
            height: 46px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 1rem;
            padding: 0 28px;
        }

        .no-data-msg {
            text-align: center;
            padding: 60px 0;
            color: #888;
            font-size: 1.1rem;
        }

        .no-data-msg i {
            font-size: 3rem;
            display: block;
            margin-bottom: 14px;
            color: #ccc;
        }

        .class-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }

        .class-card {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.06);
            padding: 24px;
            border-top: 4px solid #800000;
            transition: transform 0.2s, box-shadow 0.2s;
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
        }

        .class-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.1);
        }

        .class-card h5 {
            font-size: 1.25rem;
            font-weight: 700;
            color: #333;
            margin-bottom: 10px;
        }

        .class-card .section-info {
            font-size: 0.9rem;
            color: #666;
            margin-bottom: 20px;
        }

        .btn-print-class {
            background: #800000;
            color: #fff !important;
            padding: 10px 20px;
            border-radius: 6px;
            font-weight: 600;
            text-decoration: none !important;
            width: 100%;
            transition: background 0.2s;
        }

        .btn-print-class:hover {
            background: #b22222;
        }
    </style>
</head>

<body>
    <div class="container-scroller">
        <?php include_once('includes/header.php'); ?>
        <div class="container-fluid page-body-wrapper">
            <?php include_once('includes/sidebar.php'); ?>
            <div class="main-panel">
                <div class="content-wrapper">
                    <div class="page-header">
                        <h3 class="page-title">Download Final Report Cards</h3>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                                <li class="breadcrumb-item active" aria-current="page">Download Final Report Cards</li>
                            </ol>
                        </nav>
                    </div>

                    <div class="row">
                        <div class="col-md-12">

                            <!-- Session Selector -->
                            <div class="session-filter-card">
                                <h4 class="mb-3" style="font-weight:700; color:#800000;">
                                    <i class="icon-cloud-download"></i> &nbsp;Select Session
                                </h4>
                                <form method="GET" action="download-report-cards.php" id="sessionFilterForm">
                                    <div class="form-row align-items-end">
                                        <div class="form-group col-md-5 mb-0">
                                            <label for="session_id" style="font-weight:600;">Academic Session</label>
                                            <select name="session_id" id="session_id" class="form-control" required>
                                                <option value="">-- Select Session --</option>
                                                <?php foreach ($sessions as $sess): ?>
                                                    <option value="<?php echo htmlspecialchars($sess['session_id']); ?>"
                                                        <?php echo ($selectedSession == $sess['session_id']) ? 'selected' : ''; ?>>
                                                        <?php echo htmlspecialchars($sess['session_name']); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="form-group col-md-3 mb-0">
                                            <button type="submit" class="btn btn-primary btn-filter"
                                                id="loadSessionBtn">
                                                <i class="icon-magnifier"></i> &nbsp;Load Report Cards
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>

                            <?php if ($selectedSession && $encodedSession): ?>

                                <div class="class-grid">
                                    <?php if (!empty($classes)): ?>
                                        <?php foreach ($classes as $row): ?>
                                            <?php
                                            $encodedClass = urlencode(base64_encode($row['ID']));
                                            $printUrl = "download-all-report-cards.php?examSession={$encodedSession}&className={$encodedClass}";
                                            ?>
                                            <div class="class-card">
                                                <h5>Class: <?php echo htmlspecialchars($row['ClassName']); ?></h5>
                                                <div class="section-info">All Sections Included</div>
                                                <a href="<?php echo $printUrl; ?>" target="_blank" class="btn-print-class">
                                                    <i class="icon-printer"></i> &nbsp;Print Class Reports
                                                </a>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <div class="col-12">
                                            <div class="no-data-msg">
                                                <i class="icon-info"></i>
                                                No classes found with report data for this session.
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                </div>

                            <?php else: ?>
                                <div class="no-data-msg">
                                    <i class="icon-layers"></i>
                                    Please select an academic session to view available classes.
                                </div>
                            <?php endif; ?>

                        </div>
                    </div>
                </div>
                <?php include_once('includes/footer.php'); ?>
            </div>
        </div>
    </div>

    <script src="vendors/js/vendor.bundle.base.js"></script>
    <script src="js/off-canvas.js"></script>
    <script src="js/misc.js"></script>
</body>

</html>