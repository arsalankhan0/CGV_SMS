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
    <title>Student Management System || Contact Us</title>  
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
						<li class="nav-item active"><a class="nav-link" href="contact.php">Contact</a></li>
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
			<h1>Contact<span class="m_1">Lorem Ipsum dolroin gravida nibh vel velit.</span></h1>
		</div>
	</div>
	
	<!-- Contact -->
	<div id="contact" class="section wb">
		<div class="container">
			<div class="section-title text-center p-4">
				<h3 class="text-danger">Need Help? We're Here for You!</h3>
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
							<div class="row mt-4">
								<div class="col-md-4">
									<h2 class="text-primary">Address :</h2>
									<p><?php echo nl2br(htmlentities($row->PageDescription)); ?></p>
								</div>
								<div class="col-md-4">
									<h2 class="text-primary">Phones :</h2>
									<p><?php echo htmlentities($row->MobileNumber); ?></p>
								</div>
								<div class="col-md-4">
									<h2 class="text-primary">E-mail :</h2>
									<p><?php echo htmlentities($row->Email); ?></p>
								</div>
							</div>
				<?php
						}
					}
				?>
			</div><!-- end title -->
		</div><!-- end container -->
	</div><!-- end section -->

	


    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="row">

                <!-- Links -->
				<div class="col-lg-4 col-md-4 col-xs-12">
                    <div class="widget clearfix">
                        <div class="widget-title">
                            <h3>Information Link</h3>
                        </div>
                        <ul class="footer-links">
                            <li><a href="index.php">Home</a></li>
                            <li><a href="about.php">About</a></li>
                            <li><a href="contact.php">Contact</a></li>
                            <li><a href="../admin/login.php">Admin</a></li>
                            <li><a href="../user/login.php">Student</a></li>
                        </ul><!-- end links -->
                    </div><!-- end clearfix -->
                </div><!-- end col -->
				
                <!-- Address -->
                <div class="col-lg-4 col-md-4 col-xs-12">
                    <div class="widget clearfix">
                        <div class="widget-title">
                            <h3>Address</h3>
                        </div>

                        <?php
                        $sql="SELECT * from tblpage where PageType='contactus'";
                        $query = $dbh -> prepare($sql);
                        $query->execute();
                        $results=$query->fetchAll(PDO::FETCH_OBJ);

                        $cnt=1;
                        if($query->rowCount() > 0)
                        {
                        foreach($results as $row)
                        {               ?>
                                <div class="address">
                                <p><?php  echo htmlentities($row->PageDescription);?>
                                </p>
                                </div>
                                <div class="phone">
                                <p><?php  echo htmlentities($row->MobileNumber);?></p>
                                </div>
                            <?php $cnt=$cnt+1;}} ?>
                    </div><!-- end clearfix -->
                </div><!-- end col -->
				
            </div><!-- end row -->
        </div><!-- end container -->
    </footer><!-- end footer -->

    <!-- Copyright -->
    <div class="copyrights">
        <div class="container">
            <div class="footer-distributed">
                <div class="footer-center">                   
                    <p class="footer-company-name">All Rights Reserved. &copy; <?php echo date('Y');?> <a href="index.php">SMS</a>. Designed and Maintained By : <a href="https://cogveel.com/">Cogveel Technologies</a></p>
                </div>
            </div>
        </div><!-- end container -->
    </div><!-- end copyrights -->

    <a href="#" id="scroll-to-top" class="dmtop global-radius"><i class="fa fa-angle-up"></i></a>

    <!-- ALL JS FILES -->
    <script src="js/all.js"></script>
	<script src="https://maps.googleapis.com/maps/api/js?libraries=places&key=AIzaSyCKjLTXdq6Db3Xit_pW_GK4EXuPRtnod4o"></script>
	<!-- Mapsed JavaScript -->
	<script src="js/01-custom-places-example.js"></script>
    <!-- ALL PLUGINS -->
    <script src="js/custom.js"></script>

</body>
</html>