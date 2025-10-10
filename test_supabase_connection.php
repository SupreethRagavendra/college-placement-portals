<?php
// Test Supabase Database Connection
require __DIR__.'/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

echo "Testing Supabase Database Connection\n";
echo "====================================\n\n";

// Get database configuration from .env
$host = $_ENV['DB_HOST'] ?? 'db.wkqbukidxmzbgwauncrl.supabase.co';
$port = $_ENV['DB_PORT'] ?? '5432';
$database = $_ENV['DB_DATABASE'] ?? 'postgres';
$username = $_ENV['DB_USERNAME'] ?? '';
$password = $_ENV['DB_PASSWORD'] ?? '';

echo "Configuration:\n";
echo "Host: " . $host . "\n";
echo "Port: " . $port . "\n";
echo "Database: " . $database . "\n";
echo "Username: " . $username . "\n";
echo "Password: " . (empty($password) ? '[NOT SET]' : '[SET]') . "\n\n";

// Test DNS resolution
echo "Testing DNS Resolution:\n";
$ip = gethostbyname($host);
if ($ip === $host) {
    echo "❌ DNS resolution FAILED - Cannot resolve hostname\n";
    echo "\nPossible solutions:\n";
    echo "1. Check your internet connection\n";
    echo "2. Try using a different DNS server (8.8.8.8 or 1.1.1.1)\n";
    echo "3. Add this to your hosts file:\n";
    echo "   C:\\Windows\\System32\\drivers\\etc\\hosts\n";
    echo "   Add line: [SUPABASE_IP] db.wkqbukidxmzbgwauncrl.supabase.co\n";
    echo "4. Check if Supabase project is still active\n";
} else {
    echo "✅ DNS resolved to: " . $ip . "\n\n";
    
    // Try to connect
    echo "Testing Database Connection:\n";
    try {
        $dsn = "pgsql:host=$host;port=$port;dbname=$database;sslmode=require";
        $pdo = new PDO($dsn, $username, $password, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
        
        echo "✅ Connection SUCCESSFUL!\n";
        
        // Test query
        $stmt = $pdo->query("SELECT version()");
        $version = $stmt->fetch();
        echo "PostgreSQL Version: " . $version['version'] . "\n";
        
    } catch (PDOException $e) {
        echo "❌ Connection FAILED: " . $e->getMessage() . "\n";
        echo "\nCheck:\n";
        echo "1. Database credentials in .env file\n";
        echo "2. Supabase project is not paused\n";
        echo "3. Database password is correct\n";
    }
}

echo "\n";
