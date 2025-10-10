# Test LIMITED MODE - Quick Guide

## Quick Test (5 Minutes)

### Option 1: Test via Browser (Recommended)

1. **Start Laravel Server**
   ```bash
   php artisan serve
   ```

2. **Make sure RAG service is NOT running**
   - Don't start `python-rag` service
   - This forces LIMITED MODE

3. **Login as Student**
   - Go to: `http://localhost:8000`
   - Login with student credentials

4. **Open Chatbot**
   - Click on chatbot icon
   - Check header shows: `🟡 Limited Mode`

5. **Test These Queries:**
   ```
   Show available assessments
   What tests are available?
   Show my results
   ```

6. **Expected Result:**
   ```
   🟡 LIMITED MODE - Database Query Results:

   You have X assessment(s) available:

   📝 Assessment Name (Category) - XX minutes

   Click 'View Assessments' to start!
   ```

### Option 2: Test via Command Line

```bash
# Run automated test
php test-limited-mode-fix.php

# Run endpoint test
php test-chatbot-limited-mode-endpoint.php
```

Both should show all tests passing ✅

## What to Look For

### ✅ Success Indicators
- No SQL errors
- Shows assessment list
- Header shows "🟡 Limited Mode"
- Provides action buttons (View Assessments, View History)

### ❌ If You See Errors
- "column 'title' does not exist" → Fix not applied
- "SQLSTATE[42703]" → Database column mismatch
- Check that you're using the updated controller file

## Verify the Fix

### Check Controller Code
Look at `app/Http/Controllers/Student/OpenRouterChatbotController.php`:

**Line 368 should be:**
```php
->select('id', 'name', 'category', 'total_time')
```

**NOT:**
```php
->select('id', 'title', 'category', 'duration')  // ❌ OLD CODE
```

### Check Logs
```bash
# Watch for MODE 2 messages
tail -f storage/logs/laravel.log | grep "MODE 2"
```

Should see:
```
🟡 MODE 2: LIMITED MODE activated
🟡 MODE 2: Querying assessments from database
🟡 MODE 2: Assessment query result
```

## Troubleshooting

### Still Getting Errors?

1. **Clear Cache**
   ```bash
   php artisan cache:clear
   php artisan config:clear
   php artisan route:clear
   ```

2. **Restart Server**
   ```bash
   # Stop server (Ctrl+C)
   php artisan serve
   ```

3. **Check Database Connection**
   ```bash
   php artisan tinker
   >>> \App\Models\Assessment::count()
   ```

### Check Database Schema
```bash
php artisan tinker
>>> \DB::select("SELECT column_name FROM information_schema.columns WHERE table_name = 'assessments'");
```

Should include: `name`, `total_time`, `category`, `status`

## All Three Modes

| Mode | How to Trigger | Test |
|------|---------------|------|
| 🟢 RAG ACTIVE | Start RAG service | Ask complex questions |
| 🟡 LIMITED MODE | RAG off, Laravel on | "Show assessments" |
| 🔴 OFFLINE | Laravel off | Frontend shows error |

## Success Criteria ✅

- [ ] No SQL errors in chatbot
- [ ] Can query available assessments
- [ ] Can query student results
- [ ] Chatbot shows "🟡 Limited Mode"
- [ ] Response includes assessment names and durations
- [ ] Action buttons appear (View Assessments, View History)

---

**Ready to Test?**

```bash
# Start testing now!
php artisan serve
```

Then open: `http://localhost:8000` and test the chatbot!

