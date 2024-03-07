<?php
session_start();
error_reporting(0);
include('../includes/dbconnection.php');
?>
<!DOCTYPE html>
<html lang="en">

    <!-- Basic -->
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">   

    <!-- Mobile Metas -->
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Site Metas -->
    <title>Student Management System || Home</title>  
    <meta name="keywords" content="">
    <meta name="description" content="">
    <meta name="author" content="">

    <!-- Site Icons -->
    <link rel="shortcut icon" href="images/favicon.ico" type="image/x-icon" />
    <link rel="apple-touch-icon" href="images/apple-touch-icon.png">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <!-- Site CSS -->
    <link rel="stylesheet" href="style.css">
    <!-- ALL VERSION CSS -->
    <link rel="stylesheet" href="css/versions.css">
    <!-- Responsive CSS -->
    <link rel="stylesheet" href="css/responsive.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="css/custom.css">

    <!-- Modernizer for Portfolio -->
    <script src="js/modernizer.js"></script>

</head>
<body class="host_version"> 

    <!-- LOADER -->
	<div id="preloader">
		<div class="loader-container">
			<div class="progress-br float shadow">
				<div class="progress__item"></div>
			</div>
		</div>
	</div>
	<!-- END LOADER -->	
	
	<!-- Start header -->
	<header class="top-navbar">
		<nav class="navbar navbar-expand-lg navbar-light bg-light">
			<div class="container-fluid">
				<a class="navbar-brand" href="index.html">
					<!-- <img src="images/logo.png" alt="" /> -->
                    <h2 class="text-light">SMS</h2>
				</a>
				<button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbars-host" aria-controls="navbars-rs-food" aria-expanded="false" aria-label="Toggle navigation">
					<span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
				</button>
				<div class="collapse navbar-collapse" id="navbars-host">
					<ul class="navbar-nav ml-auto">
						<li class="nav-item active"><a class="nav-link" href="index.php">Home</a></li>
						<li class="nav-item"><a class="nav-link" href="about.php">About</a></li>
						<li class="nav-item"><a class="nav-link" href="contact.php">Contact</a></li>
						<li class="nav-item"><a class="nav-link" href="../admin/login.php">Admin</a></li>
						<li class="nav-item"><a class="nav-link" href="../user/login.php">Student</a></li>
					</ul>
				</div>
			</div>
		</nav>
	</header>
	<!-- End header -->
	
    <!-- Main Heading images -->
	<div id="carouselExampleControls" class="carousel slide bs-slider box-slider" data-ride="carousel" data-pause="hover" data-interval="false" >
		<!-- Indicators -->
		<ol class="carousel-indicators">
			<li data-target="#carouselExampleControls" data-slide-to="0" class="active"></li>
			<li data-target="#carouselExampleControls" data-slide-to="1"></li>
			<li data-target="#carouselExampleControls" data-slide-to="2"></li>
		</ol>
		<div class="carousel-inner" role="listbox">
			<div class="carousel-item active">
				<div id="home" class="first-section" style="background-image:url('images/slider-01.jpg');">
					<div class="dtab">
						<div class="container">
							<div class="row">
								<div class="col-md-12 col-sm-12 text-right">
									<div class="big-tagline">
										<h2>Student Management System</h2>
										<p class="lead">Registered students can login here</p>
											<a href="../user/login.php" class="hover-btn-new"><span>Student Login</span></a>
											&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
									</div>
								</div>
							</div><!-- end row -->            
						</div><!-- end container -->
					</div>
				</div><!-- end section -->
			</div>
			<div class="carousel-item">
				<div id="home" class="first-section" style="background-image:url('images/slider-02.jpg');">
					<div class="dtab">
						<div class="container">
							<div class="row">
								<div class="col-md-12 col-sm-12 text-left">
                                    <div class="big-tagline">
										<h2>Student Management System</h2>
										<p class="lead">Registered students can login here</p>
											<a href="../user/login.php" class="hover-btn-new"><span>Student Login</span></a>
											&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
									</div>
								</div>
							</div><!-- end row -->            
						</div><!-- end container -->
					</div>
				</div><!-- end section -->
			</div>
			<div class="carousel-item">
				<div id="home" class="first-section" style="background-image:url('images/slider-03.jpg');">
					<div class="dtab">
						<div class="container">
							<div class="row">
								<div class="col-md-12 col-sm-12 text-center">
                                    <div class="big-tagline">
										<h2>Student Management System</h2>
										<p class="lead">Registered students can login here</p>
											<a href="../user/login.php" class="hover-btn-new"><span>Student Login</span></a>
											&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
									</div>
								</div>
							</div><!-- end row -->            
						</div><!-- end container -->
					</div>
				</div><!-- end section -->
			</div>
			<!-- Left Control -->
			<a class="new-effect carousel-control-prev" href="#carouselExampleControls" role="button" data-slide="prev">
				<span class="fa fa-angle-left" aria-hidden="true"></span>
				<span class="sr-only">Previous</span>
			</a>

			<!-- Right Control -->
			<a class="new-effect carousel-control-next" href="#carouselExampleControls" role="button" data-slide="next">
				<span class="fa fa-angle-right" aria-hidden="true"></span>
				<span class="sr-only">Next</span>
			</a>
		</div>
	</div>
	
    <!-- About us -->
    <div id="overviews" class="section wb">
        <div class="container wow flipInX" data-wow-offset="300">
            <!-- start about -->
            <div class="section-title row text-center">
                <div class="col-md-8 offset-md-2">
                    <p class="lead">
                        <?php
                        $sql="SELECT * from tblpage where PageType='aboutus'";
                        $query = $dbh -> prepare($sql);
                        $query->execute();
                        $results=$query->fetchAll(PDO::FETCH_OBJ);

                        $cnt=1;
                        if($query->rowCount() > 0)
                        {
                            foreach($results as $row)
                            {               ?>
                                <h3><?php  echo htmlentities($row->PageTitle);?></h3>
                                <p><?php  echo ($row->PageDescription);?></p><?php $cnt=$cnt+1;
                            }
                        } 
                        ?>
                    </p>
                </div>
            </div><!-- end about -->
        </div><!-- end container -->
    </div><!-- end section -->

    <!-- Public Notice -->
    <div id="testimonials" class="parallax section db parallax-off" style="background-image:url('images/parallax_04.jpg');">
        <div class="container wow fadeInUp" data-wow-offset="300">
            <div class="section-title text-center">
                <h3>Public Notice</h3>
            </div><!-- end title -->

            <div class="row">
                <div class="col-md-12 col-sm-12">
                    <div class="testimonial-nfo">
                        <marquee style="height:350px; overflow:hidden;" direction="up" onmouseover="this.stop();" onmouseout="this.start();">
                            <?php
                            $currentDate = date("Y-m-d");
                            $sql="SELECT * from tblpublicnotice WHERE IsDeleted = 0 ORDER BY CreationDate DESC";
                            $query = $dbh -> prepare($sql);
                            $query->execute();
                            $results=$query->fetchAll(PDO::FETCH_OBJ);

                            $cnt=1;
                            if($query->rowCount() > 0)
                            {
                                foreach($results as $row)
                                {
                                    $noticeDate = date("Y-m-d", strtotime($row->CreationDate));
                                    $isNew = ($currentDate == $noticeDate);
                                    ?>
                                    <div class="public-notice-item border-bottom mb-3">
                                        <a href="view-public-notice.php?viewid=<?php echo htmlentities ($row->ID);?>" target="_blank" class="text-white" style="text-decoration:none; font-size: 1.6rem;">
                                            <?php  echo htmlentities($row->NoticeTitle);?>
                                            <span class="badge badge-danger"><?php echo date("j M, Y", strtotime($row->CreationDate)); ?></span>
                                            <?php if ($isNew): ?>
                                                <span class="badge animated fadeIn" style="background: red;">New</span>
                                            <?php endif; ?>
                                        </a>
                                    </div>
                                    <?php $cnt=$cnt+1;
                                }
                            } 
                            ?>
                        </marquee>
                    </div>
                </div><!-- end col -->
            </div><!-- end row -->
        </div><!-- end container -->
    </div><!-- end section -->

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="row">

                <!-- Links -->
				<div class="col-lg-4 col-md-4 col-xs-12">
                    <div class="widget clearfix">
                        <div class="widget-title">
                            <h3>Information Link</h3>
                        </div>
                        <ul class="footer-links">
                            <li><a href="index.php">Home</a></li>
                            <li><a href="about.php">About</a></li>
                            <li><a href="contact.php">Contact</a></li>
                            <li><a href="../admin/login.php">Admin</a></li>
                            <li><a href="../user/login.php">Student</a></li>
                        </ul><!-- end links -->
                    </div><!-- end clearfix -->
                </div><!-- end col -->
				
                <!-- Address -->
                <div class="col-lg-4 col-md-4 col-xs-12">
                    <div class="widget clearfix">
                        <div class="widget-title">
                            <h3>Address</h3>
                        </div>

                        <?php
                        $sql="SELECT * from tblpage where PageType='contactus'";
                        $query = $dbh -> prepare($sql);
                        $query->execute();
                        $results=$query->fetchAll(PDO::FETCH_OBJ);

                        $cnt=1;
                        if($query->rowCount() > 0)
                        {
                        foreach($results as $row)
                        {               ?>
                                <div class="address">
                                <p><?php  echo htmlentities($row->PageDescription);?>
                                </p>
                                </div>
                                <div class="phone">
                                <p><?php  echo htmlentities($row->MobileNumber);?></p>
                                </div>
                            <?php $cnt=$cnt+1;}} ?>
                    </div><!-- end clearfix -->
                </div><!-- end col -->
				
            </div><!-- end row -->
        </div><!-- end container -->
    </footer><!-- end footer -->

    <!-- Copyright -->
    <div class="copyrights">
        <div class="container">
            <div class="footer-distributed">
                <div class="footer-center">                   
                    <p class="footer-company-name">All Rights Reserved. &copy; <?php echo date('Y');?> <a href="index.php">SMS</a>. Designed and Maintained By : <a href="https://cogveel.com/">Cogveel Technologies</a></p>
                </div>
            </div>
        </div><!-- end container -->
    </div><!-- end copyrights -->

    <a href="#" id="scroll-to-top" class="dmtop global-radius"><i class="fa fa-angle-up"></i></a>

    <!-- ALL JS FILES -->
    <script src="js/all.js"></script>
    <!-- ALL PLUGINS -->
    <script src="js/custom.js"></script>
	<script src="js/timeline.min.js"></script>
	<script>
		timeline(document.querySelectorAll('.timeline'), {
			forceVerticalMode: 700,
			mode: 'horizontal',
			verticalStartPosition: 'left',
			visibleItems: 4
		});
	</script>
</body>
</html>