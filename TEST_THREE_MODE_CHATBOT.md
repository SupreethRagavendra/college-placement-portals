# Quick Testing Guide: Three-Mode Chatbot System

## 🎯 Quick Start

### Prerequisites
```bash
# 1. Laravel server must be running
php artisan serve

# 2. For Mode 1 (optional): Start RAG service
cd python-rag-groq-backup
python rag_service_groq.py
```

---

## 🧪 Test Scenarios

### Scenario 1: Test Mode 1 (RAG ACTIVE - Green)

**Setup:**
1. Laravel server running ✓
2. RAG service running ✓

**Test Steps:**
1. Login as a student
2. Click chatbot bubble (bottom-right)
3. Type: **"What are my weak areas?"**

**Expected Result:**
- ✅ Header turns **GREEN**
- ✅ Badge shows: **"🟢 Mode 1: RAG ACTIVE"**
- ✅ Status dot is **GREEN** and pulsing
- ✅ Toggle button has **GREEN** indicator
- ✅ Response is AI-powered with personalized insights

**Sample Response:**
```
Based on your performance analysis:

Weak Areas (< 60%):
• Arrays - 42% accuracy
• Database Queries - 51% accuracy

Recommendations:
1. Review array manipulation concepts
2. Practice SQL JOIN operations

Follow-up Questions:
- Would you like practice questions?
- Show me study resources
```

---

### Scenario 2: Test Mode 2 (LIMITED MODE - Yellow)

**Setup:**
1. Laravel server running ✓
2. RAG service **STOPPED** (Ctrl+C on RAG terminal)

**Test Steps:**
1. In chatbot, type: **"Show available assessments"**

**Expected Result:**
- ✅ Header turns **YELLOW**
- ✅ Badge shows: **"🟡 Mode 2: LIMITED MODE"**
- ✅ Status dot is **YELLOW** and pulsing
- ✅ Toggle button has **YELLOW** indicator
- ✅ Response shows real assessments from database

**Sample Response:**
```
Here are the available assessments:

• PHP Assessment (30 minutes)
• JavaScript Test (45 minutes)
• Database Fundamentals (60 minutes)

[View All Assessments]

What would you like to know?
- Show me my results
- How do I take a test?
```

---

### Scenario 3: Test Mode 3 (OFFLINE MODE - Red)

**Setup:**
1. Laravel server **STOPPED** (Ctrl+C on server terminal)
2. Keep browser open

**Test Steps:**
1. In chatbot, type: **"Hello"**

**Expected Result:**
- ✅ Header turns **RED**
- ✅ Badge shows: **"🔴 Mode 3: OFFLINE MODE"**
- ✅ Status dot is **RED** and pulsing
- ✅ Toggle button has **RED** indicator
- ✅ Response shows offline help

**Sample Response:**
```
👋 Hello! I'm currently in offline mode. 
Some features may be limited, but I can still help 
with basic navigation.

⚠️ Please check your connection or try again later.

[Refresh Page] [View Dashboard]
```

---

## 🔄 Mode Transition Testing

### Test Automatic Fallback

**Scenario:** RAG service crashes during conversation

1. **Start with Mode 1 (Green)**
   - RAG service running
   - Ask: "What is my performance?"
   - See GREEN mode

2. **Stop RAG service**
   - Ctrl+C on RAG terminal
   
3. **Continue conversation**
   - Ask: "Show my assessments"
   - Mode should **automatically** change to YELLOW
   - No error shown to user

4. **Restart RAG service**
   - `python rag_service_groq.py`
   
5. **Next question**
   - Ask: "What should I study?"
   - Mode should **automatically** change back to GREEN

**Expected:** Seamless transitions with no user errors!

---

## 🎨 Visual Verification Checklist

### When Mode Changes, Check:

**Header:**
- [ ] Background color changes (Green/Yellow/Red)
- [ ] Gradient animation is smooth

**Mode Badge:**
- [ ] Text updates correctly
- [ ] Badge color matches mode
- [ ] Glow/shadow effect visible
- [ ] Slides in from right

**Status Dot:**
- [ ] Color changes instantly
- [ ] Pulsing animation continues
- [ ] Has glow effect

**Toggle Button:**
- [ ] Small indicator dot appears (top-right)
- [ ] Indicator color matches mode
- [ ] Pulses gently

**Assistant Status:**
- [ ] Text shows mode name
- [ ] White text color maintained

---

## 📋 Quick Test Commands

### Test Mode 1 Queries
```
✓ "What are my weak areas?"
✓ "How am I performing?"
✓ "What should I focus on?"
✓ "Show me my progress"
✓ "Which topics do I struggle with?"
```

### Test Mode 2 Queries
```
✓ "Show available assessments"
✓ "What are my results?"
✓ "Show my history"
✓ "List all tests"
✓ "How do I take an assessment?"
```

