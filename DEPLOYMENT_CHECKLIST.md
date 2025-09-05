# ✅ Render Deployment Checklist

Use this checklist to ensure a successful deployment of your College Placement Portal to Render.

## 📋 Pre-Deployment Checklist

### Repository Preparation
- [ ] All code is committed to GitHub repository
- [ ] `.env` file is not committed (check .gitignore)
- [ ] `render.yaml` file is created and committed
- [ ] `Dockerfile` is updated for Render
- [ ] `deploy.sh` script is created
- [ ] `.env.example` file exists with all required variables

### Environment Variables Preparation
- [ ] Gmail app password generated (or SendGrid API key ready)
- [ ] Supabase database credentials available
- [ ] Application key ready to generate
- [ ] All required environment variables documented

## 🚀 Deployment Steps

### Step 1: Create Render Account
- [ ] Sign up at [render.com](https://render.com)
- [ ] Verify email address
- [ ] Connect GitHub account

### Step 2: Create Web Service
- [ ] Click "New +" → "Web Service"
- [ ] Connect GitHub repository
- [ ] Select repository: `college-placement-portal`
- [ ] Choose branch: `main` (or your default branch)

### Step 3: Configure Build Settings
- [ ] **Name**: `college-placement-portal`
- [ ] **Environment**: `PHP`
- [ ] **Region**: Choose closest to your users
- [ ] **Plan**: `Free` (or upgrade if needed)
- [ ] **Build Command**: 
  ```bash
  composer install --no-dev --optimize-autoloader
  php artisan key:generate --force
  php artisan config:cache
  php artisan route:cache
  php artisan view:cache
  npm ci
  npm run build
  ```
- [ ] **Start Command**: 
  ```bash
  php artisan serve --host=0.0.0.0 --port=$PORT
  ```

### Step 4: Set Environment Variables
- [ ] `APP_NAME` = "College Placement Portal"
- [ ] `APP_ENV` = "production"
- [ ] `APP_DEBUG` = "false"
- [ ] `APP_URL` = "https://your-app-name.onrender.com"
- [ ] `LOG_CHANNEL` = "stderr"
- [ ] `DB_CONNECTION` = "pgsql"
- [ ] `DB_HOST` = "db.wkqbukidxmzbgwauncrl.supabase.co"
- [ ] `DB_PORT` = "5432"
- [ ] `DB_DATABASE` = "postgres"
- [ ] `DB_USERNAME` = "postgres"
- [ ] `DB_PASSWORD` = "Supreeeth24#"
- [ ] `DB_SSLMODE` = "require"
- [ ] `MAIL_MAILER` = "smtp"
- [ ] `MAIL_HOST` = "smtp.gmail.com" (or your SMTP service)
- [ ] `MAIL_PORT` = "587"
- [ ] `MAIL_USERNAME` = "your-email@gmail.com"
- [ ] `MAIL_PASSWORD` = "your-app-password"
- [ ] `MAIL_ENCRYPTION` = "tls"
- [ ] `MAIL_FROM_ADDRESS` = "your-email@gmail.com"
- [ ] `MAIL_FROM_NAME` = "${APP_NAME}"
- [ ] `CACHE_DRIVER` = "file"
- [ ] `SESSION_DRIVER` = "file"
- [ ] `QUEUE_CONNECTION` = "sync"

### Step 5: Deploy
- [ ] Click "Create Web Service"
- [ ] Wait for build to complete (5-10 minutes)
- [ ] Check build logs for any errors
- [ ] Note the deployment URL

### Step 6: Database Setup
- [ ] Go to service dashboard
- [ ] Click "Shell" tab
- [ ] Run: `php artisan migrate --force`
- [ ] Run: `php artisan db:seed --class=AdminSeeder --force`
- [ ] Verify database tables created

### Step 7: Test Deployment
- [ ] Visit your Render URL
- [ ] Test registration functionality
- [ ] Check if verification email is sent
- [ ] Test login with admin credentials:
  - Email: `admin@portal.com`
  - Password: `Admin@123`
- [ ] Test student registration and login
- [ ] Verify all pages load correctly

## 🧪 Post-Deployment Testing

### Functional Testing
- [ ] Home page loads
- [ ] Registration form works
- [ ] Email verification works
- [ ] Login works for both admin and student
- [ ] Dashboard loads for both roles
- [ ] All navigation links work
- [ ] Forms submit correctly
- [ ] Error handling works

### Performance Testing
- [ ] Page load times are acceptable
- [ ] No JavaScript errors in console
- [ ] CSS loads correctly
- [ ] Images load properly
- [ ] Mobile responsiveness works

### Security Testing
- [ ] HTTPS is enabled
- [ ] Sensitive data is not exposed
- [ ] CSRF protection works
- [ ] Authentication is required for protected routes
- [ ] Role-based access control works

## 🔧 Troubleshooting Common Issues

### Build Failures
- [ ] Check PHP version compatibility
- [ ] Verify all dependencies in composer.json
- [ ] Check build logs for specific errors
- [ ] Ensure all required files are committed

### Database Connection Issues
- [ ] Verify Supabase credentials
- [ ] Check SSL mode settings
- [ ] Ensure database is accessible
- [ ] Test connection in Shell

### Email Issues
- [ ] Verify SMTP credentials
- [ ] Check email service limits
- [ ] Test with different email providers
- [ ] Check email logs

### Asset Loading Issues
- [ ] Ensure npm run build completes
- [ ] Check file permissions
- [ ] Verify asset paths
- [ ] Clear browser cache

## 📊 Performance Optimization

### After Successful Deployment
- [ ] Enable caching: `php artisan config:cache`
- [ ] Optimize routes: `php artisan route:cache`
- [ ] Cache views: `php artisan view:cache`
- [ ] Optimize autoloader: `composer install --optimize-autoloader`

### Monitoring Setup
- [ ] Set up uptime monitoring
- [ ] Monitor response times
- [ ] Track error rates
- [ ] Set up log monitoring

## 🔒 Security Checklist

- [ ] Environment variables are secure
- [ ] No sensitive data in logs
- [ ] HTTPS is enforced
- [ ] Strong passwords used
- [ ] 2FA enabled on all services
- [ ] Regular security updates planned

## 📈 Scaling Considerations

### Free Tier Limitations
- [ ] 750 hours/month limit
- [ ] Sleeps after 15 minutes of inactivity
- [ ] 512MB RAM limit
- [ ] No custom domains on free tier

### Upgrade Options
- [ ] Starter plan: $7/month (always on)
- [ ] Standard plan: $25/month (better performance)
- [ ] Pro plan: $85/month (production ready)

## 🎯 Success Criteria

Your deployment is successful when:
- [ ] Application loads without errors
- [ ] All authentication features work
- [ ] Email verification functions properly
- [ ] Database operations work correctly
- [ ] Performance is acceptable
- [ ] Security measures are in place

## 📞 Support Resources

- [Render Documentation](https://render.com/docs)
- [Laravel Deployment Guide](https://laravel.com/docs/deployment)
- [Supabase Documentation](https://supabase.com/docs)
- [Gmail SMTP Setup](https://support.google.com/mail/answer/185833)

---

**Deployment URL**: `https://your-app-name.onrender.com`

**Admin Login**:
- Email: `admin@portal.com`
- Password: `Admin@123`

**Next Steps**: After successful deployment, consider setting up monitoring, backups, and regular maintenance procedures.
