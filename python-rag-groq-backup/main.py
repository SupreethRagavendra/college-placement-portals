"""
Main FastAPI Service for Groq AI RAG System
Production-ready RAG service with dynamic knowledge sync
"""
from fastapi import FastAPI, HTTPException, BackgroundTasks
from fastapi.middleware.cors import CORSMiddleware
from fastapi.responses import JSONResponse
from pydantic import BaseModel, Field
from typing import Optional, Dict, Any
import chromadb
import os
from dotenv import load_dotenv
import logging
from datetime import datetime

# Import custom modules
from context_handler_groq import ContextHandler
from response_formatter_groq import ResponseFormatter
from knowledge_sync_groq import KnowledgeSync

# Load environment variables
load_dotenv()

# Setup logging
logging.basicConfig(
    level=logging.DEBUG if os.getenv('DEBUG', 'False').lower() == 'true' else logging.INFO,
    format='%(asctime)s - %(name)s - %(levelname)s - %(message)s',
    handlers=[
        logging.FileHandler('rag_service.log'),
        logging.StreamHandler()
    ]
)
logger = logging.getLogger(__name__)

# Initialize FastAPI
app = FastAPI(
    title="College Placement Portal RAG Service",
    description="Groq AI powered RAG service with dynamic knowledge sync",
    version="2.0.0"
)

# CORS configuration
app.add_middleware(
    CORSMiddleware,
    allow_origins=[
        "http://localhost:8000",
        "http://127.0.0.1:8000",
        "http://localhost:3000"
    ],
    allow_credentials=True,
    allow_methods=["*"],
    allow_headers=["*"],
)

# Initialize ChromaDB
chromadb_path = os.getenv('CHROMADB_PATH', './chromadb_storage')
chroma_client = chromadb.PersistentClient(path=chromadb_path)
logger.info(f"ChromaDB initialized at {chromadb_path}")

# Initialize services
try:
    context_handler = ContextHandler(
        groq_api_key=os.getenv('GROQ_API_KEY'),
        groq_model=os.getenv('GROQ_MODEL', 'llama-3.3-70b-versatile'),
        chroma_client=chroma_client
    )
    response_formatter = ResponseFormatter()
    
    knowledge_sync = KnowledgeSync(
        db_host=os.getenv('DB_HOST'),
        db_port=os.getenv('DB_PORT'),
        db_name=os.getenv('DB_NAME'),
        db_user=os.getenv('DB_USER'),
        db_password=os.getenv('DB_PASSWORD'),
        chroma_client=chroma_client
    )
    
    logger.info("All services initialized successfully")
except Exception as e:
    logger.error(f"Service initialization failed: {e}")
    raise


# Pydantic Models
class ChatRequest(BaseModel):
    student_id: int = Field(..., description="Student ID")
    query: str = Field(..., min_length=1, max_length=500, description="Student's question")
    session_id: Optional[str] = Field(None, description="Session ID for tracking")


class ChatResponse(BaseModel):
    success: bool
    message: str
    data: Optional[Dict[str, Any]] = None
    actions: list = []
    follow_up_questions: list = []
    timestamp: str
    query_type: Optional[str] = None


class SyncRequest(BaseModel):
    force: Optional[bool] = Field(False, description="Force full resync")


class SyncResponse(BaseModel):
    success: bool
    message: str
    stats: Dict[str, Any]
    timestamp: str


# Endpoints
@app.get("/")
async def root():
    """Root endpoint"""
    return {
        "service": "College Placement Portal RAG Service",
        "version": "2.0.0",
        "status": "running",
        "ai_model": os.getenv('GROQ_MODEL', 'llama-3.3-70b-versatile'),
        "provider": "Groq AI"
    }


