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

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: my-enrollments.php');
    exit();
}

if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
    die('CSRF token validation failed.');
}

if (!isset($_POST['id']) || !is_numeric($_POST['id'])) {
    header('Location: my-enrollments.php?status=delete_failed');
    exit();
}

$enrollment_id = (int)$_POST['id'];
$uid = (int)$_SESSION['uid'];

// First, get the enrollment details, especially the title for the directory name
$stmt = $pdo->prepare('SELECT title FROM enrollments WHERE id = :id AND uid = :uid');
$stmt->execute([':id' => $enrollment_id, ':uid' => $uid]);
$enrollment = $stmt->fetch(PDO::FETCH_ASSOC);

if ($enrollment) {
    $enrollment_title = $enrollment['title'];
    $enrollment_dir = __DIR__ . '/../uploads/' . $uid . '/' . $enrollment_title;

    // Securely delete the directory and its contents
    if (is_dir($enrollment_dir)) {
        // A recursive function to delete a directory and all its contents
        function deleteDir(string $dirPath): void
        {
            if (!is_dir($dirPath)) {
                return;
            }
            if (substr($dirPath, strlen($dirPath) - 1, 1) !== '/') {
                $dirPath .= '/';
            }
            $files = glob($dirPath . '*', GLOB_MARK);
            foreach ($files as $file) {
                if (is_dir($file)) {
                    deleteDir($file);
                } else {
                    unlink($file);
                }
            }
            rmdir($dirPath);
        }

        deleteDir($enrollment_dir);
    }

    // Delete the enrollment record from the database
    $stmt = $pdo->prepare('DELETE FROM enrollments WHERE id = :id AND uid = :uid');
    $stmt->execute([':id' => $enrollment_id, ':uid' => $uid]);

    header('Location: my-enrollments.php?status=deleted');
    exit();
}

// If the enrollment doesn't exist or doesn't belong to the user
header('Location: my-enrollments.php?status=auth_failed');
exit();