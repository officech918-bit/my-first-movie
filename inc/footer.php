<footer id="footer" class="dark">
    <div class="container"> 
      
      <!-- Footer Widgets
				============================================= -->
      <div class="footer-widgets-wrap clearfix">
        <div class="col_two_third">
          <div class="col_one_third">
            <div class="widget clearfix"> <?= lazy_image('images/logo-dark.png', 'My First Movie Logo', 'footer-logo') ?> </div>
          </div>
          <div class="col_two_third col_last">
            <p>My First Movie is a community of filmmakers, writers, and artists who are passionate about creating and sharing their stories with the world.</p>
          </div>
          <div class="divider divider-border divider-center"><i class="icon-email2"></i></div>
          <div class="col_half">
            <div class="widget clearfix" style="margin-bottom: -50px;">
              <div class="row">
                <div class="col-md-6 bottommargin-sm">
                  <?php
                    $total_users_count = 0;
                    $stmt = $database->db->prepare("SELECT COUNT(*) FROM web_users");
                    if ($stmt) {
                        $stmt->execute();
                        $total_users_count = $stmt->fetchColumn();
                    }
                  ?>
                  <div class="counter counter-small"><span data-from="50" data-to="124758" data-refresh-interval="80" data-speed="3000" data-comma="true"></span></div>
                  <!-- The line below is the secure way to implement this counter -->
                  <!-- <div class="counter counter-small"><span data-from="50" data-to="<?= htmlspecialchars($total_users_count) ?>" data-refresh-interval="80" data-speed="3000" data-comma="true"></span></div> -->
                  <h4 class="nobottommargin">Total Ragistration</h4>
                </div>
                <div class="col-md-6 bottommargin-sm">
                  <?php
                    $total_enrollments_count = 0;
                    $stmt = $database->db->prepare("SELECT COUNT(*) FROM enrollments");
                    if ($stmt) {
                        $stmt->execute();
                        $total_enrollments_count = $stmt->fetchColumn();
                    }
                  ?>
                  <div class="counter counter-small"><span data-from="50" data-to="18540" data-refresh-interval="80" data-speed="3000" data-comma="true"></span></div>
                  <!-- The line below is the secure way to implement this counter -->
                  <!-- <div class="counter counter-small"><span data-from="50" data-to="<?= htmlspecialchars($total_enrollments_count) ?>" data-refresh-interval="80" data-speed="3000" data-comma="true"></span></div> -->
                  <h4 class="nobottommargin">Total Entries</h4>
                </div>
              </div>
            </div>
            <div class="widget clearfix">
              <a class="social-icon si-small si-rounded si-facebook" href="#"> <i class="icon-facebook"></i> <i class="icon-facebook"></i> </a>
              <a class="social-icon si-small si-rounded si-twitter" href="#"> <i class="icon-twitter"></i> <i class="icon-twitter"></i> </a>
              <a class="social-icon si-small si-rounded si-youtube" href="#"> <i class="icon-youtube"></i> <i class="icon-youtube"></i> </a>
            </div>
          </div>
          <!-- <div class="col_half col_last">
            <div class="widget subscribe-widget clearfix">
              <h3><strong>Subscribe</strong> to Our Newsletter<br>
                <span style="font-size:15px;">Content cominsoon here demo</span></h3>
              <div id="widget-subscribe-form-result" data-notify-type="success" data-notify-msg=""></div>
              <form id="widget-subscribe-form" action="inc/subscribe.php" role="form" method="post" class="nobottommargin">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                <div class="input-group divcenter"> <span class="input-group-addon"><i class="icon-email2"></i></span>
                  <input type="email" id="widget-subscribe-form-email" name="widget-subscribe-form-email" class="form-control required email" placeholder="Enter your Email">
                  <span class="input-group-btn">
                  <button class="btn btn-success" type="submit">Subscribe Now!</button>
                  </span> </div>
              </form>
              <script type="text/javascript" nonce="<?= $nonce ?>">
								// This inline script is currently blocked by CSP.
								// To enable this form, this script must be moved to an external file.
								jQuery("#widget-subscribe-form").validate({
									submitHandler: function(form) {
										jQuery(form).find('.input-group-addon').find('.icon-email2').removeClass('icon-email2').addClass('icon-line-loader icon-spin');
										jQuery(form).ajaxSubmit({
											target: '#widget-subscribe-form-result',
											success: function() {
												jQuery(form).find('.input-group-addon').find('.icon-line-loader').removeClass('icon-line-loader icon-spin').addClass('icon-email2');
												jQuery('#widget-subscribe-form').find('.form-control').val('');
												jQuery('#widget-subscribe-form-result').attr('data-notify-msg', jQuery('#widget-subscribe-form-result').html()).html('');
												SEMICOLON.widget.notifications(jQuery('#widget-subscribe-form-result'));
											}
										});
									}
								});
							</script> 
            </div>
          </div> -->
        </div>
        <div class="col_one_third col_last">
          <div class="widget clearfix instagram-widget">
            <h4>Follow Us On Instagram</h4>
            <div class="instagram-wrapper">
              <iframe src="https://www.instagram.com/yourfilmmaker/embed" width="100%" height="300" frameborder="0" scrolling="yes" allowtransparency="true" sandbox="allow-scripts allow-same-origin"></iframe>
            </div>
            <div class="instagram-cta">
              <a href="https://www.instagram.com/yourfilmmaker/" target="_blank" class="btn btn-instagram btn-block">
                <i class="icon-instagram"></i> Visit Our Instagram
              </a>
            </div>
          </div>
        </div>
      </div>
      <!-- .footer-widgets-wrap end --> 
      
    </div>
    
    <!-- Copyrights
			============================================= -->
    <div id="copyrights">
      <div class="container clearfix">
        <div class="col_half"> Copyrights &copy; <?= date('Y') ?> My First Movie | All Rights Reserved.<br>
          <div class="copyright-links"><a href="<?php echo $sitename; ?>/index.php">Home</a> / <a href="<?php echo $sitename; ?>/about-mfm.php">About MFM</a> / <a href="<?php echo $sitename; ?>/how-it-works.php">How it works</a> / <a href="<?php echo $sitename; ?>/call-for-entry.php">Call for Entry</a> / <a href="<?php echo $sitename; ?>/behind-the-scenes.php">Behind the scenes</a> / <a href="<?php echo $sitename; ?>/selecteds.php">Selecteds</a></div>
        </div>
        <div class="col_half col_last tright"> <span style="margin-top:15px; display:block;">Developed by <a href="https://evokemediaservices.com" title="evoke" target="_blank" rel="noopener noreferrer">Evoke Media Services</a></span> </div>
      </div>
    </div>
    <!-- #copyrights end --> 
    
  </footer>
  <script type="text/javascript" src="js/functions.js" nonce="<?= $nonce ?>"></script>
  <script type="text/javascript" src="js/jquery.pulsate.js" nonce="<?= $nonce ?>"></script>
  <script type="text/javascript" src="js/custom-inline.js" nonce="<?= $nonce ?>"></script>