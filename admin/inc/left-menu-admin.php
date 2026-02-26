<ul class="page-sidebar-menu" data-keep-expanded="false" data-auto-scroll="true" data-slide-speed="200">
  <!-- DOC: To remove the sidebar toggler from the sidebar you just need to completely remove the below "sidebar-toggler-wrapper" LI element -->
  <li class="sidebar-toggler-wrapper"> 
    <!-- BEGIN SIDEBAR TOGGLER BUTTON -->
    <div class="sidebar-toggler"> </div>
    <!-- END SIDEBAR TOGGLER BUTTON --> 
  </li>
  <li class="tooltips" data-container="body" data-placement="right" data-html="true" > <a href="dashboard.php"> <i class="icon-home"></i> <span class="title"> Dashboard </span> </a> </li>

  <li class="<?php if(isset($menu) && $menu == 'testimonials') { echo 'active open'; } ?>"> <a href="all-testimonials.php"> <i class="icon-docs"></i> <span class="title">Testimonials</span></a></li>
  <li> <a href="javascript:;"> <i class="icon-docs"></i> <span class="title">Categories</span> <span class="arrow "></span> </a>
    <ul class="sub-menu">
      <li> <a href="categories.php"> Add/Edit Categories</a> </li>
      <li> <a href="all-categories.php"> Manage All</a> </li>
    </ul>
  </li>
  <li> <a href="javascript:;"> <i class="icon-docs"></i> <span class="title">Enrollments</span> <span class="arrow "></span> </a>
    <ul class="sub-menu">
      <li> <a href="enrollments.php"> Add/Edit Enrollments</a> </li>
      <li> <a href="all-enrollments.php"> Manage All</a> </li>
    </ul>
  </li>
  <li> <a href="javascript:;"> <i class="icon-crown"></i> <span class="title">Winners</span> <span class="arrow "></span> </a>
    <ul class="sub-menu">
      <!-- <li> <a href="winners.php"> Add/Edit Winners</a> </li> -->
      <li> <a href="all-winners.php"> Manage All Winners</a> </li>
    </ul>
  </li>


</li>
  <li> <a href="javascript:;"> <i class="icon-rocket"></i> <span class="title">MFM Seasons</span> <span class="arrow "></span> </a>
    <ul class="sub-menu">
      <li> <a href="seasons.php"> Add/Edit Seasons</a> </li>
      <li> <a href="all-seasons.php"> Manage All seasons</a> </li>
    </ul>
  </li>
  <li> <a href="javascript:;"> <i class="icon-rocket"></i> <span class="title">Behind the Scenes</span> <span class="arrow "></span> </a>
    <ul class="sub-menu">
      <li> <a href="bts.php"> Add/Edit BtS</a> </li>
      <li> <a href="all-bts.php"> Manage All BtS</a> </li>
    </ul>
  </li>
  <li data-container="body" data-placement="right" data-html="true" > <a href="sorting.php"> <i class="icon-puzzle"></i> <span class="title"> Sorting </span> </a> </li>
  <li> <a href="javascript:;"> <i class="icon-user"></i> <span class="title">Web Users</span> <span class="arrow "></span> </a>
    <ul class="sub-menu">
      <li> <a href="web-users"> Manage All</a> </li>
      <!-- <li> <a href="edit-web-users.php"> Edit Web User</a> </li> -->
    </ul>
  </li> 
  <li> <a href="javascript:;"> <i class="icon-users"></i> <span class="title">Panelists</span> <span class="arrow "></span> </a>
    <ul class="sub-menu">
      <li> <a href="all-panelists.php"> Manage All</a> </li>
    </ul>
  </li>
  <li> <a href="javascript:;"> <i class="icon-docs"></i> <span class="title">News</span> <span class="arrow "></span> </a>
    <ul class="sub-menu">
      <li> <a href="all-news.php"> Manage All</a> </li>
    </ul>
  </li>
  <li> <a href="javascript:;"> <i class="icon-users"></i> <span class="title">Core Team</span> <span class="arrow "></span> </a>
    <ul class="sub-menu">
      <li> <a href="all-core-team.php"> Manage All</a> </li>
    </ul>
  </li>
  <!-- <li> <a href="javascript:;"> <i class="icon-cloud-download"></i> <span class="title">Download Files</span> <span class="arrow "></span> </a>
    <ul class="sub-menu">
      <li> <a href="all-download-files.php"> Manage All</a> </li>
    </ul>
  </li> -->
  <!-- <li data-container="body" data-placement="right" data-html="true" > <a href="sorting.php"> <i class="icon-puzzle"></i> <span class="title"> Sorting </span> </a> </li> -->
  <li> <a href="javascript:;"> <i class="icon-user"></i> <span class="title">Admin</span> <span class="arrow "></span> </a>
    <ul class="sub-menu">
      <li> <a href="users.php"> users</a> </li>
    </ul>
  </li>
  <!-- <li> <a href="all-orders.php"> <i class="icon-user"></i> <span class="title">Orders</span> <span class="arrow "></span> </a>
    <ul class="sub-menu">
      <li> <a href="orders.php"> orders</a> </li>
    </ul>
  </li> -->
</ul>