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
		<title>SMS - Gallery</title>

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
								Gallery				
							</h1>	
							<p class="text-white link-nav"><a href="index.php">Home </a>  <span class="lnr lnr-arrow-right"></span>  <a href="gallery.php"> Gallery</a></p>
						</div>	
					</div>
				</div>
			</section>
			<!-- End banner Area -->	
				
			<!-- Start gallery Area -->
			<section class="gallery-area section-gap">
				<div class="container">
					<div class="row">
						<?php
							// Fetch image paths from tblgallery
							$sql = "SELECT imgPath FROM tblgallery ORDER BY ID DESC";
							$stmt = $dbh->prepare($sql);
							$stmt->execute();
							$images = $stmt->fetchAll(PDO::FETCH_COLUMN);

							if(count($images)> 0)
							{
								// Display images
								foreach ($images as $imagePath) 
								{
									?>
									<div class="col-lg-4">
										<a href="<?php echo '../admin/gallery/'.$imagePath; ?>" class="img-gal">
											<div class="single-imgs relative">
												<div class="overlay overlay-bg"></div>
												<div class="relative">
													<img class="img-fluid gallery-img" src="<?php echo '../admin/gallery/'.$imagePath; ?>" alt="img">
												</div>
											</div>
										</a>
									</div>
									<?php
								}
							}
							else
							{
								echo '<div class="col-lg-12">
										<h4 class="text-center">No Images to show!</h4>
									</div>';
							}
							?>
					</div>
				</div>	
			</section>
			<!-- End gallery Area -->

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