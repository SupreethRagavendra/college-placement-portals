# 🚦 CHATBOT MODES - QUICK REFERENCE CARD

## 📊 Three Modes Overview

| Mode | Indicator | Laravel | RAG Service | Database | AI | Speed |
|------|-----------|---------|-------------|----------|----|----|
| **Mode 1** | 🟢 GREEN | ✅ Running | ✅ Running | ✅ Yes | ✅ Yes | 2-4s |
| **Mode 2** | 🟡 YELLOW | ✅ Running | ❌ Down | ✅ Yes | ❌ No | <1s |
| **Mode 3** | 🔴 RED | ❌ Down | ❌ Down | ❌ No | ❌ No | Instant |

---

## 🟢 Mode 1: RAG ACTIVE

**Status:** Full Power
**What Works:**
- ✅ AI-generated intelligent responses
- ✅ Vector semantic search
- ✅ Context-aware answers
- ✅ Performance analysis
- ✅ Personalized recommendations
- ✅ Database queries
- ✅ Real-time student data

**Visual:**
```
┌─────────────────────────────────────┐
│ 🤖 Placement Assistant    🟢 Online │ ← GREEN header
│ Mode 1: RAG ACTIVE                  │
└─────────────────────────────────────┘
```

**Example Response:**
```
Based on your recent performance in Technical assessments,
I notice you're strong in JavaScript (85%) but struggling 
with PHP (58%). I recommend:

1. Focus on PHP loops and array functions
2. Practice 15-20 questions daily
3. Review your mistakes from Assessment #3

You have 2 new assessments available that can help improve
your weak areas.
```

---

## 🟡 Mode 2: LIMITED MODE ⭐ FIXED!

**Status:** Database Only
**What Works:**
- ✅ Real assessment data from database
- ✅ Student results and scores
- ✅ Pattern-based responses
- ✅ Navigation links
- ✅ Basic queries
- ❌ No AI generation
- ❌ No advanced analysis

**Visual:**
```
┌─────────────────────────────────────┐
│ 🤖 Placement Assistant  🟡 Limited  │ ← YELLOW header
│ Mode 2: LIMITED MODE                │
└─────────────────────────────────────┘
```

**Example Response:**
```
You have 3 assessment(s) available:

📝 PHP Programming (Technical) - 60 minutes
📝 JavaScript Basics (Technical) - 45 minutes
📝 Aptitude Test (Aptitude) - 30 minutes

Click 'View Assessments' to start!
```

**Supported Queries:**
- "What assessments are available?" → Shows real assessments from DB
- "Show my results" → Shows actual scores from DB
- "What's my score?" → Shows recent results
- "Help" → Shows navigation help

---

## 🔴 Mode 3: OFFLINE MODE

**Status:** Frontend Only
**What Works:**
- ✅ Basic static responses
- ✅ Navigation suggestions
- ❌ No database access
- ❌ No real data
- ❌ No AI

**Visual:**
```
┌─────────────────────────────────────┐
│ 🤖 Placement Assistant  🔴 Offline  │ ← RED header
│ Mode 3: OFFLINE MODE                │
└─────────────────────────────────────┘
```

**Example Response:**
```
⚠️ I'm currently in offline mode. Please check your 
connection or try again later.

You can still navigate the portal using the menu.
```

---

## 🧪 Quick Testing

### Test Current Mode
```bash
# Option 1: PowerShell Script
.\test_chatbot_modes.ps1

# Option 2: API Endpoint
curl http://localhost:8000/student/chatbot-mode-test

# Option 3: Browser
http://localhost:8000/student/chatbot-mode-test
```

### Force Specific Mode

**Force Mode 1 (Green):**
```bash
# Terminal 1
php artisan serve

# Terminal 2
cd python-rag
python rag_service.py
```

**Force Mode 2 (Yellow) - THE FIX:**
```bash
# Terminal 1
php artisan serve

# Terminal 2
# DON'T start RAG service - keep it stopped
```

**Force Mode 3 (Red):**
```bash
# Stop Laravel (Ctrl+C)
# Chat window already open will show offline
```

---

## 🔍 Troubleshooting

### Issue: Always showing Red (Mode 3)
**Cause:** Laravel is not running
**Fix:**
```bash
php artisan serve
```

### Issue: Always showing Green (Mode 1) when RAG is down
**Cause:** Cache issue or browser not refreshed
**Fix:**
```bash
# Clear Laravel cache
php artisan cache:clear

# Hard refresh browser
Ctrl + Shift + R
```

### Issue: Mode 2 not showing real data
**Cause:** Database connection issue
**Fix:**
```bash
# Check database connection
php artisan tinker
>>> DB::connection()->getPdo();

# Check if assessments exist
>>> \App\Models\Assessment::count();
```

