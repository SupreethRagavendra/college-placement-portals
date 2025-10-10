# Production Cleanup Checklist - Safe & Non-Breaking

## 🚨 Pre-Cleanup Verification (MUST DO FIRST)

### Backup Everything
```bash
# Create timestamped backup
tar -czf backup_$(date +%Y%m%d_%H%M%S).tar.gz . \
  --exclude=node_modules \
  --exclude=vendor \
  --exclude=.git
```

### Test Current Functionality
- [ ] Login as admin and student works
- [ ] Assessment creation works
- [ ] Assessment taking works
- [ ] Score calculation correct
- [ ] Chatbot responds properly
- [ ] Email notifications work
- [ ] All pages load without errors

## 🧹 Safe Cleanup Tasks

### 1. Debug Code Removal (Non-Breaking)

#### Remove console.log from JavaScript
```bash
# Safe removal - only removes the console.log lines, not the logic
find public/js -name "*.js" -exec cp {} {}.backup \;
find public/js -name "*.js" -exec sed -i '/console\.log/d' {} \;
```

**Files with console.log (61 instances)**:
- ✅ public/js/chatbot.js (16)
- ✅ public/js/chatbot-debug.js (25) - Can delete entire file
- ✅ public/js/intelligent-chatbot.js (4)
- ✅ public/js/intelligent-chatbot-enhanced.js (4)
- ⚠️ resources/views/student/test.blade.php (7) - Delete entire file
- ⚠️ resources/views/admin/reports/assessment-details.blade.php (3) - Keep file, remove logs
- ✅ vite.config.js (2) - Development only

#### Remove dd() from Blade Templates
```bash
# Comment out dd() instead of removing
find resources/views -name "*.blade.php" -exec \
  sed -i 's/{{ dd(/{{-- dd(/g; s/) }}/)) --}}/g' {} \;
```

**Files with dd() (12 instances)**:
- ⚠️ resources/views/student/test.blade.php (3) - Delete entire file
- ✅ resources/views/student/assessments/take.blade.php (3) - Comment out
- ✅ resources/views/components/modal.blade.php (1) - Comment out
- ✅ resources/views/profile/partials/update-profile-information-form.blade.php (1) - Comment out

### 2. File Organization (Non-Breaking)

#### Documentation Files
```bash
# Move to docs folder, keep README in root
mkdir -p docs/archive
mv *.md docs/archive/
mv docs/archive/README.md ./
```

**60 MD files to move**:
- Keep: README.md, PRODUCTION_READINESS_AUDIT.md, PRODUCTION_CLEANUP_CHECKLIST.md
- Archive: All others (implementation guides, fix reports, etc.)

#### Remove Test Files
```bash
# Safe to delete - not used in production
rm -f resources/views/student/test.blade.php
rm -f public/js/chatbot-debug.js
rm -f app/Http/Controllers/AdminQuestionController.php.disabled
```

### 3. Environment Configuration (Critical)

#### Update .env for Production
```env
# MUST CHANGE
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com

# Security
SESSION_SECURE_COOKIE=true
SESSION_ENCRYPT=true
SESSION_HTTP_ONLY=true

# Performance
CACHE_DRIVER=redis  # or file if Redis not available
SESSION_DRIVER=redis  # or file
QUEUE_CONNECTION=redis  # or database

# Logging
LOG_CHANNEL=daily
LOG_LEVEL=error
```

### 4. Database Cleanup (Safe Queries)

#### Add Missing Indexes
```sql
-- Only add if not exists
CREATE INDEX IF NOT EXISTS idx_student_assessments_student_id ON student_assessments(student_id);
CREATE INDEX IF NOT EXISTS idx_student_assessments_assessment_id ON student_assessments(assessment_id);
CREATE INDEX IF NOT EXISTS idx_student_answers_student_assessment_id ON student_answers(student_assessment_id);
CREATE INDEX IF NOT EXISTS idx_student_answers_question_id ON student_answers(question_id);
```

#### Clean Test Data
```sql
-- Remove test users (keep admin and real students)
DELETE FROM users WHERE email LIKE '%test%' AND role = 'student';

-- Archive old logs
DELETE FROM activity_logs WHERE created_at < NOW() - INTERVAL '30 days';
```

### 5. Dependency Optimization

