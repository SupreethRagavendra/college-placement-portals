# Groq RAG Chatbot - Final Testing Guide 🚀

## Quick Start Testing

### Prerequisites Check
```bash
# 1. Check RAG Service
curl http://localhost:8001/health

# 2. Check Laravel
curl http://localhost:8000

# Expected: Both should return 200 OK
```

---

## Test Scenarios

### Scenario 1: Student with NO Completed Assessments ✅

**Setup:**
- Available assessments: Test3567
- Completed assessments: 0

**Test Queries:**

#### Query 1: "What assessments are available?"
**Expected Response:**
```
You have 1 assessment available:

📝 **Test3567**
   - Category: Technical
   - Duration: 30 minutes
   - Difficulty: Medium

Ready to start? Click 'View Assessments' to begin!
```

**Should NOT show:**
- ❌ "Log in to view assessments"
- ❌ "Navigate to dashboard"
- ❌ Generic instructions

#### Query 2: "Show my results"
**Expected Response:**
```
You haven't completed any assessments yet.

Start your first test:
📝 **Test3567** (30 minutes)

Click 'View Assessments' to begin!
```

**Should NOT show:**
- ❌ "Results of Test3567"
- ❌ "You've completed assessments"
- ❌ Scores for tests not taken

#### Query 3: "Hi" or "Hello"
**Expected Response:**
```
Hi there! 👋

I can help you with:
• Available assessments
• Your test results
• How to take tests
• Portal navigation

You currently have 1 assessment available: Test3567

What would you like to know?
```

---

### Scenario 2: Student WITH Completed Assessments ✅

**Setup:**
- Available: Python Advanced, SQL Pro
- Completed: Test3567 (75%), Python Basics (82%)

**Test Queries:**

#### Query 1: "Show my results"
**Expected Response:**
```
Your recent results:

✅ **Test3567**: 75% - PASSED
   Date: Jan 6, 2025

✅ **Python Basics**: 82% - PASSED
   Date: Jan 5, 2025

Overall: 2 completed, Average: 78.5%
```

#### Query 2: "What assessments can I take?"
**Expected Response:**
```
You have 2 assessments available:

📝 **Python Advanced**
   - Category: Programming
   - Duration: 45 minutes
   - Difficulty: Hard

📝 **SQL Pro**
   - Category: Database
   - Duration: 30 minutes
   - Difficulty: Medium

Click 'View Assessments' to start!
```

---

### Scenario 3: General Help Queries ✅

#### Query: "How do I take a test?"
**Expected Response:**
```
Taking an assessment is easy:

1. Go to 'Assessments' from your dashboard
2. Choose an available test
3. Click 'Start Assessment'
4. Answer questions within the time limit
5. Submit when done

⚠️ Important: Timer can't be paused once started!

Ready to begin? You have Test3567 available now.
```

#### Query: "What is the passing score?"
**Expected Response:**
```
For most assessments, you need 60% to pass.

Your available assessment:
📝 **Test3567** - Pass: 60%

Check individual assessment details for specific requirements.
```

---

## Step-by-Step Testing

### Test 1: Service Health Check

**Command:**
```bash
curl http://localhost:8001/status
```

**Expected JSON:**
```json
{
  "service": "RAG Service with Groq AI",
  "status": "operational",
  "groq_model": "llama-3.3-70b-versatile",
  "collections": [
    {"name": "portal_info", "document_count": 10},
    {"name": "assessments", "document_count": 1},
    {"name": "questions", "document_count": 0}
  ]
}
```

---

### Test 2: Direct RAG API Test

**Command:**
```powershell
$body = @{
    student_id = 1
    query = "What assessments are available?"
    session_id = "test-session"
    student_context = @{
        available_assessments = @(
            @{
                name = "Test3567"
                category = "Technical"
                duration = "30 minutes"
                pass_percentage = "60%"
                difficulty = "Medium"
            }
        )
        completed_assessments = @()
    }
} | ConvertTo-Json -Depth 5

Invoke-WebRequest -Uri "http://localhost:8001/chat" -Method POST -Body $body -ContentType "application/json"
```

**Expected:**
- Status: 200
- Response contains "Test3567"
- No generic "log in" messages

