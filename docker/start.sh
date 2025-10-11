#!/bin/bash
set -e

echo "🚀 Starting Laravel application..."

# Force use of Supabase pooler connection for better reliability
export DB_HOST="aws-0-ap-south-1.pooler.supabase.com"
export DB_PORT="6543"
export DB_USERNAME="postgres.wkqbukidxmzbgwauncrl"
export DB_SSLMODE="require"
echo "📝 Using Supabase pooler connection"
echo "📍 DB_HOST: $DB_HOST"
echo "📍 DB_PORT: $DB_PORT"
echo "📍 DB_USERNAME: $DB_USERNAME"

# Test database connection with better error handling
echo "⏳ Testing database connection..."
MAX_RETRIES=10
RETRY_COUNT=0
DB_CONNECTED=false

while [ $RETRY_COUNT -lt $MAX_RETRIES ] && [ "$DB_CONNECTED" = "false" ]; do
    RETRY_COUNT=$((RETRY_COUNT + 1))
    echo "Connection attempt $RETRY_COUNT/$MAX_RETRIES..."
    
    # Try to connect using pg_isready first (if available)
    if command -v pg_isready &> /dev/null; then
        if pg_isready -h "$DB_HOST" -p "$DB_PORT" -U "$DB_USERNAME" -t 5 2>/dev/null; then
            DB_CONNECTED=true
            echo "✅ PostgreSQL server is ready"
        fi
    fi
    
    # Fallback to PHP connection test
    if [ "$DB_CONNECTED" = "false" ]; then
        if php -r "try { \$pdo = new PDO('pgsql:host=${DB_HOST};port=${DB_PORT};dbname=${DB_DATABASE};sslmode=${DB_SSLMODE}', '${DB_USERNAME}', '${DB_PASSWORD}'); echo 'connected'; } catch (Exception \$e) { exit(1); }" 2>/dev/null | grep -q "connected"; then
            DB_CONNECTED=true
            echo "✅ Database connection established via PHP"
        fi
    fi
    
    if [ "$DB_CONNECTED" = "false" ]; then
        echo "⏳ Waiting 5 seconds before retry..."
        sleep 5
    fi
done

if [ "$DB_CONNECTED" = "false" ]; then
    echo "⚠️  Could not establish database connection after $MAX_RETRIES attempts"
    echo "⚠️  Proceeding with application startup anyway..."
    echo "⚠️  Database operations may fail until connection is restored"
else
    echo "✅ Database connection verified"
fi

# Generate APP_KEY if not set
if [ -z "$APP_KEY" ] || [ "$APP_KEY" = "base64:" ]; then
    echo "🔑 Generating APP_KEY..."
    php artisan key:generate --force
    echo "⚠️  IMPORTANT: Save the generated APP_KEY to your Render environment variables!"
else
    echo "✅ APP_KEY is set"
fi

# Clear all caches
echo "🧹 Clearing caches..."
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear

# Run database migrations only if connected
if [ "$DB_CONNECTED" = "true" ]; then
    echo "🗄️  Running database migrations..."
    php artisan migrate --force --no-interaction || {
        echo "⚠️  Migration failed - will retry on next deployment"
    }
else
    echo "⚠️  Skipping migrations - database not available"
fi

# Cache configuration for production
echo "⚡ Caching configuration..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Create storage link
echo "🔗 Creating storage link..."
php artisan storage:link || true

# Set proper permissions
echo "🔐 Setting permissions..."
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

echo "✅ Laravel application ready!"
echo "🌐 Starting Nginx and PHP-FPM..."

# Start supervisor (which manages nginx and php-fpm)
exec /usr/bin/supervisord -c /etc/supervisord.conf
