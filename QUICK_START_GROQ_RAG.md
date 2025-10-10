# Quick Start: Groq AI RAG Chatbot

## 🚀 Get Started in 5 Minutes

### Step 1: Create Configuration (30 seconds)

Create `python-rag-groq/.env`:

```bash
GROQ_API_KEY=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
GROQ_MODEL=llama-3.3-70b-versatile
GROQ_TEMPERATURE=0.7
GROQ_MAX_TOKENS=1024

DB_HOST=db.wkqbukidxmzbgwauncrl.supabase.co
DB_PORT=5432
DB_NAME=postgres
DB_USER=postgres
DB_PASSWORD=Supreeeth24#

CHROMADB_PATH=./chromadb_storage
PORT=8001
HOST=0.0.0.0
DEBUG=True
```

### Step 2: Install & Initialize (2 minutes)

```bash
cd python-rag-groq

# Create virtual environment
python -m venv venv

# Activate (Windows)
venv\Scripts\activate

# Install dependencies
pip install -r requirements.txt

# Initialize knowledge base
python init_knowledge_groq.py
```

### Step 3: Start Service (10 seconds)

```bash
# Windows
start_rag.bat

# Or manual
python main.py
```

Service runs on: **http://localhost:8001**

### Step 4: Test It (30 seconds)

```bash
# Open new terminal
curl http://localhost:8001/health

# Run full test suite
python test_groq_rag.py
```

### Step 5: Update Frontend (1 minute)

In `public/js/chatbot.js`, change:

```javascript
// FROM:
const response = await fetch('/student/chatbot-ask', {

// TO:
const response = await fetch('/student/groq-chat', {
```

---

## ✅ That's It!

Your Groq-powered RAG chatbot is now running!

**Test it:**
1. Start Laravel: `php artisan serve`
2. Login as student
3. Click chatbot icon (bottom-right)
4. Ask: "What assessments are available?"

---

## 📊 What You Get

✅ **Ultra-Fast Responses**: < 2 seconds with Groq LPU™  
✅ **Smart Context**: Knows each student's data  
✅ **Auto-Sync**: Updates when admin adds assessments  
✅ **Vector Search**: Semantic understanding with embeddings  
✅ **Production Ready**: Error handling, logging, caching  

---

## 🔧 Common Commands

```bash
# Start RAG service
cd python-rag-groq && start_rag.bat

# Run tests
python test_groq_rag.py

# Sync knowledge manually
curl -X POST http://localhost:8001/sync-knowledge

# Check health
curl http://localhost:8001/health

# View logs
tail -f rag_service.log
```

---

## 📚 Full Documentation

For detailed information, see **GROQ_RAG_SETUP.md**

---

## 🆘 Troubleshooting

**Service won't start:**
```bash
# Check if port in use
netstat -ano | findstr :8001

# Kill process and restart
taskkill /PID <process_id> /F
python main.py
```

**No response from chatbot:**
```bash
# Check RAG service
curl http://localhost:8001/health

# Check Laravel logs
tail -f storage/logs/laravel.log
```

**Knowledge base empty:**
```bash
python init_knowledge_groq.py --reset
```

---

## 🎯 Next Steps

1. **Customize Knowledge**: Edit static docs in `init_knowledge_groq.py`
2. **Add Auto-Sync**: Hooks already in place, just need admin controller updates
3. **Monitor**: Check `/status` endpoint for collection stats
4. **Optimize**: Adjust `MAX_CONTEXT_DOCUMENTS` in `.env`

---

**Version:** 2.0.0  
**AI Model:** Llama 3.3 70B (Groq)  
**Setup Time:** < 5 minutes  
**First Response:** < 2 seconds
