# 🚀 START RAG CHATBOT - Quick Commands

## ⚡ Ultra-Quick Start (Copy & Paste)

### Windows PowerShell (Recommended)

```powershell
# Terminal 1 - Start RAG Service
cd python-rag
if (!(Test-Path venv)) { python -m venv venv }
.\venv\Scripts\Activate.ps1
if (!(Test-Path .env)) { Copy-Item config.env .env }
pip install -r requirements.txt
python main.py
```

```powershell
# Terminal 2 - Start Laravel (New Window)
php artisan serve
```

```powershell
# Terminal 3 - Run Tests (New Window)
cd python-rag
.\venv\Scripts\Activate.ps1
python test_openrouter_rag.py
```

---

### Windows CMD

```batch
REM Terminal 1 - RAG Service
cd python-rag
python -m venv venv
venv\Scripts\activate
copy config.env .env
pip install -r requirements.txt
python main.py
```

```batch
REM Terminal 2 - Laravel  
php artisan serve
```

```batch
REM Terminal 3 - Tests
cd python-rag
venv\Scripts\activate
python test_openrouter_rag.py
```

---

### Using Batch File (Easiest!)

```batch
REM Terminal 1
cd python-rag
start_openrouter_rag.bat

REM Terminal 2
php artisan serve

REM Terminal 3
cd python-rag
python test_openrouter_rag.py
```

---

## ✅ Verification Steps

### 1. Check RAG Service
```bash
curl http://localhost:8001/health
```

**Expected:**
```json
{
  "status": "healthy",
  "database": "connected",
  "primary_model": "qwen/qwen-2.5-72b-instruct:free"
}
```

### 2. Check Laravel
```bash
curl http://localhost:8000
```

**Expected:** HTML response (Laravel home page)

### 3. Open Chatbot
1. Browse to: `http://localhost:8000`
2. Login as student
3. Click chatbot icon (bottom-right)
4. Press `Ctrl+Shift+T` (enable debug)
5. Ask: **"What assessments are available?"**

**Expected Response:**
- Real assessment names shown
- Status: 🟢 RAG Active
- Debug shows: `[OpenRouter (Qwen) - RAG Active]`

---

## 🎯 Status Indicators Legend

| Emoji | Status | Meaning |
|-------|--------|---------|
| 🟢 | RAG Active (Primary) | Perfect - Using Qwen AI |
| 🟡 | RAG Fallback | Good - Using DeepSeek AI |
| 🟠 | Database Only | OK - No AI, database data |
| 🔴 | Offline | Limited - Static responses |

---

## 🐛 Troubleshooting

### Port 8001 Already in Use
```powershell
# Find process
netstat -ano | findstr :8001

# Kill it
taskkill /PID <process_id> /F

# Restart
cd python-rag
python main.py
```

### Missing Dependencies
```bash
cd python-rag
venv\Scripts\activate
pip install -r requirements.txt --upgrade
```

### Database Connection Failed
```bash
# Check .env file exists
ls python-rag/.env

# If not, create it
cd python-rag
copy config.env .env
```

### RAG Service Crashes
```bash
# Check logs
cat python-rag/rag_service.log

# Or view in PowerShell
Get-Content python-rag/rag_service.log -Tail 50
```

---

## 📋 Complete Test Checklist

Run through this checklist to verify everything works:

- [ ] RAG service starts without errors
- [ ] Laravel serves on port 8000
- [ ] Automated tests pass (8/8)
- [ ] Chatbot opens in browser
- [ ] Debug mode works (Ctrl+Shift+T)
- [ ] Status indicator shows 🟢
- [ ] Real assessments displayed
- [ ] Results query works
- [ ] Help queries work
- [ ] No console errors

---

## 🎉 You're Ready!

If all checks pass:
- ✅ RAG is working
- ✅ Database connected
- ✅ AI responding
- ✅ Status indicators active
- ✅ System is production-ready!

**Enjoy your intelligent RAG chatbot!** 🚀

---

**Quick Links:**
- Full Guide: `OPENROUTER_RAG_COMPLETE_GUIDE.md`
- Testing Guide: `RAG_TESTING_GUIDE.md`
- Cleanup Guide: `RAG_CLEANUP_SUMMARY.md`
- Summary: `RAG_IMPLEMENTATION_FINAL_SUMMARY.md`

