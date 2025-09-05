<?php
/**
 * Database Connection Test Script
 * Tests the connection to Supabase PostgreSQL database
 */

require_once 'vendor/autoload.php';

// Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

echo "🔍 Testing Database Connection...\n";
echo "================================\n\n";

try {
    // Create PDO connection
    $dsn = sprintf(
        'pgsql:host=%s;port=%s;dbname=%s;sslmode=%s',
        $_ENV['DB_HOST'],
        $_ENV['DB_PORT'],
        $_ENV['DB_DATABASE'],
        $_ENV['DB_SSLMODE']
    );
    
    $pdo = new PDO($dsn, $_ENV['DB_USERNAME'], $_ENV['DB_PASSWORD'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    
    echo "✅ Database connection successful!\n";
    echo "📍 Host: " . $_ENV['DB_HOST'] . "\n";
    echo "📍 Port: " . $_ENV['DB_PORT'] . "\n";
    echo "📍 Database: " . $_ENV['DB_DATABASE'] . "\n";
    echo "📍 SSL Mode: " . $_ENV['DB_SSLMODE'] . "\n\n";
    
    // Test query
    $stmt = $pdo->query("SELECT version()");
    $version = $stmt->fetchColumn();
    echo "📊 PostgreSQL Version: " . $version . "\n\n";
    
    // Check if tables exist
    $tables = ['users', 'email_verification_tokens', 'migrations'];
    echo "🔍 Checking database tables...\n";
    
    foreach ($tables as $table) {
        $stmt = $pdo->prepare("SELECT EXISTS (SELECT FROM information_schema.tables WHERE table_name = ?)");
        $stmt->execute([$table]);
        $exists = $stmt->fetchColumn();
        
        if ($exists) {
            echo "✅ Table '$table' exists\n";
            
            // Get row count
            $stmt = $pdo->query("SELECT COUNT(*) FROM $table");
            $count = $stmt->fetchColumn();
            echo "   📊 Rows: $count\n";
        } else {
            echo "❌ Table '$table' does not exist\n";
        }
    }
    
    echo "\n🎉 Database connection test completed successfully!\n";
    
} catch (PDOException $e) {
    echo "❌ Database connection failed!\n";
    echo "Error: " . $e->getMessage() . "\n";
    echo "\n🔧 Troubleshooting:\n";
    echo "1. Check your .env file configuration\n";
    echo "2. Verify Supabase credentials\n";
    echo "3. Ensure PostgreSQL driver is installed\n";
    echo "4. Check network connectivity\n";
    exit(1);
} catch (Exception $e) {
    echo "❌ Unexpected error: " . $e->getMessage() . "\n";
    exit(1);
}
?>