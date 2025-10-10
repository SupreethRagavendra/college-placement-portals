"""
Initialize Knowledge Base for Groq AI RAG System
Creates ChromaDB collections and loads static portal information
"""
import chromadb
from sentence_transformers import SentenceTransformer
from groq import Groq
import os
from dotenv import load_dotenv
import logging
from knowledge_sync_groq import KnowledgeSync

# Setup logging
logging.basicConfig(
    level=logging.INFO,
    format='%(asctime)s - %(name)s - %(levelname)s - %(message)s'
)
logger = logging.getLogger(__name__)

# Load environment
load_dotenv()


def test_groq_connection():
    """Test Groq API connection"""
    try:
        groq_client = Groq(api_key=os.getenv('GROQ_API_KEY'))
        response = groq_client.chat.completions.create(
            messages=[{"role": "user", "content": "Hello"}],
            model=os.getenv('GROQ_MODEL', 'llama-3.3-70b-versatile'),
            max_tokens=10
        )
        logger.info("✓ Groq API connection successful")
        return True
    except Exception as e:
        logger.error(f"✗ Groq API connection failed: {e}")
        return False


def get_static_knowledge():
    """Get static portal knowledge documents"""
    return [
        {
            'id': 'portal_info_1',
            'text': """
            College Placement Training Portal - Overview
            This is a comprehensive placement training portal designed to help students prepare for placement assessments.
            Students can take various assessments in different categories like Aptitude and Technical.
            All assessments are timed and have specific pass percentages.
            Students can view their results and track their progress over time.
            """,
            'metadata': {'type': 'portal_overview', 'category': 'general'}
        },
        {
            'id': 'portal_info_2',
            'text': """
            How to Take an Assessment
            1. Log in to your student account
            2. Navigate to the Assessments page from your dashboard
            3. Browse available assessments and their details
            4. Click on an assessment to view its description, duration, and requirements
            5. Click "Start Assessment" to begin
            6. Answer all questions within the time limit
            7. Submit your assessment before time runs out
            8. View your results immediately after submission
            """,
            'metadata': {'type': 'how_to', 'category': 'assessment_guide'}
        },
        {
            'id': 'portal_info_3',
            'text': """
            Assessment Rules and Guidelines
            - Each assessment has a specific time limit (duration in minutes)
            - You must score at least the pass percentage (usually 60%) to pass
            - Most assessments allow only one attempt unless specified otherwise
            - Questions are multiple choice with options A, B, C, D
            - You can skip questions and return to them later during the assessment
            - A timer shows remaining time at the top of the assessment page
            - Submit before time expires or it will auto-submit
            - Results show your score, percentage, pass/fail status
            """,
            'metadata': {'type': 'rules', 'category': 'assessment_rules'}
        },
        {
            'id': 'portal_info_4',
            'text': """
            Assessment Categories
            The portal offers assessments in two main categories:
            
            1. Aptitude Assessments
               - Logical reasoning
               - Numerical ability
               - Verbal reasoning
               - Data interpretation
               - Problem solving
            
            2. Technical Assessments
               - Programming concepts
               - Data structures
               - Algorithms
               - Database management
               - Web technologies
               - Subject-specific technical knowledge
            """,
            'metadata': {'type': 'categories', 'category': 'assessment_types'}
        },
        {
            'id': 'portal_info_5',
            'text': """
            Understanding Your Results
            After completing an assessment, you will see:
            - Total marks obtained out of total possible marks
            - Percentage score calculated automatically
            - Pass or Fail status based on the pass percentage
            - Time taken to complete the assessment
            - Date and time of submission
            
            You can view all your past results in the History section.
            Each result page shows question-wise breakdown if enabled by admin.
            """,
            'metadata': {'type': 'results', 'category': 'results_guide'}
        },
        {
            'id': 'portal_info_6',
            'text': """
            Time Management Tips for Assessments
            - Read all questions quickly before starting to answer
            - Don't spend too much time on difficult questions initially
            - Mark difficult questions and return to them later if time permits
            - Keep an eye on the timer throughout the assessment
            - Allocate time proportionally based on number of questions
            - Leave 2-3 minutes at the end to review your answers
            - Submit with at least 30 seconds remaining to avoid auto-submit issues
            """,
            'metadata': {'type': 'tips', 'category': 'time_management'}
        },
        {
            'id': 'portal_info_7',
            'text': """
            Portal Navigation Guide
            
            Student Dashboard:
            - View available assessments
            - See upcoming assessment schedules
            - Quick access to recently completed assessments
            - Performance summary
            
            Assessments Page:
            - Browse all available assessments
            - Filter by category (Aptitude/Technical)
            - View assessment details before starting
            - See start and end dates
            
            History Page:
            - View all completed assessments
            - Check scores and results
            - Review performance over time
            - Access detailed result breakdowns
            
            Profile Page:
            - Update personal information
            - Change password
            - View account details
            """,
            'metadata': {'type': 'navigation', 'category': 'portal_guide'}
        },
        {
            'id': 'portal_info_8',
            'text': """
            Frequently Asked Questions (FAQ)
            
            Q: What is the passing score?
            A: The passing score varies by assessment, typically 60%. Check each assessment's details.
            
            Q: Can I retake an assessment?
            A: Most assessments allow only one attempt. Check the assessment details for multiple attempt information.
            
            Q: What happens if time runs out?
            A: The assessment will automatically submit with your current answers.
            
            Q: Can I see correct answers after submission?
            A: This depends on the assessment settings configured by the admin.
            
            Q: How is my percentage calculated?
            A: Percentage = (Marks Obtained / Total Marks) × 100
            
            Q: What if I face technical issues during an assessment?
            A: Contact your administrator immediately. Do not close the browser.
            
            Q: Can I pause an assessment?
            A: No, once started, the timer continues until submission or timeout.
            """,
            'metadata': {'type': 'faq', 'category': 'common_questions'}
        },
        {
            'id': 'portal_info_9',
            'text': """
            Troubleshooting Common Issues
            
            Cannot see an assessment:
            - Check if the assessment is active
            - Verify the start and end dates
            - Ensure you haven't already attempted it (if single attempt)
            - Refresh your browser
            
            Timer not working:
            - Ensure JavaScript is enabled in your browser
            - Clear browser cache and refresh
            - Try a different browser (Chrome recommended)
            
            Submission failed:
            - Check your internet connection
            - Try submitting again
            - Contact administrator if issue persists
            
            Results not showing:
            - Wait a few seconds and refresh the page
            - Check the History page
            - Results may be processed on submission
            """,
            'metadata': {'type': 'troubleshooting', 'category': 'technical_help'}
        },
        {
            'id': 'portal_info_10',
            'text': """
            Best Practices for Success
            
            Before Starting:
            - Ensure stable internet connection
            - Use a laptop or desktop (not mobile) for better experience
            - Close unnecessary browser tabs
            - Have pen and paper ready for calculations
            - Find a quiet place without distractions
            
            During Assessment:
            - Read questions carefully before answering
            - Don't second-guess too much
            - Trust your preparation
            - Stay calm if you encounter difficult questions
            - Use the full time available but don't rush
            
            After Assessment:
            - Review your results carefully
            - Identify weak areas for improvement
            - Practice similar questions
            - Take note of time management
            - Prepare better for next attempts
            """,
            'metadata': {'type': 'best_practices', 'category': 'success_tips'}
        }
    ]


