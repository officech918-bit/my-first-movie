<div class="profile-userpic">
<?php 
  // Load S3Uploader for environment detection
  if (file_exists(__DIR__ . '/../classes/S3Uploader.php')) {
      require_once __DIR__ . '/../classes/S3Uploader.php';
      $s3Uploader = new S3Uploader();
  }

  /**
   * Get image URL based on environment
   */
  function getImageUrl(string $imagePath, string $uploadPath = ''): string {
      if (empty($imagePath)) {
          return 'https://via.placeholder.com/400x300?text=No+Image+Available';
      }
      
      global $s3Uploader;
      if ($s3Uploader && $s3Uploader->isS3Enabled()) {
          // In production, assume S3 URLs are stored
          return $imagePath;
      } else {
          // In local development, convert relative paths to full URLs
          if (strpos($imagePath, 'http') === 0) {
              return $imagePath; // Already a full URL
          }
          global $correct_base_path;
          
          // Handle different upload paths
          if (!empty($uploadPath) && strpos($imagePath, $uploadPath) !== 0) {
              return $correct_base_path . "/" . $uploadPath . "/" . $imagePath;
          }
          
          return $correct_base_path . "/" . $imagePath; // Convert to relative path
      }
  }

  // Get fresh avatar data from session (updated after account details update)
  $avatar = $_SESSION['avatar'] ?? $user->get('avatar');
  $avatar_path = $_SESSION['avatar_path'] ?? $user->get('avatar_path');
  
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
      // Use the getImageUrl function for environment-aware URLs
      $profile_pic = getImageUrl($avatar, $avatar_path);
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