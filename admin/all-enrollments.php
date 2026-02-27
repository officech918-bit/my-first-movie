    <?php
// This file is now loaded through the router, which handles all bootstrapping.
// We add these checks here as a fallback for direct access, ensuring the file is self-sufficient.

// Ensure the session is started.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Ensure the Composer autoloader and Eloquent are loaded.
if (!class_exists('App\Models\Enrollment')) {
    require_once __DIR__ . '/../vendor/autoload.php';
    require_once __DIR__ . '/../config/database.php';
}

// Get admin path dynamically for CSS/JS loading
$scheme = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
$host = $_SERVER['HTTP_HOST'];
$requestUri = $_SERVER['REQUEST_URI'];

// Extract the actual path from the current request
$uriParts = explode('/', trim($requestUri, '/'));
$adminIndex = array_search('admin', $uriParts);

if ($adminIndex !== false) {
    $admin_path = $scheme . '://' . $host . '/' . implode('/', array_slice($uriParts, 0, $adminIndex + 1)) . '/';
} else {
    $admin_path = $scheme . '://' . $host . '/admin/'; // fallback
}

// We can directly use the classes and variables set up by the middleware_loader.

use App\Models\Enrollment;
use Illuminate\Database\Capsule\Manager as DB;

// Fetch category counts
$categoryCounts = Enrollment::select('title', DB::raw('count(*) as total'))
    ->groupBy('title')
    ->pluck('total', 'title');

// Determine the correct menu to include based on the user's role.
// This session variable is guaranteed to exist by the AuthMiddleware.
if ($_SESSION['user_type'] == 'webmaster') {
    $menu = 'inc/left-menu-webmaster.php';
} else {
    $menu = 'inc/left-menu-admin.php';
}

// Fetch all enrollments from the database using our Eloquent model.
// The `orderBy` clause ensures they are displayed in the correct order.
$enrollments = Enrollment::orderBy('id', 'desc')->get();
// Calculate total earnings for completed enrollments
$totalEarnings = $enrollments->where('status', 'completed')->sum('fee');

// Generate a CSRF token for delete actions.
// The token is stored in the session to be validated on the deletion request.
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];
?>
<!DOCTYPE html>
<html lang="en" class="no-js">
<head>
    <meta charset="utf-8"/>
    <title>All Enrollments | My First Movie</title>
    <base href="<?php echo $admin_path; ?>">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta content="width=device-width, initial-scale=1" name="viewport"/>
    <meta content="Enrollment management page" name="description"/>
    <meta content="" name="author"/>
    <!-- BEGIN GLOBAL MANDATORY STYLES -->
    <link href="http://fonts.googleapis.com/css?family=Open+Sans:400,300,600,700&subset=all" rel="stylesheet" type="text/css"/>
    <link href="assets/global/plugins/font-awesome/css/font-awesome.min.css" rel="stylesheet" type="text/css"/>
    <link href="assets/global/plugins/simple-line-icons/simple-line-icons.min.css" rel="stylesheet" type="text/css"/>
    <link href="assets/global/plugins/bootstrap/css/bootstrap.min.css" rel="stylesheet" type="text/css"/>
    <link href="assets/global/plugins/uniform/css/uniform.default.css" rel="stylesheet" type="text/css"/>
    <!-- END GLOBAL MANDATORY STYLES -->
    <!-- BEGIN PAGE LEVEL STYLES -->
    <link rel="stylesheet" type="text/css" href="assets/global/plugins/datatables/plugins/bootstrap/dataTables.bootstrap.css"/>
    <!-- END PAGE LEVEL STYLES -->
    <!-- BEGIN THEME STYLES -->
    <link href="assets/global/css/components.css" id="style_components" rel="stylesheet" type="text/css"/>
    <link href="assets/global/css/plugins.css" rel="stylesheet" type="text/css"/>
    <link href="assets/admin/layout/css/layout.css" rel="stylesheet" type="text/css"/>
    <link href="assets/admin/layout/css/themes/darkblue.css" rel="stylesheet" type="text/css" id="style_color"/>
    <link href="assets/admin/layout/css/custom.css" rel="stylesheet" type="text/css"/>
    <!-- END THEME STYLES -->
    <link rel="shortcut icon" href="favicon.ico"/>
