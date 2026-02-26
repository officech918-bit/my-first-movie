<ul class="nav nav-tabs">
  <li <?php if($_SERVER['REQUEST_URI']=="/members/current-enrollments.php"){echo 'class="active"';} ?>> <a href="current-enrollments.php">Current Enrollments</a> </li>
  <li <?php if($_SERVER['REQUEST_URI']=="/members/my-enrollments.php"){echo 'class="active"';} ?>> <a href="my-enrollments.php">My Enrollments</a> </li>
  <li <?php if($_SERVER['REQUEST_URI']=="/members/payment-history.php"){echo 'class="active"';} ?>> <a href="payment-history.php">Payment History</a> </li>
</ul>