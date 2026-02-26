<?php
require_once 'inc/requires.php';

use App\Models\DownloadFile;

$file = new DownloadFile();
$is_edit = false;
$error_message = '';

// Check if we are editing an existing file
if (isset($_GET['id'])) {
    $file = DownloadFile::find($_GET['id']);
    if ($file) {
        $is_edit = true;
    } else {
        // Redirect or show an error if the file is not found
        header('Location: all-download-files.php');
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF token validation
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die('CSRF token validation failed.');
    }

    // Populate the file model with form data
    $file->title = $_POST['title'];
    $file->description = $_POST['description'];
    $file->status = isset($_POST['status']) ? 1 : 0;
    $file->display_order = $_POST['display_order'];

    // Handle file upload
    if (isset($_FILES['file']) && $_FILES['file']['error'] == UPLOAD_ERR_OK) {
        $upload_dir = '../uploads/downloads/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        $filename = time() . '_' . basename($_FILES['file']['name']);
        $filepath = $upload_dir . $filename;

        if (move_uploaded_file($_FILES['file']['tmp_name'], $filepath)) {
            $file->filename = $filename;
            $file->file_path = 'uploads/downloads/' . $filename;
            $file->file_type = $_FILES['file']['type'];
            $file->file_size = $_FILES['file']['size'];
        } else {
            $error_message = 'Failed to upload file.';
        }
    }

    if (empty($error_message)) {
        $file->save();
        $_SESSION['success_message'] = $is_edit ? 'File updated successfully.' : 'File added successfully.';
        header('Location: all-download-files.php');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $is_edit ? 'Edit' : 'Add'; ?> Download File</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <?php include 'inc/header.php'; ?>
<?php include 'inc/left-menu-admin.php'; ?>

    <div class="content">
        <div class="container-fluid">
            <h2><?php echo $is_edit ? 'Edit' : 'Add'; ?> Download File</h2>

            <?php if ($error_message): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($error_message); ?></div>
            <?php endif; ?>

            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                
                <div class="form-group">
                    <label for="title">Title</label>
                    <input type="text" class="form-control" id="title" name="title" value="<?php echo htmlspecialchars($file->title ?? ''); ?>" required>
                </div>

                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea class="form-control" id="description" name="description"><?php echo htmlspecialchars($file->description ?? ''); ?></textarea>
                </div>

                <div class="form-group">
                    <label for="file">File</label>
                    <input type="file" class="form-control-file" id="file" name="file" <?php echo $is_edit ? '' : 'required'; ?>>
                    <?php if ($is_edit && $file->filename): ?>
                        <p class="mt-2">Current file: <?php echo htmlspecialchars($file->filename); ?></p>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="display_order">Display Order</label>
                    <input type="number" class="form-control" id="display_order" name="display_order" value="<?php echo htmlspecialchars($file->display_order ?? 0); ?>" required>
                </div>

                <div class="form-group form-check">
                    <input type="checkbox" class="form-check-input" id="status" name="status" value="1" <?php echo ($file->status ?? 1) ? 'checked' : ''; ?>>
                    <label class="form-check-label" for="status">Active</label>
                </div>

                <button type="submit" class="btn btn-primary"><?php echo $is_edit ? 'Update' : 'Submit'; ?></button>
                <a href="all-download-files.php" class="btn btn-secondary">Cancel</a>
            </form>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
</body>
</html>