@app.get("/health")
async def health_check():
    """Health check endpoint"""
    try:
        # Check ChromaDB
        collections = chroma_client.list_collections()
        collection_count = len(collections)
        
        # Check database connection
        try:
            conn = knowledge_sync.connect_db()
            conn.close()
            db_status = "connected"
        except:
            db_status = "disconnected"
        
        return {
            "status": "healthy",
            "timestamp": datetime.utcnow().isoformat() + "Z",
            "chromadb_collections": collection_count,
            "database": db_status,
            "groq_model": os.getenv('GROQ_MODEL')
        }
    except Exception as e:
        logger.error(f"Health check failed: {e}")
        return JSONResponse(
            status_code=503,
            content={
                "status": "unhealthy",
                "error": str(e),
                "timestamp": datetime.utcnow().isoformat() + "Z"
            }
        )


@app.get("/status")
async def get_status():
    """Get detailed service status"""
    try:
        collections = chroma_client.list_collections()
        collection_info = []
        
        for collection in collections:
            try:
                coll = chroma_client.get_collection(collection.name)
                count = coll.count()
                collection_info.append({
                    "name": collection.name,
                    "document_count": count
                })
            except:
                collection_info.append({
                    "name": collection.name,
                    "document_count": 0
                })
        
        return {
            "service": "RAG Service with Groq AI",
            "status": "operational",
            "groq_model": os.getenv('GROQ_MODEL'),
            "collections": collection_info,
            "last_sync": knowledge_sync.last_sync,
            "timestamp": datetime.utcnow().isoformat() + "Z"
        }
    except Exception as e:
        logger.error(f"Status check failed: {e}")
        raise HTTPException(status_code=500, detail=str(e))


@app.post("/chat", response_model=ChatResponse)
async def chat(request: ChatRequest):
    """
    Main chat endpoint for student queries
    Processes queries with RAG and Groq AI with comprehensive fallback chain
    """
    logger.info(f"Chat request from student {request.student_id}: {request.query}")
    
    try:
        # Get student-specific context from database
        try:
            student_context = knowledge_sync.get_student_context(request.student_id)
            logger.info(f"Retrieved context for student {request.student_id}")
        except Exception as db_error:
            logger.warning(f"Database context retrieval failed: {db_error}")
            # Continue with empty context if database fails
            student_context = {
                'available_assessments': [],
                'completed_assessments': [],
                'performance_summary': {}
            }
        
        # Process query with context handler (includes Groq + fallback)
        message, data, query_type = context_handler.process_query(
            student_id=request.student_id,
            query=request.query,
            student_context=student_context,
            max_context_docs=int(os.getenv('MAX_CONTEXT_DOCUMENTS', 5))
        )
        
        # Format response with status indicators
        response = response_formatter.format_response(
            message=message,
            data=data,
            query_type=query_type,
            student_id=request.student_id,
            service_status='operational',
            fallback_used=context_handler.last_fallback_used
        )
        
        logger.info(f"Response generated successfully for student {request.student_id}")
        return response
    
    except Exception as e:
        logger.error(f"Chat endpoint error: {e}")
        
        # Try hardcoded fallback
        try:
            from fallback_responses import get_hardcoded_response
            fallback_message = get_hardcoded_response(request.query)
            
            return ChatResponse(
                success=True,
                message=fallback_message,
                data={
                    'service_status': 'degraded',
                    'fallback_used': 'hardcoded',
                    'error': str(e)
                },
                actions=[],
                follow_up_questions=[],
                timestamp=datetime.utcnow().isoformat() + "Z",
                query_type='fallback'
            )
        except:
            # Final fallback
            return ChatResponse(
                success=True,
                message="I'm experiencing technical difficulties right now. Please try again in a moment or contact support if the issue persists.",
                data={
                    'service_status': 'error',
                    'fallback_used': 'final',
                    'error': str(e)
                },
                actions=[],
                follow_up_questions=[],
                timestamp=datetime.utcnow().isoformat() + "Z",
                query_type='error'
            )


