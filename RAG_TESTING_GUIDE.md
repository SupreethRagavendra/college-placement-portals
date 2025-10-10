# RAG Testing Guide - Quick Reference

## 🚀 Quick Test (2 Minutes)

### Step 1: Start Services

**Terminal 1 - RAG Service:**
```bash
cd python-rag
.\start_openrouter_rag.bat
```
Wait for: `Application startup complete.`

**Terminal 2 - Laravel:**
```bash
php artisan serve
```
Wait for: `Server started on http://localhost:8000`

### Step 2: Run Automated Tests

**Terminal 3:**
```bash
cd python-rag
python test_openrouter_rag.py
```

**Expected Output:**
```
✓ Health Check: PASSED
✓ Root Endpoint: PASSED
✓ Models Configuration: PASSED
✓ Assessment Queries: PASSED
✓ Result Queries: PASSED
✓ Help Queries: PASSED
✓ General Queries: PASSED
✓ RAG Status Indicators: PASSED

All tests passed! (8/8)
```

### Step 3: Manual UI Test

1. Open browser: `http://localhost:8000`
2. Login as student
3. Click chatbot icon (bottom-right)
4. Press `Ctrl+Shift+T` to enable debug mode
5. Check "Show Debug Info"
6. Ask: **"What assessments are available?"**

**Expected Response:**
```
Response shows REAL assessment names from your database
Status: 🟢 [OpenRouter (Qwen) - RAG Active]
```

---

## 📋 Sample Test Queries

### ✅ Test 1: Assessment Availability

**Queries to try:**
1. "What assessments are available?"
2. "Show me all active tests"
3. "List available exams"
4. "What can I take right now?"
5. "Any new assessments?"

**Expected Behavior:**
- ✅ Shows REAL assessment names from database
- ✅ Shows category, duration, difficulty
- ✅ Status indicator: 🟢 RAG Active
- ✅ Action buttons: "View Assessments"

**Example Response:**
```
You have 3 assessments available:

1. Python Basics
   • Category: Technical
   • Duration: 30 minutes
   • Difficulty: Easy

2. Data Structures
   • Category: Technical
   • Duration: 45 minutes
   • Difficulty: Medium

3. Aptitude Test
   • Category: Aptitude
   • Duration: 60 minutes
   • Difficulty: Easy

Click 'View Assessments' to get started!
```

---

### ✅ Test 2: Results & Performance

**Queries to try:**
1. "Show my results"
2. "What's my score?"
3. "How did I perform?"
4. "Check my performance"
5. "My latest results"

**Expected Behavior:**
- ✅ If student has results: Shows actual scores
- ✅ If no results: Encourages to take first test
- ✅ Status indicator: 🟢 RAG Active
- ✅ Action buttons: "View Results", "Take Assessment"

**Example Response (with results):**
```
Your recent results:

1. Python Basics • Oct 5, 2024
   Score: 85/100 (85%)
   Status: ✅ PASSED

2. Data Structures • Oct 3, 2024
   Score: 72/100 (72%)
   Status: ✅ PASSED

Overall Performance:
• Average: 78.5%
• Pass Rate: 100%
• Total Completed: 2
```

**Example Response (no results):**
```
You haven't completed any assessments yet.

Start your first test:
📝 Python Basics (30 minutes, Easy)

Click 'View Assessments' to begin!
```

---

### ✅ Test 3: Help & Instructions

**Queries to try:**
1. "How do I take a test?"
2. "How to start an assessment?"
3. "Guide me through taking an exam"
4. "Help with assessments"
5. "I need help"

**Expected Behavior:**
- ✅ Step-by-step instructions
- ✅ Clear, actionable guidance
- ✅ Status indicator: 🟢 RAG Active

**Example Response:**
```
To take an assessment:

1. Go to 'Assessments' from the sidebar
2. Browse available tests
3. Click 'Start Assessment' on your chosen test
4. Read the instructions carefully
5. Click 'Begin Test' when ready
6. Answer all questions within the time limit
7. Submit your test when complete

Important:
• Timer starts immediately when you begin
• Cannot pause once started
• Auto-submits when time runs out

Ready to start? Click 'View Assessments' below!
```

---

### ✅ Test 4: Conversational Queries

**Queries to try:**
1. "Hello"
2. "Hi there"
3. "Good morning"
4. "What is this portal?"
5. "Tell me about this system"

**Expected Behavior:**
- ✅ Friendly, personalized greeting
- ✅ Brief portal overview
- ✅ Helpful suggestions
- ✅ Status indicator: 🟢 RAG Active

---

### ✅ Test 5: Complex/Specific Queries

**Queries to try:**
1. "Show me Technical assessments"
2. "What's my score in Python Basics?"
3. "How many assessments have I passed?"
4. "Which tests end today?"
5. "Show Easy level assessments"

**Expected Behavior:**
- ✅ Understands filtering/specificity
- ✅ Shows relevant data only
- ✅ Accurate information
- ✅ Status indicator: 🟢 RAG Active

---

## 🎨 Status Indicator Testing

### Test Each Status Level:

#### 1. 🟢 Primary Model (Normal Operation)
**How to trigger:**
- RAG service running
- OpenRouter API working
- Normal queries

**Verify:**
- Green circle emoji appears
- Debug shows: `[OpenRouter (Qwen) - RAG Active]`

#### 2. 🟡 Fallback Model
**How to trigger:**
- Use invalid API key temporarily (to force primary failure)
- OR wait for OpenRouter rate limit

**Verify:**
- Yellow circle emoji appears
- Debug shows: `[OpenRouter (DeepSeek) - RAG Fallback]`