</head>
<body class="page-header-fixed page-quick-sidebar-over-content page-style-square"> 
<!-- BEGIN HEADER -->
<?php include('inc/header.php'); ?>
<!-- END HEADER -->
<div class="clearfix">
</div>
<!-- BEGIN CONTAINER -->
<div class="page-container">
	<!-- BEGIN SIDEBAR -->
	<div class="page-sidebar-wrapper">
		<div class="page-sidebar navbar-collapse collapse">
			<!-- BEGIN SIDEBAR MENU -->
			<?php include($menu); ?>
			<!-- END SIDEBAR MENU -->
		</div>
	</div>
	<!-- END SIDEBAR -->
    <div class="page-content-wrapper">
        <div class="page-content">
            <div class="page-bar">
                <ul class="page-breadcrumb">
                    <li>
                        <i class="fa fa-home"></i>
                        <a href="dashboard.php">Home</a>
                        <i class="fa fa-angle-right"></i>
                    </li>
                    <li>
                        <a href="#">Enrollments</a>
                        <i class="fa fa-angle-right"></i>
                    </li>
                    <li>
                        <a href="all-enrollments.php">All Enrollments</a>
                    </li>
                </ul>
            </div>
            <ul class="nav nav-tabs" role="tablist" style="margin-bottom:15px;">
                <li role="presentation" class="active"><a href="#enroll-all" aria-controls="enroll-all" role="tab" data-toggle="tab">All</a></li>
                <?php foreach ($categoryCounts as $category => $count): ?>
                    <li role="presentation">
                        <a href="#" aria-controls="enroll-category" role="tab" data-toggle="tab" data-category="<?php echo htmlspecialchars($category); ?>">
                            <?php echo htmlspecialchars($category); ?> (<?php echo $count; ?>)
                        </a>
                    </li>
                <?php endforeach; ?>
                <li role="presentation"><a href="#enroll-today" aria-controls="enroll-today" role="tab" data-toggle="tab">Today</a></li>
                <li role="presentation"><a href="#enroll-month" aria-controls="enroll-month" role="tab" data-toggle="tab">This Month</a></li>
            </ul>
            <div class="row">
                <div class="col-md-12">
                    <div class="well">
                        <h4>Total Earnings (Completed): <strong>Rs.<?php echo number_format($totalEarnings, 2); ?></strong></h4>
                    </div>
                </div>
            </div>
            <h3 class="page-title">All Enrollments</h3>
            <div class="row">
                <div class="col-md-12">
                    <div class="portlet box blue">
                        <div class="portlet-title">
                            <div class="caption">
                                <i class="fa fa-globe"></i>Enrollments
                            </div>
                            <div class="tools">
                                <a href="javascript:;" class="collapse"></a>
                            </div>
                        </div>
                        <div class="portlet-body">
                            <div class="table-toolbar">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="btn-group">
                                            <a href="enrollments/create" class="btn green">
                                                Add New <i class="fa fa-plus"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <table class="table table-striped table-bordered table-hover" id="enrollments-table">
                                <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Title</th>
                                    <th>User ID</th>
                                    <th>Fee</th>
                                    <th>Date</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php if ($enrollments->count() > 0): ?>
                                    <?php foreach ($enrollments as $enrollment): ?>
                                        <?php $dateYmd = htmlspecialchars(date('Y-m-d', strtotime($enrollment->dt))); ?>
                                        <tr class="odd gradeX" id="enrollment-<?php echo $enrollment->id; ?>" data-date="<?php echo $dateYmd; ?>" data-category="<?php echo htmlspecialchars($enrollment->title); ?>">
                                            <td><?php echo htmlspecialchars($enrollment->id); ?></td>
                                            <td><?php echo htmlspecialchars($enrollment->title); ?></td>
                                            <td><?php echo htmlspecialchars($enrollment->uid); ?></td>
                                            <td><?php echo htmlspecialchars($enrollment->fee); ?></td>
                                            <td><?php echo $dateYmd; ?></td>
                                            <td>
                                                <?php
                                                $status = htmlspecialchars($enrollment->status);
                                                $label_class = 'label-default';
                                                if ($status === 'completed') {
                                                    $label_class = 'label-success';
                                                } elseif ($status === 'pending') {
                                                    $label_class = 'label-warning';
                                                } elseif ($status === 'failed') {
                                                    $label_class = 'label-danger';
                                                }
                                                ?>
                                                <span class="label <?= $label_class ?>"><?= ucfirst($status) ?></span>
                                            </td>
                                            <td>
                                                <a href="view-enrollment.php?id=<?php echo $enrollment->id; ?>" class="btn btn-xs btn-info">View</a>
                                            
                                                <a href="enrollments/<?php echo $enrollment->id; ?>/edit" class="btn btn-xs btn-primary">Edit</a>
                                                <form method="POST" action="enrollments/delete" style="display:inline-block;">
                                                    <input type="hidden" name="id" value="<?php echo $enrollment->id; ?>">
                                                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                                                    <button type="submit" class="btn btn-xs btn-danger" onclick="return confirm('Are you sure you want to delete this enrollment?');">Delete</button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="7" class="text-center">No enrollments found.</td>
                                    </tr>
                                <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- BEGIN FOOTER -->
