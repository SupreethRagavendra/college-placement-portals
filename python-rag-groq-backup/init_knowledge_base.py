"""
Initialize Knowledge Base for Groq RAG System
Syncs data from Supabase and creates initial ChromaDB collections
"""
import os
import sys
from dotenv import load_dotenv
import chromadb
from sentence_transformers import SentenceTransformer
import logging
from knowledge_sync_groq import KnowledgeSync

# Load environment variables
load_dotenv()

# Setup logging
logging.basicConfig(
    level=logging.INFO,
    format='%(asctime)s - %(levelname)s - %(message)s'
)
logger = logging.getLogger(__name__)


def initialize_portal_info(chroma_client, embedding_model):
    """Initialize portal information collection with static knowledge"""
    logger.info("Initializing portal information...")
    
    try:
        # Delete existing collection if it exists
        try:
            chroma_client.delete_collection('portal_info')
        except:
            pass
        
        # Create new collection
        collection = chroma_client.create_collection('portal_info')
        
        # Portal knowledge documents
        documents = [
            {
                "id": "portal_overview",
                "text": """
                College Placement Portal Overview
                This is a comprehensive placement training portal for students.
                Students can take assessments, view results, and track their progress.
                The portal includes various assessment categories: Technical, Aptitude, and more.
                All assessments require a minimum of 60% to pass.
                Students can retake assessments if allowed by the administrator.
                """,
                "metadata": {"type": "overview", "category": "general"}
            },
            {
                "id": "assessment_rules",
                "text": """
                Assessment Rules and Guidelines
                1. Each assessment has a fixed duration that cannot be paused
                2. The passing percentage is typically 60% unless specified otherwise
                3. Students must complete all questions before submitting
                4. Timer starts immediately when you begin the assessment
                5. Results are shown immediately after submission
                6. Some assessments allow multiple attempts
                7. Questions are presented one at a time or all at once depending on settings
                """,
                "metadata": {"type": "rules", "category": "assessment"}
            },
            {
                "id": "how_to_take_test",
                "text": """
                How to Take an Assessment
                Step 1: Navigate to the Assessments page from the sidebar
                Step 2: Browse available assessments and check their details
                Step 3: Click 'Start Assessment' on the test you want to take
                Step 4: Read the instructions carefully before beginning
                Step 5: Click 'Begin Test' to start the timer
                Step 6: Answer all questions within the time limit
                Step 7: Review your answers if time permits
                Step 8: Click 'Submit' to complete the assessment
                Note: The timer cannot be paused once started!
                """,
                "metadata": {"type": "guide", "category": "how-to"}
            },
            {
                "id": "result_checking",
                "text": """
                How to Check Your Results
                Navigate to 'Test History' or 'Results' from the sidebar menu
                You will see all your completed assessments listed
                Each result shows: Assessment name, Score, Percentage, Pass/Fail status, Date
                Click on any result to view detailed breakdown
                The detailed view shows question-wise performance
                You can see which questions you got right or wrong
                Performance analytics help identify weak areas
                """,
                "metadata": {"type": "guide", "category": "results"}
            },
            {
                "id": "preparation_tips",
                "text": """
                Assessment Preparation Tips
                1. Review the assessment description and requirements before starting
                2. Ensure you have a stable internet connection
                3. Find a quiet environment free from distractions
                4. Have rough paper and pen ready for calculations
                5. Read each question carefully before answering
                6. Manage your time wisely - don't spend too long on one question
                7. If unsure, make an educated guess rather than leaving blank
                8. Review all answers if time permits before submitting
                9. Practice with available mock tests to improve
                10. Focus on your weak areas identified in previous results
                """,
                "metadata": {"type": "tips", "category": "preparation"}
            },
            {
                "id": "technical_assessments",
                "text": """
                Technical Assessments Information
                Technical assessments test your programming and technical knowledge
                Topics may include: Programming languages, Data structures, Algorithms
                Database concepts, Web development, Software engineering principles
                Questions can be multiple choice or coding problems
                Duration typically ranges from 30 to 90 minutes
                Difficulty levels: Easy, Medium, Hard
                Focus on understanding concepts rather than memorization
                """,
                "metadata": {"type": "info", "category": "technical"}
            },
            {
                "id": "aptitude_assessments",
                "text": """
                Aptitude Assessments Information
                Aptitude tests evaluate logical reasoning and analytical skills
                Topics include: Quantitative aptitude, Logical reasoning, Verbal ability
                Data interpretation, Pattern recognition, Problem solving
                These are time-bound tests requiring quick thinking
                Practice is key to improving speed and accuracy
                Duration typically 30-60 minutes with 20-50 questions
                """,
                "metadata": {"type": "info", "category": "aptitude"}
            },
            {
                "id": "troubleshooting",
                "text": """
                Common Issues and Solutions
                Issue: Timer not visible - Refresh the page, timer is at top of assessment
                Issue: Cannot submit test - Ensure all questions are answered
                Issue: Results not showing - Check Test History page or wait a moment
                Issue: Cannot start assessment - Check if you meet prerequisites
                Issue: Page frozen during test - Do not refresh, contact admin immediately
                Issue: Network disconnection - Reconnect quickly, progress is auto-saved
                For urgent issues during assessment, contact administrator
                """,
                "metadata": {"type": "troubleshooting", "category": "support"}
            },
            {
                "id": "profile_management",
                "text": """
                Managing Your Profile
                Access your profile from the sidebar menu
                Update personal information like name and email
                Change your password regularly for security
                View your overall statistics and progress
                Check your account status and verification
                Update notification preferences if available
                Ensure your email is correct for important updates
                """,
                "metadata": {"type": "guide", "category": "profile"}
            },
            {
                "id": "performance_tracking",
                "text": """
                Track Your Performance
                The portal tracks various performance metrics
                Overall pass rate shows success percentage
                Average score indicates typical performance
                Category-wise analysis shows strengths and weaknesses
                Time management metrics help improve speed
                Progress over time shows improvement trends
                Use these insights to focus your preparation
                """,
                "metadata": {"type": "info", "category": "analytics"}
            }
        ]
        
        # Add documents to collection
        for doc in documents:
            embedding = embedding_model.encode(doc["text"]).tolist()
            collection.add(
                embeddings=[embedding],
                documents=[doc["text"]],
                metadatas=[doc["metadata"]],
                ids=[doc["id"]]
            )
        
        logger.info(f"Added {len(documents)} documents to portal_info collection")
        return True
        
    except Exception as e:
        logger.error(f"Error initializing portal info: {e}")
        return False


