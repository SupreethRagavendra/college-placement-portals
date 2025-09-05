<?php
echo "Installing PostgreSQL driver for PHP...\n";

// Check current PHP version
$phpVersion = PHP_VERSION;
echo "PHP Version: $phpVersion\n";

// Check if pdo_pgsql is already available
if (extension_loaded('pdo_pgsql')) {
    echo "✅ PostgreSQL driver is already installed!\n";
    exit(0);
}

// Check available extensions
echo "Available PDO drivers: " . implode(', ', PDO::getAvailableDrivers()) . "\n";

// Try to load the extension
if (function_exists('dl')) {
    $extensions = [
        'pdo_pgsql',
        'pgsql',
        'php_pdo_pgsql',
        'php_pgsql'
    ];
    
    foreach ($extensions as $ext) {
        if (dl($ext)) {
            echo "✅ Successfully loaded $ext\n";
            break;
        }
    }
}

// Final check
if (extension_loaded('pdo_pgsql')) {
    echo "✅ PostgreSQL driver installed successfully!\n";
} else {
    echo "❌ Failed to install PostgreSQL driver automatically.\n";
    echo "Manual installation required:\n";
    echo "1. Download php_pdo_pgsql.dll for PHP $phpVersion\n";
    echo "2. Place it in your PHP ext folder\n";
    echo "3. Add 'extension=pdo_pgsql' to php.ini\n";
    echo "4. Restart your web server\n";
}
?>
