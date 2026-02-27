<!-- start popup enquiry form -->   
<div class="enquire-now"><a href="javascript:;" class="slide_open" data-animation="bounceIn" data-delay="700"></a></div> 
<!--<script src="assets/frontend/layout/scripts/jquery-1.10.2.min.js" type="text/javascript"></script>   -->
<script src="<?php echo $path ?>assets/frontend/layout/scripts/jquery.popupoverlay.js"></script> 
<script>
$(document).ready(function () {
$('#slide').popup({
        focusdelay: 400,
        outline: true,
        vertical: 'top'
    });

});
</script>

<script src="<?php echo $path ?>assets/frontend/layout/scripts/skyform/jquery.form.min.js"></script> 
<script src="<?php echo $path ?>assets/frontend/layout/scripts/skyform/jquery.validate.min.js"></script> 
<script type="text/javascript">
			$(function()
			{
				// Validation
				$("#sky-form").validate(
				{					
					// Rules for form validation
					rules:
					{
						name:
						{
							required: true
						},
						email:
						{
							required: true,
							email: true
						},
						contact:
						{
							required: true,
							digits: true,
							minlength: 10,
							maxlength:10
						},										
					},
										
					// Messages for form validation
					messages:
					{
						name:
						{
							required: 'Please enter your name',
						},
						email:
						{
							required: 'Please enter your email address',
							email: 'Please enter a VALID email address'
						},						
						contact:
						{
							required: 'Please enter your contact no.',
						},											
					},

				});
			});			
		</script> 
<script type="text/javascript">
	$(document).ready(function() {
		<?php 
			
			if(isset($_SESSION['message_string']))
			echo "alert('".$_SESSION['message_string']."');";
			
				  
			if(isset($_SESSION['error_str']))
			echo "alert('".$_SESSION['error_str']."');";
			
			unset($_SESSION['message_string']);
			unset($_SESSION['message_number']);
			unset($_SESSION['error_str']);
			unset($_SESSION['is_error']);

		?>
		
			
	});
</script> 
<script src='https://www.google.com/recaptcha/api.js'></script>   
<div id="slide" class="well" style="padding-left:20px;">
  <form action="<?php echo $path ?>inc/enquire-process.php" method="post" id="sky-form" class="sky-form" />
  
  <header>Enquire Now</header>
  <div class="clearfix"></div>
  <fieldset>
    <div class="row">
      <section class="col col-6">
        <label class="input"> <i class="icon-append icon-user"></i>
          <input type="text" name="name" id="name" placeholder="Fullname" />
        </label>
      </section>
      <section class="col col-6">
        <label class="input"> <i class="icon-append icon-envelope-alt"></i>
          <input type="email" name="email" id="email" placeholder="Email" />
        </label>
      </section>
    </div>
    <div class="row">
      <section class="col col-11">
      	<label class="input"> <i class="icon-append icon-phone"></i>
        <input type="text" name="contact" id="contact" placeholder="Contact No."  />
      	</label>    
      </section>         
    </div> 
    <section>
      <label class="textarea">
        <textarea rows="4" name="message" id="message" placeholder="Your Message" ></textarea>
      </label>
    </section>
    <section>
      <div class="g-recaptcha" data-sitekey="6Lf72QgTAAAAACa07eJFoTuyI0ihM5pkuJ7w96Lw" style="clear:both;"></div>
    </section>
    <div class="clearfix"></div>
  </fieldset>
  <footer>
    <button type="submit" name="submit" class="button">Submit</button>
    <button class="slide_close button">Cancel</button>
  </footer>
  <div class="message"> <i class="icon-ok"></i>
    <p>Your message was successfully sent!</p>
  </div>
  </form>
</div>    
<!-- end popup enquiry form -->  