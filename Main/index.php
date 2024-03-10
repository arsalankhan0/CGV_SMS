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

    <!-- For gallery -->
    <link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/fancybox/3.5.7/jquery.fancybox.min.css">

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
	<div id="carouselExampleControls" class="carousel slide bs-slider box-slider" data-ride="carousel" data-interval="5000" data-pause="hover">
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
    <div id="overviews" class="section wb pb-2">
        <div class="container wow slideInLeft" data-wow-offset="300">
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

    <!-- Gallery Section -->
    <div id="gallery" class="section wb wow fadeInUp pt-1" data-wow-duration="1s" data-wow-delay="0.2s">
        <div class="container">
            <div class="section-title row text-center">
                <div class="col-md-8 offset-md-2">
                    <h3>Gallery</h3>
                    <p class="lead">Explore our gallery</p>
                </div>
            </div>

            <!-- Image Grid -->
            <div class="gallery-grid">
                <div class="img-container wow fadeIn" data-wow-duration="1s" data-wow-delay="0.4s">
                    <a data-fancybox="gallery" href="./images/blog_1.jpg">
                        <img src="./images/blog_1.jpg" alt="Image 1" class="img-fluid">
                    </a>
                </div>
                <div class="img-container wow fadeIn" data-wow-duration="1s" data-wow-delay="0.6s">
                    <a data-fancybox="gallery" href="./images/blog_2.jpg">
                        <img src="./images/blog_2.jpg" alt="Image 2" class="img-fluid">
                    </a>
                </div>
                <div class="img-container wow fadeIn" data-wow-duration="1s" data-wow-delay="0.8s">
                    <a data-fancybox="gallery" href="./images/blog_5.jpg">
                        <img src="./images/blog_5.jpg" alt="Image 3" class="img-fluid">
                    </a>
                </div>
                <div class="img-container wow fadeIn" data-wow-duration="1s" data-wow-delay="0.8s">
                    <a data-fancybox="gallery" href="./images/blog_3.jpg">
                        <img src="./images/blog_3.jpg" alt="Image 3" class="img-fluid">
                    </a>
                </div>
                <div class="img-container wow fadeIn" data-wow-duration="1s" data-wow-delay="0.8s">
                    <a data-fancybox="gallery" href="./images/blog_4.jpg">
                        <img src="./images/blog_4.jpg" alt="Image 3" class="img-fluid">
                    </a>
                </div>
                <div class="img-container wow fadeIn" data-wow-duration="1s" data-wow-delay="0.8s">
                    <a data-fancybox="gallery" href="./images/blog_6.jpg">
                        <img src="./images/blog_6.jpg" alt="Image 3" class="img-fluid">
                    </a>
                </div>
            </div>

            <!-- View All Button -->
            <div class="row text-center mt-4">
                <div class="col-md-12">
                    <a href="view-all-gallery.php" class="btn btn-maroon wow fadeInUp" data-wow-duration="1s" data-wow-delay="1s">View All</a>
                </div>
            </div>
        </div>
    </div><!-- end section -->

    <!-- Public Notice -->
    <div id="testimonials" class="parallax section db parallax-off" style="background-image:url('images/parallax_04.jpg');">
        <div class="container wow fadeIn" data-wow-offset="300">
            <div class="section-title text-center">
                <h3>Public Notice</h3>
            </div><!-- end title -->

            <div class="public-notice-container">
                <?php
                $currentDate = date("Y-m-d");
                $sql = "SELECT * FROM tblpublicnotice WHERE IsDeleted = 0 ORDER BY CreationDate DESC";
                $query = $dbh->prepare($sql);
                $query->execute();
                $results = $query->fetchAll(PDO::FETCH_OBJ);

                if ($query->rowCount() > 0) {
                    ?>
                    <marquee style="height: 350px; overflow: hidden;" id="public-notice" direction="up" onmouseover="this.stop();" onmouseout="this.start();">
                        <?php
                        foreach ($results as $row) {
                            $noticeDate = date("Y-m-d", strtotime($row->CreationDate));
                            $isNew = ($currentDate == $noticeDate);
                            ?>
                            <div class="public-notice-item border-bottom mb-3">
                                <a href="view-public-notice.php?viewid=<?php echo htmlentities($row->ID);?>" target="_blank">
                                    <?php echo htmlentities($row->NoticeTitle);?>
                                    <span class="badge badge-primary"><?php echo date("j M, Y", strtotime($row->CreationDate)); ?></span>
                                    <?php if ($isNew): ?>
                                        <span class="badge badge-animated badge-maroon">New</span>
                                    <?php endif; ?>
                                </a>
                            </div>
                            <?php
                        }
                        ?>
                    </marquee>
                <?php
                } else {
                    echo '<p class="text-center">No public notices available.</p>';
                }
                ?>
            </div>
        </div><!-- end container -->
    </div><!-- end section -->

    <?php include_once('../includes/footer.php');?>

    <a href="#" id="scroll-to-top" class="dmtop global-radius"><i class="fa fa-angle-up"></i></a>

    <!-- ALL JS FILES -->
    <script src="js/all.js"></script>
    <!-- ALL PLUGINS -->
    <script src="js/custom.js"></script>
	<script src="js/timeline.min.js"></script>

    <script>
        new WOW().init();

		timeline(document.querySelectorAll('.timeline'), {
			forceVerticalMode: 700,
			mode: 'horizontal',
			verticalStartPosition: 'left',
			visibleItems: 4
		});
    </script>

<!-- For gallery -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/fancybox/3.5.7/jquery.fancybox.min.js"></script>

</body>
</html>