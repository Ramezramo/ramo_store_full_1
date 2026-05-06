#!/bin/bash
set -e

# Load or generate a persistent APP_KEY
APP_KEY_FILE=".app_key"
if [ -f "$APP_KEY_FILE" ]; then
    SAVED_KEY=$(cat "$APP_KEY_FILE")
    echo "✓ App key loaded from file"
else
    # Generate a fresh key and save it
    SAVED_KEY=$(php -r "echo 'base64:' . base64_encode(random_bytes(32));")
    echo "$SAVED_KEY" > "$APP_KEY_FILE"
    echo "✓ App key generated and saved"
fi

# Write .env from environment variables
cat > .env << ENVEOF
APP_NAME="Ramo Store"
APP_ENV=local
APP_DEBUG=true
APP_URL=https://${REPLIT_DEV_DOMAIN}
APP_KEY=${SAVED_KEY}

LOG_CHANNEL=stack
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=debug

DB_CONNECTION=pgsql
DB_HOST=${PGHOST}
DB_PORT=${PGPORT}
DB_DATABASE=${PGDATABASE}
DB_USERNAME=${PGUSER}
DB_PASSWORD=${PGPASSWORD}

BROADCAST_DRIVER=log
CACHE_DRIVER=file
FILESYSTEM_DISK=local
QUEUE_CONNECTION=sync
SESSION_DRIVER=file
SESSION_LIFETIME=120

MAIL_MAILER=log
MAIL_HOST=mailpit
MAIL_PORT=1025
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS="hello@example.com"
MAIL_FROM_NAME="Ramo Store"

SANCTUM_STATEFUL_DOMAINS=${REPLIT_DEV_DOMAIN}
SESSION_COOKIE=rms_session
SESSION_SECURE_COOKIE=true
SESSION_SAME_SITE=none
ENVEOF

echo "✓ .env written"

# Clear config/cache
php artisan config:clear || true
php artisan cache:clear || true
echo "✓ Cache cleared"

# Install npm deps if missing
if [ ! -d "node_modules" ]; then
    echo "Installing npm dependencies..."
    npm install
fi

# Build frontend assets
npm run build
echo "✓ Vite assets built"

# Import database if tables don't exist
TABLE_COUNT=$(PGPASSWORD=$PGPASSWORD psql -h $PGHOST -p $PGPORT -U $PGUSER -d $PGDATABASE -t -c "SELECT COUNT(*) FROM information_schema.tables WHERE table_schema='public' AND table_type='BASE TABLE';" 2>/dev/null | tr -d ' ')
echo "Existing tables: $TABLE_COUNT"

if [ "${TABLE_COUNT:-0}" -lt "5" ] 2>/dev/null; then
    echo "Importing database..."
    SQL_FILE="project_last_database_exported_1.sql.sql"
    if [ -f "$SQL_FILE" ]; then
        grep -v '\\restrict' "$SQL_FILE" | PGPASSWORD=$PGPASSWORD psql -h $PGHOST -p $PGPORT -U $PGUSER -d $PGDATABASE 2>&1 | grep -E "(ERROR|FATAL)" || true
        echo "✓ Database imported from $SQL_FILE"
    else
        echo "No SQL file found, running migrations instead..."
        php artisan migrate --force || true
        echo "✓ Migrations run"
    fi
else
    echo "✓ Database already populated (${TABLE_COUNT} tables)"
fi

# Create required storage directories
mkdir -p storage/framework/sessions storage/framework/views storage/framework/cache/data storage/app/public
mkdir -p storage/app/public/products/thumbnails storage/app/public/products/other_images storage/app/public/products/natural_images storage/app/public/products/variations
chmod -R 775 storage bootstrap/cache

# Fix storage symlink — remove real dir if it exists, then create proper symlink
if [ -d "public/storage" ] && [ ! -L "public/storage" ]; then
    cp -rn public/storage/. storage/app/public/ 2>/dev/null || true
    rm -rf public/storage
fi
ln -sfn ../storage/app/public public/storage
echo "✓ Storage symlink ready"

echo "✓ Starting Laravel on port 5000..."
exec php artisan serve --host=0.0.0.0 --port=5000
