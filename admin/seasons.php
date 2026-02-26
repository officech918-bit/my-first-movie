<?php
declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. BOOTSTRAPPING: Load essential configurations, database, and session management.
// This is included by the router, but we have it here as a fallback.
if (!function_exists('isAdmin')) {
    require_once 'inc/requires.php';
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

// Define a base URL for all assets to fix pathing issues on routed pages.
$base_url = $admin_path;

// 2. DEPENDENCIES
use App\Models\Season;
use App\Models\Category;

if (isset($_SESSION['user_type']) && $_SESSION['user_type'] == 'webmaster') {
    $menu = 'inc/left-menu-webmaster.php';
} else {
    $menu = 'inc/left-menu-admin.php';
}

// Ensure required columns exist for seasons table
try {
    $dbCheck = new \MySQLDB();
    $conn = $dbCheck->db;
    $stmt = $conn->prepare("SELECT 1 FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'seasons' AND column_name = 'status'");
    if ($stmt) {
        $stmt->execute();
        $hasStatus = $stmt->get_result()->num_rows > 0;
        $stmt->close();
        if (!$hasStatus) {
            $conn->query("ALTER TABLE seasons ADD COLUMN status TINYINT(1) NOT NULL DEFAULT 0");
        }
    }
    $stmt = $conn->prepare("SELECT 1 FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'seasons' AND column_name = 'is_default'");
    if ($stmt) {
        $stmt->execute();
        $hasDefault = $stmt->get_result()->num_rows > 0;
        $stmt->close();
        if (!$hasDefault) {
            $conn->query("ALTER TABLE seasons ADD COLUMN is_default TINYINT(1) NOT NULL DEFAULT 0");
        }
    }
} catch (\Throwable $e) {
}

// 4. INITIALIZATION & ROUTING LOGIC
$csrf_token = $_SESSION['csrf_token'];

$is_edit = false;
$season = new Season(); // Create a new, empty season object by default.
$page_title = "Add New Season";
$form_action = $base_url . "seasons/create";
$errors = [];
$selected_categories = [];

// Fetch all active categories for the dropdown.
$categories = Category::where('status', 1)->orderBy('title')->get();

// **THE CRITICAL FIX**: Check for the ID in the $_GET array.
// The router has been configured to place the ID here.
if (isset($_GET['id'])) {
    $is_edit = true;
    $season_id = (int)$_GET['id'];
    $retrieved_season = Season::find($season_id);

    if ($retrieved_season) {
        $season = $retrieved_season;
        $page_title = "Edit Season";
        $form_action = "{$base_url}seasons/{$season_id}/edit";
        
        // For editing, load the currently associated category IDs
        $selected_categories = $season->categories()->pluck('categories.id')->toArray();

    } else {
        // Season not found, redirect to the list with an error.
        header("Location: {$base_url}all-seasons.php?error_message=" . urlencode("Season not found."));
        exit();
    }
}

// 5. FORM SUBMISSION HANDLING (POST REQUEST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // A. CSRF Validation
    if (!isset($_POST['csrf_token']) || !hash_equals($csrf_token, $_POST['csrf_token'])) {
        $errors[] = "CSRF token mismatch. Please try again.";
    } else {
        // B. Data Sanitization and Validation
        $season->title = trim($_POST['title'] ?? '');
        $season->display_note = trim($_POST['display_note'] ?? '');
        $allowed = ['UPCOMING','ACTIVE','COMPLETED'];
        $statusPosted = strtoupper(trim($_POST['status'] ?? ''));
        if (!in_array($statusPosted, $allowed)) {
            $statusPosted = 'UPCOMING';
        }
        // Map string status to numeric tinyint for DB compatibility:
        // UPCOMING=0, ACTIVE=1, COMPLETED=2
        $statusMap = ['UPCOMING' => 0, 'ACTIVE' => 1, 'COMPLETED' => 2];
        $season->status = $statusMap[$statusPosted];
        $season->start_date = !empty($_POST['start_date']) ? date('Y-m-d', strtotime(trim($_POST['start_date']))) : null;
        $season->end_date = !empty($_POST['end_date']) ? date('Y-m-d', strtotime(trim($_POST['end_date']))) : null;
        
        // Handle category IDs from the multi-select dropdown
        $posted_category_ids = $_POST['category_ids'] ?? [];
        $selected_categories = array_map('intval', $posted_category_ids);


        if (empty($season->title)) {
            $errors[] = "Season title is required.";
        }
        if (empty($selected_categories)) {
            $errors[] = "At least one category must be selected.";
        }
        if (empty($season->start_date)) {
            $errors[] = "Start date is required.";
        }
        if (empty($season->end_date)) {
            $errors[] = "End date is required.";
        }
        if ($season->start_date && $season->end_date && $season->start_date > $season->end_date) {
            $errors[] = "The season's end date must be after its start date.";
        }

        // C. Database Operation
        if (empty($errors)) {
            try {
                // Set audit fields
                if (!$is_edit) {
                    $season->created_by = $_SESSION['uid'];
                    $season->create_date = date('Y-m-d H:i:s');
                }
                $season->last_updated_by = $_SESSION['uid'];
                $season->last_update = date('Y-m-d H:i:s');
                $season->last_update_ip = $_SERVER['REMOTE_ADDR'];

                $season->save();
                
                // Sync the categories in the pivot table
                $season->categories()->sync($selected_categories);

                $success_message = "Season was " . ($is_edit ? "updated" : "created") . " successfully!";
                // Use Post-Redirect-Get (PRG) pattern to prevent form resubmission
                header("Location: {$base_url}all-seasons.php?success_message=" . urlencode($success_message));
                exit();
            } catch (\Exception $e) {
                // In a real app, you would log this error.
                error_log($e->getMessage());
                $errors[] = "Database error: Could not save the season. Details: " . $e->getMessage();
            }
        }
    }
    // If there were errors, the script will continue and display them on the form.
}