def main():
    """Main initialization function"""
    logger.info("=" * 60)
    logger.info("Groq RAG Knowledge Base Initialization")
    logger.info("=" * 60)
    
    # Initialize ChromaDB
    chromadb_path = os.getenv('CHROMADB_PATH', './chromadb_storage')
    chroma_client = chromadb.PersistentClient(path=chromadb_path)
    logger.info(f"ChromaDB initialized at {chromadb_path}")
    
    # Initialize embedding model
    logger.info("Loading embedding model...")
    embedding_model = SentenceTransformer('all-MiniLM-L6-v2')
    logger.info("Embedding model loaded")
    
    # Initialize portal information
    if initialize_portal_info(chroma_client, embedding_model):
        logger.info("✓ Portal information initialized")
    else:
        logger.error("✗ Failed to initialize portal information")
        sys.exit(1)
    
    # Initialize knowledge sync
    try:
        logger.info("\nSyncing data from database...")
        knowledge_sync = KnowledgeSync(
            db_host=os.getenv('DB_HOST'),
            db_port=os.getenv('DB_PORT', '5432'),
            db_name=os.getenv('DB_NAME', 'postgres'),
            db_user=os.getenv('DB_USER', 'postgres'),
            db_password=os.getenv('DB_PASSWORD'),
            chroma_client=chroma_client
        )
        
        # Perform full sync
        stats = knowledge_sync.sync_all(force=True)
        
        logger.info("\n" + "=" * 40)
        logger.info("Sync Statistics:")
        logger.info(f"  Assessments synced: {stats['assessments_synced']}")
        logger.info(f"  Questions synced: {stats['questions_synced']}")
        logger.info(f"  Categories found: {stats['categories_synced']}")
        
        if stats['errors']:
            logger.warning(f"  Errors encountered: {len(stats['errors'])}")
            for error in stats['errors'][:5]:  # Show first 5 errors
                logger.warning(f"    - {error}")
        
        logger.info("=" * 40)
        
    except Exception as e:
        logger.warning(f"Database sync failed: {e}")
        logger.info("Continuing with static knowledge only...")
    
    # List all collections
    logger.info("\nFinal collections in ChromaDB:")
    collections = chroma_client.list_collections()
    for collection in collections:
        try:
            coll = chroma_client.get_collection(collection.name)
            count = coll.count()
            logger.info(f"  - {collection.name}: {count} documents")
        except:
            logger.info(f"  - {collection.name}: unknown count")
    
    logger.info("\n✅ Knowledge base initialization complete!")
    logger.info("You can now start the Groq RAG service.")


if __name__ == "__main__":
    main()
