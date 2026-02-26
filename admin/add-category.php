<?php
declare(strict_types=1);

/**
 * Modernized Category Management
 * PHP 8.2+ Compatible
 */

session_start();

// Set Timezone
date_default_timezone_set('Asia/Kolkata');

// 1. Dependency Injection & Autoloading (Simulated)
require_once 'inc/requires.php';

// Assuming $database is now a PDO instance and $user is our auth object
$db = new Database(); // Your PDO wrapper
$user = new Visitor();

// 2. Authentication & Authorization Check
if (!$user->check_session()) {
    header("Location: index.php");
    exit();
}

// Access Control
$is_admin = in_array($_SESSION['user_type'] ?? '', ['webmaster', 'admin'], true);
if (!$is_admin) {
    die("Unauthorized access.");
}

// 3. CSRF Token Management
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];

// 4. Initialization
$categoryTitle = '';
$notifications = [
    'error'   => null,
    'success' => null
];

// 5. POST Request Handling
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Validate CSRF
    $providedToken = $_POST['csrf_token'] ?? '';
    if (!hash_equals($csrf_token, $providedToken)) {
        $notifications['error'] = "Security validation failed. Please refresh and try again.";
    } else {
        
        // Sanitize and Validate Input
        $categoryTitle = trim($_POST['title'] ?? '');

        if (empty($categoryTitle)) {
            $notifications['error'] = "Category name is required.";
        } elseif (strlen($categoryTitle) > 255) {
            $notifications['error'] = "Category name is too long.";
        } else {
            try {
                // Check for duplicates using PDO
                $checkStmt = $db->prepare("SELECT id FROM categories WHERE title = :title LIMIT 1");
                $checkStmt->execute(['title' => $categoryTitle]);

                if ($checkStmt->fetch()) {
                    $notifications['error'] = "A category with this name already exists.";
                } else {
                    // Insert using PDO
                    $insertStmt = $db->prepare("INSERT INTO categories (title, created_at) VALUES (:title, NOW())");
                    $result = $insertStmt->execute(['title' => $categoryTitle]);

                    if ($result) {
                        $notifications['success'] = "Category '<strong>" . htmlspecialchars($categoryTitle) . "</strong>' added successfully!";
                        $categoryTitle = ''; // Reset on success
                    }
                }
            } catch (PDOException $e) {
                // Log error safely, don't show raw DB errors to users
                error_log("Database Error: " . $e->getMessage());
                $notifications['error'] = "A system error occurred. Please try again later.";
            }
        }
    }
}

// 6. View Rendering
include 'inc/header.php';
include 'inc/sidebar.php';
?>

<div class="content-wrapper">
    <section class="content-header">
        <h1>
            Add New Category
            <small>Version 2.0</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="dashboard.php"><i class="fa fa-dashboard"></i> Home</a></li>
            <li><a href="all-categories.php">Categories</a></li>
            <li class="active">Add Category</li>
        </ol>
    </section>

    <section class="content">
        <div class="row">
            <div class="col-md-8 col-md-offset-2">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">Category Details</h3>
                    </div>

                    <form role="form" method="POST" action="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>">
                        <div class="box-body">
                            
                            <?php if ($notifications['error']): ?>
                                <div class="alert alert-danger alert-dismissible">
                                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                                    <h4><i class="icon fa fa-ban"></i> Error</h4>
                                    <?= $notifications['error'] ?>
                                </div>
                            <?php endif; ?>

                            <?php if ($notifications['success']): ?>
                                <div class="alert alert-success alert-dismissible">
                                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                                    <h4><i class="icon fa fa-check"></i> Success</h4>
                                    <?= $notifications['success'] ?>
                                </div>
                            <?php endif; ?>

                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                            
                            <div class="form-group <?= $notifications['error'] ? 'has-error' : '' ?>">
                                <label for="title">Category Name</label>
                                <input type="text" 
                                       class="form-control" 
                                       id="title" 
                                       name="title" 
                                       placeholder="e.g. Technology" 
                                       value="<?= htmlspecialchars($categoryTitle) ?>" 
                                       required 
                                       autofocus>
                                <p class="help-block">Keep names unique and descriptive.</p>
                            </div>
                        </div>

                        <div class="box-footer">
                            <button type="submit" class="btn btn-primary">
                                <i class="fa fa-plus"></i> Save Category
                            </button>
                            <a href="all-categories.php" class="btn btn-default pull-right">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>
</div>

<?php include 'inc/footer.php'; ?>