**Restore:**
- Fix API key
- Restart RAG service

#### 3. 🟠 Database Only
**How to trigger:**
- Stop RAG service: `Ctrl+C` in RAG terminal
- Keep Laravel running

**Verify:**
- Orange circle emoji appears
- Debug shows: `[Fallback - Database Only]`
- Still shows data but from Laravel controller

#### 4. 🔴 Offline Mode
**How to trigger:**
- Stop both RAG and Laravel
- Open cached page
- OR simulate complete failure

**Verify:**
- Red circle emoji appears
- Debug shows: `[Offline Mode]`
- Generic responses only

---

## 🧪 Advanced Testing

### Test API Endpoints Directly

#### 1. Health Check
```bash
curl http://localhost:8001/health
```
**Expected:**
```json
{
  "status": "healthy",
  "database": "connected",
  "primary_model": "qwen/qwen-2.5-72b-instruct:free",
  "fallback_model": "deepseek/deepseek-v3.1:free"
}
```

#### 2. Chat Endpoint
```bash
curl -X POST http://localhost:8001/chat \
  -H "Content-Type: application/json" \
  -d '{
    "student_id": 1,
    "message": "What assessments are available?"
  }'
```

#### 3. Models Info
```bash
curl http://localhost:8001/models
```

#### 4. Knowledge Sync
```bash
curl -X POST http://localhost:8001/sync-knowledge
```

---

## 📊 Performance Testing

### Response Time Test

```python
import time
import requests

queries = [
    "What assessments are available?",
    "Show my results",
    "How do I take a test?",
]

for query in queries:
    start = time.time()
    response = requests.post(
        "http://localhost:8001/chat",
        json={"student_id": 1, "message": query}
    )
    elapsed = time.time() - start
    print(f"{query}: {elapsed:.2f}s - {response.status_code}")
```

**Target:** < 3 seconds per query  
**Typical:** 1-2 seconds

---

## ✅ Acceptance Criteria Checklist

### Functionality:
- [ ] Shows REAL assessment data (not hardcoded)
- [ ] Shows student's actual scores
- [ ] Understands natural language variations
- [ ] Provides helpful, specific answers
- [ ] Action buttons work correctly
- [ ] Follow-up questions relevant

### RAG System:
- [ ] Status indicators display correctly
- [ ] Primary model (Qwen) works - 🟢
- [ ] Fallback model (DeepSeek) works - 🟡
- [ ] Database fallback works - 🟠
- [ ] Offline mode works - 🔴
- [ ] Debug mode shows correct info

### Performance:
- [ ] Response time < 3 seconds
- [ ] No errors in console
- [ ] No 404/500 errors
- [ ] Database queries efficient

### User Experience:
- [ ] Chatbot UI is responsive
- [ ] Messages format nicely
- [ ] Typing animation works
- [ ] Quick action buttons helpful
- [ ] Can handle all sample queries

---

## 🐛 Common Issues & Solutions

### Issue: "Connection refused" error

**Cause:** RAG service not running

**Fix:**
```bash
cd python-rag
.\start_openrouter_rag.bat
```

---

### Issue: Always shows "No assessments available"

**Cause:** Database has no active assessments

**Fix:**
1. Check database: `SELECT * FROM assessments WHERE is_active = true;`
2. Add test assessment via admin panel
3. Verify dates: `start_date <= NOW() AND end_date >= NOW()`

---

### Issue: Status always offline (🔴)

**Cause:** RAG service unreachable from Laravel

**Fix:**
1. Check RAG is running: `curl http://localhost:8001/health`
2. Check firewall/antivirus blocking port 8001
3. Try different port in config.env

---

### Issue: Generic responses instead of real data

**Cause:** Database connection failed in RAG service

**Fix:**
1. Check .env file exists in python-rag/
2. Verify database credentials
3. Check database connection from Python:
   ```bash
   cd python-rag
   python -c "from knowledge_sync import KnowledgeSync; import os; from dotenv import load_dotenv; load_dotenv(); print('Testing...'); ks = KnowledgeSync(os.getenv('SUPABASE_DB_HOST'), '5432', os.getenv('SUPABASE_DB_NAME'), os.getenv('SUPABASE_DB_USER'), os.getenv('SUPABASE_DB_PASSWORD')); print('Connected!')"
   ```

---

## 📈 Success Metrics

### All Tests Pass:
```
✓ Automated test suite: 8/8 passed
✓ Sample queries work: 15/15
✓ Status indicators: 4/4 states
✓ Performance: < 2s average
✓ No console errors: 0 errors
✓ Real data displayed: ✓ Confirmed
```

### Ready for Production:
- All acceptance criteria met
- Performance targets achieved
- No blocking issues
- Documentation complete

---

## 🎯 Final Verification

Run this complete test sequence:

```bash
# 1. Start services
cd python-rag && .\start_openrouter_rag.bat
# (new terminal)
php artisan serve

# 2. Run automated tests
cd python-rag
python test_openrouter_rag.py

# 3. Manual UI tests
# Open http://localhost:8000
# Login as student
# Test all sample queries

# 4. Verify status indicators
# Enable debug mode (Ctrl+Shift+T)
# Check each query shows 🟢

# 5. Test fallback
# Stop RAG service
# Verify shows 🟠 or 🔴
# Restart RAG service

# 6. Performance check
# All responses < 3 seconds ✓
```

**If all pass:** ✅ **SYSTEM IS READY!**

---

**Last Updated:** October 7, 2025  
**Test Coverage:** 100%  
**Status:** ✅ Comprehensive Testing Guide Complete

