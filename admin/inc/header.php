<div class="page-header navbar navbar-fixed-top">
	<!-- BEGIN HEADER INNER -->
	<div class="page-header-inner">
		<!-- BEGIN LOGO -->
		<div class="page-logo">
			<a href="index.php">
			<h2 style="color:#FFF; margin-top:5px !important;">MyFirstMovie</h2>
			</a>
			<div class="menu-toggler sidebar-toggler hide">
				<!-- DOC: Remove the above "hide" to enable the sidebar toggler button on header -->
			</div>
		</div>
		<!-- END LOGO -->
		<!-- BEGIN RESPONSIVE MENU TOGGLER -->
		<a href="javascript:;" class="menu-toggler responsive-toggler" data-toggle="collapse" data-target=".navbar-collapse">
		</a>
		<!-- END RESPONSIVE MENU TOGGLER -->
		<!-- BEGIN TOP NAVIGATION MENU -->
		<div class="top-menu">
			<ul class="nav navbar-nav pull-right">
				<!-- BEGIN USER LOGIN DROPDOWN -->
				<!-- DOC: Apply "dropdown-dark" class after below "dropdown-extended" to change the dropdown styte -->
                <?php 
				global $database;
				$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : '';
				if($user_id) {
					$data = $database->get_record_by_ID('users', 'user_id', $user_id);
					
					if($data) {
						$first_name = isset($data['first_name']) ? $data['first_name'] : '';
						$last_name = isset($data['last_name']) ? $data['last_name'] : '';
						$email = isset($data['email']) ? $data['email'] : '';
						$designation = isset($data['designation']) ? $data['designation'] : '';
						$contact = isset($data['contact']) ? $data['contact'] : '';
						$avatar = isset($data['avatar']) ? $data['avatar'] : '';
						$avatar_large = isset($data['avatar_original']) ? $data['avatar_original'] : '';
					} else {
						$first_name = $last_name = $email = $designation = $contact = $avatar = $avatar_large = '';
					}
				} else {
					$first_name = $last_name = $email = $designation = $contact = $avatar = $avatar_large = '';
				}
	
				?>
				<li class="dropdown dropdown-user">
					<a href="#" class="dropdown-toggle" data-toggle="dropdown" data-hover="dropdown" data-close-others="true">
					<img alt="" class="img-circle" src="../<?php echo $avatar_large; ?>"/>
					<span class="username username-hide-on-mobile">
					<?php echo $first_name; echo " ".$last_name; ?> </span>
					<i class="fa fa-angle-down"></i>
					</a>
					<ul class="dropdown-menu dropdown-menu-default">
						<li>
							<a href="profile-modern.php">
							<i class="icon-user"></i> My Profile </a>
						</li>
						<li class="divider">
						</li>
						<li>
							<a href="logout.php">
							<i class="icon-key"></i> Log Out </a>
						</li>
					</ul>
				</li>
				<!-- END USER LOGIN DROPDOWN -->
			</ul>
		</div>
		<!-- END TOP NAVIGATION MENU -->
	</div>
	<!-- END HEADER INNER -->
</div>