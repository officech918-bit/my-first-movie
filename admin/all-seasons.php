<?php
// Ensure the session is started.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Ensure the Composer autoloader and Eloquent are loaded.
if (!class_exists('App\Models\Season')) {
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

use App\Models\Season;

// Determine the correct menu to include based on the user's role.
if (isset($_SESSION['user_type']) && $_SESSION['user_type'] == 'webmaster') {
    $menu = 'inc/left-menu-webmaster.php';
} else {
    $menu = 'inc/left-menu-admin.php';
}

// Fetch all seasons from the database using our Eloquent model.
$seasons = Season::orderBy('title')->get();

// Generate a CSRF token for delete actions.
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];
?>
<!DOCTYPE html>
<html lang="en" class="no-js">
<head>
    <meta charset="utf-8"/>
    <title>All Seasons | My First Movie</title>
    <base href="<?php echo $admin_path; ?>">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta content="width=device-width, initial-scale=1" name="viewport"/>
    <meta content="Season management page" name="description"/>
    <meta content="" name="author"/>
    <!-- BEGIN GLOBAL MANDATORY STYLES -->
    <link href="//fonts.googleapis.com/css?family=Open+Sans:400,300,600,700&subset=all" rel="stylesheet" type="text/css"/>
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
                        <a href="#">Seasons</a>
                        <i class="fa fa-angle-right"></i>
                    </li>
                    <li>
                        <a href="all-seasons">All Seasons</a>
                    </li>
                </ul>
            </div>
            <h3 class="page-title">
                All Seasons
            </h3>

            <!-- This container is for dynamic alerts from JavaScript -->
            <div id="alert-container"></div>

            <div class="row">
                <div class="col-md-12">
                    <div class="portlet box blue">
                        <div class="portlet-title">
                            <div class="caption">
                                <i class="fa fa-folder-open"></i>Seasons
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
                                            <a href="seasons/create" class="btn green">
                                                Add New <i class="fa fa-plus"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <table class="table table-striped table-bordered table-hover" id="seasons-table">
                                <thead>
                                <tr>
                                    <th>Title</th>
                                    <th>Status</th>
                                    <th>Default</th>
                                    <th>Actions</th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php if ($seasons->count() > 0): ?>
                                    <?php foreach ($seasons as $season): ?>
                                        <tr class="odd gradeX" id="season-<?php echo $season->id; ?>">
                                            <td><?php echo htmlspecialchars($season->title); ?></td>
                                            <td>
                                                <?php if ($season->status == 1): ?>
                                                    <span class="label label-sm label-success">Active</span>
                                                <?php else: ?>
                                                    <span class="label label-sm label-warning">Inactive</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if (!empty($season->is_default)): ?>
                                                    <span class="label label-sm label-info">Default</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <a href="seasons/<?php echo $season->id; ?>/edit" class="btn btn-xs blue">
                                                    <i class="fa fa-edit"></i> Edit
                                                </a>
                                                
                                                <!-- Secure "Set Default" action with a POST form -->
                                                <form action="seasons/<?php echo $season->id; ?>/set-default" method="POST" style="display: inline-block;">
                                                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                                                    <button type="submit" class="btn btn-xs green" <?php if (!empty($season->is_default)) echo 'disabled'; ?>>
                                                        <i class="fa fa-check"></i> Set Default
                                                    </button>
                                                </form>

                                                <button class="btn btn-xs red delete-season" data-id="<?php echo $season->id; ?>">
                                                    <i class="fa fa-trash-o"></i> Delete
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="4" class="text-center">No seasons found.</td>
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
        Metronic.init();
        Layout.init();
        $('#seasons-table').DataTable();

        function showAlert(type, message) {
            var alertHtml = '<div class="alert alert-' + type + ' alert-dismissable">' +
                                '<button type="button" class="close" data-dismiss="alert" aria-hidden="true"></button>' +
                                message +
                            '</div>';
            $('#alert-container').html(alertHtml);
        }

        // Handle the delete button click
        $('#seasons-table').on('click', '.delete-season', function() {
            var seasonId = $(this).data('id');
            if (confirm('Are you sure you want to delete this season? This action cannot be undone.')) {
                $.ajax({
                    url: 'seasons/delete', 
                    type: 'POST',
                    data: {
                        id: seasonId,
                        csrf_token: '<?php echo $csrf_token; ?>'
                    },
                    dataType: 'json',                            
                    success: function(response) {
                        if (response.success) {
                            $('#season-' + seasonId).fadeOut(300, function() { $(this).remove(); });
                            showAlert('success', response.message);
                        } else {
                            showAlert('danger', response.message);
                        }
                    },
                    error: function() {
                        showAlert('danger', 'An error occurred while trying to delete the season.');
                    }
                });
            }
        });
    });
</script>
<!-- END JAVASCRIPTS -->
</body>
</html>