# 🚀 Quick Setup Guide - Fix Supabase Connection

## ❌ Current Issue
You're getting: `cURL error 6: Could not resolve host: your-project.supabase.co`

This means your `.env` file is missing the Supabase configuration.

## ✅ Quick Fix

### Step 1: Update your `.env` file

Add these lines to your `.env` file:

```env
# Supabase Database Configuration
DB_CONNECTION=pgsql
DB_HOST=db.wkqbukidxmzbgwauncrl.supabase.co
DB_PORT=5432
DB_DATABASE=postgres
DB_USERNAME=postgres
DB_PASSWORD="Supreeeth24#"
DB_SSLMODE=require

# Supabase Configuration - GET THESE FROM YOUR SUPABASE DASHBOARD
SUPABASE_URL=https://wkqbukidxmzbgwauncrl.supabase.co
SUPABASE_ANON_KEY=your_supabase_anon_key_here
SUPABASE_SERVICE_ROLE_KEY=your_supabase_service_role_key_here
```

### Step 2: Get Your Supabase API Keys

1. **Go to your Supabase Dashboard:**
   - Visit: https://supabase.com/dashboard/project/wkqbukidxmzbgwauncrl
   - Or go to https://supabase.com/dashboard and select your project

2. **Navigate to Settings → API**

3. **Copy the keys:**
   - **anon public** key → Replace `your_supabase_anon_key_here`
   - **service_role** key → Replace `your_supabase_service_role_key_here`

### Step 3: Clear Laravel Cache

```bash
php artisan config:clear
php artisan cache:clear
```

### Step 4: Test the Connection

1. **Test Database Connection:**
   ```
   Visit: http://127.0.0.1:8000/test-db
   ```

2. **Test Registration:**
   ```
   Visit: http://127.0.0.1:8000/register
   ```

## 🔍 Example .env Configuration

Your `.env` file should look like this:

```env
APP_NAME="College Placement Portal"
APP_ENV=local
APP_KEY=base64:YourAppKeyHere123456789012345678901234567890=
APP_DEBUG=true
APP_URL=http://127.0.0.1:8000

# Supabase Database
DB_CONNECTION=pgsql
DB_HOST=db.wkqbukidxmzbgwauncrl.supabase.co
DB_PORT=5432
DB_DATABASE=postgres
DB_USERNAME=postgres
DB_PASSWORD="Supreeeth24#"
DB_SSLMODE=require

# Supabase API Keys (GET THESE FROM SUPABASE DASHBOARD)
SUPABASE_URL=https://wkqbukidxmzbgwauncrl.supabase.co
SUPABASE_ANON_KEY=eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...your_actual_key
SUPABASE_SERVICE_ROLE_KEY=eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...your_actual_key
```

## 🎯 What to Look For

### ✅ Correct Supabase URL Format:
- ✅ `https://wkqbukidxmzbgwauncrl.supabase.co`
- ❌ `https://your-project.supabase.co`

### ✅ API Keys Format:
- Should start with `eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...`
- Should be long strings (usually 100+ characters)

## 🚨 Common Issues

1. **"Could not resolve host"** → Wrong SUPABASE_URL
2. **"Invalid API key"** → Wrong or missing API keys
3. **"Connection failed"** → Wrong database credentials

## 📞 Still Having Issues?

1. **Check Laravel logs:**
   ```bash
   tail -f storage/logs/laravel.log
   ```

2. **Verify Supabase project is active:**
   - Go to your Supabase dashboard
   - Make sure the project is not paused

3. **Test with curl:**
   ```bash
   curl -I https://wkqbukidxmzbgwauncrl.supabase.co
   ```

## ✅ Success!

Once configured correctly, you should be able to:
- ✅ Register new users
- ✅ Receive email verification
- ✅ Login after verification
- ✅ Admin can approve/reject students

Your College Placement Portal will be fully functional!
