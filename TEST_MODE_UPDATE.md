# Test Mode Update - Step by Step

## ✅ CHANGES APPLIED

1. **JavaScript polling:** 30 sec → **3 sec**
2. **Check before send:** Added
3. **Cache busting:** Added timestamp to JS file
4. **Debug logs:** Added console output
5. **Laravel cache:** Cleared

## 🧪 TESTING STEPS

### Step 1: Hard Refresh Browser
**DO THIS FIRST!**

**Windows/Linux:**
- Press `Ctrl + Shift + R` or `Ctrl + F5`

**Mac:**
- Press `Cmd + Shift + R`

### Step 2: Open Developer Console
1. Press `F12` (or right-click → Inspect)
2. Click **Console** tab
3. Refresh page again

### Step 3: Look for Console Messages
You should see:
```
🤖 Chatbot initialized - Mode checking every 3 seconds
🔄 Mode updated: 🟢 RAG Active {mode: "rag_active", color: "#10b981"}
```

### Step 4: Monitor Mode Changes
**Watch the console every 3 seconds**

If RAG is running:
```
🔄 Mode updated: 🟢 RAG Active
```

If RAG is stopped:
```
🔄 Mode updated: 🟡 Limited Mode
```

### Step 5: Test Message Send
1. Type a message
2. Click Send
3. Should see in console:
```
🔄 Mode updated: [current mode]
```

## 🔍 TROUBLESHOOTING

### If you DON'T see console messages:

**1. Clear browser cache completely:**
- Chrome: `Ctrl + Shift + Delete`
- Select "Cached images and files"
- Click "Clear data"

**2. Check JavaScript is loading:**
- Go to Network tab in DevTools
- Refresh page
- Look for `intelligent-chatbot-enhanced.js?v=`
- Should have a timestamp parameter

**3. Verify file path:**
Open: `http://localhost:8000/js/intelligent-chatbot-enhanced.js`
Search for: `console.log('🤖 Chatbot initialized`
Should find the text

**4. Try incognito mode:**
- Open new incognito/private window
- Go to your app
- Check console

### If mode is NOT updating:

**1. Check health endpoint:**
Open in browser: `http://localhost:8000/student/rag-health`

Should return JSON like:
```json
{
  "status": "limited",
  "rag_service": false,
  "mode": "limited"
}
```

**2. Check RAG service:**
Is python RAG running?
```bash
# Check if running
cd python-rag
# If not running, start it:
python main.py
```

**3. Check Laravel logs:**
```bash
tail -f storage/logs/laravel.log
```

## ✅ SUCCESS INDICATORS

When working properly, you'll see:

1. **Console on page load:**
   ```
   🤖 Chatbot initialized - Mode checking every 3 seconds
   ```

2. **Every 3 seconds:**
   ```
   🔄 Mode updated: [emoji] [mode name]
   ```

3. **On message send:**
   ```
   🔄 Mode updated: [emoji] [mode name]
   (appears immediately before sending)
   ```

4. **Visual indicator:**
   - Header color changes
   - Badge shows: 🟢/🟡/🔴 with text
   - Status dot pulses

## 📋 QUICK FIX COMMANDS

Run these if still not working:

```bash
# Clear all caches
php artisan cache:clear
php artisan view:clear
php artisan config:clear

# Then hard refresh browser
# Ctrl + Shift + R
```

---

**After hard refresh, mode should update every 3 seconds!** ⚡

