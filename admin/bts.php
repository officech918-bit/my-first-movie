<?php
declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. BOOTSTRAPPING
if (!function_exists('isAdmin')) {
    require_once 'inc/requires.php';
}

// Load environment variables from .env file
if (class_exists('Dotenv\Dotenv')) {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
    $dotenv->load();
}

require_once __DIR__ . '/../classes/imageResizer.php';

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

// Define a base URL for all assets
$base_url = $admin_path;

// 2. DEPENDENCIES
use App\Models\BehindTheScene;
use App\Models\BehindTheSceneImage;
use App\Models\Season;

if (isset($_SESSION['user_type']) && $_SESSION['user_type'] == 'webmaster') {
    $menu = 'inc/left-menu-webmaster.php';
} else {
    $menu = 'inc/left-menu-admin.php';
}

// 4. INITIALIZATION & ROUTING LOGIC
$csrf_token = $_SESSION['csrf_token'];

$is_edit = false;
$bts = new BehindTheScene(); // New, empty object
$page_title = "Add New Behind The Scenes";
$form_action = $base_url . "bts/create";
$errors = [];
$selected_season = null;

// Fetch all active seasons for the dropdown
$seasons = Season::where('status', 1)->orderBy('title')->get();

if (isset($_GET['id'])) {
    $is_edit = true;
    $bts_id = (int)$_GET['id'];
    $retrieved_bts = BehindTheScene::with('images')->find($bts_id);

    if ($retrieved_bts) {
        $bts = $retrieved_bts;
        $page_title = "Edit Behind The Scenes";
        $form_action = "{$base_url}bts/{$bts_id}/edit";
        $selected_season = $bts->season;
    } else {
        header("Location: {$base_url}all-bts.php?error_message=" . urlencode("BTS entry not found."));
        exit();
    }
}

