# 🚀 How to Start Your Enhanced Chatbot - Simple Guide

## ⚡ Quick Start (2 Steps)

### Step 1: Start Laravel
Open Terminal 1 (PowerShell or CMD):
```bash
cd d:\project-mini\college-placement-portal
php artisan serve
```

✅ **Wait for:** `Laravel development server started: http://127.0.0.1:8000`

### Step 2: Start RAG Service
Open Terminal 2 (PowerShell or CMD):
```bash
cd d:\project-mini\college-placement-portal\python-rag
python main.py
```

✅ **Wait for:** `Application startup complete` and `Uvicorn running on http://0.0.0.0:8001`

---

## 🎯 What You Should See

### Terminal 1 (Laravel):
```
   INFO  Server running on [http://127.0.0.1:8000].

  Press Ctrl+C to stop the server
```

### Terminal 2 (RAG Service):
```
INFO:     Started server process
INFO:     Waiting for application startup.
INFO:     Application startup complete.
INFO:     Uvicorn running on http://0.0.0.0:8001 (Press CTRL+C to quit)
```

---

## 🌐 Test It Works

### 1. Test Laravel (Should show your site)
```
Open browser: http://localhost:8000
```

### 2. Test RAG Service (Should show JSON)
```
Open browser: http://localhost:8001/health
```

**Expected Response:**
```json
{
  "status": "healthy",
  "timestamp": "2025-10-08T...",
  "database": "connected",
  "primary_model": "qwen/qwen-2.5-72b-instruct:free"
}
```

### 3. Test Chatbot
```
1. Go to: http://localhost:8000/student/dashboard
2. Login as student
3. Click purple button (bottom-right)
4. Look at header - should be GREEN
5. Ask: "What assessments are available?"
6. Get AI response!
```

---

## 🎨 Visual Guide

### You Should See This:

```
┌──────────────────────────────────────────────────────────┐
│ 🤖 Placement Assistant      [🟢 RAG Active]   [×] [-]   │ ← GREEN HEADER
│ ● RAG Active                                             │
└──────────────────────────────────────────────────────────┘

[Welcome message with feature cards]

┌──────────────────────────────────────────────────────────┐
│ Type your message...                                 [>] │
└──────────────────────────────────────────────────────────┘
```

---

## 🐛 Troubleshooting

### Problem: "Port 8000 already in use"
**Solution:**
```bash
# Stop any existing PHP process
Get-Process php -ErrorAction SilentlyContinue | Stop-Process -Force

# Try again
php artisan serve
```

### Problem: "Port 8001 already in use"
**Solution:**
```bash
# Stop any existing Python process
Get-Process python -ErrorAction SilentlyContinue | Stop-Process -Force

# Try again
cd python-rag
python main.py
```

### Problem: Red header instead of green
**Cause:** RAG service not running  
**Solution:** Check Terminal 2, make sure `python main.py` is running

### Problem: Yellow header instead of green
**Cause:** RAG service crashed or not responding  
**Solution:**
1. Stop Terminal 2 (Ctrl+C)
2. Restart: `python main.py`
3. Wait for "Application startup complete"

### Problem: Can't find python
**Solution:**
```bash
# Use python3 if python doesn't work
cd python-rag
python3 main.py

# OR use full path
cd python-rag
C:\Users\[YourUsername]\AppData\Local\Programs\Python\Python311\python.exe main.py
```

---

## 📝 Common Issues

### Issue 1: RAG Service Shows Errors
```
Check these files exist:
- python-rag/main.py ✓
- python-rag/openrouter_client.py ✓
- python-rag/context_handler.py ✓
- python-rag/response_formatter.py ✓
- python-rag/knowledge_sync.py ✓
- python-rag/.env ✓
```

### Issue 2: Database Connection Failed
```
Check .env file in python-rag folder:
- SUPABASE_DB_HOST=aws-0-ap-south-1...
- SUPABASE_DB_PORT=5432
- SUPABASE_DB_NAME=postgres
- SUPABASE_DB_USER=[your user]
- SUPABASE_DB_PASSWORD=[your password]
```

### Issue 3: Chatbot Not Responding
```
1. Open browser console (F12)
2. Look for errors
3. Check Network tab
4. Verify /student/rag-chat returns 200 OK
```

---

## ✅ Success Checklist

Before declaring victory, verify:

- [ ] Terminal 1 shows "Server running on http://127.0.0.1:8000"
- [ ] Terminal 2 shows "Uvicorn running on http://0.0.0.0:8001"
- [ ] http://localhost:8000 opens your site
- [ ] http://localhost:8001/health shows {"status":"healthy"}
- [ ] Chatbot header is GREEN
- [ ] Chatbot badge shows "🟢 RAG Active"
- [ ] Can send messages
- [ ] Getting intelligent AI responses

---

## 🎯 Mode Testing

### Test All 3 Modes:

**Mode 1 (Green):**
```
Both terminals running → GREEN header → AI responses
```

**Mode 2 (Yellow):**
```
Stop Terminal 2 (RAG) → YELLOW header → Database responses
```

**Mode 3 (Red):**
```
Stop both terminals → RED header → Offline messages
```

---

## 💡 Pro Tips

### Keep Both Terminals Open
Don't close the terminals - your chatbot needs both services running!

### Use Batch File
Double-click `START_RAG_SERVICE.bat` to start RAG service automatically

### Check Health Often
```bash
# Quick health check
curl http://localhost:8001/health
```

### Watch for Errors
Keep an eye on both terminal windows for any error messages

---

## 🎉 You're Done!

If you see:
- ✅ GREEN header
- ✅ "🟢 RAG Active" badge
- ✅ AI responses working

**Congratulations! Your chatbot is fully operational!** 🎊

---

**Remember:** 
- Keep **both terminals open** while using the chatbot
- **Terminal 1** = Laravel (port 8000)
- **Terminal 2** = RAG Service (port 8001)

**Enjoy your intelligent AI-powered chatbot!** 🚀