def init_chromadb():
    """Initialize ChromaDB client and collections"""
    try:
        chromadb_path = os.getenv('CHROMADB_PATH', './chromadb_storage')
        client = chromadb.PersistentClient(path=chromadb_path)
        logger.info(f"✓ ChromaDB initialized at {chromadb_path}")
        return client
    except Exception as e:
        logger.error(f"✗ ChromaDB initialization failed: {e}")
        raise


def create_collections(client: chromadb.ClientAPI, reset: bool = False):
    """Create or reset ChromaDB collections"""
    collections = ['portal_info', 'assessments', 'questions']
    
    for collection_name in collections:
        try:
            if reset:
                try:
                    client.delete_collection(collection_name)
                    logger.info(f"Deleted existing collection: {collection_name}")
                except:
                    pass
            
            collection = client.get_or_create_collection(collection_name)
            logger.info(f"✓ Collection '{collection_name}' ready")
        
        except Exception as e:
            logger.error(f"✗ Error with collection '{collection_name}': {e}")
    
    return True


def load_static_knowledge(client: chromadb.ClientAPI):
    """Load static portal knowledge into ChromaDB"""
    try:
        logger.info("Loading static knowledge...")
        embedding_model = SentenceTransformer('all-MiniLM-L6-v2')
        
        collection = client.get_collection('portal_info')
        static_docs = get_static_knowledge()
        
        for doc in static_docs:
            # Create embedding
            embedding = embedding_model.encode(doc['text']).tolist()
            
            # Add to collection
            collection.add(
                embeddings=[embedding],
                documents=[doc['text']],
                metadatas=[doc['metadata']],
                ids=[doc['id']]
            )
        
        logger.info(f"✓ Loaded {len(static_docs)} static knowledge documents")
        return True
    
    except Exception as e:
        logger.error(f"✗ Failed to load static knowledge: {e}")
        return False


