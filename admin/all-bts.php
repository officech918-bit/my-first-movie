<?php
// This file is now loaded through the router, which handles all bootstrapping.
// We add these checks here as a fallback for direct access, ensuring the file is self-sufficient.

// Ensure session is started.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Load environment variables
if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    require_once __DIR__ . '/../vendor/autoload.php';
    if (class_exists('Dotenv\Dotenv')) {
        $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
        $dotenv->load();
    }
}

// Load S3Uploader for environment detection
if (file_exists(__DIR__ . '/../classes/S3Uploader.php')) {
    require_once __DIR__ . '/../classes/S3Uploader.php';
    $s3Uploader = new S3Uploader();
    $isProduction = $s3Uploader->isS3Enabled();
}

// Load database connection using the same approach as dashboard.php
include('inc/requires.php');

// Auth Guard: Ensure user is logged in and is an admin or webmaster.
if (!isset($_SESSION['user_type']) || !in_array($_SESSION['user_type'], ['webmaster', 'admin'])) {
    header('Location: login.php'); // Or a dedicated access-denied page.
    exit();
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

use App\Models\BehindTheScene;

// Determine the correct menu to include based on the user's role.
// This session variable is guaranteed to exist by the AuthMiddleware.
if ($_SESSION['user_type'] == 'webmaster') {
    $menu = 'inc/left-menu-webmaster.php';
} else {
    $menu = 'inc/left-menu-admin.php';
}

// Generate a CSRF token for delete actions.
// The token is stored in the session to be validated on the deletion request.
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

// Generate a simple CSRF token if one doesn't exist
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8"/>
    <title>All Behind the Scenes | Admin</title>
    <base href="<?php echo $admin_path; ?>">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:400,300,600,700&subset=all" rel="stylesheet" type="text/css"/>
    <link href="assets/global/plugins/font-awesome/css/font-awesome.min.css" rel="stylesheet" type="text/css"/>
    <link href="assets/global/plugins/simple-line-icons/simple-line-icons.min.css" rel="stylesheet" type="text/css"/>
    <link href="assets/global/plugins/bootstrap/css/bootstrap.min.css" rel="stylesheet" type="text/css"/>
    <link href="assets/global/plugins/uniform/css/uniform.default.css" rel="stylesheet" type="text/css"/>
    
    <link rel="stylesheet" type="text/css" href="assets/global/plugins/select2/select2.css"/>
    <link rel="stylesheet" type="text/css" href="assets/global/plugins/datatables/plugins/bootstrap/dataTables.bootstrap.css"/>
    
    <link href="assets/global/css/components.css" rel="stylesheet" type="text/css"/>
    <link href="assets/global/css/plugins.css" rel="stylesheet" type="text/css"/>
    <link href="assets/admin/layout/css/layout.css" rel="stylesheet" type="text/css"/>
    <link href="assets/admin/layout/css/themes/darkblue.css" rel="stylesheet" type="text/css"/>
    <link rel="stylesheet" href="assets/admin/layout/css/zebra_dialog.css" type="text/css">
    
    <style>
        /* Improved Loading Spinner Styles */
        #loading-overlay {
            position: fixed;
            top: 0; left: 0;
            width: 100%; height: 100%;
            background: rgba(255,255,255,0.7);
            z-index: 9999;
            display: none;
        }
        #loading-overlay img {
            position: absolute;
            top: 50%; left: 50%;
            transform: translate(-50%, -50%);
        }
    </style>
</head>

<body class="page-header-fixed page-quick-sidebar-over-content">

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
            <h3 class="page-title">Managed All Behind the Scenes</h3>
            
            <div class="page-bar">
                <ul class="page-breadcrumb">
                    <li>
                        <i class="fa fa-home"></i>
                        <a href="dashboard.php">Dashboard</a>
                        <i class="fa fa-angle-right"></i>
                    </li>
                    <li><a href="#">All Behind the Scenes</a></li>
                </ul>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <div class="portlet box grey-cascade">
                        <div class="portlet-title">
                            <div class="caption"><i class="fa fa-globe"></i>Record List</div>
                        </div>
                        <div class="portlet-body">
                            <div class="table-toolbar">
                                <div class="row">
                                    <div class="col-md-6">
                                        <a href="bts/create" class="btn green">
                                            Add New <i class="fa fa-plus"></i>
                                        </a>
                                    </div>
                                    <div class="col-md-6 text-right">
                                        <div class="form-inline">
                                            <select id="per_page_control" class="form-control input-sm">
                                                <option value="10">10</option>
                                                <option value="25" selected>25</option>
                                                <option value="50">50</option>
                                            </select>
                                            <input type="text" id="search_data" class="form-control input-sm" placeholder="Search...">
                                            <button id="search_btn" class="btn btn-sm green">Search</button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div id="loading-overlay"><img src="assets/admin/layout/img/loading.gif" alt="Loading..."></div>
                            
                            <div id="ajax-container">
                                <div class="data-content"></div>
                                <div class="pagination-container"></div>
                            </div>
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
<script src="assets/global/scripts/metronic.js" type="text/javascript"></script>
<script src="assets/admin/layout/scripts/layout.js" type="text/javascript"></script>
<script type="text/javascript" src="assets/admin/layout/scripts/zebra_dialog.js"></script>

<script type="text/javascript">
    $(document).ready(function() {
        Metronic.init();
        Layout.init();

        const CSRF_TOKEN = '<?php echo $_SESSION['csrf_token']; ?>';

        function toggleLoading(show) {
            show ? $('#loading-overlay').show() : $('#loading-overlay').hide();
        }

        function loadData(page = 1) {
            const dataPerPage = $("#per_page_control").val();
            const searchTerm = $("#search_data").val();

            toggleLoading(true);

            $.ajax({
                url: 'load_all_bts',
                type: 'POST',
                cache: false,
                data: {
                    page: page,
                    data_per_page: dataPerPage,
                    search_term: searchTerm,
                    csrf_token: CSRF_TOKEN
                },
                success: function(response) {
                    $("#ajax-container").html(response);
                },
                error: function() {
                    new $.Zebra_Dialog('An error occurred while fetching data.', { type: 'error' });
                },
                complete: function() {
                    toggleLoading(false);
                }
            });
        }

        // Initial Load
        loadData(1);

        // UI Events
        $('#search_btn').on('click', () => loadData(1));
        $('#per_page_control').on('change', () => loadData(1));
        $('#search_data').on('keypress', (e) => { if(e.which === 13) loadData(1); });

        $(document).on('click', '.pagination-link', function(e) {
            e.preventDefault();
            loadData($(this).data('page'));
        });

        // Delete Logic
        $(document).on('click', '.delete-bts', function(e) {
            e.preventDefault();
            const btn = $(this);
            const id = btn.data('id');
            const title = btn.data('title');

            new $.Zebra_Dialog(`Are you sure you want to delete <strong>${title}</strong>?`, {
                type: 'question',
                buttons: ['Yes', 'No'],
                onClose: function(caption) {
                    if (caption === 'Yes') {
                        $.post('delete_bts', { id: id, csrf_token: CSRF_TOKEN }, function() {
                            new $.Zebra_Dialog('Deleted successfully.', {
                                type: 'confirmation',
                                onClose: () => loadData(1) // Go to first page after delete
                            });
                        }).fail(() => {
                            new $.Zebra_Dialog('Delete failed. The record may have already been removed.', { type: 'error' });
                        });
                    }
                }
            });
        });
    });
</script>
</body>
</html>