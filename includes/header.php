<header id="header" id="home">
	<div class="header-top">
		<div class="container">
			<div class="row">
				<div class="col-lg-6 col-sm-6 col-8 header-top-left no-padding">
					<ul>
						<li><a href="https://www.facebook.com/profile.php?id=100063700347020&mibextid=LQQJ4d" target="_blank"><i class="fa fa-facebook"></i></a></li>
						<li><a href="#"><i class="fa fa-twitter"></i></a></li>
						<li><a href="https://www.instagram.com/tibetan_school_official?igsh=YnhsYnRzNDJ5bzc0" target="_blank"><i class="fa fa-instagram"></i></a></li>
					</ul>			
				</div>
				<div class="col-lg-6 col-sm-6 col-4 header-top-right no-padding">
					<?php
						$sql="SELECT * from tblpage where PageType='contactus'";
						$query = $dbh -> prepare($sql);
						$query->execute();
						$results=$query->fetchAll(PDO::FETCH_OBJ);

						if($query->rowCount() > 0)
						{
							foreach($results as $row)
							{             
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
								<a href="tel:<?php echo $phoneNumber; ?>">
									<span class="lnr lnr-phone-handset"></span>
									<span class="text"><?php echo $phoneNumber; ?></span>
								</a>								
								<a href="<?php  echo 'mailto:' . htmlentities($row->Email);?>"><span class="lnr lnr-envelope"></span> <span class="text"><?php  echo htmlentities($row->Email);?></span></a>			
							<?php
							}
						}
					?>
				</div>
			</div>			  					
		</div>
	</div>
	<div class="container-fluid px-5 main-menu">
		<div class="row align-items-center justify-content-between d-flex">
			<div id="logo">
				<a href="index.php"><img src="Main/img/logo.png" alt="logo" /></a>
			</div>
			<nav id="nav-menu-container">
				<ul class="nav-menu">
					<li><a href="./index.php">Home</a></li>
					<li><a href="./about.php">About</a></li>
					<li><a href="./gallery.php">Gallery</a></li>	
					<!-- Items to be hidden in desktop view -->
                    <li class="desktop-hide"><a href="./resources.php">Resources</a></li>
                    <li class="desktop-hide"><a href="./contact.php">Contact</a></li>
                    <li class="desktop-hide"><a href="./admin/login.php">Admin</a></li>
                    <li class="desktop-hide"><a href="./user/login.php">Student</a></li>
                    <!-- Dropdown for desktop view -->
                    <li class="dropdown desktop-show">
                        <a href="#" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Services</a>
                        <ul class="dropdown-menu">
                            <li><a href="./resources.php">Resources</a></li>
                            <li><a href="./contact.php">Contact</a></li>
                            <li><a href="./admin/login.php">Admin</a></li>
                            <li><a href="./user/login.php">Student</a></li>
                        </ul>
                    </li>
				</ul>
			</nav><!-- #nav-menu-container -->		    		
		</div>
	</div>
</header><!-- #header -->