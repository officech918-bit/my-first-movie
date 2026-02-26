<div class="profile-userpic">
<?php 
  $avatar = $user->get('avatar');
  $avatar_path = $user->get('avatar_path');
  
  // Get the correct base path from current request
  $script = $_SERVER['SCRIPT_NAME'] ?? '';
  $basePath = '';
  if ($script) {
      $parts = explode('/', trim($script, '/'));
      if (!empty($parts)) {
          $basePath = '/' . $parts[0]; // This will give us /myfirstmovie3
      }
  }
  $correct_base_path = $basePath;
  
  if($avatar != '') {
      // Check if it's an S3 URL or local path
      if (strpos($avatar, 'http') === 0) {
          // S3 URL or full URL
          $profile_pic = $avatar;
      } else {
          // Local path - construct proper URL using correct base path
          $profile_pic = $correct_base_path . "/" . $avatar_path . "/" . $avatar;
      }
      echo '<img alt="" class="img-responsive" src="'.$profile_pic.'" />';
  } else {
      // Use default profile picture with correct base path
      $default_pic = $correct_base_path . "/members/assets/frontend/layout/img/profile_user.jpg";
      echo '<img alt="" class="img-responsive" src="'.$default_pic.'" />';
  }
?>
</div>
<div class="profile-usertitle">
  <div class="profile-usertitle-name"> <?php echo $user->get('first_name').' '.$user->get('last_name'); ?> </div>
  <div class="profile-usertitle-job"> <?php echo $user->get('city').' - '.$user->get('state'); ?> </div>
</div>
<div class="profile-usermenu">
  <ul class="nav">
    <li> <a href="dashboard.php"> <i class="fa fa-home"></i> Dashboard </a> </li>
    <li class="active"> <a href="change-password.php"> <i class="fa fa-lock"></i> Change Password </a> </li>
    <li> <a href="account-details.php"> <i class="fa fa-cog"></i> Account Details </a> </li>
    <li> <a href="contact-support.php"> <i class="fa fa-life-ring"></i> Contact Support </a> </li>
  </ul>
</div>