### Issue: Header not changing colors
**Cause:** CSS not loading or browser cache
**Fix:**
```bash
# Clear browser cache
Ctrl + Shift + R

# Check console for errors
F12 → Console tab
```

---

## 📝 Database Queries in Mode 2

### Available Assessments
```php
Assessment::active()
    ->whereDoesntHave('studentResults', function($q) use ($studentId) {
        $q->where('student_id', $studentId);
    })
    ->select('id', 'title', 'category', 'duration')
    ->limit(3)
    ->get();
```

### Student Results
```php
StudentResult::where('student_id', $studentId)
    ->with('assessment:id,title')
    ->orderBy('submitted_at', 'desc')
    ->limit(3)
    ->get();
```

### In-Progress Assessments
```php
StudentAssessment::where('student_id', $studentId)
    ->whereNull('submitted_at')
    ->with('assessment:id,title,category,duration')
    ->get();
```

---

## 🎯 Keywords for Mode 2 Pattern Matching

| User Query Contains | Response Type |
|---------------------|---------------|
| assessment, test, exam | Available assessments from DB |
| result, score, performance | Recent results from DB |
| help, how | Help and navigation |
| Default | General greeting |

---

## 📊 Response Time Comparison

```
Mode 1 (AI):     ████████████████████░░░░  2-4 seconds
Mode 2 (DB):     ███░░░░░░░░░░░░░░░░░░░░░  <1 second
Mode 3 (Static): █░░░░░░░░░░░░░░░░░░░░░░░  Instant
```

---

## 🔗 Useful Endpoints

| Endpoint | Method | Purpose |
|----------|--------|---------|
| `/student/rag-chat` | POST | Main chat endpoint |
| `/student/rag-health` | GET | Check RAG service health |
| `/student/chatbot-mode-test` | GET | Check current mode |

---

## 📋 Verification Checklist

**Mode 1 (Green) Testing:**
- [ ] Both services running
- [ ] Header is green
- [ ] AI-generated responses
- [ ] Context-aware answers
- [ ] Response time 2-4s
- [ ] Shows personalized insights

**Mode 2 (Yellow) Testing:** ⭐
- [ ] Laravel running, RAG stopped
- [ ] Header is yellow (NOT red)
- [ ] Shows real assessment names
- [ ] Shows actual student scores
- [ ] Database queries execute
- [ ] Response time <1s
- [ ] Navigation links work

**Mode 3 (Red) Testing:**
- [ ] Laravel stopped
- [ ] Header is red
- [ ] Shows offline message
- [ ] No database access
- [ ] Instant response
- [ ] Generic suggestions only

---

## 💡 Pro Tips

1. **Check Mode Before Testing**
   ```bash
   .\test_chatbot_modes.ps1
   ```

2. **Watch Logs in Real-Time**
   ```bash
   tail -f storage/logs/laravel.log | grep "MODE"
   ```

3. **Verify Mode in Browser Console**
   ```javascript
   // F12 → Console
   // After chatbot responds, check network tab
   // Look for mode field in response
   ```

4. **Force Cache Clear**
   ```bash
   php artisan cache:clear
   php artisan config:clear
   php artisan route:clear
   ```

5. **Test Mode Transitions**
   - Start in Mode 1 → Stop RAG → Should switch to Mode 2
   - Verify header color changes from green to yellow

---

## 🎨 Visual Indicators

### Header Gradients
```css
/* Mode 1 - Green */
background: linear-gradient(135deg, #10b981 0%, #059669 100%);

/* Mode 2 - Yellow */
background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);

/* Mode 3 - Red */
background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
```

### Status Dot
- **Mode 1:** Green pulsing dot (9px)
- **Mode 2:** Yellow pulsing dot (9px)
- **Mode 3:** Red pulsing dot (9px)

### Mode Badge
- Position: Top-right of header
- Shows: "🟢 Mode 1" / "🟡 Mode 2" / "🔴 Mode 3"
- Animation: Slide in from right

---

## 📞 Quick Commands Reference

```bash
# Start Laravel
php artisan serve

# Start RAG Service
cd python-rag && python rag_service.py

# Check Mode
curl http://localhost:8000/student/chatbot-mode-test

# Watch Logs
Get-Content storage\logs\laravel.log -Wait -Tail 20

# Clear Cache
php artisan optimize:clear

# Test Database Connection
php artisan tinker
>>> Assessment::count();
```

---

**🎯 Remember:** Mode 2 (Limited Mode) now correctly shows YELLOW header with REAL database data!

**Updated:** 2025-10-08
**Status:** ✅ Working
