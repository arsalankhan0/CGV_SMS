			<!-- start footer Area -->		
			<footer class="footer-area section-gap">
				<div class="container">
					<div class="row">
						<div class="col-lg-2 col-md-6 col-sm-6">
							<div class="single-footer-widget">
								<h4>Quick links</h4>
								<ul>
									<li><a href="./index.php">Home</a></li>
									<li><a href="./about.php">About</a></li>
									<li><a href="./gallery.php">Gallery</a></li>
									<li><a href="./contact.php">Contact</a></li>
								</ul>								
							</div>
						</div>
						<div class="col-lg-2 col-md-6 col-sm-6">
							<div class="single-footer-widget">
								<h4>Login</h4>
								<ul>
									<li><a href="./user/login.php">Student</a></li>
								</ul>								
							</div>
						</div>
						<div class="col-lg-4 col-md-6 col-sm-6">
							<div class="single-footer-widget">
								<h4>Address</h4>
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
											<div class="address text-light">
											<p><?php  echo htmlentities($row->PageDescription);?>
											</p>
											</div>
											<div class="phone text-light">
											<p><?php  echo htmlentities($row->MobileNumber);?></p>
											</div>
										<?php $cnt=$cnt+1;
									}
								} ?>							
							</div>
						</div>																								
					</div>
					<div class="footer-bottom row align-items-center justify-content-between">
						<p class="footer-text m-0 col-lg-6 col-md-12 text-light">
							Tibetan Public school Copyright &copy;<script>document.write(new Date().getFullYear());</script> All rights reserved </a>
						</p>
						<div class="col-lg-6 col-sm-12 footer-social">
							<a href="https://www.facebook.com/profile.php?id=100063700347020&mibextid=LQQJ4d" target="_blank"><i class="fa fa-facebook"></i></a>
							<a href="#"><i class="fa fa-twitter"></i></a>
							<a href="https://www.instagram.com/tibetan_school_official?igsh=YnhsYnRzNDJ5bzc0" target="_blank"><i class="fa fa-instagram"></i></a>
						</div>
					</div>						
				</div>
			</footer>	
			<!-- End footer Area -->