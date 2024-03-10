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
    <title>Student Management System || Gallery</title>  
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

    <!-- For gallery -->
    <link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/fancybox/3.5.7/jquery.fancybox.min.css">


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
						<li class="nav-item"><a class="nav-link" href="index.php">Home</a></li>
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
	
	<div class="all-title-box">
		<div class="container text-center">
			<h1>Our Gallery</h1>
		</div>
	</div>

    <!-- Gallery Section -->
    <div id="gallery" class="section wb wow fadeInUp pt-1" data-wow-duration="1s" data-wow-delay="0.2s">
        <div class="container">
            <!-- Image Grid -->
            <div class="gallery-grid mt-5">
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
        </div>
    </div><!-- end section -->


    <?php include_once('../includes/footer.php');?>

    <a href="#" id="scroll-to-top" class="dmtop global-radius"><i class="fa fa-angle-up"></i></a>

    <!-- ALL JS FILES -->
    <script src="js/all.js"></script>
    <!-- ALL PLUGINS -->
    <script src="js/custom.js"></script>
    <script>
        new WOW().init();
    </script>

<!-- For gallery -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/fancybox/3.5.7/jquery.fancybox.min.js"></script>

</body>
</html>