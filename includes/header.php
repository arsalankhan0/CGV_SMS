<header id="header" id="home">
				<div class="header-top">
					<div class="container">
						<div class="row">
							<div class="col-lg-6 col-sm-6 col-8 header-top-left no-padding">
								<ul>
									<li><a href="#"><i class="fa fa-facebook"></i></a></li>
									<li><a href="#"><i class="fa fa-twitter"></i></a></li>
									<li><a href="#"><i class="fa fa-dribbble"></i></a></li>
									<li><a href="#"><i class="fa fa-behance"></i></a></li>
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
									{               ?>
										<a href="tel:+953 012 3654 896"><span class="lnr lnr-phone-handset"></span> <span class="text"><?php  echo '+91 ' . htmlentities($row->MobileNumber);?></span></a>
										<a href="mailto:support@colorlib.com"><span class="lnr lnr-envelope"></span> <span class="text"><?php  echo htmlentities($row->Email);?></span></a>			
									<?php
									}
								}?>
							</div>
						</div>			  					
					</div>
				</div>
				<div class="container main-menu">
					<div class="row align-items-center justify-content-between d-flex">
					<div id="logo">
						<!-- <a href="index.html"><img src="img/logo.png" alt="" title="" /></a> -->
						<h1 class="text-light">SMS</h1>
					</div>
					<nav id="nav-menu-container">
						<ul class="nav-menu">
						<li><a href="index.php">Home</a></li>
						<li><a href="about.php">About</a></li>
						<li><a href="courses.php">Courses</a></li>
						<li><a href="gallery.php">Gallery</a></li>					          					          		          
						<li><a href="contact.php">Contact</a></li>
						<li><a href="../admin/login.php">Admin</a></li>
						<li><a href="../user/login.php">Student</a></li>
						</ul>
					</nav><!-- #nav-menu-container -->		    		
					</div>
				</div>
			</header><!-- #header -->