<?php include('inc/footer.php'); ?>
<!-- END FOOTER -->
<!-- BEGIN JAVASCRIPTS -->
<script src="assets/global/plugins/jquery.min.js" type="text/javascript"></script>
<script src="assets/global/plugins/bootstrap/js/bootstrap.min.js" type="text/javascript"></script>
<script src="assets/global/plugins/jquery-slimscroll/jquery.slimscroll.min.js" type="text/javascript"></script>
<!-- BEGIN PAGE LEVEL PLUGINS -->
<script type="text/javascript" src="assets/global/plugins/datatables/media/js/jquery.dataTables.min.js"></script>
<script type="text/javascript" src="assets/global/plugins/datatables/plugins/bootstrap/dataTables.bootstrap.js"></script>
<!-- END PAGE LEVEL PLUGINS -->
<script src="assets/global/scripts/metronic.js" type="text/javascript"></script>
<script src="assets/admin/layout/scripts/layout.js" type="text/javascript"></script>
<script>
    jQuery(document).ready(function() {
        Metronic.init(); // init metronic core components
        Layout.init(); // init current layout
        var dt = $('#enrollments-table').DataTable();

        // Custom filtering function
        $.fn.dataTable.ext.search.push(
            function(settings, data, dataIndex) {
                var activeFilter = $('ul.nav-tabs li.active a').data('filter');
                var activeCategory = $('ul.nav-tabs li.active a').data('category');
                
                var rowDate = $(dt.row(dataIndex).node()).data('date');
                var rowCategory = $(dt.row(dataIndex).node()).data('category');

                var show = true;

                if (activeCategory) {
                    if (rowCategory !== activeCategory) {
                        show = false;
                    }
                }

                if (show && activeFilter) {
                    var today = new Date();
                    var y = today.getFullYear();
                    var m = ('0' + (today.getMonth() + 1)).slice(-2);
                    var d = ('0' + today.getDate()).slice(-2);
                    var todayStr = y + '-' + m + '-' + d;

                    if (activeFilter === 'today') {
                        if (rowDate !== todayStr) {
                            show = false;
                        }
                    } else if (activeFilter === 'month') {
                        if (!rowDate || !rowDate.startsWith(y + '-' + m)) {
                            show = false;
                        }
                    }
                }
                
                return show;
            }
        );

        $('a[data-toggle="tab"]').on('shown.bs.tab', function(e) {
            // Add data-filter attributes to the tabs
            var target = $(e.target);
            var href = target.attr("href");
            if(href === '#enroll-today') {
                target.data('filter', 'today');
            } else if (href === '#enroll-month') {
                target.data('filter', 'month');
            } else {
                target.data('filter', 'all');
            }
            dt.draw();
        });

        // On load, show all
        $('ul.nav-tabs li.active a').data('filter', 'all');
        dt.draw();
    });
</script>
<!-- END JAVASCRIPTS -->
</body>
</html>