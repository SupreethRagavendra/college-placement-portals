# 🚨 QUICK FIX: Render 500 Error

## Immediate Actions (Do These Now!)

### 1. Set Environment Variables in Render Dashboard

Go to: https://dashboard.render.com → Your Service → **Environment** tab

**Copy and paste these variables:**

```
APP_NAME=College Placement Portal
APP_ENV=production
APP_DEBUG=false
APP_URL=https://college-placement-portals.onrender.com

DB_CONNECTION=pgsql
DB_HOST=db.wkqbukidxmzbgwauncrl.supabase.co
DB_PORT=5432
DB_DATABASE=postgres
DB_USERNAME=postgres
DB_PASSWORD=Supreeeth24#
DB_SSLMODE=require

LOG_CHANNEL=stderr
LOG_LEVEL=error

CACHE_DRIVER=file
SESSION_DRIVER=file
QUEUE_CONNECTION=sync

RAG_ENABLED=false
```

### 2. Generate APP_KEY

**Option A: Generate Locally**
```bash
php artisan key:generate --show
```
Copy the output and add it to Render Environment variables as `APP_KEY`

**Option B: Let Render Generate**
The `render.yaml` is already configured to auto-generate. Skip this step.

### 3. Deploy the Fixed Configuration

```bash
# Add all changes
git add .

# Commit
git commit -m "Fix Render deployment configuration"

# Push to trigger deployment
git push origin main
```

### 4. Monitor Deployment

1. Go to Render dashboard → **Logs** tab
2. Watch for: `✅ Build completed successfully!`
3. If build succeeds, wait for app to start
4. Visit: https://college-placement-portals.onrender.com

### 5. If Still Getting 500 Error

**Enable Debug Mode Temporarily:**

In Render Environment tab:
```
APP_DEBUG=true
LOG_LEVEL=debug
```

Then redeploy and check the error message on the website.

**⚠️ REMEMBER TO DISABLE DEBUG AFTER FIXING:**
```
APP_DEBUG=false
LOG_LEVEL=error
```

## Common Errors & Quick Fixes

### Error: "No application encryption key"
→ Add `APP_KEY` to environment variables (see step 2)

### Error: "Database connection failed"
→ Verify database credentials in environment variables
→ Check if Supabase database is running

### Error: "Permission denied"
→ The build script now handles this automatically
→ Redeploy after pushing the new `render-build.sh`

### Error: "Class not found"
→ In Render dashboard: Manual Deploy → **Clear build cache**
→ Then redeploy

## Verification Checklist

After deployment, verify:

- [ ] Build logs show: `✅ Build completed successfully!`
- [ ] No error messages in runtime logs
- [ ] Website loads (may take 30-60 seconds on first request)
- [ ] Can access login page
- [ ] Database connection works

## Need More Help?

See detailed guide: `RENDER_DEPLOYMENT.md`

---

**Files Changed:**
- ✅ `render.yaml` - Updated build configuration
- ✅ `render-build.sh` - New build script
- ✅ `.gitignore` - Allow necessary cache directories
- ✅ `storage/framework/cache/data/.gitkeep` - Ensure directory exists

**Next Steps:**
1. Set environment variables (above)
2. Commit and push changes
3. Monitor deployment
4. Test the application
