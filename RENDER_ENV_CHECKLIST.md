# Render Environment Variables Checklist

## Critical Environment Variables for Render

Make sure these are set in your Render Dashboard:

### 1. Application Key
```
APP_KEY=base64:fvFW2KjdWMyge7MU/UKWOLomIou4I9Wtxerq887cp4Y=
```
⚠️ **IMPORTANT**: Use YOUR app key from local `.env` file

### 2. Database Password
```
DB_PASSWORD=Supreeeth24#
```
⚠️ **IMPORTANT**: Use your actual Supabase password

### 3. Session Configuration (Already in render.yaml)
```
SESSION_DRIVER=database
SESSION_SAME_SITE=lax
SESSION_DOMAIN=.onrender.com
```

## How to Set in Render Dashboard

1. Go to https://dashboard.render.com
2. Select your `college-placement-portal` service
3. Go to **Environment** tab
4. Add/Update these variables:
   - `APP_KEY` → Use the value from your local `.env` file
   - `DB_PASSWORD` → `Supreeeth24#`

## Verify After Deployment

1. Check logs for database connection:
   ```
   ✅ Database connection established
   ✅ Laravel application ready!
   ```

2. Access login page:
   - URL: https://college-placement-portals.onrender.com/login
   - Should show login form (not 500 error)

3. Test login with:
   - Email: `admin@portal.com`
   - Password: `Admin@123`

## Troubleshooting

If still getting 500 error:

1. **Check Render Logs**:
   - Dashboard → Your Service → Logs tab
   - Look for "Database connection" and "Session" errors

2. **Manual Deploy**:
   - Dashboard → Your Service → Manual Deploy → Deploy Latest Commit

3. **Clear Caches** (in Render shell):
   ```bash
   php artisan config:clear
   php artisan cache:clear
   php artisan view:clear
   php artisan migrate --force
   ```

