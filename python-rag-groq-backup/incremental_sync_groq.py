"""
Incremental Sync System for Groq AI RAG
Updates only changed data instead of full resync
"""
import psycopg2
from psycopg2.extras import RealDictCursor
import chromadb
from sentence_transformers import SentenceTransformer
from typing import Dict, List, Any
import logging
from datetime import datetime, timedelta
import json

logger = logging.getLogger(__name__)


class IncrementalSync:
    """Incremental knowledge sync to minimize database load"""
    
    def __init__(
        self,
        db_config: Dict[str, str],
        chroma_client: chromadb.ClientAPI
    ):
        self.db_config = db_config
        self.chroma_client = chroma_client
        self.embedding_model = SentenceTransformer('all-MiniLM-L6-v2')
        self.sync_state_file = 'sync_state.json'
        self.sync_state = self._load_sync_state()
    
    def _load_sync_state(self) -> Dict[str, Any]:
        """Load last sync state from file"""
        try:
            with open(self.sync_state_file, 'r') as f:
                return json.load(f)
        except FileNotFoundError:
            return {
                'last_assessment_sync': None,
                'last_question_sync': None,
                'synced_assessment_ids': [],
                'synced_question_ids': []
            }
    
    def _save_sync_state(self):
        """Save sync state to file"""
        try:
            with open(self.sync_state_file, 'w') as f:
                json.dump(self.sync_state, f, default=str)
        except Exception as e:
            logger.error(f"Failed to save sync state: {e}")
    
    def sync_new_assessments(self) -> Dict[str, Any]:
        """Sync only new or updated assessments"""
        logger.info("Starting incremental assessment sync...")
        stats = {'new': 0, 'updated': 0, 'deleted': 0, 'errors': []}
        
        try:
            conn = psycopg2.connect(**self.db_config, cursor_factory=RealDictCursor)
            cursor = conn.cursor()
            
            # Get timestamp of last sync
            last_sync = self.sync_state.get('last_assessment_sync')
            
            if last_sync:
                # Get assessments updated since last sync
                query = """
                    SELECT 
                        id, title, name, description, category, 
                        total_time, pass_percentage, start_date, end_date, 
                        status, difficulty_level, created_at, updated_at, deleted_at
                    FROM assessments 
                    WHERE updated_at > %s OR deleted_at > %s
                    ORDER BY updated_at DESC
                """
                cursor.execute(query, (last_sync, last_sync))
            else:
                # First sync - get all active assessments
                query = """
                    SELECT 
                        id, title, name, description, category, 
                        total_time, pass_percentage, start_date, end_date, 
                        status, difficulty_level, created_at, updated_at, deleted_at
                    FROM assessments 
                    WHERE deleted_at IS NULL AND status = 'active'
                    ORDER BY updated_at DESC
                """
                cursor.execute(query)
            
            assessments = cursor.fetchall()
            
            # Get collection
            try:
                collection = self.chroma_client.get_collection('assessments')
            except:
                collection = self.chroma_client.create_collection('assessments')
            
            synced_ids = []
            
            for assessment in assessments:
                try:
                    assessment_id = assessment['id']
                    doc_id = f"assessment_{assessment_id}"
                    
                    # Check if deleted
                    if assessment['deleted_at']:
                        # Remove from collection
                        try:
                            collection.delete(ids=[doc_id])
                            stats['deleted'] += 1
                            logger.info(f"Deleted assessment {assessment_id} from knowledge base")
                        except:
                            pass
                        continue
                    
                    # Create document
                    title = assessment['title'] or assessment['name'] or 'Untitled'
                    description = assessment['description'] or 'No description'
                    category = assessment['category'] or 'General'
                    
                    doc_text = f"""
                    Assessment: {title}
                    Category: {category}
                    Description: {description}
                    Duration: {assessment['total_time']} minutes
                    Pass Percentage: {assessment['pass_percentage']}%
                    Difficulty: {assessment.get('difficulty_level', 'Medium')}
                    Status: {assessment['status']}
                    Start Date: {assessment['start_date']}
                    End Date: {assessment['end_date']}
                    """.strip()
                    
                    # Create embedding
                    embedding = self.embedding_model.encode(doc_text).tolist()
                    
                    # Check if exists
                    is_new = assessment_id not in self.sync_state.get('synced_assessment_ids', [])
                    
                    # Upsert to collection
                    collection.upsert(
                        embeddings=[embedding],
                        documents=[doc_text],
                        metadatas=[{
                            'id': str(assessment_id),
                            'title': title,
                            'category': category,
                            'type': 'assessment',
                            'updated_at': str(assessment['updated_at'])
                        }],
                        ids=[doc_id]
                    )
                    
                    synced_ids.append(assessment_id)
                    
                    if is_new:
                        stats['new'] += 1
                    else:
                        stats['updated'] += 1
                
                except Exception as e:
                    logger.error(f"Error syncing assessment {assessment.get('id')}: {e}")
                    stats['errors'].append(str(e))
            
            # Update sync state
            self.sync_state['last_assessment_sync'] = datetime.utcnow().isoformat()
            self.sync_state['synced_assessment_ids'] = synced_ids
            self._save_sync_state()
            
            cursor.close()
            conn.close()
            
            logger.info(f"Assessment sync: {stats['new']} new, {stats['updated']} updated, {stats['deleted']} deleted")
            
        except Exception as e:
            logger.error(f"Incremental assessment sync failed: {e}")
            stats['errors'].append(str(e))
        
        return stats
    
    def sync_new_questions(self) -> Dict[str, Any]:
        """Sync only new or updated questions"""
        logger.info("Starting incremental question sync...")
        stats = {'new': 0, 'updated': 0, 'deleted': 0, 'errors': []}
        
        try:
            conn = psycopg2.connect(**self.db_config, cursor_factory=RealDictCursor)
            cursor = conn.cursor()
            
            # Get timestamp of last sync
            last_sync = self.sync_state.get('last_question_sync')
            
            if last_sync:
                # Get questions updated since last sync
                query = """
                    SELECT 
                        id, question_text, category, difficulty, 
                        option_a, option_b, option_c, option_d, 
                        correct_option, correct_answer, is_active,
                        created_at, updated_at
                    FROM questions 
                    WHERE updated_at > %s
                    ORDER BY updated_at DESC
                    LIMIT 500
                """
                cursor.execute(query, (last_sync,))
            else:
                # First sync
                query = """
                    SELECT 
                        id, question_text, category, difficulty, 
                        option_a, option_b, option_c, option_d, 
                        correct_option, correct_answer, is_active,
                        created_at, updated_at
                    FROM questions 
                    WHERE is_active = true
                    ORDER BY created_at DESC
                    LIMIT 1000
                """
                cursor.execute(query)
            
            questions = cursor.fetchall()
            
            # Get collection
            try:
                collection = self.chroma_client.get_collection('questions')
            except:
                collection = self.chroma_client.create_collection('questions')
            
            synced_ids = []
            
            for question in questions:
                try:
                    question_id = question['id']
                    doc_id = f"question_{question_id}"
                    
                    # Check if inactive (treated as deleted)
                    if not question['is_active']:
                        try:
                            collection.delete(ids=[doc_id])
                            stats['deleted'] += 1
                        except:
                            pass
                        continue
                    
                    # Parse category
                    category = question.get('category', 'General')
                    if isinstance(category, str) and category.startswith('{'):
                        try:
                            import json
                            cat_obj = json.loads(category)
                            category = cat_obj.get('name', 'General')
                        except:
                            category = 'General'
                    
                    # Create document
                    doc_text = f"""
                    Question: {question['question_text']}
                    Category: {category}
                    Difficulty: {question.get('difficulty', 'Medium')}
                    Options:
                    A) {question.get('option_a', '')}
                    B) {question.get('option_b', '')}
                    C) {question.get('option_c', '')}
                    D) {question.get('option_d', '')}
                    """.strip()
                    
                    # Create embedding
                    embedding = self.embedding_model.encode(doc_text).tolist()
                    
                    # Check if new
                    is_new = question_id not in self.sync_state.get('synced_question_ids', [])
                    
                    # Upsert to collection
                    collection.upsert(
                        embeddings=[embedding],
                        documents=[doc_text],
                        metadatas=[{
                            'id': str(question_id),
                            'category': category,
                            'difficulty': question.get('difficulty', 'Medium'),
                            'type': 'question',
                            'updated_at': str(question['updated_at'])
                        }],
                        ids=[doc_id]
                    )
                    
                    synced_ids.append(question_id)
                    
                    if is_new:
                        stats['new'] += 1
                    else:
                        stats['updated'] += 1
                
                except Exception as e:
                    logger.error(f"Error syncing question {question.get('id')}: {e}")
                    stats['errors'].append(str(e))
            
            # Update sync state
            self.sync_state['last_question_sync'] = datetime.utcnow().isoformat()
            self.sync_state['synced_question_ids'] = synced_ids
            self._save_sync_state()
            
            cursor.close()
            conn.close()
            
            logger.info(f"Question sync: {stats['new']} new, {stats['updated']} updated, {stats['deleted']} deleted")
            
        except Exception as e:
            logger.error(f"Incremental question sync failed: {e}")
            stats['errors'].append(str(e))
        
        return stats
    
    def sync_all_incremental(self) -> Dict[str, Any]:
        """Perform incremental sync of all data"""
        logger.info("Starting full incremental sync...")
        
        assessment_stats = self.sync_new_assessments()
        question_stats = self.sync_new_questions()
        
        return {
            'assessments': assessment_stats,
            'questions': question_stats,
            'timestamp': datetime.utcnow().isoformat()
        }
