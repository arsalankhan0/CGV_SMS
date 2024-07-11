<?php
session_start();
// error_reporting(0);
include ('includes/dbconnection.php');
?>
<!DOCTYPE html>
<html lang="zxx" class="no-js">

<head>
	<!-- Mobile Specific Meta -->
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<!-- Favicon-->
	<link rel="shortcut icon" href="./Main/img/favicon.png">
	<!-- Author Meta -->
	<meta name="author" content="Tibetan public school">
	<!-- Meta Description -->
	<meta name="description" content="Tibetan Public school Srinagar">
	<!-- Meta Keyword -->
	<meta name="keywords" content="Tibetan Public School, Home">
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
	<link rel="stylesheet" href="./Main/css/jquery-ui.css">
	<link rel="stylesheet" href="./Main/css/main.css">
	<link rel="stylesheet" href="./Main/css/custom.css">
	<link rel="stylesheet" href="./Main/css/owl.carousel.css">
</head>

<body>
	<?php
	include_once ('includes/header.php');

	$isDisplayed = 0;
	$imagePath = "";

	// Check if the ad data already exists in the database
	$sqlCheck = "SELECT IsDisplayed, ImagePath FROM tblads";
	$queryCheck = $dbh->prepare($sqlCheck);
	$queryCheck->execute();
	$existingData = $queryCheck->fetch(PDO::FETCH_ASSOC);
	if ($existingData) {
		$imagePath = $existingData['ImagePath'];
		$isDisplayed = $existingData['IsDisplayed'];
	}
	?>

	<!-- Automatically trigger button to show modal when the page loads -->
	<button type="button" class="d-none" id="modalTrigger" data-toggle="modal" data-target="#adsModal" <?php echo (isset($isDisplayed) && $isDisplayed == 0) ? "disabled" : ""; ?>></button>
	<!-- Ads Modal -->
	<div class="modal fade" id="adsModal" tabindex="-1" aria-labelledby="adsModalLabel" aria-hidden="true">
		<div class="modal-dialog modal-dialog-centered modal-lg">
			<div class="modal-content bg-maroon">
				<div class="modal-header">
					<h5 class="modal-title text-light text-uppercase" id="adsModalLabel">Advertisement</h5>
					<button type="button" class="close text-light" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<img src="<?php echo './Main/img/Advertisement/' . $imagePath . ''; ?>" class="img-fluid"
					alt="Modal Image">
			</div>
		</div>
	</div>

	<!-- start banner Area -->
	<div id="carouselExampleIndicators" class="banner-area relative carousel slide" data-ride="carousel">
		<div class="overlay overlay-bg"></div>
		<!-- Indicators -->
		<ol class="carousel-indicators">
			<?php
			$sql = "SELECT ImagePath FROM tblbanners ORDER BY ID DESC";
			$stmt = $dbh->prepare($sql);
			$stmt->execute();
			$images = $stmt->fetchAll(PDO::FETCH_ASSOC);

			$numImages = count($images);
			for ($i = 0; $i < $numImages; $i++) {
				echo '<li data-target="#carouselExampleIndicators" data-slide-to="' . $i . '"';
				if ($i === 0) {
					echo ' class="active"';
				}
				echo '></li>';
			}
			?>
		</ol>
		<!-- Wrapper for slides -->
		<div class="carousel-inner">
			<?php
			if (count($images) > 0) {
				$firstImage = true;
				foreach ($images as $imagePath) {
					?>
					<div class="carousel-item <?php echo $firstImage ? 'active' : ''; ?>">
						<img src="<?php echo './admin/images/MainBanners/' . $imagePath['ImagePath']; ?>" class="d-block w-100"
							height="700px" style="object-fit: cover;" alt="img">
					</div>
					<?php
					$firstImage = false;
				}
			} else {
				echo '<div class="carousel-item active">
							<img src="./Main/img/School/1000146556_x4.png" class="d-block w-100" height="700px" style="object-fit: cover;" alt="img">
						</div>';
			}
			?>
		</div>

		<?php
		if (count($images) > 1) {
			?>
			<!-- Left and right controls -->
			<a class="carousel-control-prev" href="#carouselExampleIndicators" role="button" data-slide="prev">
				<span class="carousel-control-prev-icon" aria-hidden="true"></span>
				<span class="sr-only">Previous</span>
			</a>
			<a class="carousel-control-next" href="#carouselExampleIndicators" role="button" data-slide="next">
				<span class="carousel-control-next-icon" aria-hidden="true"></span>
				<span class="sr-only">Next</span>
			</a>
			<?php
		}
		?>
		<!-- Common caption for all slides -->
		<div class="carousel-caption banner-content d-md-block">
			<div class="row fullscreen d-flex align-items-center pt-5 justify-content-start">
				<div class="banner-content col-lg-9 col-md-12">
					<h1 class="text-uppercase welcome">Welcome to Tibetan Public School</h1>
					<p class="pt-10 pb-10 text-light">Registered students can login here</p>
					<a href="./user/login.php" class="primary-btn text-uppercase">Student Login</a>
				</div>
			</div>
		</div>
	</div>
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
							<p class="text-left">Whether it's updates, events, or important announcements, we've got you
								covered.</p>
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

								$sql = "SELECT * FROM tblpublicnotice ORDER BY CreationDate DESC";
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
													<div class="day">
														<?php echo $day; ?>
													</div>
													<div class="month">
														<?php echo $month; ?>
													</div>
													<div class="year">
														<?php echo $year; ?>
													</div>
												</div>
												<a href="view-public-notice.php?viewid=<?php echo htmlentities($row->ID); ?>"
													target="_blank">
													<div class="notice-content text-light mx-4">
														<span class="notice-title">
															<?php echo htmlentities($row->NoticeTitle); ?>
														</span>
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
								} else {
									echo '<p class="text-center text-light">No notices available.</p>';
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

	<!-- Latest Updates Section -->
	<section class="latest-updates my-5">
		<div class="container">
			<div class="title text-center mb-4">
				<h2>Latest Updates</h2>
				<p>Check out our recent events and activities</p>
			</div>
			<div id="latestUpdatesCarousel" class="owl-carousel owl-theme">
				<?php
				$sql = "SELECT title, `description`, `image` FROM tbllatestupdates ORDER BY created_at DESC";
				$query = $dbh->prepare($sql);
				$query->execute();
				$results = $query->fetchAll(PDO::FETCH_ASSOC);

				if ($query->rowCount() > 0) {
					foreach ($results as $row) {
						$fullDesc = $row['description'];
						$shortDesc = strlen($fullDesc) > 90 ? substr($fullDesc, 0, 90) . '...' : $fullDesc;
						echo '
							<div class="item">
								<div class="card custom-card">
									<div class="img-container">
										<img class="card-img-top" src="Main/img/' . $row['image'] . '" alt="img">
									</div>
									<div class="card-body">
										<h5 class="card-title text-capitalize">' . $row['title'] . '</h5>
										<p class="card-text text-capitalize short-desc">' . $shortDesc . '</p>
										<p class="card-text text-capitalize full-desc d-none">' . $fullDesc . '</p>';
								if (strlen($fullDesc) > 90) {
									echo '<a href="javascript:void(0);" class="see-more">See more</a>';
								}
						echo '
									</div>
								</div>
							</div>
						';
					}
					echo '</div>';
				} else {
					echo '</div>';
					echo '
							<p class="text-center">No Updates Available.</p>
						';
				}
				?>
			</div>
	</section>



	<!-- Start principal's message Area -->
	<section class="container principal-msg my-5">
		<div class="principal-msg-container">
			<div class="title text-center">
				<h1 class="mb-10">Principal's Message</h1>
			</div>
			<div class="message-box">
				<p>Dear Students, Parents, and Guardians,</p>
				<p>Welcome to Tibetan Public School, where we are dedicated to providing an exceptional educational
					experience that transcends traditional boundaries. At TPS, we believe in fostering an environment
					where curiosity is nurtured, creativity is celebrated, and collaboration thrives. With a relentless
					commitment to excellence, integrity, and respect, we empower every student to embrace their unique
					talents and abilities, equipping them with the skills and confidence to navigate an ever-changing
					world. As principal, I am honored to lead this journey of transformation, working hand in hand with
					our dedicated team of educators to inspire a love for learning that extends far beyond the classroom
					walls. Together, let us embark on an extraordinary adventure of discovery and growth, shaping a
					future where every student's potential knows no limits.</p>
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
	<section id="about" class="about-section mt-5">
		<div class="container">
			<div class="about-section-title text-center">
				<h2>About Us</h2>
				<p>Learn about our mission, vision, and values</p>
			</div>
			<div class="row">
				<div class="col-md-4">
					<a href="./about.php" class="card-link text-dark">
						<div class="about-card">
							<i class="fa fa-bullseye card-icon"></i>
							<h3 class="card-title">Our Mission</h3>
							<p class="card-text">Our mission is to provide an exceptional educational experience that
								nurtures intellectual curiosity, creativity, and personal growth in every student. We
								believe that education is not just about acquiring knowledge but also about fostering a
								love for learning that lasts a lifetime...</p>
						</div>
					</a>
				</div>
				<div class="col-md-4">
					<a href="./about.php" class="card-link text-dark">
						<div class="about-card">
							<i class="fa fa-eye card-icon"></i>
							<h3 class="card-title">Our Vision</h3>
							<p class="card-text">Our vision is to empower students to become compassionate, responsible,
								and lifelong learners who positively impact their communities and the world. We envision
								a future where every individual is equipped with the skills, knowledge, and values to
								thrive in a diverse and interconnected world...</p>
						</div>
					</a>
				</div>
				<div class="col-md-4">
					<a href="./about.php" class="card-link text-dark">
						<div class="about-card">
							<i class="fa fa-users card-icon"></i>
							<h3 class="card-title">About Us</h3>
							<p class="card-text">
								<?php
								$sql = "SELECT * from tblpage where PageType='aboutus'";
								$query = $dbh->prepare($sql);
								$query->execute();
								$results = $query->fetchAll(PDO::FETCH_OBJ);
								if ($query->rowCount() > 0) {
									foreach ($results as $row) {
										// Show only 42 words
										$description = implode(' ', array_slice(explode(' ', $row->PageDescription), 0, 42));
										echo $description . '...';
									}
								}
								?>
							</p>
						</div>
					</a>
				</div>
			</div>
		</div>
	</section>

	<!-- Start gallery Area -->
	<section class="gallery-area my-5">
		<div class="container">
			<div class="row d-flex justify-content-center">
				<div class="menu-content pb-30 col-lg-12">
					<div class="title text-center">
						<h2 class="mb-10">Gallery</h2>
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

				if (count($images) > 0) {
					// Display images
					foreach ($images as $imagePath) {
						?>
						<div class="col-lg-4">
							<a href="<?php echo './admin/gallery/' . $imagePath; ?>" class="img-gal">
								<div class="single-imgs relative">
									<div class="overlay overlay-bg"></div>
									<div class="relative overflow-hidden">
										<img class="img-fluid gallery-img" src="<?php echo './admin/gallery/' . $imagePath; ?>"
											alt="img">
									</div>
								</div>
							</a>
						</div>
						<?php
					}
				} else {
					echo '<div class="col-lg-12">
										<h4 class="text-center">No Images to show!</h4>
									</div>';
				}
				?>
			</div>
		</div>
		<?php
		if (count($images) >= 6) {
			?>
			<!-- View All Button -->
			<div class="text-center mt-4">
				<a href="gallery.php" class="btn btn-maroon wow fadeInUp" data-wow-duration="1s" data-wow-delay="1s">View
					All</a>
			</div>
			<?php
		}
		?>
	</section>
	<!-- End gallery Area -->

	<!-- Footer -->
	<?php include_once ('./includes/footer.php'); ?>


	<script src="./Main/js/vendor/jquery-2.2.4.min.js"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js"
		integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q"
		crossorigin="anonymous"></script>
	<script src="./Main/js/vendor/bootstrap.min.js"></script>
	<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBhOdIF3Y9382fqJYt5I_sswSrEw5eihAA"></script>
	<script src="./Main/js/easing.min.js"></script>
	<script src="./Main/js/hoverIntent.js"></script>
	<script src="./Main/js/superfish.min.js"></script>
	<script src="./Main/js/jquery.ajaxchimp.min.js"></script>
	<script src="./Main/js/jquery.magnific-popup.min.js"></script>
	<script src="./Main/js/jquery.tabs.min.js"></script>
	<script src="./Main/js/jquery.nice-select.min.js"></script>
	<script src="./Main/js/main.js"></script>
	<script src="./Main/js/owl.carousel.min.js"></script>
	<script src="./Main/js/custom.js"></script>
</body>

</html>