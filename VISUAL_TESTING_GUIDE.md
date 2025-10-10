# 👁️ Visual Testing Guide - Three-Mode Chatbot

## 🎨 What You Should See

This guide shows exactly what the chatbot should look like in each mode.

---

## 🟢 Mode 1: RAG ACTIVE

### When Active
- ✅ Laravel running on port 8000
- ✅ RAG service running on port 8001

### Visual Appearance

```
┌─────────────────────────────────────────────────────────┐
│  🤖 Placement Assistant          🟢 Mode 1: RAG ACTIVE  │  ← GREEN GRADIENT HEADER
│  ● Online                                                │  ← Green pulsing dot
├─────────────────────────────────────────────────────────┤
│                                                          │
│  ┌────────────────────────────────────────┐            │
│  │ 👤 You                          3:45 PM │            │
│  │ What are my weak areas?                 │            │
│  └────────────────────────────────────────┘            │
│                                                          │
│  ┌────────────────────────────────────────┐            │
│  │ 🤖 AI                           3:45 PM │            │
│  │ Based on your recent performance,       │            │
│  │ I notice you're struggling with PHP     │            │
│  │ (58% accuracy). I recommend:            │            │
│  │                                          │            │
│  │ 1. Focus on array functions & loops     │            │
│  │ 2. Practice 15-20 questions daily       │            │
│  │ 3. Review Assessment #3 mistakes        │            │
│  │                                          │            │
│  │ You have 2 new PHP assessments          │            │
│  │ available that can help!                │            │
│  └────────────────────────────────────────┘            │
│                                                          │
│  💡 Suggestions:                                        │
│  [What's my strongest topic?] [Show practice plan]      │
│                                                          │
├─────────────────────────────────────────────────────────┤
│  Type your message...                            [Send]  │
└─────────────────────────────────────────────────────────┘
```

