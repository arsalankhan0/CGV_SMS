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