def sync_database_knowledge():
    """Sync knowledge from database"""
    try:
        logger.info("Syncing database knowledge...")
        
        # Initialize ChromaDB
        chromadb_path = os.getenv('CHROMADB_PATH', './chromadb_storage')
        client = chromadb.PersistentClient(path=chromadb_path)
        
        # Initialize sync
        sync = KnowledgeSync(
            db_host=os.getenv('DB_HOST'),
            db_port=os.getenv('DB_PORT'),
            db_name=os.getenv('DB_NAME'),
            db_user=os.getenv('DB_USER'),
            db_password=os.getenv('DB_PASSWORD'),
            chroma_client=client
        )
        
        # Perform sync
        stats = sync.sync_all(force=True)
        
        logger.info(f"✓ Database sync completed:")
        logger.info(f"  - Assessments: {stats['assessments_synced']}")
        logger.info(f"  - Questions: {stats['questions_synced']}")
        logger.info(f"  - Categories: {stats['categories_synced']}")
        
        if stats['errors']:
            logger.warning(f"  - Errors: {len(stats['errors'])}")
        
        return True
    
    except Exception as e:
        logger.error(f"✗ Database sync failed: {e}")
        return False


def main(reset: bool = False, skip_db_sync: bool = False):
    """Main initialization function"""
    logger.info("=" * 60)
    logger.info("Initializing Groq AI RAG Knowledge Base")
    logger.info("=" * 60)
    
    # Test Groq connection
    if not test_groq_connection():
        logger.error("Cannot proceed without Groq API connection")
        return False
    
    # Initialize ChromaDB
    client = init_chromadb()
    
    # Create collections
    if not create_collections(client, reset=reset):
        return False
    
    # Load static knowledge
    if not load_static_knowledge(client):
        return False
    
    # Sync database knowledge
    if not skip_db_sync:
        if not sync_database_knowledge():
            logger.warning("Database sync had issues, but continuing...")
    else:
        logger.info("Skipping database sync (--skip-db-sync flag)")
    
    logger.info("=" * 60)
    logger.info("✓ Knowledge base initialization completed successfully!")
    logger.info("=" * 60)
    logger.info("\nYou can now start the RAG service with:")
    logger.info("  python main.py")
    logger.info("or")
    logger.info("  ./start_rag.bat (Windows)")
    logger.info("  ./start_rag.sh (Linux/Mac)")
    
    return True


if __name__ == "__main__":
    import argparse
    
    parser = argparse.ArgumentParser(description='Initialize Groq RAG Knowledge Base')
    parser.add_argument('--reset', action='store_true', help='Reset existing collections')
    parser.add_argument('--skip-db-sync', action='store_true', help='Skip database sync')
    
    args = parser.parse_args()
    
    success = main(reset=args.reset, skip_db_sync=args.skip_db_sync)
    exit(0 if success else 1)
