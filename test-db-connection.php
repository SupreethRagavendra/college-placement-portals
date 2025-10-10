<?php

echo "Testing Supabase Database Connection...\n";
echo "========================================\n\n";

$host = "db.wkqbukidxmzbgwauncrl.supabase.co";
$port = "5432";
$dbname = "postgres";
$user = "postgres";
$password = "Supreeeth24#";

echo "Host: $host\n";
echo "Port: $port\n";
echo "Database: $dbname\n\n";

// Test 1: Check if host resolves
echo "Test 1: DNS Resolution\n";
$ip = gethostbyname($host);
if ($ip === $host) {
    echo "❌ FAILED: Cannot resolve hostname\n";
    echo "This is the problem - DNS resolution is failing!\n\n";
} else {
    echo "✅ SUCCESS: Resolved to IP: $ip\n\n";
}

// Test 2: Try to connect
echo "Test 2: PostgreSQL Connection\n";
try {
    $dsn = "pgsql:host=$host;port=$port;dbname=$dbname;sslmode=require";
    $pdo = new PDO($dsn, $user, $password);
    echo "✅ SUCCESS: Connected to database!\n";
    
    // Test query
    $stmt = $pdo->query("SELECT current_database()");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "✅ Connected to database: " . $result['current_database'] . "\n";
    
} catch (PDOException $e) {
    echo "❌ FAILED: " . $e->getMessage() . "\n";
    echo "\nPossible causes:\n";
    echo "1. IPv6 connectivity issue\n";
    echo "2. Firewall blocking connection\n";
    echo "3. PHP pgsql extension not configured for IPv6\n";
    echo "4. Network/VPN blocking Supabase\n";
}

echo "\n========================================\n";
echo "Testing complete.\n";

