"""
Knowledge Sync System for Groq AI RAG
Dynamically syncs data from Supabase PostgreSQL to ChromaDB
"""
import psycopg2
from psycopg2.extras import RealDictCursor
import chromadb
from sentence_transformers import SentenceTransformer
from typing import Dict, List, Any, Optional
import logging
import os
from datetime import datetime

logger = logging.getLogger(__name__)


class KnowledgeSync:
    """Sync knowledge base from database to ChromaDB"""
    
    def __init__(
        self,
        db_host: str,
        db_port: str,
        db_name: str,
        db_user: str,
        db_password: str,
        chroma_client: chromadb.ClientAPI
    ):
        self.db_config = {
            'host': db_host,
            'port': db_port,
            'database': db_name,
            'user': db_user,
            'password': db_password
        }
        self.chroma_client = chroma_client
        self.embedding_model = SentenceTransformer('all-MiniLM-L6-v2')
        self.last_sync = {}
    
    def connect_db(self):
        """Create database connection"""
        try:
            conn = psycopg2.connect(**self.db_config, cursor_factory=RealDictCursor)
            logger.info("Database connection established")
            return conn
        except Exception as e:
            logger.error(f"Database connection failed: {e}")
            raise
    
    def sync_all(self, force: bool = False) -> Dict[str, Any]:
        """
        Sync all knowledge base data
        
        Args:
            force: Force full resync even if data hasn't changed
        
        Returns:
            Sync statistics
        """
        logger.info("Starting full knowledge base sync...")
        stats = {
            'assessments_synced': 0,
            'questions_synced': 0,
            'categories_synced': 0,
            'errors': []
        }
        
        try:
            # Sync assessments
            assessment_stats = self.sync_assessments(force)
            stats['assessments_synced'] = assessment_stats['synced']
            
            # Sync questions
            question_stats = self.sync_questions(force)
            stats['questions_synced'] = question_stats['synced']
            
            # Sync categories
            category_stats = self.sync_categories(force)
            stats['categories_synced'] = category_stats['synced']
            
            self.last_sync['all'] = datetime.utcnow().isoformat()
            logger.info(f"Full sync completed: {stats}")
            
        except Exception as e:
            logger.error(f"Sync failed: {e}")
            stats['errors'].append(str(e))
        
        return stats
    
    def sync_assessments(self, force: bool = False) -> Dict[str, Any]:
        """Sync assessments from database to ChromaDB"""
        logger.info("Syncing assessments...")
        stats = {'synced': 0, 'errors': []}
        
        try:
            conn = self.connect_db()
            
            # Fetch active assessments
            query = """
                SELECT 
                    id, name, description, category, 
                    duration, total_time, pass_percentage, start_date, end_date,
                    status, is_active
                FROM assessments 
                WHERE deleted_at IS NULL 
                AND (status = 'active' OR is_active = true)
                ORDER BY created_at DESC
            """
            
            cursor = conn.cursor()
            cursor.execute(query)
            assessments = cursor.fetchall()
            
            # Get or create collection
            try:
                collection = self.chroma_client.get_collection('assessments')
                if force:
                    # Delete and recreate for full resync
                    self.chroma_client.delete_collection('assessments')
                    collection = self.chroma_client.create_collection('assessments')
            except:
                collection = self.chroma_client.create_collection('assessments')
            
            # Process each assessment
            for assessment in assessments:
                try:
                    # Create document text
                    title = assessment.get('name') or 'Untitled Assessment'
                    description = assessment.get('description') or 'No description'
                    category = assessment.get('category') or 'General'
                    
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
                    
                    # Add to collection
                    collection.add(
                        embeddings=[embedding],
                        documents=[doc_text],
                        metadatas=[{
                            'id': str(assessment['id']),
                            'title': title,
                            'category': category,
                            'type': 'assessment',
                            'updated_at': str(assessment['updated_at'])
                        }],
                        ids=[f"assessment_{assessment['id']}"]
                    )
                    
                    stats['synced'] += 1
                
                except Exception as e:
                    logger.error(f"Error syncing assessment {assessment.get('id')}: {e}")
                    stats['errors'].append(f"Assessment {assessment.get('id')}: {str(e)}")
            
            cursor.close()
            conn.close()
            
            logger.info(f"Synced {stats['synced']} assessments")
            self.last_sync['assessments'] = datetime.utcnow().isoformat()
            
        except Exception as e:
            logger.error(f"Assessment sync failed: {e}")
            stats['errors'].append(str(e))
        
        return stats
    
    def sync_questions(self, force: bool = False) -> Dict[str, Any]:
        """Sync questions from database to ChromaDB"""
        logger.info("Syncing questions...")
        stats = {'synced': 0, 'errors': []}
        
        try:
            conn = self.connect_db()
            cursor = conn.cursor()
            
            # Fetch active questions
            query = """
                SELECT 
                    id, question_text, category, difficulty, 
                    option_a, option_b, option_c, option_d, 
                    correct_option, correct_answer, created_at
                FROM questions 
                WHERE is_active = true
                ORDER BY created_at DESC
                LIMIT 1000
            """
            
            cursor.execute(query)
            questions = cursor.fetchall()
            
            # Get or create collection
            try:
                collection = self.chroma_client.get_collection('questions')
                if force:
                    self.chroma_client.delete_collection('questions')
                    collection = self.chroma_client.create_collection('questions')
            except:
                collection = self.chroma_client.create_collection('questions')
            
            # Process each question
            for question in questions:
                try:
                    # Create document text
                    category = question.get('category', 'General')
                    if isinstance(category, str) and category.startswith('{'):
                        # Parse JSON category
                        import json
                        try:
                            cat_obj = json.loads(category)
                            category = cat_obj.get('name', 'General')
                        except:
                            category = 'General'
                    
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
                    
                    # Add to collection
                    collection.add(
                        embeddings=[embedding],
                        documents=[doc_text],
                        metadatas=[{
                            'id': str(question['id']),
                            'category': category,
                            'difficulty': question.get('difficulty', 'Medium'),
                            'type': 'question'
                        }],
                        ids=[f"question_{question['id']}"]
                    )
                    
                    stats['synced'] += 1
                
                except Exception as e:
                    logger.error(f"Error syncing question {question.get('id')}: {e}")
                    stats['errors'].append(f"Question {question.get('id')}: {str(e)}")
            
            cursor.close()
            conn.close()
            
            logger.info(f"Synced {stats['synced']} questions")
            self.last_sync['questions'] = datetime.utcnow().isoformat()
            
        except Exception as e:
            logger.error(f"Question sync failed: {e}")
            stats['errors'].append(str(e))
        
        return stats
    
    def sync_categories(self, force: bool = False) -> Dict[str, Any]:
        """Sync categories from database"""
        logger.info("Syncing categories...")
        stats = {'synced': 0, 'errors': []}
        
        try:
            conn = self.connect_db()
            cursor = conn.cursor()
            
            # Fetch unique categories from assessments
            query = """
                SELECT DISTINCT category 
                FROM assessments 
                WHERE category IS NOT NULL 
                AND deleted_at IS NULL
            """
            
            cursor.execute(query)
            categories = cursor.fetchall()
            
            stats['synced'] = len(categories)
            logger.info(f"Found {stats['synced']} categories")
            
            cursor.close()
            conn.close()
            
            self.last_sync['categories'] = datetime.utcnow().isoformat()
            
        except Exception as e:
            logger.error(f"Category sync failed: {e}")
            stats['errors'].append(str(e))
        
        return stats
    
    def get_student_context(self, student_id: int) -> Dict[str, Any]:
        """
        Get student-specific context from database
        
        Args:
            student_id: Student ID
        
        Returns:
            Student context dictionary
        """
        context = {
            'available_assessments': [],
            'completed_assessments': [],
            'performance_summary': {}
        }
        
        try:
            conn = self.connect_db()
            cursor = conn.cursor()
            
            # Get available assessments
            query = """
                SELECT 
                    id, name, category, duration, total_time,
                    pass_percentage, start_date, end_date, status, is_active
                FROM assessments 
                WHERE deleted_at IS NULL 
                AND (status = 'active' OR is_active = true)
                AND (start_date IS NULL OR start_date <= CURRENT_DATE)
                AND (end_date IS NULL OR end_date >= CURRENT_DATE)
                ORDER BY created_at DESC
            """
            
            cursor.execute(query)
            assessments = cursor.fetchall()
            context['available_assessments'] = [dict(a) for a in assessments]
            
            # Get completed assessments
            query = """
                SELECT 
                    sa.id, sa.obtained_marks, sa.total_marks, 
                    sa.percentage, sa.pass_status, sa.submit_time,
                    a.name as assessment_name, a.name as assessment_title
                FROM student_assessments sa
                JOIN assessments a ON sa.assessment_id = a.id
                WHERE sa.student_id = %s 
                AND sa.status = 'completed'
                ORDER BY sa.submit_time DESC
                LIMIT 10
            """
            
            cursor.execute(query, (student_id,))
            results = cursor.fetchall()
            
            completed = []
            for result in results:
                result_dict = dict(result)
                result_dict['assessment_title'] = result_dict.get('assessment_title') or result_dict.get('assessment_name')
                completed.append(result_dict)
            
            context['completed_assessments'] = completed
            
            # Calculate performance summary
            if completed:
                total_attempts = len(completed)
                avg_score = sum(r['percentage'] for r in completed if r['percentage']) / total_attempts
                passed = sum(1 for r in completed if r.get('pass_status') == 'pass')
                pass_rate = (passed / total_attempts) * 100
                
                context['performance_summary'] = {
                    'total_attempts': total_attempts,
                    'average_score': round(avg_score, 2),
                    'pass_rate': round(pass_rate, 2)
                }
            
            cursor.close()
            conn.close()
            
        except Exception as e:
            logger.error(f"Error getting student context: {e}")
        
        return context
