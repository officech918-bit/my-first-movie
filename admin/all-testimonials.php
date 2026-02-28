<?php
declare(strict_types=1);

// Bootstrap
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/database.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

use App\Models\Testimonial;
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

// Load S3Uploader for environment detection
require_once __DIR__ . '/../classes/S3Uploader.php';
$s3Uploader = new S3Uploader();
$isProduction = $s3Uploader->isS3Enabled();

// Auth Check
if (!isset($_SESSION['user_type']) || !in_array($_SESSION['user_type'], ['webmaster', 'admin'])) {
    header('Location: access-denied.php');
    exit();
}

// Determine menu
$menu = $_SESSION['user_type'] == 'webmaster' ? 'inc/left-menu-webmaster.php' : 'inc/left-menu-admin.php';

// Fetch all testimonials
$testimonials = Testimonial::orderBy('short_order', 'asc')->get();

// CSRF token for delete actions
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];

/**
 * Get image URL based on environment
 */
function getImageUrl(string $imagePath, bool $isProduction): string {
    if (empty($imagePath)) {
        return 'assets/admin/layout/img/no-image.png';
    }
    
    if ($isProduction) {
        // In production, assume S3 URLs are stored
        return $imagePath;
    } else {
        // In local development, convert relative paths to full URLs
        if (strpos($imagePath, 'http') === 0) {
            return $imagePath; // Already a full URL
        }
        return '../' . $imagePath; // Convert to relative path
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8"/>
    <title>All Testimonials | My First Movie</title>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta content="width=device-width, initial-scale=1" name="viewport"/>
    <link href="http://fonts.googleapis.com/css?family=Open+Sans:400,300,600,700&subset=all" rel="stylesheet" type="text/css"/>
    <link href="assets/global/plugins/font-awesome/css/font-awesome.min.css" rel="stylesheet" type="text/css"/>
    <link href="assets/global/plugins/simple-line-icons/simple-line-icons.min.css" rel="stylesheet" type="text/css"/>
    <link href="assets/global/plugins/bootstrap/css/bootstrap.min.css" rel="stylesheet" type="text/css"/>
    <link href="assets/global/plugins/uniform/css/uniform.default.css" rel="stylesheet" type="text/css"/>
    <link rel="stylesheet" type="text/css" href="assets/global/plugins/datatables/plugins/bootstrap/dataTables.bootstrap.css"/>
    <link href="assets/global/css/components.css" id="style_components" rel="stylesheet" type="text/css"/>
    <link href="assets/admin/layout/css/layout.css" rel="stylesheet" type="text/css"/>
    <link href="assets/admin/layout/css/themes/darkblue.css" rel="stylesheet" type="text/css" id="style_color"/>
</head>
<body class="page-header-fixed page-quick-sidebar-over-content page-style-square">
<?php include('inc/header.php'); ?>
<div class="clearfix"></div>
<div class="page-container">
    <div class="page-sidebar-wrapper">
        <div class="page-sidebar navbar-collapse collapse">
            <?php include($menu); ?>
        </div>
    </div>
    <div class="page-content-wrapper">
        <div class="page-content">
            <h3 class="page-title">All Testimonials</h3>
            <div class="row">
                <div class="col-md-12">
                    <div class="portlet box blue">
                        <div class="portlet-title">
                            <div class="caption"><i class="fa fa-comments"></i>Testimonials</div>
                        </div>
                        <div class="portlet-body">
                            <div class="table-toolbar">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="btn-group">
                                            <a href="testimonials/create" class="btn green">
                                                Add New <i class="fa fa-plus"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <table class="table table-striped table-bordered table-hover" id="testimonials_table">
                                <thead>
                                <tr>
                                    <th>Order</th>
                                    <th>Client Name</th>
                                    <th>Company</th>
                                    <th>Logo</th>
                                    <th>Status</th>
                                    <th>Edit</th>
                                    <th>Delete</th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php foreach ($testimonials as $testimonial): ?>
                                    <tr id="testimonial-<?= $testimonial->id ?>">
                                        <td><?= htmlspecialchars((string)$testimonial->short_order) ?></td>
                                        <td><?= htmlspecialchars($testimonial->client_name) ?></td>
                                        <td><?= htmlspecialchars($testimonial->company) ?></td>
                                        <td>
                                            <img src="<?= getImageUrl($testimonial->logo, $isProduction) ?>" 
                                                 alt="<?= htmlspecialchars($testimonial->client_name) ?>" 
                                                 style="max-width: 80px; max-height: 60px; object-fit: cover;"
                                                 onerror="this.src='assets/admin/layout/img/no-image.png'">
                                        </td>
                                        <td>
                                            <?php if ($testimonial->status == 1): ?>
                                                <span class="label label-sm label-success">Active</span>
                                            <?php else: ?>
                                                <span class="label label-sm label-warning">Inactive</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <a href="testimonials/<?= $testimonial->id ?>/edit" class="btn btn-xs blue">
                                                <i class="fa fa-edit"></i> Edit
                                            </a>
                                        </td>
                                        <td>
                                            <button class="btn btn-xs red delete-testimonial" data-id="<?= $testimonial->id ?>">
                                                <i class="fa fa-trash-o"></i> Delete
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include('inc/footer.php'); ?>
<script src="assets/global/plugins/jquery.min.js" type="text/javascript"></script>
<script src="assets/global/plugins/bootstrap/js/bootstrap.min.js" type="text/javascript"></script>
<script type="text/javascript" src="assets/global/plugins/datatables/media/js/jquery.dataTables.min.js"></script>
<script type="text/javascript" src="assets/global/plugins/datatables/plugins/bootstrap/dataTables.bootstrap.js"></script>
<script src="assets/global/scripts/metronic.js" type="text/javascript"></script>
<script src="assets/admin/layout/scripts/layout.js" type="text/javascript"></script>
<script>
    jQuery(document).ready(function() {
        Metronic.init();
        Layout.init();
        $('#testimonials_table').DataTable();

        $('.delete-testimonial').on('click', function() {
            var testimonialId = $(this).data('id');
            if (confirm('Are you sure you want to delete this testimonial?')) {
                $.ajax({
                    url: 'delete_testimonial.php',
                    type: 'POST',
                    data: {
                        id: testimonialId,
                        csrf_token: '<?= $csrf_token ?>'
                    },
                    dataType: 'json',
                    success: function(response) {
                        if(response.success) {
                            $('#testimonial-' + testimonialId).fadeOut(300, function() { $(this).remove(); });
                        } else {
                            alert('Error: ' + response.message);
                        }
                    },
                    error: function() {
                        alert('An error occurred while deleting the testimonial.');
                    }
                });
            }
        });
    });
</script>
</body>
</html>