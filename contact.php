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
		<link rel="shortcut icon" href="img/fav.png">
		<!-- Author Meta -->
		<meta name="author" content="">
		<!-- Meta Description -->
		<meta name="description" content="">
		<!-- Meta Keyword -->
		<meta name="keywords" content="Tibetan Public School">
		<!-- meta character set -->
		<meta charset="UTF-8">
		<!-- Site Title -->
		<title>TPS - Contact Us</title>

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
		</head>
		<body>	
			<?php include_once('./includes/header.php'); ?>

			<!-- start banner Area -->
			<section class="banner-area relative about-banner" id="home">	
				<div class="overlay overlay-bg"></div>
				<div class="container">				
					<div class="row d-flex align-items-center justify-content-center">
						<div class="about-content col-lg-12">
							<h1 class="text-white">
								Contact Us				
							</h1>	
							<p class="text-white link-nav"><a href="index.php">Home </a>  <span class="lnr lnr-arrow-right"></span>  <a href="contact.php"> Contact Us</a></p>
						</div>	
					</div>
				</div>
			</section>
			<!-- End banner Area -->				  

			<!-- Start contact-page Area -->
			<section class="contact-page-area section-gap">
				<div class="container">
					<div class="row">
						<!-- <div class="map-wrap" style="width:100%; height: 445px;" id="map"></div> -->
						<div class="col-lg-12 d-flex flex-column flex-md-row address-wrap">
							<?php
							$sql="SELECT * from tblpage where PageType='contactus'";
							$query = $dbh->prepare($sql);
							$query->execute();
							$results=$query->fetchAll(PDO::FETCH_OBJ);

							if($query->rowCount() > 0)
							{
								foreach($results as $row)
								{
							?>
									<div class="single-contact-address col-md-4 d-flex flex-column flex-md-row">
										<div class="icon">
											<span class="lnr lnr-home"></span>
										</div>
										<div class="contact-details">
											<h5>Address</h5>
											<p>
												<?php echo nl2br(htmlentities($row->PageDescription)); ?>
											</p>
										</div>
									</div>
									<div class="single-contact-address col-md-4 d-flex flex-column flex-md-row">
										<div class="icon">
											<span class="lnr lnr-phone-handset"></span>
										</div>
										<div class="contact-details">
											<h5><?php echo "+91 ". htmlentities($row->MobileNumber); ?></h5>
										</div>
									</div>
									<div class="single-contact-address col-md-4 d-flex flex-column flex-md-row">
										<div class="icon">
											<span class="lnr lnr-envelope"></span>
										</div>
										<div class="contact-details">
											<h5><?php echo htmlentities($row->Email); ?></h5>
											<p>Send us your query anytime!</p>
										</div>
									</div>						
							<?php
								}
							}
							?>
						</div>
					</div>
				</div>	
			</section>
			<!-- End contact-page Area -->

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
		</body>
	</html>