// 5. FORM SUBMISSION HANDLING (POST REQUEST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !hash_equals($csrf_token, $_POST['csrf_token'])) {
        $errors[] = "CSRF token mismatch. Please try again.";
    } else {
        // Sanitize and validate main BTS data
        $bts->title = trim($_POST['title'] ?? '');
        $bts->season = (int)($_POST['season'] ?? 0);
        $bts->day = trim($_POST['day'] ?? '');
        $bts->display_note = trim($_POST['display_note'] ?? '');
        $bts->video_url = trim($_POST['video_url'] ?? '');
        $bts->short_order = (int)($_POST['short_order'] ?? 0);
        $bts->status = isset($_POST['status']) ? 1 : 0;
        
        $selected_season = $bts->season;

        if (empty($bts->title)) $errors[] = "Title is required.";
        if (empty($bts->season)) $errors[] = "A season must be selected.";
        if (empty($bts->day)) $errors[] = "Day is required.";

        // Validate title image for new entries
        if (!$is_edit && (!isset($_FILES['title_image']) || $_FILES['title_image']['error'] !== UPLOAD_ERR_OK)) {
            $errors[] = "The Title Image is required.";
        }

        // Database Operation
        if (empty($errors)) {
            try {
                if (!$is_edit) {
                    $bts->created_by = $_SESSION['uid'];
                    $bts->create_date = date('Y-m-d H:i:s');
                }
                $bts->last_updated_by = $_SESSION['uid'];
                $bts->last_update = date('Y-m-d H:i:s');
                $bts->last_update_ip = $_SERVER['REMOTE_ADDR'];

                // Shared upload directory and allowed types
                $upload_dir = __DIR__ . '/uploads/bts/';
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                }
                $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];

                // Handle the single "Title Image" upload
                if (isset($_FILES['title_image']) && $_FILES['title_image']['error'] === UPLOAD_ERR_OK) {
                    $tmp_name = $_FILES['title_image']['tmp_name'];
                    $file_type = mime_content_type($tmp_name);

                    if (in_array($file_type, $allowed_types)) {
                        // Delete old image and its thumb if they exist
                        if ($is_edit) {
                            if ($bts->screenshot && file_exists($upload_dir . $bts->screenshot)) {
                                unlink($upload_dir . $bts->screenshot);
                            }
                            if ($bts->screenshot_thumb && file_exists($upload_dir . $bts->screenshot_thumb)) {
                                unlink($upload_dir . $bts->screenshot_thumb);
                            }
                        }

                        $file_extension = pathinfo($_FILES['title_image']['name'], PATHINFO_EXTENSION);
                        $new_filename = uniqid('bts_title_', true) . '.' . $file_extension;
                        $destination = $upload_dir . $new_filename;

                        if (move_uploaded_file($tmp_name, $destination)) {
                            $bts->screenshot = $new_filename;

                            // Generate thumbnail
                            $thumb_filename = 'thumb_' . $new_filename;
                            $thumb_destination = $upload_dir . $thumb_filename;
                            if (create_thumbnail($destination, $thumb_destination, 150)) {
                                $bts->screenshot_thumb = $thumb_filename;
                            } else {
                                $errors[] = "Failed to create a thumbnail for the title image.";
                            }
                        } else {
                            $errors[] = "Failed to move the title image.";
                        }
                    } else {
                        $errors[] = "Invalid file type for the title image.";
                    }
                }

                // Save the main BTS entry to get an ID
                if (empty($errors)) {
                    $bts->save();

                    // Handle multiple "Image Gallery" uploads
                    if (isset($_FILES['images'])) {
                        foreach ($_FILES['images']['name'] as $key => $name) {
                            if ($_FILES['images']['error'][$key] === UPLOAD_ERR_OK) {
                                $tmp_name = $_FILES['images']['tmp_name'][$key];
                                $file_type = mime_content_type($tmp_name);

                                if (in_array($file_type, $allowed_types)) {
                                    $file_extension = pathinfo($name, PATHINFO_EXTENSION);
                                    $new_filename = uniqid('bts_gallery_', true) . '.' . $file_extension;
                                    $destination = $upload_dir . $new_filename;

                                    if (move_uploaded_file($tmp_name, $destination)) {
                                        // Generate thumbnail for the gallery image
                                        $thumb_filename = 'thumb_' . $new_filename;
                                        $thumb_destination = $upload_dir . $thumb_filename;
                                        
                                        $bts_image = new BehindTheSceneImage();
                                        $bts_image->bts = $bts->id;
                                        $bts_image->image = $new_filename;
                                        
                                        if (create_thumbnail($destination, $thumb_destination, 150)) {
                                            $bts_image->image_thumb = $thumb_filename;
                                        } else {
                                            $errors[] = "Failed to create thumbnail for gallery image: " . htmlspecialchars($name);
                                        }
                                        
                                        $bts_image->created_by = $_SESSION['uid'];
                                        $bts_image->save();
                                    } else {
                                        $errors[] = "Failed to move gallery image: " . htmlspecialchars($name);
                                    }
                                } else {
                                    $errors[] = "Invalid file type for gallery image: " . htmlspecialchars($name);
                                }
                            } elseif ($_FILES['images']['error'][$key] !== UPLOAD_ERR_NO_FILE) {
                                $errors[] = "Error uploading gallery image: " . htmlspecialchars($name);
                            }
                        }
                    }
                }

                // Redirect only if there were no upload errors
                if (empty($errors)) {
                    $success_message = "BTS entry was " . ($is_edit ? "updated" : "created") . " successfully!";
                    header("Location: {$base_url}all-bts.php?success_message=" . urlencode($success_message));
                    exit();
                }
            } catch (\Exception $e) {
                error_log($e->getMessage());
                $errors[] = "Database error: Could not save the BTS entry. Details: " . $e->getMessage();
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8"/>
    <title><?php echo htmlspecialchars($page_title); ?> | My First Movie</title>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta content="width=device-width, initial-scale=1" name="viewport"/>
    <link href="//fonts.googleapis.com/css?family=Open+Sans:400,300,600,700&subset=all" rel="stylesheet" type="text/css"/>
    <link href="<?php echo $base_url; ?>assets/global/plugins/font-awesome/css/font-awesome.min.css" rel="stylesheet" type="text/css"/>
    <link href="<?php echo $base_url; ?>assets/global/plugins/simple-line-icons/simple-line-icons.min.css" rel="stylesheet" type="text/css"/>
    <link href="<?php echo $base_url; ?>assets/global/plugins/bootstrap/css/bootstrap.min.css" rel="stylesheet" type="text/css"/>
    <link href="<?php echo $base_url; ?>assets/global/plugins/uniform/css/uniform.default.css" rel="stylesheet" type="text/css"/>
    <link href="<?php echo $base_url; ?>assets/global/plugins/bootstrap-switch/css/bootstrap-switch.min.css" rel="stylesheet" type="text/css"/>
    <link href="<?php echo $base_url; ?>assets/global/css/components.css" id="style_components" rel="stylesheet" type="text/css"/>
    <link href="<?php echo $base_url; ?>assets/global/css/plugins.css" rel="stylesheet" type="text/css"/>
    <link href="<?php echo $base_url; ?>assets/admin/layout/css/layout.css" rel="stylesheet" type="text/css"/>
    <link href="<?php echo $base_url; ?>assets/admin/layout/css/themes/darkblue.css" rel="stylesheet" type="text/css" id="style_color"/>
    <link href="<?php echo $base_url; ?>assets/admin/layout/css/custom.css" rel="stylesheet" type="text/css"/>
    <link rel="shortcut icon" href="<?php echo $base_url; ?>favicon.ico"/>
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
                    <li><a href="<?php echo $base_url; ?>all-bts.php">Behind The Scenes</a><i class="fa fa-angle-right"></i></li>
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
                                <i class="fa fa-film"></i>BTS Details
                            </div>
                        </div>
                        <div class="portlet-body form">
                            <form action="<?php echo $form_action; ?>" method="POST" class="form-horizontal" enctype="multipart/form-data">
                                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                                <div class="form-body">
                                    <div class="form-group">
                                        <label class="col-md-3 control-label">Title</label>
                                        <div class="col-md-6">
                                            <input type="text" name="title" class="form-control" placeholder="e.g., Day 1 Shoot" value="<?php echo htmlspecialchars($bts->title ?? ''); ?>" required>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-md-3 control-label">Season</label>
                                        <div class="col-md-6">
                                            <select name="season" class="form-control" required>
                                                <option value="">Select a Season</option>
                                                <?php foreach ($seasons as $season): ?>
                                                    <option value="<?php echo $season->id; ?>" <?php echo ($selected_season == $season->id) ? 'selected' : ''; ?>>
                                                        <?php echo htmlspecialchars($season->title); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-md-3 control-label">Day</label>
                                        <div class="col-md-6">
                                            <input type="text" name="day" class="form-control" placeholder="e.g., 1 or A" value="<?php echo htmlspecialchars($bts->day ?? ''); ?>" required>
                                        </div>
                                    </div>
                                     <div class="form-group">
                                        <label class="col-md-3 control-label">Display Note</label>
                                        <div class="col-md-6">
                                            <textarea name="display_note" class="form-control" rows="3"><?php echo htmlspecialchars($bts->display_note ?? ''); ?></textarea>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-md-3 control-label">Video URL</label>
                                        <div class="col-md-6">
                                            <input type="text" name="video_url" class="form-control" placeholder="e.g., explore.mp4" value="<?php echo htmlspecialchars($bts->video_url ?? ''); ?>">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-md-3 control-label">Sort Order</label>
                                        <div class="col-md-6">
                                            <input type="number" name="short_order" class="form-control" value="<?php echo htmlspecialchars((string)($bts->short_order ?? 0)); ?>">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-md-3 control-label">Title Image</label>
                                        <div class="col-md-6">
                                            <input type="file" name="title_image" class="form-control" <?php echo $is_edit ? '' : 'required'; ?>>
                                            <span class="help-block">This is the main image displayed next to the title. Required for new entries.</span>
                                            <?php if ($is_edit && $bts->screenshot): ?>
                                                <div class="thumbnail" style="margin-top: 10px; width: 150px;">
                                                    <?php 
                                                    // Check if it's an S3 URL or local path
                                                    $screenshotPath = $bts->screenshot;
                                                    if (strpos($screenshotPath, 'http') === 0) {
                                                        // S3 URL or full URL
                                                        $imageUrl = $screenshotPath;
                                                    } else {
                                                        // Local path - construct proper URL
                                                        $imageUrl = $base_url . 'uploads/bts/' . $screenshotPath;
                                                    }
                                                    ?>
                                                    <img src="<?php echo htmlspecialchars($imageUrl); ?>"
                                                         onerror="this.src='<?php echo $base_url; ?>assets/admin/layout/img/no-image.png';" />
                                                    <div class="caption">
                                                        <p>Current Image</p>
                                                    </div>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label class="col-md-3 control-label">Image Gallery</label>
                                        <div class="col-md-6">
                                            <input type="file" name="images[]" multiple class="form-control">
                                            <span class="help-block">You can select multiple new images for the gallery.</span>
                                        </div>
                                    </div>

                                    <?php if ($is_edit && $bts->images->isNotEmpty()): ?>
                                    <div class="form-group">
                                        <label class="col-md-3 control-label">Existing Gallery Images</label>
                                        <div class="col-md-9">
                                            <div class="row">
                                                <?php foreach ($bts->images as $image): ?>
                                                    <div class="col-md-3 col-sm-4 col-xs-6" style="margin-bottom: 15px;" id="image-container-<?php echo $image->id; ?>">
                                                        <div class="thumbnail">
                                                            <?php 
                                                            // Check if it's an S3 URL or local path
                                                            $galleryImagePath = $image->image;
                                                            if (strpos($galleryImagePath, 'http') === 0) {
                                                                // S3 URL or full URL
                                                                $imageUrl = $galleryImagePath;
                                                            } else {
                                                                // Local path - construct proper URL
                                                                $imageUrl = $base_url . 'uploads/bts/' . $galleryImagePath;
                                                            }
                                                            ?>
                                                            <img src="<?php echo htmlspecialchars($imageUrl); ?>" style="height: 100px; width: auto; max-width: 100%;"
                                                                 onerror="this.src='<?php echo $base_url; ?>assets/admin/layout/img/no-image.png';" />
                                                            <div class="caption text-center">
                                                                <a href="#" class="btn btn-danger btn-xs delete-image-btn" data-image-id="<?php echo $image->id; ?>" role="button">Delete</a>
                                                            </div>
                                                        </div>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                            <span class="help-block">Previously uploaded images. Click delete to remove an image.</span>
                                        </div>
                                    </div>
                                    <?php endif; ?>
                                    <div class="form-group">
                                        <label class="col-md-3 control-label">Status</label>
                                        <div class="col-md-9">
                                            <input type="checkbox" name="status" class="make-switch" data-on-text="&nbsp;Active&nbsp;" data-off-text="&nbsp;Inactive&nbsp;" <?php echo ($bts->status ?? 0) == 1 ? 'checked' : ''; ?>>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-actions">
                                    <div class="row">
                                        <div class="col-md-offset-3 col-md-9">
                                            <button type="submit" class="btn green">Submit</button>
                                            <a href="<?php echo $base_url; ?>all-bts.php" class="btn default">Cancel</a>
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
<script src="<?php echo $base_url; ?>assets/global/plugins/jquery.min.js" type="text/javascript"></script>
<script src="<?php echo $base_url; ?>assets/global/plugins/jquery-migrate.min.js" type="text/javascript"></script>
<script src="<?php echo $base_url; ?>assets/global/plugins/jquery-ui/jquery-ui.min.js" type="text/javascript"></script>
<script src="<?php echo $base_url; ?>assets/global/plugins/bootstrap/js/bootstrap.min.js" type="text/javascript"></script>
<script src="<?php echo $base_url; ?>assets/global/plugins/bootstrap-hover-dropdown/bootstrap-hover-dropdown.min.js" type="text/javascript"></script>
<script src="<?php echo $base_url; ?>assets/global/plugins/jquery-slimscroll/jquery.slimscroll.min.js" type="text/javascript"></script>
<script src="<?php echo $base_url; ?>assets/global/plugins/jquery.blockui.min.js" type="text/javascript"></script>
<script src="<?php echo $base_url; ?>assets/global/plugins/jquery.cokie.min.js" type="text/javascript"></script>
<script src="<?php echo $base_url; ?>assets/global/plugins/uniform/jquery.uniform.min.js" type="text/javascript"></script>
<script src="<?php echo $base_url; ?>assets/global/plugins/bootstrap-switch/js/bootstrap-switch.min.js" type="text/javascript"></script>
<script src="<?php echo $base_url; ?>assets/global/scripts/metronic.js" type="text/javascript"></script>
<script src="<?php echo $base_url; ?>assets/admin/layout/scripts/layout.js" type="text/javascript"></script>
<script>
jQuery(document).ready(function() {    
   Metronic.init(); // init metronic core components
   Layout.init(); // init current layout
   $('.make-switch').bootstrapSwitch();

    // Handle image deletion via AJAX
    $('.delete-image-btn').on('click', function(e) {
        e.preventDefault();
        
        var $button = $(this);
        var imageId = $button.data('image-id');
        var container = $('#image-container-' + imageId);
        var csrfToken = '<?php echo $csrf_token; ?>';

        if (confirm('Are you sure you want to delete this image?')) {
            $.ajax({
                url: '<?php echo $base_url; ?>delete_bts_image.php',
                type: 'POST',
                data: {
                    image_id: imageId,
                    csrf_token: csrfToken
                },
                dataType: 'json',
                beforeSend: function() {
                    $button.prop('disabled', true).text('Deleting...');
                },
                success: function(response) {
                    if (response.status === 'success') {
                        container.fadeOut(300, function() { $(this).remove(); });
                    } else {
                        alert('Error: ' + response.message);
                        $button.prop('disabled', false).text('Delete');
                    }
                },
                error: function() {
                    alert('An unexpected error occurred. Please try again.');
                    $button.prop('disabled', false).text('Delete');
                }
            });
        }
    });
});
</script>
</body>
</html>