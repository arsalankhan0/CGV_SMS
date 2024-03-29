<?php
session_start();
error_reporting(0);
include('includes/dbconnection.php');
?>
	<!DOCTYPE html>
	<html lang="zxx" class="no-js">
	<head>
		<!-- Mobile Specific Meta -->
		<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
		<!-- Favicon-->
		<link rel="shortcut icon" href="img/fav.png">
		<!-- Author Meta -->
		<meta name="author" content="Tibetan public school">
		<!-- Meta Description -->
		<meta name="description" content="">
		<!-- Meta Keyword -->
		<meta name="keywords" content="Tibetan Public School">
		<!-- meta character set -->
		<meta charset="UTF-8">
		<!-- Site Title -->
		<title>TPS - Home</title>

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
			<?php include_once('includes/header.php'); ?>
		
			<!-- start banner Area -->
			<section class="banner-area relative" id="home">
				<div class="overlay overlay-bg"></div>	
				<div class="container">
					<div class="row fullscreen d-flex align-items-center justify-content-between">
						<div class="banner-content col-lg-9 col-md-12">
							<h1 class="text-uppercase welcome">
								Welcome to Tibetan Public School
							</h1>
							<p class="pt-10 pb-10 text-light">
								Registered students can login here
							</p>
							<a href="./user/login.php" class="primary-btn text-uppercase">Student Login</a>
						</div>										
					</div>
				</div>					
			</section>
			<!-- End banner Area -->

			<!-- public notices -->
			<section class="feature-area">
				<div class="container">
					<div class="row d-flex justify-content-end">
						<div class="col-lg-4 d-none d-lg-block">
							<div class="single-feature">
								<div class="title">
									<h4>Stay Connected</h4>
								</div>
								<div class="desc-wrap stay-connected text-light">
									<h4 class="mb-4 text-light">Don't Miss Out on Important Notices</h4>
									<p class="text-left">Keep yourself in the loop with our latest notifications.</p>
									<p class="text-left">Whether it's updates, events, or important announcements, we've got you covered.</p>
									<p class="text-left">Explore the notices on the right to stay connected.</p>
								</div>
							</div>
						</div>
						<div class="col-lg-8">
							<div class="single-feature">
								<div class="title">
									<h4>Notifications</h4>
								</div>
								<div class="desc-wrap">
									<div class="public-notice-container">
										<?php
										$currentDateTime = new DateTime();
										$currentDateTime->modify('-24 hours');
										
										$sql = "SELECT * FROM tblpublicnotice WHERE IsDeleted = 0 ORDER BY CreationDate DESC";
										$query = $dbh->prepare($sql);
										$query->execute();
										$results = $query->fetchAll(PDO::FETCH_OBJ);

										if ($query->rowCount() > 0) {
											?>
											<div class="public-notice-scroll" id="public-notice">
												<?php
												foreach ($results as $row) {
													$noticeDateTime = new DateTime($row->CreationDate);
													$isNew = ($noticeDateTime > $currentDateTime);
													$formattedDate = date("j M Y", strtotime($row->CreationDate));
													$day = date("j", strtotime($row->CreationDate));
													$month = date("M", strtotime($row->CreationDate));
													$year = date("Y", strtotime($row->CreationDate));
													?>
													<div class="public-notice-item mb-3">
														<div class="notice-date text-primary">
															<div class="day"><?php echo $day; ?></div>
															<div class="month"><?php echo $month; ?></div>
															<div class="year"><?php echo $year; ?></div>
														</div>
														<a href="view-public-notice.php?viewid=<?php echo htmlentities($row->ID);?>" target="_blank">
															<div class="notice-content text-light mx-4">
																<span class="notice-title"><?php echo htmlentities($row->NoticeTitle);?></span>
																<?php if ($isNew): ?>
																	<span class="badge badge-animated badge-danger">New</span>
																<?php endif; ?>
															</div>
															
														</a>
													</div>
												<?php
												}
												?>
											</div>
										<?php
										} 
										else 
										{
											echo '<p class="text-center">No notices available.</p>';
										}
										?>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</section>
			<!-- End feature Area -->

			<!-- Start principal's message Area -->
			<section class="principal-msg my-4">
				<div class="principal-msg-container">
					<div class="title text-center">
						<h1 class="mb-10">Principal's Message</h1>
					</div>
					<div class="message-box">
						<p>Dear Students, Parents, and Guardians,</p>
						<p>Welcome to Tibetan Public School, where we are dedicated to providing an exceptional educational experience that transcends traditional boundaries. At TPS, we believe in fostering an environment where curiosity is nurtured, creativity is celebrated, and collaboration thrives. With a relentless commitment to excellence, integrity, and respect, we empower every student to embrace their unique talents and abilities, equipping them with the skills and confidence to navigate an ever-changing world. As principal, I am honored to lead this journey of transformation, working hand in hand with our dedicated team of educators to inspire a love for learning that extends far beyond the classroom walls. Together, let us embark on an extraordinary adventure of discovery and growth, shaping a future where every student's potential knows no limits.</p>
						<div class="signature">
							<p>Warm regards,</p>
							<p>Abida Ali</p>
							<p>Principal, TPS</p>
						</div>
					</div>
				</div>
			</section>
			<!-- End principal's message Area -->
			
			<!-- About Us Area -->
			<section class="search-course-area relative">
				<div class="overlay overlay-bg"></div>
				<div class="container">
					<div class="row justify-content-between align-items-center">
						<div class="col-lg-4 col-md-4 search-course-left text-center">

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
										<h1 class="text-light"><?php  echo htmlentities($row->PageTitle);?></h1>
						</div>
										<div class="col-lg-6 col-md-8 section-gap px-4">
							
											<p><?php  echo ($row->PageDescription);?></p><?php $cnt=$cnt+1;?>
										</div>
									<?php
									}
								} 
								?>
							</p>
					</div>
				</div>	
			</section>
			<!-- End search-course Area -->

			<!-- Start gallery Area -->
			<section class="gallery-area section-gap">
				<div class="container">
					<div class="row d-flex justify-content-center">
						<div class="menu-content pb-70 col-lg-8">
							<div class="title text-center">
								<h1 class="mb-10">Gallery</h1>
								<p>Explore our gallery</p>
							</div>
						</div>
					</div>
					<div class="row">
						<?php
							// Fetch image paths from tblgallery
							$sql = "SELECT imgPath FROM tblgallery ORDER BY ID DESC LIMIT 6";
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
										<a href="<?php echo './admin/gallery/'.$imagePath; ?>" class="img-gal">
											<div class="single-imgs relative">
												<div class="overlay overlay-bg"></div>
												<div class="relative">
													<img class="img-fluid gallery-img" src="<?php echo './admin/gallery/'.$imagePath; ?>" alt="img">
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
				<?php 
				if(count($images) >= 6)
				{
				?>
				<!-- View All Button -->
				<div class="row text-center mt-4">
					<div class="col-md-12">
						<a href="gallery.php" class="btn btn-maroon wow fadeInUp" data-wow-duration="1s" data-wow-delay="1s">View All</a>
					</div>
				</div>
				<?php
				}
				?>
			</section>
			<!-- End gallery Area -->

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