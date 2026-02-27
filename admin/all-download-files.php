<?php
require_once 'inc/requires.php';

use App\Models\DownloadFile;

// Check for a success message from a previous operation
$success_message = '';
if (isset($_SESSION['success_message'])) {
    $success_message = $_SESSION['success_message'];
    unset($_SESSION['success_message']);
}

// Fetch all download files, ordered by display_order
$files = DownloadFile::orderBy('display_order', 'asc')->get();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Download Files</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    <?php include 'includes/sidebar.php'; ?>

    <div class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                    <h2>All Download Files</h2>

                    <?php if ($success_message): ?>
                        <div class="alert alert-success"><?php echo htmlspecialchars($success_message); ?></div>
                    <?php endif; ?>

                    <div class="mb-3">
                        <a href="download-files/create" class="btn btn-primary">Add New File</a>
                    </div>

                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>Description</th>
                                <th>Filename</th>
                                <th>Status</th>
                                <th>Display Order</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($files->isEmpty()): ?>
                                <tr>
                                    <td colspan="6" class="text-center">No files found.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($files as $file): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($file->title); ?></td>
                                        <td><?php echo htmlspecialchars($file->description); ?></td>
                                        <td><?php echo htmlspecialchars($file->filename); ?></td>
                                        <td><?php echo $file->status ? 'Active' : 'Inactive'; ?></td>
                                        <td><?php echo htmlspecialchars($file->display_order); ?></td>
                                        <td>
                                            <a href="download-files/<?php echo $file->id; ?>/edit" class="btn btn-xs btn-primary">Edit</a>
                                            <form method="POST" action="download-files/delete" style="display:inline;">
                                                <input type="hidden" name="id" value="<?php echo $file->id; ?>">
                                                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                                                <button type="submit" class="btn btn-xs btn-danger" onclick="return confirm('Are you sure you want to delete this file?');">Delete</button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
</body>
</html>