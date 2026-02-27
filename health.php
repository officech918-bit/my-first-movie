<?php
// Health check with database testing
require_once 'vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->safeLoad();

// Basic health response
http_response_code(200);
echo "OK\n";

// Database connection test
echo "\n=== Database Connection Test ===\n";

$host = $_ENV['DB_HOST'] ?? 'not_set';
$port = $_ENV['DB_PORT'] ?? 'not_set';
$dbname = $_ENV['DB_DATABASE'] ?? 'not_set';
$username = $_ENV['DB_USERNAME'] ?? 'not_set';
$password = $_ENV['DB_PASSWORD'] ?? 'not_set';

echo "Host: $host\n";
echo "Port: $port\n";
echo "Database: $dbname\n";
echo "Username: $username\n";

try {
    $dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4";
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
        PDO::ATTR_TIMEOUT => 5,
    ];
    
    $pdo = new PDO($dsn, $username, $password, $options);
    echo "✅ Database: Connected\n";
    
    // Test query
    $stmt = $pdo->query("SELECT VERSION() as version");
    $result = $stmt->fetch();
    echo "MySQL Version: " . $result['version'] . "\n";
    
} catch (PDOException $e) {
    echo "❌ Database Error: " . $e->getMessage() . "\n";
}

// Basic connectivity test
echo "\n=== Network Test ===\n";
$socket = @fsockopen($host, $port, $errno, $errstr, 3);
if ($socket) {
    echo "✅ Port $port: Reachable\n";
    fclose($socket);
} else {
    echo "❌ Port $port: Not reachable ($errno: $errstr)\n";
}

echo "\n=== Environment ===\n";
echo "PHP Version: " . PHP_VERSION . "\n";
echo "Time: " . date('Y-m-d H:i:s') . "\n";
?>
