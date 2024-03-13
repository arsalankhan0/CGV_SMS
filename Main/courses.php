<?php 
	session_start();
	error_reporting(0);
	include('../includes/dbconnection.php');
?>
	<!DOCTYPE html>
	<html lang="zxx" class="no-js">
	<head>
		<!-- Mobile Specific Meta -->
		<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
		<!-- Favicon-->
		<link rel="shortcut icon" href="img/fav.png">
		<!-- Author Meta -->
		<meta name="author" content="colorlib">
		<!-- Meta Description -->
		<meta name="description" content="">
		<!-- Meta Keyword -->
		<meta name="keywords" content="">
		<!-- meta character set -->
		<meta charset="UTF-8">
		<!-- Site Title -->
		<title>SMS - Courses</title>

		<link href="https://fonts.googleapis.com/css?family=Poppins:100,200,400,300,500,600,700" rel="stylesheet"> 
			<!--
			CSS
			============================================= -->
			<link rel="stylesheet" href="css/linearicons.css">
			<link rel="stylesheet" href="css/font-awesome.min.css">
			<link rel="stylesheet" href="css/bootstrap.css">
			<link rel="stylesheet" href="css/magnific-popup.css">
			<link rel="stylesheet" href="css/nice-select.css">							
			<link rel="stylesheet" href="css/animate.min.css">
			<link rel="stylesheet" href="css/owl.carousel.css">			
			<link rel="stylesheet" href="css/jquery-ui.css">			
			<link rel="stylesheet" href="css/main.css">
		</head>
		<body>	
			<?php include_once('../includes/header.php'); ?>

			<!-- start banner Area -->
			<section class="banner-area relative about-banner" id="home">	
				<div class="overlay overlay-bg"></div>
				<div class="container">				
					<div class="row d-flex align-items-center justify-content-center">
						<div class="about-content col-lg-12">
							<h1 class="text-white">
								Popular Courses		
							</h1>	
							<p class="text-white link-nav"><a href="index.php">Home </a>  <span class="lnr lnr-arrow-right"></span>  <a href="courses.php"> Popular Courses</a></p>
						</div>	
					</div>
				</div>
			</section>
			<!-- End banner Area -->	

			<!-- Start popular-courses Area --> 
			<section class="popular-courses-area section-gap courses-page">
				<div class="container">
					<div class="row d-flex justify-content-center">
						<div class="menu-content pb-70 col-lg-8">
							<div class="title text-center">
								<h1 class="mb-10">Popular Courses we offer</h1>
								<p>There is a moment in the life of any aspiring.</p>
							</div>
						</div>
					</div>						
					<div class="row">
						<div class="single-popular-carusel col-lg-3 col-md-6">
							<div class="thumb-wrap relative">
								<div class="thumb relative">
									<div class="overlay overlay-bg"></div>	
									<img class="img-fluid" src="img/p1.jpg" alt="">
								</div>
								<div class="meta d-flex justify-content-between">
									<p><span class="lnr lnr-users"></span> 355 <span class="lnr lnr-bubble"></span>35</p>
									<h4>$150</h4>
								</div>									
							</div>
							<div class="details">
								<a href="course-details.html">
									<h4>
										Learn Designing
									</h4>
								</a>
								<p>
									When television was young, there was a hugely popular show based on the still popular fictional characte										
								</p>
							</div>
						</div>	
						<div class="single-popular-carusel col-lg-3 col-md-6">
							<div class="thumb-wrap relative">
								<div class="thumb relative">
									<div class="overlay overlay-bg"></div>	
									<img class="img-fluid" src="img/p2.jpg" alt="">
								</div>
								<div class="meta d-flex justify-content-between">
									<p><span class="lnr lnr-users"></span> 355 <span class="lnr lnr-bubble"></span>35</p>
									<h4>$150</h4>
								</div>									
							</div>
							<div class="details">
								<a href="course-details.html">
									<h4>
										Learn React js beginners
									</h4>
								</a>
								<p>
									When television was young, there was a hugely popular show based on the still popular fictional characte										
								</p>
							</div>
						</div>	
						<div class="single-popular-carusel col-lg-3 col-md-6">
							<div class="thumb-wrap relative">
								<div class="thumb relative">
									<div class="overlay overlay-bg"></div>	
									<img class="img-fluid" src="img/p3.jpg" alt="">
								</div>
								<div class="meta d-flex justify-content-between">
									<p><span class="lnr lnr-users"></span> 355 <span class="lnr lnr-bubble"></span>35</p>
									<h4>$150</h4>
								</div>									
							</div>
							<div class="details">
								<a href="course-details.html">
									<h4>
										Learn Photography
									</h4>
								</a>
								<p>
									When television was young, there was a hugely popular show based on the still popular fictional characte										
								</p>
							</div>
						</div>	
						<div class="single-popular-carusel col-lg-3 col-md-6">
							<div class="thumb-wrap relative">
								<div class="thumb relative">
									<div class="overlay overlay-bg"></div>	
									<img class="img-fluid" src="img/p4.jpg" alt="">
								</div>
								<div class="meta d-flex justify-content-between">
									<p><span class="lnr lnr-users"></span> 355 <span class="lnr lnr-bubble"></span>35</p>
									<h4>$150</h4>
								</div>									
							</div>
							<div class="details">
								<a href="course-details.html">
									<h4>
										Learn Surveying
									</h4>
								</a>
								<p>
									When television was young, there was a hugely popular show based on the still popular fictional characte										
								</p>
							</div>
						</div>
						<div class="single-popular-carusel col-lg-3 col-md-6">
							<div class="thumb-wrap relative">
								<div class="thumb relative">
									<div class="overlay overlay-bg"></div>	
									<img class="img-fluid" src="img/p1.jpg" alt="">
								</div>
								<div class="meta d-flex justify-content-between">
									<p><span class="lnr lnr-users"></span> 355 <span class="lnr lnr-bubble"></span>35</p>
									<h4>$150</h4>
								</div>									
							</div>
							<div class="details">
								<a href="course-details.html">
									<h4>
										Learn Designing
									</h4>
								</a>
								<p>
									When television was young, there was a hugely popular show based on the still popular fictional characte										
								</p>
							</div>
						</div>	
						<div class="single-popular-carusel col-lg-3 col-md-6">
							<div class="thumb-wrap relative">
								<div class="thumb relative">
									<div class="overlay overlay-bg"></div>	
									<img class="img-fluid" src="img/p2.jpg" alt="">
								</div>
								<div class="meta d-flex justify-content-between">
									<p><span class="lnr lnr-users"></span> 355 <span class="lnr lnr-bubble"></span>35</p>
									<h4>$150</h4>
								</div>									
							</div>
							<div class="details">
								<a href="course-details.html">
									<h4>
										Learn React js beginners
									</h4>
								</a>
								<p>
									When television was young, there was a hugely popular show based on the still popular fictional characte										
								</p>
							</div>
						</div>	
						<div class="single-popular-carusel col-lg-3 col-md-6">
							<div class="thumb-wrap relative">
								<div class="thumb relative">
									<div class="overlay overlay-bg"></div>	
									<img class="img-fluid" src="img/p3.jpg" alt="">
								</div>
								<div class="meta d-flex justify-content-between">
									<p><span class="lnr lnr-users"></span> 355 <span class="lnr lnr-bubble"></span>35</p>
									<h4>$150</h4>
								</div>									
							</div>
							<div class="details">
								<a href="course-details.html">
									<h4>
										Learn Photography
									</h4>
								</a>
								<p>
									When television was young, there was a hugely popular show based on the still popular fictional characte										
								</p>
							</div>
						</div>	
						<div class="single-popular-carusel col-lg-3 col-md-6">
							<div class="thumb-wrap relative">
								<div class="thumb relative">
									<div class="overlay overlay-bg"></div>	
									<img class="img-fluid" src="img/p4.jpg" alt="">
								</div>
								<div class="meta d-flex justify-content-between">
									<p><span class="lnr lnr-users"></span> 355 <span class="lnr lnr-bubble"></span>35</p>
									<h4>$150</h4>
								</div>									
							</div>
							<div class="details">
								<a href="course-details.html">
									<h4>
										Learn Surveying
									</h4>
								</a>
								<p>
									When television was young, there was a hugely popular show based on the still popular fictional characte										
								</p>
							</div>
						</div>								
						<a href="#" class="primary-btn text-uppercase mx-auto">Load More Courses</a>													
					</div>
				</div>	
			</section>
			<!-- End popular-courses Area -->			

	


			<!-- Footer -->
			<?php include_once('../includes/footer.php');?>	


			<script src="js/vendor/jquery-2.2.4.min.js"></script>
			<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
			<script src="js/vendor/bootstrap.min.js"></script>			
			<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBhOdIF3Y9382fqJYt5I_sswSrEw5eihAA"></script>
  			<script src="js/easing.min.js"></script>			
			<script src="js/hoverIntent.js"></script>
			<script src="js/superfish.min.js"></script>	
			<script src="js/jquery.ajaxchimp.min.js"></script>
			<script src="js/jquery.magnific-popup.min.js"></script>	
    		<script src="js/jquery.tabs.min.js"></script>						
			<script src="js/jquery.nice-select.min.js"></script>	
			<script src="js/owl.carousel.min.js"></script>									
			<script src="js/mail-script.js"></script>	
			<script src="js/main.js"></script>	
		</body>
	</html>