// --- HTML VIEW ---
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8"/>
    <title><?php echo htmlspecialchars($page_title); ?> | My First Movie</title>
    <base href="<?php echo $admin_path; ?>">
    
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta content="width=device-width, initial-scale=1" name="viewport"/>
    <link href="//fonts.googleapis.com/css?family=Open+Sans:400,300,600,700&subset=all" rel="stylesheet" type="text/css"/>
    <link href="<?php echo $admin_path; ?>assets/global/plugins/font-awesome/css/font-awesome.min.css" rel="stylesheet" type="text/css"/>
    <link href="<?php echo $admin_path; ?>assets/global/plugins/simple-line-icons/simple-line-icons.min.css" rel="stylesheet" type="text/css"/>
    <link href="<?php echo $admin_path; ?>assets/global/plugins/bootstrap/css/bootstrap.min.css" rel="stylesheet" type="text/css"/>
    <link href="<?php echo $admin_path; ?>assets/global/plugins/uniform/css/uniform.default.css" rel="stylesheet" type="text/css"/>
    <link href="<?php echo $admin_path; ?>assets/global/plugins/bootstrap-switch/css/bootstrap-switch.min.css" rel="stylesheet" type="text/css"/>
    
    <!-- BEGIN PAGE LEVEL STYLES -->
    <link rel="stylesheet" type="text/css" href="<?php echo $admin_path; ?>assets/global/plugins/bootstrap-datepicker/css/datepicker3.css"/>
    <link rel="stylesheet" type="text/css" href="<?php echo $admin_path; ?>assets/global/plugins/bootstrap-select/bootstrap-select.min.css"/>
    <link rel="stylesheet" type="text/css" href="<?php echo $admin_path; ?>assets/global/plugins/select2/select2.css"/>
    <link rel="stylesheet" type="text/css" href="<?php echo $admin_path; ?>assets/global/plugins/jquery-multi-select/css/multi-select.css"/>
    <!-- END PAGE LEVEL SCRIPTS -->

    <link href="<?php echo $admin_path; ?>assets/global/css/components.css" id="style_components" rel="stylesheet" type="text/css"/>
    <link href="<?php echo $admin_path; ?>assets/global/css/plugins.css" rel="stylesheet" type="text/css"/>
    <link href="<?php echo $admin_path; ?>assets/admin/layout/css/layout.css" rel="stylesheet" type="text/css"/>
    <link href="<?php echo $admin_path; ?>assets/admin/layout/css/themes/darkblue.css" rel="stylesheet" type="text/css" id="style_color"/>
    <link href="<?php echo $admin_path; ?>assets/admin/layout/css/custom.css" rel="stylesheet" type="text/css"/>
    <link rel="shortcut icon" href="<?php echo $admin_path; ?>favicon.ico"/>
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
            <div class="page-bar">
                <ul class="page-breadcrumb">
                    <li><i class="fa fa-home"></i><a href="<?php echo $base_url; ?>dashboard">Home</a><i class="fa fa-angle-right"></i></li>
                    <li><a href="<?php echo $base_url; ?>all-seasons.php">Seasons</a><i class="fa fa-angle-right"></i></li>
                    <li><a href="#"><?php echo htmlspecialchars($page_title); ?></a></li>
                </ul>
            </div>
            <h3 class="page-title"><?php echo htmlspecialchars($page_title); ?></h3>

            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <button class="close" data-dismiss="alert"></button>
                    <strong>Error!</strong>
                    <ul>
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo $error; ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <div class="row">
                <div class="col-md-12">
                    <div class="portlet box blue-hoki">
                        <div class="portlet-title">
                            <div class="caption">
                                <i class="fa fa-film"></i>Season Details
                            </div>
                        </div>
                        <div class="portlet-body form">
                            <form action="<?php echo $form_action; ?>" method="POST" class="form-horizontal">
                                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                                <div class="form-body">
                                    <div class="form-group">
                                        <label class="col-md-3 control-label">Season Title</label>
                                        <div class="col-md-6">
                                            <input type="text" name="title" class="form-control" placeholder="e.g., Season 1" value="<?php echo htmlspecialchars($season->title ?? ''); ?>" required>
                                        </div>
                                    </div>
                                     <div class="form-group">
                                        <label class="col-md-3 control-label">Display Note</label>
                                        <div class="col-md-6">
                                            <textarea name="display_note" class="form-control" rows="3"><?php echo htmlspecialchars($season->display_note ?? ''); ?></textarea>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-md-3 control-label">Categories</label>
                                        <div class="col-md-6">
                                            <select name="category_ids[]" class="form-control select2-multiple" multiple required>
                                                <?php foreach ($categories as $category): ?>
                                                    <option value="<?php echo $category->id; ?>" <?php echo in_array($category->id, $selected_categories) ? 'selected' : ''; ?>>
                                                        <?php echo htmlspecialchars($category->title); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="control-label col-md-3">Start Date</label>
                                        <div class="col-md-3">
                                            <div class="input-group date date-picker" data-date-format="dd-mm-yyyy">
                                                <input type="text" class="form-control" readonly name="start_date" value="<?php echo htmlspecialchars(isset($season->start_date) ? date('d-m-Y', strtotime($season->start_date)) : ''); ?>">
                                                <span class="input-group-btn">
                                                    <button class="btn default" type="button"><i class="fa fa-calendar"></i></button>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="control-label col-md-3">End Date</label>
                                        <div class="col-md-3">
                                            <div class="input-group date date-picker" data-date-format="dd-mm-yyyy">
                                                <input type="text" class="form-control" readonly name="end_date" value="<?php echo htmlspecialchars(isset($season->end_date) ? date('d-m-Y', strtotime($season->end_date)) : ''); ?>">
                                                <span class="input-group-btn">
                                                    <button class="btn default" type="button"><i class="fa fa-calendar"></i></button>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-md-3 control-label">Status</label>
                                        <div class="col-md-6">
                                            <select name="status" class="form-control">
                                                <?php
                                                    $currVal = $season->status ?? 'UPCOMING';
                                                    if (is_numeric($currVal)) {
                                                        $curr = ((int)$currVal===1)?'ACTIVE':(((int)$currVal===2)?'COMPLETED':'UPCOMING');
                                                    } else {
                                                        $curr = strtoupper($currVal);
                                                    }
                                                ?>
                                                <option value="UPCOMING" <?php echo $curr==='UPCOMING'?'selected':''; ?>>UPCOMING</option>
                                                <option value="ACTIVE" <?php echo $curr==='ACTIVE'?'selected':''; ?>>ACTIVE</option>
                                                <option value="COMPLETED" <?php echo $curr==='COMPLETED'?'selected':''; ?>>COMPLETED</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-actions">
                                    <div class="row">
                                        <div class="col-md-offset-3 col-md-9">
                                            <button type="submit" class="btn green">Submit</button>
                                            <a href="<?php echo $base_url; ?>all-seasons.php" class="btn default">Cancel</a>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include('inc/footer.php'); ?>
