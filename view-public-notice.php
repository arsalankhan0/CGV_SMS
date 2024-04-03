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
		<meta name="author" content="colorlib">
		<!-- Meta Description -->
		<meta name="description" content="">
		<!-- Meta Keyword -->
		<meta name="keywords" content="">
		<!-- meta character set -->
		<meta charset="UTF-8">
		<!-- Site Title -->
		<title>TPS - View Notice</title>

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
			<link rel="stylesheet" href="./Main/css/custom.css">
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
								Notice Details				
							</h1>	
						</div>	
					</div>
				</div>
			</section>
			<!-- End banner Area -->	
				
			<!-- Start events-list Area -->
			<section class="events-list-area section-gap event-page-lists">
				<div class="container">
					<div class="row align-items-center">
					<table class="table table-bordered">
                    <?php
                    $vid = $_GET['viewid'];
                    $sql = "SELECT * from tblpublicnotice where ID=:vid";
                    $query = $dbh->prepare($sql);
                    $query->bindParam(':vid', $vid, PDO::PARAM_STR);
                    $query->execute();
                    $results = $query->fetchAll(PDO::FETCH_OBJ);
                    $cnt = 1;
                    if ($query->rowCount() > 0) {
                        foreach ($results as $row) { ?>
                            <tr>
                                <th>Notice Announced Date</th>
                                <td><?php echo date('j F, Y', strtotime($row->CreationDate)); ?></td>
                            </tr>
                            <tr>
                                <th>Noitice Title</th>
                                <td><?php echo $row->NoticeTitle; ?></td>
                            </tr>
                            <tr>
                                <th>Message</th>
                                <td><?php echo $row->NoticeMessage; ?></td>
                            </tr>
                    <?php
                            $cnt = $cnt + 1;
                        }
                    } ?>
                </table>														
								
					</div>
				</div>	
			</section>
			<!-- End events-list Area -->

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