@app.post("/sync-knowledge", response_model=SyncResponse)
async def sync_knowledge(
    request: SyncRequest = SyncRequest(),
    background_tasks: BackgroundTasks = BackgroundTasks()
):
    """
    Trigger knowledge base sync from database
    Called by Laravel when admin updates assessments/questions
    """
    logger.info(f"Knowledge sync requested (force={request.force})")
    
    try:
        # Perform sync in background for non-blocking
        if request.force:
            stats = knowledge_sync.sync_all(force=True)
        else:
            # Quick incremental sync
            stats = knowledge_sync.sync_all(force=False)
        
        return {
            "success": True,
            "message": "Knowledge base synced successfully",
            "stats": stats,
            "timestamp": datetime.utcnow().isoformat() + "Z"
        }
    
    except Exception as e:
        logger.error(f"Sync failed: {e}")
        return {
            "success": False,
            "message": f"Sync failed: {str(e)}",
            "stats": {"errors": [str(e)]},
            "timestamp": datetime.utcnow().isoformat() + "Z"
        }


@app.post("/init-student-context")
async def init_student_context(student_id: int):
    """
    Initialize or refresh student-specific context
    Can be called when student logs in
    """
    logger.info(f"Initializing context for student {student_id}")
    
    try:
        context = knowledge_sync.get_student_context(student_id)
        
        return {
            "success": True,
            "student_id": student_id,
            "context_summary": {
                "available_assessments": len(context.get('available_assessments', [])),
                "completed_assessments": len(context.get('completed_assessments', [])),
                "has_performance_data": bool(context.get('performance_summary'))
            },
            "timestamp": datetime.utcnow().isoformat() + "Z"
        }
    
    except Exception as e:
        logger.error(f"Context initialization failed for student {student_id}: {e}")
        raise HTTPException(status_code=500, detail=str(e))


@app.post("/test-groq")
async def test_groq():
    """Test Groq API connection"""
    try:
        from groq import Groq
        
        groq_client = Groq(api_key=os.getenv('GROQ_API_KEY'))
        response = groq_client.chat.completions.create(
            messages=[
                {
                    "role": "user",
                    "content": "Say 'Groq RAG is working!' in a friendly way."
                }
            ],
            model=os.getenv('GROQ_MODEL', 'llama-3.3-70b-versatile'),
            max_tokens=50
        )
        
        return {
            "success": True,
            "groq_response": response.choices[0].message.content,
            "model": os.getenv('GROQ_MODEL'),
            "timestamp": datetime.utcnow().isoformat() + "Z"
        }
    
    except Exception as e:
        logger.error(f"Groq test failed: {e}")
        raise HTTPException(status_code=500, detail=f"Groq API error: {str(e)}")


@app.on_event("startup")
async def startup_event():
    """Startup event handler"""
    logger.info("=" * 60)
    logger.info("RAG Service Starting Up")
    logger.info("=" * 60)
    logger.info(f"Groq Model: {os.getenv('GROQ_MODEL')}")
    logger.info(f"ChromaDB Path: {chromadb_path}")
    logger.info(f"Database: {os.getenv('DB_HOST')}")
    logger.info("=" * 60)


@app.on_event("shutdown")
async def shutdown_event():
    """Shutdown event handler"""
    logger.info("RAG Service shutting down...")


# Error handlers
@app.exception_handler(HTTPException)
async def http_exception_handler(request, exc):
    """Handle HTTP exceptions"""
    return JSONResponse(
        status_code=exc.status_code,
        content={
            "success": False,
            "error": exc.detail,
            "timestamp": datetime.utcnow().isoformat() + "Z"
        }
    )


@app.exception_handler(Exception)
async def general_exception_handler(request, exc):
    """Handle general exceptions"""
    logger.error(f"Unhandled exception: {exc}")
    return JSONResponse(
        status_code=500,
        content={
            "success": False,
            "error": "Internal server error",
            "timestamp": datetime.utcnow().isoformat() + "Z"
        }
    )


if __name__ == "__main__":
    import uvicorn
    
    port = int(os.getenv('PORT', 8001))
    host = os.getenv('HOST', '0.0.0.0')
    
    logger.info(f"Starting server on {host}:{port}")
    
    uvicorn.run(
        "main:app",
        host=host,
        port=port,
        reload=os.getenv('DEBUG', 'False').lower() == 'true',
        log_level="info"
    )