<!-- BEGIN JAVASCRIPTS(Load javascripts at bottom, this will reduce page load time) -->
<!-- BEGIN CORE PLUGINS -->
<!--[if lt IE 9]>
<script src="<?php echo $base_url; ?>assets/global/plugins/respond.min.js"></script>
<script src="<?php echo $base_url; ?>assets/global/plugins/excanvas.min.js"></script>
<![endif]-->
<script src="<?php echo $base_url; ?>assets/global/plugins/jquery.min.js" type="text/javascript"></script>
<script src="<?php echo $base_url; ?>assets/global/plugins/jquery-migrate.min.js" type="text/javascript"></script>
<!-- IMPORTANT! Load jquery-ui.min.js before bootstrap.min.js to fix bootstrap tooltip conflict with jquery ui tooltip -->
<script src="<?php echo $base_url; ?>assets/global/plugins/jquery-ui/jquery-ui.min.js" type="text/javascript"></script>
<script src="<?php echo $base_url; ?>assets/global/plugins/bootstrap/js/bootstrap.min.js" type="text/javascript"></script>
<script src="<?php echo $base_url; ?>assets/global/plugins/bootstrap-hover-dropdown/bootstrap-hover-dropdown.min.js" type="text/javascript"></script>
<script src="<?php echo $base_url; ?>assets/global/plugins/jquery-slimscroll/jquery.slimscroll.min.js" type="text/javascript"></script>
<script src="<?php echo $base_url; ?>assets/global/plugins/jquery.blockui.min.js" type="text/javascript"></script>
<script src="<?php echo $base_url; ?>assets/global/plugins/jquery.cokie.min.js" type="text/javascript"></script>
<script src="<?php echo $base_url; ?>assets/global/plugins/uniform/jquery.uniform.min.js" type="text/javascript"></script>
<script src="<?php echo $base_url; ?>assets/global/plugins/bootstrap-switch/js/bootstrap-switch.min.js" type="text/javascript"></script>
<!-- END CORE PLUGINS -->
<!-- BEGIN PAGE LEVEL PLUGINS -->
<script type="text/javascript" src="<?php echo $base_url; ?>assets/global/plugins/bootstrap-datepicker/js/bootstrap-datepicker.js"></script>
<script type="text/javascript" src="<?php echo $base_url; ?>assets/global/plugins/bootstrap-select/bootstrap-select.min.js"></script>
<script type="text/javascript" src="<?php echo $base_url; ?>assets/global/plugins/select2/select2.min.js"></script>
<script type="text/javascript" src="<?php echo $base_url; ?>assets/global/plugins/jquery-multi-select/js/jquery.multi-select.js"></script>
<!-- END PAGE LEVEL PLUGINS -->
<!-- BEGIN PAGE LEVEL SCRIPTS -->
<script src="<?php echo $base_url; ?>assets/global/scripts/metronic.js" type="text/javascript"></script>
<script src="<?php echo $base_url; ?>assets/admin/layout/scripts/layout.js" type="text/javascript"></script>
<script src="<?php echo $base_url; ?>assets/admin/pages/scripts/components-pickers.js"></script>
<!-- END PAGE LEVEL SCRIPTS -->
<script>
jQuery(document).ready(function() {
   Metronic.init(); // init metronic core components
   Layout.init(); // init layout
   ComponentsPickers.init(); // init page level components
   $('.make-switch').bootstrapSwitch();
});
</script>
</body>
</html>
