# 🚀 Render Deployment Guide for College Placement Portal

This guide will help you deploy your Laravel college placement portal to Render, a modern cloud platform that supports PHP applications.

## 📋 Prerequisites

Before deploying to Render, ensure you have:
- A GitHub repository with your project
- A Render account (free tier available)
- Your Supabase PostgreSQL database credentials
- SMTP email service credentials (Gmail, SendGrid, etc.)

## 🏗️ Render Configuration Files

### 1. Create `render.yaml` (Optional but Recommended)

Create a `render.yaml` file in your project root:

```yaml
services:
  - type: web
    name: college-placement-portal
    env: php
    plan: free
    buildCommand: |
      composer install --no-dev --optimize-autoloader
      php artisan key:generate --force
      php artisan config:cache
      php artisan route:cache
      php artisan view:cache
      npm ci
      npm run build
    startCommand: php artisan serve --host=0.0.0.0 --port=$PORT
    envVars:
      - key: APP_ENV
        value: production
      - key: APP_DEBUG
        value: false
      - key: LOG_CHANNEL
        value: stderr
```

### 2. Update Dockerfile for Render

Your existing Dockerfile needs minor adjustments for Render:

```dockerfile
FROM php:8.2-fpm

# Install system dependencies
RUN apt-get update && apt-get install -y \
    libpq-dev \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    nodejs \
    npm

# Install PHP extensions
RUN docker-php-ext-install pdo pdo_pgsql mbstring exif pcntl bcmath gd

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Copy composer files
COPY composer.json composer.lock ./

# Install PHP dependencies
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Copy package files
COPY package.json package-lock.json ./

# Install Node dependencies
RUN npm ci

# Copy application files
COPY . .

# Build assets
RUN npm run build

# Set permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html/storage \
    && chmod -R 755 /var/www/html/bootstrap/cache

# Expose port
EXPOSE 8000

# Start command
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]
```

## 🔧 Environment Variables Setup

In your Render dashboard, configure these environment variables:

### Required Environment Variables

```env
# Application
APP_NAME="College Placement Portal"
APP_ENV=production
APP_KEY=base64:your-generated-app-key
APP_DEBUG=false
APP_URL=https://your-app-name.onrender.com

# Database (Supabase PostgreSQL)
DB_CONNECTION=pgsql
DB_HOST=db.wkqbukidxmzbgwauncrl.supabase.co
DB_PORT=5432
DB_DATABASE=postgres
DB_USERNAME=postgres
DB_PASSWORD="Supreeeth24#"
DB_SSLMODE=require

# Email Configuration (Choose one)
# Option 1: Gmail SMTP
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="your-email@gmail.com"
MAIL_FROM_NAME="${APP_NAME}"

# Option 2: SendGrid (Recommended for production)
# MAIL_MAILER=smtp
# MAIL_HOST=smtp.sendgrid.net
# MAIL_PORT=587
# MAIL_USERNAME=apikey
# MAIL_PASSWORD=your-sendgrid-api-key
# MAIL_ENCRYPTION=tls
# MAIL_FROM_ADDRESS="noreply@yourdomain.com"
# MAIL_FROM_NAME="${APP_NAME}"

# Cache & Session
CACHE_DRIVER=file
SESSION_DRIVER=file
QUEUE_CONNECTION=sync

# Logging
LOG_CHANNEL=stderr
LOG_LEVEL=error
```

## 🚀 Step-by-Step Deployment Process

### Step 1: Prepare Your Repository

1. **Create `.env.example`** (if not exists):
```env
APP_NAME="College Placement Portal"
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_CONNECTION=pgsql
DB_HOST=
DB_PORT=5432
DB_DATABASE=
DB_USERNAME=
DB_PASSWORD=
DB_SSLMODE=require

MAIL_MAILER=smtp
MAIL_HOST=
MAIL_PORT=587
MAIL_USERNAME=
MAIL_PASSWORD=
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=
MAIL_FROM_NAME="${APP_NAME}"

CACHE_DRIVER=file
SESSION_DRIVER=file
QUEUE_CONNECTION=sync
LOG_CHANNEL=stderr
```

2. **Update `.gitignore`** to ensure sensitive files are not committed:
```gitignore
.env
.env.backup
.env.production
.phpunit.result.cache
Homestead.json
Homestead.yaml
auth.json
npm-debug.log
yarn-error.log
storage/app/*
!storage/app/.gitkeep
storage/framework/cache/*
!storage/framework/cache/.gitkeep
storage/framework/sessions/*
!storage/framework/sessions/.gitkeep
storage/framework/views/*
!storage/framework/views/.gitkeep
storage/logs/*
!storage/logs/.gitkeep
```

### Step 2: Deploy to Render