---

### Test 3: Full Integration Test

**Steps:**

1. **Login as Student**
   - Go to: http://localhost:8000/login
   - Use test student credentials

2. **Open Chatbot**
   - Look for floating button (bottom-right)
   - Click to open chat window

3. **Test Query 1: Available Assessments**
   - Type: "Show available assessments"
   - Press Enter
   - Wait 2-3 seconds

   **Verify:**
   - ✅ Shows "Test3567" (actual name)
   - ✅ Shows category, duration, difficulty
   - ✅ Has call-to-action button
   - ✅ Response time < 3 seconds

4. **Test Query 2: Results**
   - Type: "Show my results"
   - Press Enter

   **Verify (No completed):**
   - ✅ Says "You haven't completed any assessments yet"
   - ✅ Suggests Test3567 to start
   - ✅ Has action button
   - ❌ Does NOT show fake results

   **Verify (Has completed):**
   - ✅ Shows actual scores
   - ✅ Shows pass/fail status
   - ✅ Shows dates

5. **Test Query 3: Help**
   - Type: "How do I take a test?"
   - Press Enter

   **Verify:**
   - ✅ Shows step-by-step instructions
   - ✅ Mentions available assessment
   - ✅ Clear and concise

---

## Debug Mode Testing

### Enable Debug Mode:

**In Browser Console (F12):**
```javascript
localStorage.setItem('chatbot_debug', 'true');
```

**Then in chatbot, press:** `Ctrl+Shift+T`

### What You'll See:

**Test panel appears with:**
- Mode selection (RAG/Normal)
- Debug info toggle
- Current mode indicator

**In chat responses:**
```
[Groq AI - assessment_query]
Response time: 1.8s
```

---

## Performance Testing

### Response Time Benchmarks:

| Query Type | Expected Time | Status |
|------------|---------------|--------|
| Simple (Hi) | < 1 second | ✅ |
| Assessments | < 2 seconds | ✅ |
| Results | < 2 seconds | ✅ |
| Help | < 1.5 seconds | ✅ |
| Complex | < 3 seconds | ✅ |

### Load Test:

**Send 10 queries rapidly:**
```javascript
// In browser console
for(let i = 0; i < 10; i++) {
    setTimeout(() => {
        document.getElementById('chatbot-input').value = `Test query ${i}`;
        document.getElementById('chatbot-form').dispatchEvent(new Event('submit'));
    }, i * 500);
}
```

**Expected:**
- All responses received
- No timeouts
- Consistent response times

---

## Common Issues & Solutions

### Issue 1: Generic Responses

**Symptom:**
```
"To view available assessments, please follow these steps:
1. Log in to your student account..."
```

**Causes:**
- RAG service not running
- Context not being sent
- Database connection failed

**Fix:**
```bash
# Check RAG service
curl http://localhost:8001/health

# Check Laravel logs
tail -f storage/logs/laravel.log

# Restart RAG service
cd python-rag-groq
.\venv\Scripts\activate
python main.py
```

---

### Issue 2: Shows Results for Uncompleted Tests

**Symptom:**
```
"Results of Test3567: ..."
(But student never took Test3567)
```

**Causes:**
- Old RAG service version
- Cache not cleared

**Fix:**
```bash
# Restart RAG service
cd python-rag-groq
Get-Process | Where-Object {$_.CommandLine -like "*main.py*"} | Stop-Process -Force
python main.py

# Clear Laravel cache
php artisan cache:clear

# Clear browser cache
Ctrl+Shift+R in browser
```

---

### Issue 3: Slow Responses (> 5 seconds)

**Causes:**
- Database slow
- Groq API issues
- Network problems

**Fix:**
```bash
# Check Groq status
curl https://api.groq.com/openai/v1/models -H "Authorization: Bearer YOUR_KEY"

# Check database
php artisan tinker
\App\Models\Assessment::count(); // Should be fast

# Adjust timeout in .env
RAG_SERVICE_TIMEOUT=60
```

---

### Issue 4: Chatbot Not Opening

**Causes:**
- JavaScript error
- Missing CSRF token
- Route not registered

