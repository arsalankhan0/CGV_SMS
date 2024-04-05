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
    <meta name="author" content="">
    <!-- Meta Description -->
    <meta name="description" content="">
    <!-- Meta Keyword -->
    <meta name="keywords" content="Tibetan Public School">
    <!-- meta character set -->
    <meta charset="UTF-8">
    <!-- Site Title -->
    <title>TPS - About Us</title>

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

        <section id="about" class="about-section section-gap mt-80">
            <div class="container">
                <div class="section-title text-center">
                    <h2>About Us</h2>
                    <p>Learn more about our mission, vision, and values</p>
                </div>
                <div class="row">
                    <div class="col-lg-6 col-md-6 col-sm-12">
                        <div class="about-card mission-card">
                            <div class="card-body">
                                <i class="fa fa-bullseye card-icon"></i>
                                <h3 class="card-title">Our Mission</h3>
                                <p class="card-text">Our mission is to provide an exceptional educational experience that nurtures intellectual curiosity, creativity, and personal growth in every student. We believe that education is not just about acquiring knowledge but also about fostering a love for learning that lasts a lifetime. Through innovative teaching methods, personalized attention, and a supportive learning environment, we empower our students to explore their interests, discover their strengths, and reach their full potential. By instilling values of integrity, empathy, and resilience, we prepare them to become responsible global citizens who contribute positively to society</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6 col-md-6 col-sm-12">
                        <div class="about-card vision-card">
                            <div class="card-body">
                                <i class="fa fa-eye card-icon"></i>
                                <h3 class="card-title">Our Vision</h3>
                                <p class="card-text">Our vision is to empower students to become compassionate, responsible, and lifelong learners who positively impact their communities and the world. We envision a future where every individual is equipped with the skills, knowledge, and values to thrive in a diverse and interconnected world. By fostering a culture of collaboration, innovation, and social responsibility, we inspire our students to become agents of positive change and leaders in their fields. Through experiential learning opportunities and global perspectives, we prepare them to navigate complex challenges with confidence and resilience, making meaningful contributions to society and shaping a better future for all.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
			<div class="container-fluid p-0 mt-4">
				<img src="./Main/img/1000146556_x4.png" alt="About Us Image" class="main-about-img mt-50">
				<h3 class="text-center mt-50">Why TPS?</h3>
            	<div class="mt-5 container">
                    <p class="section-text text-dark" style="font-size: 1rem">
							<?php
								$sql="SELECT * from tblpage where PageType='aboutus'";
								$query = $dbh -> prepare($sql);
								$query->execute();
								$results=$query->fetchAll(PDO::FETCH_OBJ);
								if($query->rowCount() > 0)
								{
									foreach($results as $row)
									{ ?>						
									<?php  echo ($row->PageDescription);?>
									<?php
									}
								} 
								?>
					</p>
                </div>
        	</div>
        </section>

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