1. **Connect GitHub Repository**:
   - Go to [Render Dashboard](https://dashboard.render.com)
   - Click "New +" → "Web Service"
   - Connect your GitHub account
   - Select your repository

2. **Configure Build Settings**:
   - **Name**: `college-placement-portal`
   - **Environment**: `PHP`
   - **Region**: Choose closest to your users
   - **Branch**: `main` (or your default branch)
   - **Root Directory**: Leave empty (root of repo)
   - **Build Command**: 
     ```bash
     composer install --no-dev --optimize-autoloader
     php artisan key:generate --force
     php artisan config:cache
     php artisan route:cache
     php artisan view:cache
     npm ci
     npm run build
     ```
   - **Start Command**: 
     ```bash
     php artisan serve --host=0.0.0.0 --port=$PORT
     ```

3. **Set Environment Variables**:
   - Add all the environment variables listed above
   - **Important**: Generate a new `APP_KEY` using: `php artisan key:generate --show`

4. **Deploy**:
   - Click "Create Web Service"
   - Wait for the build to complete (5-10 minutes)

### Step 3: Database Setup

1. **Run Migrations**:
   After deployment, you need to run database migrations. You can do this by:
   
   **Option A: Using Render Shell** (Recommended):
   - Go to your service dashboard
   - Click "Shell"
   - Run: `php artisan migrate --force`
   - Run: `php artisan db:seed --class=AdminSeeder --force`

   **Option B: Create a one-time job**:
   - Create a new "Background Worker" service
   - Use the same repository
   - Build Command: `composer install --no-dev --optimize-autoloader`
   - Start Command: `php artisan migrate --force && php artisan db:seed --class=AdminSeeder --force`
   - Run once, then delete the service

### Step 4: Configure Custom Domain (Optional)

1. **Add Custom Domain**:
   - Go to your service settings
   - Click "Custom Domains"
   - Add your domain
   - Update DNS records as instructed

2. **Update Environment Variables**:
   - Update `APP_URL` to your custom domain
   - Redeploy the service

## 🔧 Post-Deployment Configuration

### 1. Verify Deployment

1. **Check Application**:
   - Visit your Render URL
   - Test registration and login
   - Verify email functionality

2. **Check Logs**:
   - Go to "Logs" tab in Render dashboard
   - Look for any errors or warnings

### 2. Performance Optimization

1. **Enable Caching**:
   ```bash
   # In Render Shell
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   ```

2. **Optimize Composer**:
   ```bash
   composer install --optimize-autoloader --no-dev
   ```

### 3. Security Considerations

1. **Environment Variables**:
   - Never commit `.env` file
   - Use strong passwords
   - Enable 2FA on all services

2. **Database Security**:
   - Use strong database passwords
   - Enable SSL connections
   - Regular backups

## 🚨 Troubleshooting

### Common Issues

1. **Build Failures**:
   - Check PHP version compatibility
   - Verify all dependencies in `composer.json`
   - Check build logs for specific errors

2. **Database Connection Issues**:
   - Verify Supabase credentials
   - Check SSL mode settings
   - Ensure database is accessible from Render

3. **Email Not Working**:
   - Verify SMTP credentials
   - Check email service limits
   - Test with different email providers

4. **Asset Loading Issues**:
   - Ensure `npm run build` completes successfully
   - Check file permissions
   - Verify asset paths in templates

### Debug Commands

```bash
# Check application status
php artisan about

# Clear all caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Check database connection
php artisan tinker
# Then run: DB::connection()->getPdo();

# Test email
php artisan tinker
# Then run: Mail::raw('Test', function($m) { $m->to('test@example.com')->subject('Test'); });
```

## 📊 Monitoring & Maintenance

### 1. Performance Monitoring

- Monitor response times in Render dashboard
- Set up uptime monitoring
- Track error rates

### 2. Regular Maintenance

- Update dependencies regularly
- Monitor storage usage
- Review and rotate API keys
- Backup database regularly

### 3. Scaling Considerations

- **Free Tier Limitations**:
  - 750 hours/month
  - Sleeps after 15 minutes of inactivity
  - 512MB RAM limit

- **Upgrade Options**:
  - Starter plan: $7/month
  - Standard plan: $25/month
  - Pro plan: $85/month

## 🎯 Production Checklist

- [ ] Environment variables configured
- [ ] Database migrations run
- [ ] Email service working
- [ ] SSL certificate active
- [ ] Custom domain configured
- [ ] Error monitoring set up
- [ ] Backup strategy implemented
- [ ] Performance optimized
- [ ] Security measures in place

## 📞 Support Resources

- [Render Documentation](https://render.com/docs)
- [Laravel Deployment Guide](https://laravel.com/docs/deployment)
- [Supabase Documentation](https://supabase.com/docs)

---

**Note**: The free tier of Render has limitations including sleep mode after inactivity. For production use, consider upgrading to a paid plan for better performance and reliability.