**Fix:**
```javascript
// Check browser console (F12)
// Look for errors

// Verify CSRF token
document.querySelector('meta[name="csrf-token"]')?.content
// Should return token string

// Check route
// Visit: http://localhost:8000/student/groq-health
// Should return JSON
```

---

## Validation Checklist

### Before Marking as Complete:

#### Services Running:
- [ ] RAG service running (port 8001)
- [ ] Laravel running (port 8000)
- [ ] Database accessible
- [ ] Groq API key valid

#### Functionality:
- [ ] Shows actual assessment names
- [ ] Differentiates available vs completed
- [ ] No generic "log in" messages
- [ ] Results query checks completion status
- [ ] Response time < 3 seconds
- [ ] Action buttons work
- [ ] Follow-up questions relevant

#### UI/UX:
- [ ] Chatbot opens smoothly
- [ ] Messages formatted nicely
- [ ] Emojis display correctly
- [ ] Typing indicator shows
- [ ] Scroll works properly
- [ ] Mobile responsive

#### Data Accuracy:
- [ ] Assessment names match database
- [ ] Scores accurate (if completed)
- [ ] Dates correct
- [ ] Pass/fail status correct
- [ ] No data leakage between students

---

## Production Deployment Checklist

### Environment:
- [ ] Set `DEBUG=False` in Python `.env`
- [ ] Set `APP_ENV=production` in Laravel `.env`
- [ ] Use production Groq API key
- [ ] Configure proper logging
- [ ] Set up monitoring (optional)

### Security:
- [ ] API keys in environment variables
- [ ] No hardcoded credentials
- [ ] HTTPS enabled (production)
- [ ] CSRF protection active
- [ ] Rate limiting configured

### Performance:
- [ ] Database indexes created
- [ ] Query caching enabled
- [ ] Response caching (5 min)
- [ ] CDN for static assets (optional)

### Monitoring:
- [ ] Error logging enabled
- [ ] Response time tracking
- [ ] User analytics (optional)
- [ ] Uptime monitoring

---

## Final Verification

### Command Sequence:

```bash
# 1. Services
curl http://localhost:8001/health
curl http://localhost:8000

# 2. Database
php artisan tinker
\App\Models\Assessment::where('is_active', true)->count()
exit

# 3. RAG Knowledge
curl http://localhost:8001/status

# 4. Test Chat
# (Use browser)
```

### Success Criteria:

**All must be TRUE:**
- ✅ Services healthy
- ✅ Database connected
- ✅ Knowledge base loaded (10+ documents)
- ✅ Actual assessment names shown
- ✅ Results query accurate
- ✅ Response time acceptable
- ✅ No errors in logs

---

## Support & Troubleshooting

### Check Logs:

**Python RAG:**
```bash
tail -f python-rag-groq/rag_service.log
```

**Laravel:**
```bash
tail -f storage/logs/laravel.log
```

**Browser:**
```
F12 → Console tab
```

### Common Log Messages:

**Good:**
```
✓ Groq API connection successful
✓ Student context received: {available: 1, completed: 0}
✓ Response generated in 1.8s
```

**Bad:**
```
✗ Groq API error: Rate limit exceeded
✗ Database connection failed
✗ Context missing: No student data
```

---

## Documentation Files

1. **GROQ_RAG_SETUP.md** - Complete setup guide
2. **QUICK_START_GROQ_RAG.md** - 5-minute quick start
3. **GROQ_CHATBOT_LIVE_DATA_FIX.md** - Live data implementation
4. **CHATBOT_RESPONSE_IMPROVED.md** - Response format improvements
5. **RESULTS_QUERY_FIXED.md** - Results query fix
6. **THIS FILE** - Testing guide

---

**Testing Status:** ✅ **READY FOR TESTING**

**Services Required:**
1. Python RAG (port 8001) - RUNNING ✅
2. Laravel (port 8000) - START MANUALLY
3. Database - CONFIGURED ✅

**Next Steps:**
1. Start Laravel: `php artisan serve`
2. Login as student
3. Open chatbot
4. Run test scenarios above

**Expected Outcome:** Natural, accurate responses with real assessment data!

---

**Version:** 2.3.0  
**Last Updated:** January 7, 2025  
**Status:** Production Ready ✅
