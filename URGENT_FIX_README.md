# 🚨 URGENT: Your RAG Service is Not Running!

## The Problem

Your chatbot is giving **fallback responses** because the RAG service isn't running.

## The Fix (2 Minutes)

### Step 1: Open Command Prompt
Press `Win + R`, type `cmd`, press Enter

### Step 2: Run These Commands
```bash
cd d:\project-mini\college-placement-portal
START_RAG_NOW.bat
```

### Step 3: Keep Window Open
**Do NOT close the terminal!** RAG service needs to keep running.

### Step 4: Test
Go to chatbot and ask: **"What's the passing score?"**

**Should say:** "60%" (not show assessment list!)

---

## ✅ Quick Test

| Question | RAG Working | RAG NOT Working |
|----------|-------------|-----------------|
| "What's the passing score?" | "60%..." | Shows assessment list |
| "Can I pause timer?" | "No, timer cannot..." | Shows assessment list |
| "Can I retake?" | "Yes, most assessments..." | Shows assessment list |

---

## Need Help?

Read: `FIX_RAG_NOT_WORKING.md` for detailed instructions.

---

**TL;DR: Run `START_RAG_NOW.bat` and keep it running!**

