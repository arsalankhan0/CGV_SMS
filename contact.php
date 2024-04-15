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
		<meta name="author" content="Tibetan Public school">
		<!-- Meta Description -->
		<meta name="description" content="Tibetan Public school Srinagar">
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

			<!-- Start contact-page Area -->
			<section class="contact-page-area section-gap mt-80">
				<div class="container">
					<div class="section-title text-center">
						<h2>Contact Us</h2>
						<p>Feel free to reach out to us</p>
					</div>
					<div class="row contact-section">
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
							<div class="col-md-4 mb-4">
								<div class="card h-100 shadow">
									<div class="card-body">
										<div class="icon">
											<span class="lnr lnr-home large-icon"></span>
										</div>
										<div class="contact-details">
											<h5 class="card-title">Address</h5>
											<p class="card-text"><?php echo nl2br(htmlentities($row->PageDescription)); ?></p>
										</div>
									</div>
								</div>
							</div>
							<div class="col-md-4 mb-4">
								<div class="card h-100 shadow">
									<div class="card-body">
										<div class="icon">
											<span class="lnr lnr-phone-handset large-icon"></span>
										</div>
										<div class="contact-details">
											<?php
											$mobileNumber = htmlentities($row->MobileNumber);
											// if the mobile number starts with '0194' and contains a hyphen '-'
											if (strpos($mobileNumber, '01') === 0 && strpos($mobileNumber, '-') !== false) 
											{
												$phoneNumber = $mobileNumber;
											} 
											else 
											{
												$phoneNumber = '+91 ' . $mobileNumber;
											}
											?>
											<!-- <h5 class="card-title"><?php echo "+91 ". htmlentities($row->MobileNumber); ?></h5> -->
											<h5 class="card-title"><?php echo $phoneNumber; ?></h5>
											<p class="card-text">For inquiries or assistance, please feel free to call us!</p>
										</div>
									</div>
								</div>
							</div>
							<div class="col-md-4 mb-4">
								<div class="card h-100 shadow">
									<div class="card-body">
										<div class="icon">
											<span class="lnr lnr-envelope large-icon"></span>
										</div>
										<div class="contact-details">
											<h5 class="card-title"><?php echo htmlentities($row->Email); ?></h5>
											<p class="card-text">Send us your query anytime!</p>
										</div>
									</div>
								</div>
							</div>                    
						<?php
							}
						}
						?>
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