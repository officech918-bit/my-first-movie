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

$stmt = $pdo->prepare("SELECT explanation, title FROM enrollments WHERE uid = :uid AND id = :id");
$stmt->execute([':uid' => $_SESSION['uid'], ':id' => $id]);
$expl = $stmt->fetch(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Explanation for <?php echo $expl ? e($expl['title']) : 'Enrollment'; ?></title>
    <link href="assets/global/plugins/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            padding: 20px;
            font-family: 'Open Sans', sans-serif;
            background-color: #f8f9fa; /* Light background for the page */
        }
        .explanation-card {
            max-width: 800px; /* Limit width for readability */
            margin: 30px auto; /* Center the card and add vertical margin */
            box-shadow: 0 4px 8px rgba(0,0,0,0.1); /* Subtle shadow for depth */
            border-radius: 8px; /* Rounded corners */
        }
        .card-title {
            font-size: 2.5rem; /* Larger title */
            margin-bottom: 1.5rem;
            color: #343a40;
        }
        .card-text {
            font-size: 1.1rem;
            line-height: 1.6;
            color: #495057;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="card explanation-card">
            <div class="card-body">
                <?php
                if ($expl) {
                    echo '<h1 class="card-title text-center">' . e($expl['title']) . '</h1>';
                    echo '<p class="card-text">' . $expl['explanation'] . '</p>'; // WARNING: Potential XSS vulnerability if $expl['explanation'] contains untrusted user input. Consider using an HTML purifier.
                } else {
                    echo '<p class="card-text text-center">Explanation not found.</p>';
                }
                ?>
            </div>
        </div>
    </div>
</body>
</html>