<?php
echo "==============================================\n";
echo "     SUPABASE CONNECTION DIAGNOSTIC\n";
echo "==============================================\n\n";

// Check if .env exists
if (!file_exists('.env')) {
    die("ERROR: .env file not found!\n");
}

// Load .env manually
$env = parse_ini_file('.env', false, INI_SCANNER_RAW);

$host = $env['DB_HOST'] ?? 'unknown';
$port = $env['DB_PORT'] ?? '5432';
$database = $env['DB_DATABASE'] ?? 'postgres';
$username = $env['DB_USERNAME'] ?? 'postgres';
$password = $env['DB_PASSWORD'] ?? '';

echo "Database Configuration:\n";
echo "Host: $host\n";
echo "Port: $port\n";
echo "Database: $database\n";
echo "Username: $username\n";
echo "Password: " . (empty($password) ? "[NOT SET]" : "[SET - " . strlen($password) . " chars]") . "\n\n";

// Test DNS resolution methods
echo "Testing DNS Resolution:\n";
echo "-----------------------\n";

// Method 1: gethostbyname
echo "1. gethostbyname(): ";
$ip = gethostbyname($host);
if ($ip === $host) {
    echo "❌ FAILED\n";
} else {
    echo "✅ SUCCESS - IP: $ip\n";
}

// Method 2: dns_get_record
echo "2. dns_get_record(): ";
$dns_records = @dns_get_record($host, DNS_A);
if ($dns_records === false || empty($dns_records)) {
    echo "❌ FAILED\n";
} else {
    echo "✅ SUCCESS - IP: " . $dns_records[0]['ip'] . "\n";
}

// Method 3: Use IPv6
echo "3. IPv6 resolution: ";
$dns_records_v6 = @dns_get_record($host, DNS_AAAA);
if ($dns_records_v6 === false || empty($dns_records_v6)) {
    echo "❌ No IPv6 address\n";
} else {
    echo "✅ IPv6: " . $dns_records_v6[0]['ipv6'] . "\n";
}

echo "\nPossible Solutions:\n";
echo "-------------------\n";

if ($ip === $host) {
    echo "1. ⚠️ DNS RESOLUTION FAILED!\n";
    echo "   - Check your internet connection\n";
    echo "   - Verify Supabase project is active at: https://app.supabase.com\n";
    echo "   - Project might be PAUSED (inactive for 7+ days)\n";
    echo "   - Run EMERGENCY_FIX_SUPABASE.bat as Administrator\n\n";
    
    echo "2. Alternative: Use Local SQLite\n";
    echo "   - Run: use_local_database.bat\n";
    echo "   - This will switch to local database temporarily\n\n";
    
    echo "3. Check Supabase Project Status:\n";
    echo "   - Go to: https://app.supabase.com/project/wkqbukidxmzbgwauncrl\n";
    echo "   - If project is paused, click 'Restore' button\n";
    echo "   - Wait 2-3 minutes for DNS to propagate\n";
} else {
    echo "✅ DNS Resolution works! IP: $ip\n\n";
    
    // Try actual connection
    echo "Testing Database Connection:\n";
    echo "----------------------------\n";
    
    try {
        $dsn = "pgsql:host=$host;port=$port;dbname=$database;sslmode=require";
        $pdo = new PDO($dsn, $username, $password, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_TIMEOUT => 5,
        ]);
        
        echo "✅ CONNECTION SUCCESSFUL!\n";
        
        // Test query
        $stmt = $pdo->query("SELECT current_database(), version()");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        echo "Database: " . $result['current_database'] . "\n";
        echo "Version: " . substr($result['version'], 0, 50) . "...\n";
        
    } catch (PDOException $e) {
        echo "❌ CONNECTION FAILED!\n";
        echo "Error: " . $e->getMessage() . "\n\n";
        
        if (strpos($e->getMessage(), 'password') !== false) {
            echo "⚠️ Authentication failed - check DB_PASSWORD in .env\n";
        } elseif (strpos($e->getMessage(), 'timeout') !== false) {
            echo "⚠️ Connection timeout - Supabase might be paused\n";
        } elseif (strpos($e->getMessage(), 'SSL') !== false) {
            echo "⚠️ SSL issue - ensure sslmode=require in config/database.php\n";
        }
    }
}

echo "\n==============================================\n";
echo "If nothing works, your Supabase project ID might have changed.\n";
echo "Check: https://app.supabase.com/projects\n";
echo "==============================================\n";
