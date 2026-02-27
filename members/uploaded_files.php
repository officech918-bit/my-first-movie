<?php declare(strict_types=1);

/**
 * @author   closemarketing
 * @license  https://www.closemarketing.com/
 * @version  1.0.0
 * @since    1.0.0
 */

require_once __DIR__ . '/inc/requires.php';

if (!$user->check_session() || !$user->isActive()) {
    header('Location: index.php');
    exit();
}

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id) {
    die("Invalid request: Enrollment ID is missing or invalid.");
}

$stmt = $pdo->prepare("SELECT title, no_of_files FROM enrollments WHERE uid = :uid AND id = :id");
$stmt->execute([':uid' => $_SESSION['uid'], ':id' => $id]);
$enrollment = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$enrollment) {
    die("Enrollment not found or you do not have permission to view it.");
}

$nos = (int)$enrollment['no_of_files'];
$category = $enrollment['title'];

// Securely build the path
$base_path = realpath(__DIR__ . '/../uploads');
$user_path = $base_path . DIRECTORY_SEPARATOR . $_SESSION['uid'];
$category_path = $user_path . DIRECTORY_SEPARATOR . $category;

if (!$base_path || !is_dir($user_path) || !is_dir($category_path)) {
    // This check prevents directory traversal.
    // realpath will return false if the path is invalid or doesn't exist.
    $enrollment_path = null;
} else {
    $enrollment_path = $category_path;
}

/**
 * Recursively displays files in a directory.
 *
 * @param string $path  The path to the directory.
 * @param int    $level The current recursion level for indentation.
 */
function display_files(string $path, int $level = 0): void
{
    $items = scandir($path);
    if ($items === false) {
        return;
    }

    foreach ($items as $item) {
        if ($item[0] === '.') {
            continue;
        }

        $fullPath = $path . DIRECTORY_SEPARATOR . $item;
        $displayName = str_repeat('&nbsp;', $level * 4) . e($item);

        if (is_file($fullPath)) {
            // We only provide a link relative to the script's location for security.
            // The absolute path is not exposed to the client.
            $relativePath = '..' . str_replace(realpath(__DIR__ . '/..'), '', realpath($fullPath));
            echo "<table width='100%'><tr>";
            echo "<td width='20%' align='right'>";
            echo '<a href="' . e($relativePath) . '" style="text-decoration:none;" target="_blank">View File ---</a>';
            echo "</td>";
            echo "<td align='left'>---> " . $displayName . "</td>";
            echo "</tr></table>";
        } elseif (is_dir($fullPath)) {
            echo "<br><strong>" . $displayName . "</strong>";
            display_files($fullPath, $level + 1);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Uploaded Files for <?php echo e($category); ?></title>
    <link href="assets/global/plugins/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            padding: 20px;
            font-family: 'Open Sans', sans-serif;
        }
        table {
            margin-top: 5px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h3>Uploaded Files for "<?php echo e($category); ?>"</h3>
        <?php
        if ($nos === 0) {
            echo '<div class="alert alert-info">You have not uploaded a single file for this enrollment.</div>';
        } else {
            $file_word = ($nos > 1) ? 'files' : 'file';
            echo '<p>You have uploaded ' . $nos . ' ' . $file_word . '. You may find them below:</p>';
            if ($enrollment_path && is_dir($enrollment_path)) {
                display_files($enrollment_path);
            } else {
                echo '<div class="alert alert-danger">Error: The directory for this enrollment could not be found.</div>';
            }
        }
        ?>
    </div>
</body>
</html>