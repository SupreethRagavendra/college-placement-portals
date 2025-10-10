#!/bin/bash
# Startup script for Groq AI RAG Service (Linux/Mac)
# This script starts the RAG service on port 8001

echo "============================================================"
echo "Starting Groq AI RAG Service for College Placement Portal"
echo "============================================================"
echo

# Check if .env file exists
if [ ! -f .env ]; then
    echo "[ERROR] .env file not found!"
    echo "Please copy .env.example to .env and configure it."
    echo
    echo "Run: cp .env.example .env"
    echo "Then edit .env with your database credentials"
    exit 1
fi

# Check if virtual environment exists
if [ ! -d "venv" ]; then
    echo "[INFO] Virtual environment not found. Creating..."
    python3 -m venv venv
    if [ $? -ne 0 ]; then
        echo "[ERROR] Failed to create virtual environment"
        exit 1
    fi
fi

# Activate virtual environment
echo "[INFO] Activating virtual environment..."
source venv/bin/activate

# Install/upgrade dependencies
echo "[INFO] Installing dependencies..."
pip install -r requirements.txt --quiet
if [ $? -ne 0 ]; then
    echo "[WARNING] Some dependencies may have failed to install"
fi

# Check if ChromaDB storage exists
if [ ! -d "chromadb_storage" ]; then
    echo
    echo "[WARNING] ChromaDB storage not found!"
    echo "Please initialize the knowledge base first:"
    echo "  python init_knowledge_groq.py"
    echo
    read -p "Do you want to initialize now? (y/n): " INIT
    if [ "$INIT" = "y" ] || [ "$INIT" = "Y" ]; then
        echo "[INFO] Initializing knowledge base..."
        python init_knowledge_groq.py
        if [ $? -ne 0 ]; then
            echo "[ERROR] Knowledge base initialization failed"
            exit 1
        fi
    else
        echo "[WARNING] Starting without initialized knowledge base"
    fi
fi

echo
echo "============================================================"
echo "Starting RAG Service on http://localhost:8001"
echo "============================================================"
echo "Press Ctrl+C to stop the service"
echo

# Start the service
python main.py

# If service exits
echo
echo "[INFO] RAG Service stopped"
