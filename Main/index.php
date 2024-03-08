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

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/odometer.js/0.4.8/themes/odometer-theme-default.min.css" />


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

<!-- Distinctions and Positions -->
<div id="distinctions" class="parallax section db parallax-off" style="background-color: maroon; color: white; padding: 60px 0;">
    <div class="container">
        <!-- start distinctions and positions -->
        <div class="section-title text-center mb-5">
            <h2 class="text-uppercase font-weight-bold">Total Distinctions and Positions</h2>

            <div class="row mt-5">
                <!-- Animated Total Distinctions -->
                <div class="col-md-6">
                    <div class="animated-number text-white" id="totalDistinctions" data-from="0" data-to="100" data-speed="1000" data-refresh-interval="50" style="font-size: 48px; font-weight: bold;"></div>
                    <p class="font-weight-bold">Distinctions</p>
                </div>

                <!-- Animated Total Positions -->
                <div class="col-md-6">
                    <div class="animated-number text-white" id="totalPositions" data-from="0" data-to="50" data-speed="1000" data-refresh-interval="50" style="font-size: 48px; font-weight: bold;"></div>
                    <p class="font-weight-bold">Positions</p>
                </div>
            </div>
        </div><!-- end distinctions and positions -->
    </div><!-- end container -->
</div><!-- end section -->


<!-- Toppers Section -->
<div id="toppers" class="section wb">
    <div class="container wow slideInRight" data-wow-offset="300">
        <div class="section-title text-center">
            <h3>Topper's Gallery</h3>
        </div><!-- end title -->

        <!-- Toppers Carousel -->
        <div id="toppersCarousel" class="carousel slide" data-ride="carousel">
            <div class="carousel-inner">
                <!-- Toppers Content Goes Here -->
                <!-- Repeat the following structure for each topper -->
                <div class="carousel-item active">
                    <div class="topper-card" style="background-color: maroon;">
                        <img src="https://source.unsplash.com/800x600/?student" class="d-block mx-auto" alt="Topper 1">
                        <div class="topper-details text-white">
                            <h5>Topper Name 1</h5>
                            <p>Class: 10th</p>
                            <p>Marks: 95%</p>
                            <p>Rank: 1st</p>
                        </div>
                    </div>
                </div>
                <div class="carousel-item">
                    <div class="topper-card" style="background-color: maroon;">
                        <img src="https://source.unsplash.com/800x600/?student" class="d-block mx-auto" alt="Topper 1">
                        <div class="topper-details text-white">
                            <h5>Topper Name 1</h5>
                            <p>Class: 10th</p>
                            <p>Marks: 95%</p>
                            <p>Rank: 1st</p>
                        </div>
                    </div>
                </div>
                <!-- End of Topper Card -->
            </div>

            <!-- Left Control -->
            <a class="carousel-control-prev" href="#toppersCarousel" role="button" data-slide="prev">
                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                <span class="sr-only">Previous</span>
            </a>
            <!-- Right Control -->
            <a class="carousel-control-next" href="#toppersCarousel" role="button" data-slide="next">
                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                <span class="sr-only">Next</span>
            </a>
        </div><!-- End Toppers Carousel -->
    </div><!-- end container -->
</div><!-- end section -->


    <!-- Public Notice -->
    <div id="testimonials" class="parallax section db parallax-off" style="background-image:url('images/parallax_04.jpg');">
        <div class="container wow slideInRight" data-wow-offset="300">
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

    <?php include_once('../includes/footer.php');?>

    <a href="#" id="scroll-to-top" class="dmtop global-radius"><i class="fa fa-angle-up"></i></a>

    <!-- ALL JS FILES -->
    <script src="js/all.js"></script>
    <!-- ALL PLUGINS -->
    <script src="js/custom.js"></script>
	<script src="js/timeline.min.js"></script>
    <!-- odometer.js library -->
    <script src="js/odometer.min.js"></script>


    
	<script>
		timeline(document.querySelectorAll('.timeline'), {
			forceVerticalMode: 700,
			mode: 'horizontal',
			verticalStartPosition: 'left',
			visibleItems: 4
		});


        document.addEventListener('DOMContentLoaded', function() {
            // if Odometer is defined
            if (typeof Odometer !== 'undefined') 
            {
                // Initialize Odometer for Total Distinctions
                var totalDistinctions = new Odometer({
                    el: document.querySelector('#totalDistinctions'),
                    value: 0,
                    format: '(,ddd)', // You can customize the format as needed
                    theme: 'default'
                });
                // Animate the odometer
                totalDistinctions.update(80);

                // Initialize Odometer for Total Positions
                var totalPositions = new Odometer({
                    el: document.querySelector('#totalPositions'),
                    value: 0,
                    format: '(,ddd)',
                    theme: 'default'
                });
                totalPositions.update(50);
            } 
            else 
            {
                console.error('Odometer is not defined. Check if the library is loaded correctly.');
            }
        });
	</script>
</body>
</html>