			<!-- start footer Area -->		
			<footer class="footer-area section-gap">
				<div class="container">
					<div class="row">
						<div class="col-lg-2 col-md-6 col-sm-6">
							<div class="single-footer-widget">
								<h4>Quick links</h4>
								<ul>
									<li><a href="../education-master/index.php">Home</a></li>
									<li><a href="../education-master/about.php">About</a></li>
									<li><a href="../education-master/courses.php">Courses</a></li>
									<li><a href="../education-master/gallery.php">Gallery</a></li>
									<li><a href="../education-master/contact.php">Contact</a></li>
								</ul>								
							</div>
						</div>
						<div class="col-lg-2 col-md-6 col-sm-6">
							<div class="single-footer-widget">
								<h4>Login</h4>
								<ul>
									<li><a href="../admin/login.php">Admin</a></li>
									<li><a href="../user/login.php">Student</a></li>
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
											<div class="address">
											<p><?php  echo htmlentities($row->PageDescription);?>
											</p>
											</div>
											<div class="phone">
											<p><?php  echo htmlentities($row->MobileNumber);?></p>
											</div>
										<?php $cnt=$cnt+1;
									}
								} ?>							
							</div>
						</div>																								
					</div>
					<div class="footer-bottom row align-items-center justify-content-between">
						<p class="footer-text m-0 col-lg-6 col-md-12"><!-- Link back to Colorlib can't be removed. Template is licensed under CC BY 3.0. -->
							SMS Copyright &copy;<script>document.write(new Date().getFullYear());</script> All rights reserved </a>
							<!-- | Made with <i class="fa fa-heart-o" aria-hidden="true"></i> by <a href="https://colorlib.com" target="_blank">Colorlib</a> &amp; distributed by <a href="https://themewagon.com" target="_blank">ThemeWagon -->
							<!-- Link back to Colorlib can't be removed. Template is licensed under CC BY 3.0. --></p>
						<div class="col-lg-6 col-sm-12 footer-social">
							<a href="#"><i class="fa fa-facebook"></i></a>
							<a href="#"><i class="fa fa-twitter"></i></a>
							<a href="#"><i class="fa fa-dribbble"></i></a>
							<a href="#"><i class="fa fa-behance"></i></a>
						</div>
					</div>						
				</div>
			</footer>	
			<!-- End footer Area -->