### Key Visual Elements
- **Header:** Bright green gradient (#10b981 to #059669)
- **Status Dot:** Green, pulsing animation
- **Mode Badge:** "🟢 Mode 1: RAG ACTIVE" in top-right
- **Response Quality:** Contextual, personalized, intelligent
- **Suggestions:** Dynamic, based on conversation

---

## 🟡 Mode 2: LIMITED MODE ⭐ FIXED!

### When Active
- ✅ Laravel running on port 8000
- ❌ RAG service DOWN (stopped)

### Visual Appearance

```
┌─────────────────────────────────────────────────────────┐
│  🤖 Placement Assistant      🟡 Mode 2: LIMITED MODE    │  ← YELLOW GRADIENT HEADER
│  ● Limited                                               │  ← Yellow pulsing dot
├─────────────────────────────────────────────────────────┤
│                                                          │
│  ┌────────────────────────────────────────┐            │
│  │ 👤 You                          3:45 PM │            │
│  │ What assessments are available?         │            │
│  └────────────────────────────────────────┘            │
│                                                          │
│  ┌────────────────────────────────────────┐            │
│  │ 🤖 Bot                          3:45 PM │            │
│  │ You have 3 assessment(s) available:     │            │
│  │                                          │            │
│  │ 📝 PHP Programming (Technical)          │            │
│  │    Duration: 60 minutes                 │            │
│  │                                          │            │
│  │ 📝 JavaScript Basics (Technical)        │            │
│  │    Duration: 45 minutes                 │            │
│  │                                          │            │
│  │ 📝 Aptitude Test (Aptitude)             │            │
│  │    Duration: 30 minutes                 │            │
│  │                                          │            │
│  │ Click 'View Assessments' to start!      │            │
│  │                                          │            │
│  │ [View Assessments]  [View Results]      │  ← Action buttons
│  └────────────────────────────────────────┘            │
│                                                          │
│  💡 Suggestions:                                        │
│  [What assessments are available?] [Show my results]    │
│                                                          │
├─────────────────────────────────────────────────────────┤
│  Type your message...                            [Send]  │
└─────────────────────────────────────────────────────────┘
```

### Key Visual Elements
- **Header:** Amber/yellow gradient (#f59e0b to #d97706)
- **Status Dot:** Yellow, pulsing animation
- **Mode Badge:** "🟡 Mode 2: LIMITED MODE" in top-right
- **Response Quality:** Direct, factual, data-driven
- **Data Source:** REAL data from PostgreSQL database
- **Action Buttons:** Navigation links to portal features

### What Makes It Different
✅ **Shows REAL assessment names** (from your database)
✅ **Shows REAL categories** (Technical, Aptitude, etc.)
✅ **Shows REAL durations** (60 min, 45 min, etc.)
✅ **Fast response** (<1 second)
✅ **No AI generation** (pattern-based only)

---

## 🔴 Mode 3: OFFLINE MODE

### When Active
- ❌ Laravel DOWN (port 8000 not accessible)

### Visual Appearance

```
┌─────────────────────────────────────────────────────────┐
│  🤖 Placement Assistant      🔴 Mode 3: OFFLINE MODE    │  ← RED GRADIENT HEADER
│  ● Offline                                               │  ← Red pulsing dot
├─────────────────────────────────────────────────────────┤
│                                                          │
│  ┌────────────────────────────────────────┐            │
│  │ 👤 You                          3:45 PM │            │
│  │ What assessments are available?         │            │
│  └────────────────────────────────────────┘            │
│                                                          │
│  ┌────────────────────────────────────────┐            │
│  │ 🤖 Bot                          3:45 PM │            │
│  │ ⚠️ I'm currently in offline mode.      │            │
│  │ Please check your connection or try     │            │
│  │ again later.                            │            │
│  │                                          │            │
│  │ You can still navigate the portal       │            │
│  │ using the menu.                         │            │
│  └────────────────────────────────────────┘            │
│                                                          │
│  💡 Suggestions:                                        │
│  [Refresh Page] [View Dashboard]                        │
│                                                          │
├─────────────────────────────────────────────────────────┤
│  Type your message...                            [Send]  │
└─────────────────────────────────────────────────────────┘
```

### Key Visual Elements
- **Header:** Red gradient (#ef4444 to #dc2626)
- **Status Dot:** Red, pulsing animation
- **Mode Badge:** "🔴 Mode 3: OFFLINE MODE" in top-right
- **Response:** Generic offline message
- **No Data:** Cannot access database
- **Suggestions:** Static navigation help

---

## 🎯 Testing Checklist

### Visual Verification for Mode 2 (The Fix)

Use this checklist when testing Limited Mode:

#### Header
- [ ] Header background is YELLOW/AMBER (not green, not red)
- [ ] Gradient goes from lighter yellow to darker orange
- [ ] Mode badge in top-right shows "🟡 Mode 2: LIMITED MODE"
- [ ] Status text says "Limited" (not "Offline")

#### Status Indicator
- [ ] Status dot is yellow color (#f59e0b)
- [ ] Dot has pulsing animation
- [ ] Dot has glow effect around it

#### Message Content
- [ ] Ask: "What assessments are available?"
- [ ] Response shows ACTUAL assessment names (not generic)
- [ ] Each assessment includes:
  - [ ] Title (e.g., "PHP Programming")
  - [ ] Category (e.g., "Technical")
  - [ ] Duration (e.g., "60 minutes")
- [ ] Response includes emoji icons (📝)
- [ ] Response time is fast (<1 second)

#### Action Buttons
- [ ] "View Assessments" button appears
- [ ] "View Results" button appears
- [ ] Buttons are clickable links
- [ ] Links go to correct portal pages

#### Suggestions
- [ ] Suggestion pills appear below response
- [ ] Suggestions are relevant to current context
- [ ] Examples: "Show my recent results", "How do I take a test?"

#### Toggle Button
- [ ] Toggle button (bottom-right) has colored indicator
- [ ] Indicator color is yellow
- [ ] Small dot appears on toggle button

---

## 🔍 Side-by-Side Comparison

### Header Colors

| Mode | Color Name | Hex Start | Hex End | Visual |
|------|-----------|-----------|---------|--------|
| Mode 1 | Green | #10b981 | #059669 | ████████████ Bright green |
| Mode 2 | Yellow | #f59e0b | #d97706 | ████████████ Amber/orange |
| Mode 3 | Red | #ef4444 | #dc2626 | ████████████ Danger red |

### Response Examples

**Query:** "What assessments are available?"

| Mode | Response Type | Example |
|------|---------------|---------|
| **Mode 1** | AI-generated | "Based on your skill level, I recommend starting with the JavaScript Basics assessment. You've shown strength in this area with your recent 85% score..." |
| **Mode 2** | Database query | "You have 3 assessment(s) available:\n📝 PHP Programming (Technical) - 60 minutes\n📝 JavaScript Basics..." |
| **Mode 3** | Static fallback | "To view assessments, please navigate to the Assessments page from the menu. I'm currently in offline mode..." |

---

## 📱 Mobile View

On mobile devices, the chatbot should:
- Scale properly to screen size
- Maintain color indicators
- Show mode badge (may be smaller)
- Keep functionality intact

---

## 🖼️ Browser Developer Tools Check

### What to Check in Console (F12)

#### Mode 2 Active
```javascript
// Check response object
{
  success: true,
  message: "You have 3 assessment(s) available...",
  mode: "database_only",              // ← Should be "database_only"
  mode_name: "🟡 Mode 2: LIMITED MODE", // ← Should say LIMITED MODE
  mode_color: "#f59e0b",               // ← Should be yellow
  actions: [...],
  follow_up_questions: [...]
}
```

#### Network Tab
- Request URL: `http://localhost:8000/student/rag-chat`
- Method: POST
- Status: 200 OK
- Response includes `mode` field

#### Console Logs
Look for:
```
MODE 2: LIMITED MODE activated - Using database fallback
```

---

## ⚠️ Common Visual Issues

### Issue: Header is Red instead of Yellow
**Expected:** Yellow header in Limited Mode
**Actual:** Red header
**Cause:** Frontend receiving Mode 3 response instead of Mode 2
**Fix:** Check backend is running (`php artisan serve`)

### Issue: No Mode Badge Visible
**Expected:** Badge in top-right showing mode
**Actual:** No badge appears
**Cause:** CSS not loading or JavaScript error
**Fix:** Hard refresh (Ctrl+Shift+R), check console for errors

### Issue: Header Right Color But Shows "Offline"
**Expected:** "Limited" status text
**Actual:** "Offline" text
**Cause:** Frontend not reading mode_name correctly
**Fix:** Check response includes `mode_name` field

---

## ✅ Success Criteria

Mode 2 is working correctly if:
1. ✅ Header is YELLOW (amber/orange gradient)
2. ✅ Badge says "🟡 Mode 2: LIMITED MODE"
3. ✅ Status says "Limited" (not "Offline")
4. ✅ Shows REAL assessment names from database
5. ✅ Shows REAL categories (Technical, Aptitude)
6. ✅ Shows REAL durations (60 min, 45 min)
7. ✅ Response time is fast (<1 second)
8. ✅ Action buttons appear and work
9. ✅ No error messages in console
10. ✅ Backend logs show "MODE 2: LIMITED MODE activated"

---

## 📸 Screenshot Checklist

If documenting, capture:
- [ ] Mode 1 full chat window (green header)
- [ ] Mode 2 full chat window (yellow header) ⭐
- [ ] Mode 3 full chat window (red header)
- [ ] Mode 2 with assessment query response
- [ ] Mode 2 with result query response
- [ ] Toggle button with mode indicator
- [ ] Browser console showing response object
- [ ] Browser network tab showing request
- [ ] Laravel logs showing mode activation

---

**Remember:** The key fix is that Mode 2 now shows **YELLOW** with **REAL DATABASE DATA**, not red with offline messages!

**Test now:** `.\test_chatbot_modes.ps1`