### Test Mode 3 Queries
```
✓ "Hello"
✓ "Help"
✓ "What can you do?"
✓ "Show assessments"
✓ "View results"
```

---

## 🐛 Common Issues & Fixes

### Issue: Always showing Mode 3 (Red)

**Problem:** Laravel server not running  
**Fix:**
```bash
cd d:/project-mini/college-placement-portal
php artisan serve
```

---

### Issue: Always showing Mode 2 (Yellow)

**Problem:** RAG service not running  
**Fix:**
```bash
cd python-rag-groq-backup
python rag_service_groq.py
```

---

### Issue: Header color not changing

**Problem:** Browser cache  
**Fix:**
1. Hard refresh: `Ctrl + Shift + R`
2. Clear cache: `Ctrl + Shift + Delete`
3. Or use incognito mode

---

### Issue: Badge not showing

**Problem:** JavaScript not loaded  
**Fix:**
1. Check console (F12) for errors
2. Verify file exists: `/public/js/intelligent-chatbot.js`
3. Clear cache and reload

---

## 📊 Validation Checklist

After testing all three modes:

### Functionality
- [ ] Mode 1 shows AI-powered responses
- [ ] Mode 2 shows database responses
- [ ] Mode 3 shows offline responses
- [ ] All modes handle queries without errors
- [ ] Follow-up suggestions appear
- [ ] Action buttons work

### Visual
- [ ] Header changes color for each mode
- [ ] Mode badge shows correct text
- [ ] Status dot color matches mode
- [ ] Toggle button indicator appears
- [ ] All colors match design (Green/Yellow/Red)
- [ ] Animations are smooth

### User Experience
- [ ] No error messages during mode switches
- [ ] Transitions are seamless
- [ ] User is always informed of current mode
- [ ] Chat remains functional in all modes
- [ ] Helpful suggestions in each mode

---

## 🎯 Success Criteria

✅ **All three modes work independently**  
✅ **Automatic fallback between modes**  
✅ **Visual indicators change correctly**  
✅ **No breaking errors in any mode**  
✅ **Smooth user experience**  

---

## 📸 Expected Screenshots

### Mode 1 (Green)
```
┌─────────────────────────────────┐
│  🟢 Mode 1: RAG ACTIVE        [×]│
│  Intelligent Assistant           │
│  🟢 Ready to help               │
├─────────────────────────────────┤
│  AI: Based on your performance  │
│  you're struggling with...      │
└─────────────────────────────────┘
```

### Mode 2 (Yellow)
```
┌─────────────────────────────────┐
│  🟡 Mode 2: LIMITED MODE      [×]│
│  Intelligent Assistant           │
│  🟡 Database Only               │
├─────────────────────────────────┤
│  AI: Available assessments:     │
│  • PHP Assessment               │
│  • JavaScript Test              │
└─────────────────────────────────┘
```

### Mode 3 (Red)
```
┌─────────────────────────────────┐
│  🔴 Mode 3: OFFLINE MODE      [×]│
│  Intelligent Assistant           │
│  🔴 Frontend Fallback           │
├─────────────────────────────────┤
│  AI: ⚠️ I'm in offline mode.    │
│  Please check connection.       │
│  [Refresh Page]                 │
└─────────────────────────────────┘
```

---

## 🚀 Production Testing

### Before Deployment
1. Test all three modes
2. Verify automatic fallbacks
3. Check visual indicators
4. Test on different browsers
5. Test on mobile devices
6. Verify performance (no lag)

### Browser Compatibility
- [ ] Chrome (latest)
- [ ] Firefox (latest)
- [ ] Edge (latest)
- [ ] Safari (if available)
- [ ] Mobile browsers

---

## 📞 Support

If you encounter issues:

1. **Check Laravel Logs:**
   ```bash
   tail -f storage/logs/laravel.log
   ```

2. **Check Browser Console:**
   - Press F12
   - Go to Console tab
   - Look for errors

3. **Check Network Tab:**
   - F12 → Network
   - Look for failed requests
   - Check `/student/groq-chat` status

---

## ✅ Final Verification

Run this complete test:

```bash
# 1. Start clean
php artisan serve
cd python-rag-groq-backup && python rag_service_groq.py

# 2. Login as student
# 3. Open chatbot
# 4. Type each query and verify mode:

"What are my weak areas?"        → 🟢 Green (Mode 1)
<Stop RAG service>
"Show my results"                → 🟡 Yellow (Mode 2)
<Stop Laravel>
"Hello"                          → 🔴 Red (Mode 3)
<Restart Laravel>
"Show assessments"               → 🟡 Yellow (Mode 2)
<Restart RAG>
"Analyze my performance"         → 🟢 Green (Mode 1)
```

**If all transitions work smoothly: ✅ SUCCESS!**

---

**Testing Time:** ~5 minutes  
**Difficulty:** Easy  
**Status:** Ready to test!