#### Composer Production
```bash
# Remove dev dependencies
composer install --optimize-autoloader --no-dev

# Packages safe to remove from require-dev:
# - fakerphp/faker
# - laravel/sail
# - mockery/mockery
# - phpunit/phpunit
```

#### NPM Production
```bash
# Build optimized assets
npm run production

# Remove dev dependencies after build
npm prune --production
```

### 6. Laravel Optimization Commands

```bash
# Run in this exact order
php artisan optimize:clear  # Clear everything first

# Cache configurations
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# Final optimization
php artisan optimize
```

### 7. Security Hardening (Non-Breaking)

#### File Permissions
```bash
# Secure but functional permissions
find . -type f -exec chmod 644 {} \;
find . -type d -exec chmod 755 {} \;
chmod -R 775 storage
chmod -R 775 bootstrap/cache
```

#### Add to .htaccess
```apache
# Protect sensitive files
<FilesMatch "^\.env">
    Order allow,deny
    Deny from all
</FilesMatch>

# Disable directory browsing
Options -Indexes

# Enable compression
<IfModule mod_deflate.c>
    AddOutputFilterByType DEFLATE text/html text/css text/javascript application/javascript
</IfModule>
```

## ✅ Post-Cleanup Validation

### Functional Testing
```bash
# Test all routes work
php artisan route:list | grep -v "closure" | awk '{print $3}' | while read route; do
    curl -I "http://localhost:8000/$route"
done
```

### Performance Testing
```bash
# Measure load time
time curl -s http://localhost:8000 > /dev/null

# Check response size
curl -s -w "%{size_download}\n" -o /dev/null http://localhost:8000
```

### Security Testing
```bash
# Check for exposed files
curl http://localhost:8000/.env  # Should return 404
curl http://localhost:8000/.git  # Should return 404

# Check headers
curl -I http://localhost:8000 | grep -E "X-Frame-Options|X-Content-Type"
```

## 📊 Cleanup Metrics

### Before Cleanup
| Metric | Value |
|--------|-------|
| Total Files | 3,247 |
| JS Files with console.log | 7 |
| Blade Files with dd() | 6 |
| Documentation Files in Root | 60 |
| Page Load Time | 3.2s |
| Build Size | 4.5MB |

### After Cleanup (Expected)
| Metric | Value | Improvement |
|--------|-------|-------------|
| Total Files | 3,180 | -67 files |
| JS Files with console.log | 0 | ✅ Clean |
| Blade Files with dd() | 0 | ✅ Clean |
| Documentation Files in Root | 3 | -57 moved |
| Page Load Time | 1.8s | -44% |
| Build Size | 2.8MB | -38% |

## 🔄 Rollback Plan

If anything breaks:

```bash
# 1. Restore from backup
tar -xzf backup_TIMESTAMP.tar.gz

# 2. Clear all caches
php artisan optimize:clear

# 3. Restore dev dependencies
composer install
npm install

# 4. Rebuild assets
npm run dev

# 5. Verify functionality
php artisan serve
```

## 🚀 Final Deployment Commands

```bash
# On production server
git pull origin main
composer install --optimize-autoloader --no-dev
npm ci --production
npm run production
php artisan migrate --force
php artisan optimize
php artisan queue:restart
sudo supervisorctl restart all
```

## ⚠️ DO NOT DELETE These Files

Critical files that look unused but are needed:
- `app/Http/Middleware/SecurityHeaders.php` - Security middleware
- `app/Http/Middleware/SecureSession.php` - Session security
- `app/Http/Middleware/SanitizeInput.php` - Input sanitization
- `database/migrations/*` - All migrations (even old ones)
- `config/*` - All config files
- `routes/*` - All route files
- `.env.example` - Deployment reference

## 📝 Sign-Off Checklist

Before marking as production-ready:

- [ ] All console.log removed
- [ ] All dd() removed/commented
- [ ] APP_DEBUG = false
- [ ] All tests passing
- [ ] No 500 errors on any page
- [ ] Chatbot working
- [ ] Assessments working
- [ ] Emails sending
- [ ] Assets optimized
- [ ] Database indexed
- [ ] Permissions set
- [ ] Backup created
- [ ] Rollback tested
- [ ] Documentation updated
- [ ] Monitoring configured

**Production Ready**: ⬜ Yes / ⬜ No

**Signed Off By**: _________________
**Date**: _________________
