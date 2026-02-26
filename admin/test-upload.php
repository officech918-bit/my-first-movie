<?php
// Simple upload test
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['test_file'])) {
    $uploadDir = __DIR__ . '/../uploads/categories';
    
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }
    
    $fileName = 'test_' . time() . '.jpg';
    $destPath = $uploadDir . '/' . $fileName;
    
    echo "Upload Dir: " . $uploadDir . "<br>";
    echo "Dest Path: " . $destPath . "<br>";
    echo "Temp File: " . $_FILES['test_file']['tmp_name'] . "<br>";
    echo "Upload Dir Exists: " . (is_dir($uploadDir) ? 'Yes' : 'No') . "<br>";
    echo "Upload Dir Writable: " . (is_writable($uploadDir) ? 'Yes' : 'No') . "<br>";
    echo "File Error: " . $_FILES['test_file']['error'] . "<br>";
    
    if (move_uploaded_file($_FILES['test_file']['tmp_name'], $destPath)) {
        echo "SUCCESS: File uploaded to " . $destPath;
    } else {
        echo "FAILED: Could not move file";
    }
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Upload Test</title>
</head>
<body>
    <h2>Upload Test</h2>
    <form method="POST" enctype="multipart/form-data">
        <input type="file" name="test_file" required>
        <button type="submit">Upload</button>
    </form>
</body>
</html>
