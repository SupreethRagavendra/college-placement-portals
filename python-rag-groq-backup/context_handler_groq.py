"""
Context Handler for Groq AI RAG System
Processes queries with student-specific context and ChromaDB retrieval
"""
from typing import Dict, List, Any, Optional, Tuple
from groq import Groq
import chromadb
from sentence_transformers import SentenceTransformer
import logging
import os
import re
from datetime import datetime

logger = logging.getLogger(__name__)


class ContextHandler:
    """Handle context-aware query processing with Groq AI"""
    
    def __init__(self, groq_api_key: str, groq_model: str, chroma_client: chromadb.ClientAPI):
        self.groq_client = Groq(api_key=groq_api_key or 'your_groq_api_key_here')
        self.groq_model = groq_model
        self.chroma_client = chroma_client
        self.embedding_model = SentenceTransformer('all-MiniLM-L6-v2')
        self.last_fallback_used = None  # Track which fallback was used
        
        # Load collections
        try:
            self.portal_collection = chroma_client.get_collection('portal_info')
            self.assessment_collection = chroma_client.get_collection('assessments')
            self.question_collection = chroma_client.get_collection('questions')
            logger.info("Successfully loaded all ChromaDB collections")
        except Exception as e:
            logger.warning(f"Some collections not found: {e}")
            self.portal_collection = None
            self.assessment_collection = None
            self.question_collection = None
    
    def process_query(
        self,
        student_id: int,
        query: str,
        student_context: Optional[Dict[str, Any]] = None,
        max_context_docs: int = 5
    ) -> Tuple[str, Dict[str, Any], str]:
        """
        Process a student query with RAG
        
        Args:
            student_id: Student ID
            query: Student's question
            student_context: Additional context about student (assessments, results)
            max_context_docs: Maximum number of documents to retrieve
        
        Returns:
            Tuple of (response_message, data_dict, query_type)
        """
        # Detect query type
        query_type = self._detect_query_type(query)
        logger.info(f"Query type detected: {query_type} for student {student_id}")
        
        # Retrieve relevant context from ChromaDB
        relevant_docs = self._retrieve_relevant_context(query, max_context_docs)
        
        # Build comprehensive context
        context = self._build_context(student_id, query, relevant_docs, student_context)
        
        # Build prompt for Groq
        system_message, user_message = self._build_groq_prompt(context, query, query_type)
        
        # Query Groq AI with fallback chain
        try:
            response = self._query_groq(system_message, user_message)
            self.last_fallback_used = 'groq_ai'
            
            # Extract structured data if applicable
            data = self._extract_structured_data(student_context, query_type)
            
            return response, data, query_type
        
        except Exception as groq_error:
            logger.error(f"Groq API error: {groq_error}")
            
            # Try ChromaDB-based response without Groq
            if relevant_docs:
                try:
                    logger.info("Falling back to ChromaDB-based response")
                    response = self._generate_chromadb_response(query, relevant_docs, student_context, query_type)
                    self.last_fallback_used = 'chromadb'
                    data = self._extract_structured_data(student_context, query_type)
                    return response, data, query_type
                except Exception as chroma_error:
                    logger.error(f"ChromaDB fallback error: {chroma_error}")
            
            # Final fallback to context-based response
            logger.info("Using final context-based fallback")
            fallback = self._generate_fallback(query_type, student_context)
            self.last_fallback_used = 'context_based'
            data = self._extract_structured_data(student_context, query_type)
            return fallback, data, query_type
    
    def _detect_query_type(self, query: str) -> str:
        """Detect the type of query using semantic understanding"""
        query_lower = query.lower()
        
        # Greeting queries (simple, short queries)
        greeting_keywords = ['hi', 'hello', 'hey', 'good morning', 'good afternoon', 'good evening', 'sup', 'yo', 'howdy', 'buddy', "what's up", 'whats up']
        if any(greeting in query_lower for greeting in greeting_keywords) and len(query_lower) < 20:
            return "greeting_query"
        
        # Use semantic patterns instead of hardcoded keywords
        # Assessment availability queries
        if any(word in query_lower for word in ['available', 'show', 'list', 'active', 'upcoming', 'new']) and \
           any(word in query_lower for word in ['assessment', 'test', 'exam', 'quiz']):
            return "assessment_query"
        
        # Result/Performance queries
        if any(word in query_lower for word in ['result', 'score', 'mark', 'performance', 'grade', 'percentage']) or \
           ('how' in query_lower and any(word in query_lower for word in ['did', 'perform', 'do'])):
            return "result_query"
        
        # How-to queries
        if query_lower.startswith('how') or 'how to' in query_lower or 'how do' in query_lower:
            return "help_query"
        
        # Preparation/Study queries
        if any(word in query_lower for word in ['prepare', 'study', 'tips', 'improve', 'practice', 'strategy']):
            return "preparation_query"
        
        # Motivational queries
        if any(word in query_lower for word in ['nervous', 'afraid', 'scared', 'worried', 'confidence', 'motivation']):
            return "motivational_query"
        
        # Default: Let RAG handle it with full context
        return "general_query"
    
    def _retrieve_relevant_context(self, query: str, max_docs: int) -> List[Dict[str, Any]]:
        """Retrieve relevant documents from ChromaDB"""
        relevant_docs = []
        
        try:
            # Generate query embedding
            query_embedding = self.embedding_model.encode(query).tolist()
            
            # Search in portal info collection
            if self.portal_collection:
                try:
                    portal_results = self.portal_collection.query(
                        query_embeddings=[query_embedding],
                        n_results=min(3, max_docs)
                    )
                    if portal_results['documents'] and portal_results['documents'][0]:
                        for i, doc in enumerate(portal_results['documents'][0]):
                            relevant_docs.append({
                                'text': doc,
                                'metadata': portal_results['metadatas'][0][i] if portal_results['metadatas'] else {},
                                'source': 'portal_info'
                            })
                except Exception as e:
                    logger.warning(f"Error querying portal collection: {e}")
            
            # Search in assessment collection
            if self.assessment_collection and len(relevant_docs) < max_docs:
                try:
                    assessment_results = self.assessment_collection.query(
                        query_embeddings=[query_embedding],
                        n_results=min(2, max_docs - len(relevant_docs))
                    )
                    if assessment_results['documents'] and assessment_results['documents'][0]:
                        for i, doc in enumerate(assessment_results['documents'][0]):
                            relevant_docs.append({
                                'text': doc,
                                'metadata': assessment_results['metadatas'][0][i] if assessment_results['metadatas'] else {},
                                'source': 'assessments'
                            })
                except Exception as e:
                    logger.warning(f"Error querying assessment collection: {e}")
            
        except Exception as e:
            logger.error(f"Error retrieving context: {e}")
        
        logger.info(f"Retrieved {len(relevant_docs)} relevant documents")
        return relevant_docs
    
    def _build_context(
        self,
        student_id: int,
        query: str,
        relevant_docs: List[Dict],
        student_context: Optional[Dict[str, Any]]
    ) -> str:
        """Build comprehensive context for Groq"""
        context_parts = []
        
        # Add retrieved documents
        if relevant_docs:
            context_parts.append("=== KNOWLEDGE BASE INFORMATION ===")
            for i, doc in enumerate(relevant_docs, 1):
                context_parts.append(f"\n[Document {i} - {doc['source']}]")
                context_parts.append(doc['text'])
        
        # Add student-specific context
        if student_context:
            # Available assessments
            if 'available_assessments' in student_context:
                assessments = student_context['available_assessments']
                if assessments:
                    context_parts.append("\n=== AVAILABLE ASSESSMENTS FOR THIS STUDENT ===")
                    for assessment in assessments:
                        name = assessment.get('name', assessment.get('title', 'Unnamed Assessment'))
                        category = assessment.get('category', 'General')
                        duration = assessment.get('duration', str(assessment.get('total_time', '30')) + ' minutes')
                        pass_pct = assessment.get('pass_percentage', '60%')
                        difficulty = assessment.get('difficulty', 'Medium')
                        description = assessment.get('description', 'No description available')
                        
                        context_parts.append(
                            f"✓ Assessment Name: {name}\n"
                            f"  Category: {category}\n"
                            f"  Duration: {duration}\n"
                            f"  Passing: {pass_pct}\n"
                            f"  Difficulty: {difficulty}\n"
                            f"  Description: {description}"
                        )
                    context_parts.append(f"\nTotal Available: {len(assessments)} assessment(s)")
            
            # Student's completed assessments
            if 'completed_assessments' in student_context:
                completed = student_context['completed_assessments']
                if completed and len(completed) > 0:
                    context_parts.append("\n=== STUDENT'S COMPLETED ASSESSMENTS ===")
                    for result in completed:
                        name = result.get('assessment_name', result.get('assessment_title', 'Unknown'))
                        score = result.get('score', 'N/A')
                        percentage = result.get('percentage', '0%')
                        status = result.get('status', 'unknown')
                        date = result.get('date', 'Unknown date')
                        
                        context_parts.append(
                            f"✓ {name}: {percentage} ({score}) - "
                            f"{'PASSED' if status == 'pass' else 'FAILED'} on {date}"
                        )
                    context_parts.append(f"\nTotal Completed: {len(completed)} assessment(s)")
                else:
                    context_parts.append("\n=== STUDENT'S COMPLETED ASSESSMENTS ===")
                    context_parts.append("NONE - Student has not completed any assessments yet.")
                    context_parts.append("Total Completed: 0 assessment(s)")
            
            # Student performance summary
            if 'performance_summary' in student_context:
                perf = student_context['performance_summary']
                if perf:
                    context_parts.append("\n=== STUDENT PERFORMANCE SUMMARY ===")
                    context_parts.append(
                        f"Total Completed: {perf.get('total_completed', 0)}\n"
                        f"Passed: {perf.get('total_passed', 0)}\n"
                        f"Failed: {perf.get('total_failed', 0)}\n"
                        f"Average Score: {perf.get('average_percentage', '0%')}\n"
                        f"Pass Rate: {perf.get('pass_rate', '0%')}"
                    )
        
        return "\n".join(context_parts)
    
    def _build_groq_prompt(
        self,
        context: str,
        query: str,
        query_type: str
    ) -> Tuple[str, str]:
        """Build optimized prompt for Groq API"""
        
        system_message = f"""You are an intelligent AI assistant for a College Placement Training Portal with access to comprehensive student data.

YOUR ROLE:
- Answer questions using ONLY the context provided below
- Provide specific, data-driven responses based on THIS student's information
- Be helpful, encouraging, and professional
- Give actionable guidance with exact details

CRITICAL RULES:
1. **Use Real Data**: Always reference EXACT names, numbers, dates from context
2. **Student-Specific**: Only show THIS student's assessments/results/performance
3. **Be Direct**: No generic instructions like "navigate to dashboard" - provide actual data
4. **Format Well**: ALWAYS use proper line breaks (\n) between paragraphs and sections
5. **Structure**: Use bullet points (•), blank lines, and bold (**text**) for readability
6. **Be Accurate**: If data isn't in context, say "I don't have that information"
7. **Security**: NEVER show other students' data or admin information

FORMATTING REQUIREMENTS (CRITICAL):
- Put blank line (\n\n) after greeting
- Put blank line before and after lists
- Each bullet point on new line with • prefix
- Assessment details: one detail per line with dash prefix
- Always add line breaks for better readability

RESPONSE GUIDELINES BY QUERY TYPE:

**ASSESSMENT AVAILABILITY**:
- List actual assessment names with category, duration, difficulty
- If filtered (e.g., "Aptitude only"), apply that filter
- If time-based (e.g., "ending soon"), mention deadlines
- Include "Total Available: X assessments"

**ASSESSMENT DETAILS**:
- Provide specific info about requested assessment
- Include: duration, marks, passing %, difficulty, description
- Mention rules if query asks about requirements

**PERSONAL RESULTS**:
- Show THIS student's actual scores with dates
- Calculate averages, pass/fail counts from provided data
- If no completed assessments, say so clearly and suggest starting one
- For detailed results, mention what's available

**HOW-TO QUESTIONS**:
- Give clear step-by-step instructions
- Include warnings (e.g., "timer can't be paused")
- Suggest actual assessments they can practice with

**PREPARATION & TIPS**:
- Provide study strategies
- Reference actual assessments they have
- Give time management tips
- Encourage based on their performance data

**MOTIVATIONAL**:
- Be supportive and encouraging
- Reference their actual progress if available
- Provide concrete next steps

CONTEXT PROVIDED:
{context}

Query Type: {query_type}

RESPONSE FORMAT EXAMPLES (FOLLOW EXACTLY):

FOR GREETINGS (hi/hello/hey):
Hi there! 👋 I'm your placement assistant.

I can help you with:
• Viewing available assessments
• Checking your results  
• Taking tests
• Portal navigation

You have 1 assessment ready to take. Would you like to see it?

FOR ASSESSMENTS:
You have 1 assessment available:

📝 **Test3567**
- Category: Technical
- Duration: 30 minutes
- Difficulty: Medium

Ready to start? Click 'View Assessments' to begin!

FOR RESULTS - IF STUDENT HAS COMPLETED TESTS:
"Your recent results:

✅ **Test3567**: 75% - PASSED
   Date: Jan 6, 2025"

FOR RESULTS - IF STUDENT HAS NOT COMPLETED ANY TESTS:
"You haven't completed any assessments yet. 

Start your first test:
📝 **Test3567** (30 minutes)

Click 'View Assessments' to begin!"

CRITICAL: 
- For GREETINGS: Be warm and friendly, briefly mention what you can help with
- For ASSESSMENTS: Show full details with emoji formatting
- For RESULTS: Check "STUDENT'S COMPLETED ASSESSMENTS" - if EMPTY or "0 assessment(s)", say "You haven't completed any assessments yet"
- Don't confuse available assessments with completed assessments!
"""
        
        user_message = f"{query}\n\n(Please format your response with proper line breaks and bullet points as shown in examples)"
        
        return system_message, user_message
    
    def _query_groq(self, system_message: str, user_message: str) -> str:
        """Query Groq API"""
        try:
            chat_completion = self.groq_client.chat.completions.create(
                messages=[
                    {
                        "role": "system",
                        "content": system_message
                    },
                    {
                        "role": "user",
                        "content": user_message
                    }
                ],
                model=self.groq_model,
                temperature=float(os.getenv('GROQ_TEMPERATURE', 0.5)),  # Lower for more focused responses
                max_tokens=int(os.getenv('GROQ_MAX_TOKENS', 512)),  # Shorter, more concise
                top_p=0.9,  # Slightly more focused
                stream=False
            )
            
            response = chat_completion.choices[0].message.content
            
            # Post-process to ensure proper formatting
            response = self._enforce_formatting(response)
            
            logger.info(f"Groq API response received (length: {len(response)})")
            return response
        
        except Exception as e:
            logger.error(f"Groq API error: {e}")
            raise
    
    def _enforce_formatting(self, text: str) -> str:
        """Enforce proper formatting in AI response"""
        if not text:
            return text
        
        # Step 1: Fix greeting
        text = re.sub(r'(Hi there!\s*👋)\s+([iI]\'?m)', r'\1\n\n\2', text)
        
        # Step 2: Fix "I can help you with:" section
        text = re.sub(r'(assistant\.?)\s+(I can help you with:)', r'\1\n\n\2\n', text)
        
        # Step 3: Fix bullet points - force each on new line
        # Replace all • with newline + •
        text = re.sub(r'\s*•\s*', '\n• ', text)
        # Ensure first bullet starts after colon
        text = re.sub(r':\n•', ':\n• ', text)
        
        # Step 4: Fix "You have X assessment" section
        text = re.sub(r'(navigation)\s+(you have \d+)', r'\1\n\n\2', text, flags=re.IGNORECASE)
        text = re.sub(r'(Would you like to see it\?)\s+(you have)', r'\1\n\n\2', text, flags=re.IGNORECASE)
        
        # Step 5: Fix assessment list format
        text = re.sub(r'(you have \d+ assessment available:)\s*•', r'\1\n\n📝 ', text, flags=re.IGNORECASE)
        
        # Step 6: Fix assessment details with dashes
        text = re.sub(r'(\w+)\s+-\s+category:', r'\1\n- Category:', text)
        text = re.sub(r'(\w+)\s+-\s+duration:', r'\1\n- Duration:', text)  
        text = re.sub(r'(\w+)\s+-\s+difficulty:', r'\1\n- Difficulty:', text)
        
        # Step 7: Fix "ready to start" section
        text = re.sub(r'(easy)\s+(ready to start\?)', r'\1\n\n\2', text, flags=re.IGNORECASE)
        
        # Step 8: Fix "click" instructions
        text = re.sub(r'(ready to start\?)\s+(click)', r'\1\n\n\2', text, flags=re.IGNORECASE)
        
        # Step 9: Clean up - ensure space after emojis
        text = re.sub(r'(📝|✅|⚠️|❌|👋)([A-Za-z0-9])', r'\1 \2', text)
        
        # Step 10: Remove extra spaces and fix line breaks
        text = re.sub(r'  +', ' ', text)
        text = re.sub(r'\n{3,}', '\n\n', text)
        
        # Step 11: Capitalize sentences properly
        lines = text.split('\n')
        formatted_lines = []
        for line in lines:
            line = line.strip()
            if line:
                # Capitalize first word of new sentences (not bullets/dashes)
                if not line.startswith(('•', '-', '📝', '✅')):
                    if line and line[0].islower():
                        # Check if this should be capitalized (new sentence)
                        if len(formatted_lines) == 0 or (formatted_lines and formatted_lines[-1].endswith(('.', '!', '?'))):
                            line = line[0].upper() + line[1:]
                formatted_lines.append(line)
            else:
                formatted_lines.append('')
        
        return '\n'.join(formatted_lines).strip()
    
    def _extract_structured_data(
        self,
        student_context: Optional[Dict[str, Any]],
        query_type: str
    ) -> Dict[str, Any]:
        """Extract structured data for response"""
        data = {}
        
        if not student_context:
            return data
        
        if query_type == "assessment_query":
            if 'available_assessments' in student_context:
                data['assessments'] = student_context['available_assessments']
        
        elif query_type == "result_query":
            if 'completed_assessments' in student_context:
                data['results'] = student_context['completed_assessments']
            if 'performance_summary' in student_context:
                data['summary'] = student_context['performance_summary']
        
        return data
    
    def _generate_fallback(
        self,
        query_type: str,
        student_context: Optional[Dict[str, Any]]
    ) -> str:
        """Generate fallback response when Groq fails"""
        if query_type == "assessment_query":
            if student_context and 'available_assessments' in student_context:
                assessments = student_context['available_assessments']
                if assessments and len(assessments) > 0:
                    response = "📚 **Your Available Assessments**\n\n"
                    
                    for i, assessment in enumerate(assessments[:10], 1):
                        # Get assessment details
                        name = assessment.get('name') or assessment.get('title', 'Assessment')
                        category = assessment.get('category', 'General')
                        duration = assessment.get('total_time', 30)
                        pass_pct = assessment.get('pass_percentage', 60)
                        
                        # Format dates
                        start_date = assessment.get('start_date', '')
                        end_date = assessment.get('end_date', '')
                        date_info = ""
                        if start_date or end_date:
                            if end_date:
                                date_info = f" (Available until: {end_date})"
                            elif start_date:
                                date_info = f" (Started: {start_date})"
                        
                        # Build assessment card
                        response += f"**{i}. {name}**{date_info}\n"
                        response += f"   📂 Category: {category}\n"
                        response += f"   ⏱️ Duration: {duration} minutes\n"
                        response += f"   🎯 Pass Score: {pass_pct}%\n\n"
                    
                    if len(assessments) > 10:
                        response += f"➕ Plus {len(assessments) - 10} more assessments available\n\n"
                    
                    response += "💡 Click 'View Assessments' in your dashboard to start!"
                    return response
                else:
                    return "⚠️ **No Active Assessments**\n\nThere are currently no available assessments. Please check back later or contact your administrator."
            return "📋 To view available assessments:\n\n• Click on 'Assessments' in the sidebar\n• You'll see all available tests with their details\n• Each assessment shows duration, category, and difficulty\n• Click 'Start Assessment' to begin any test"
        
        elif query_type == "result_query":
            if student_context and 'completed_assessments' in student_context:
                completed = student_context['completed_assessments']
                if completed and len(completed) > 0:
                    response = "📊 **Your Assessment Results**\n\n"
                    
                    for i, result in enumerate(completed[:10], 1):
                        # Get result details
                        name = result.get('assessment_name') or result.get('assessment_title', 'Assessment')
                        percentage = result.get('percentage', 0)
                        obtained = result.get('obtained_marks', 0)
                        total = result.get('total_marks', 0)
                        status = result.get('pass_status', '')
                        submit_time = result.get('submit_time', '')
                        
                        # Status emoji
                        status_emoji = "✅" if status == 'pass' else "❌"
                        status_text = "PASSED" if status == 'pass' else "FAILED"
                        
                        # Format date
                        date_str = ""
                        if submit_time:
                            try:
                                from datetime import datetime
                                if isinstance(submit_time, str):
                                    date_obj = datetime.fromisoformat(submit_time.replace('Z', '+00:00'))
                                    date_str = f" • {date_obj.strftime('%d %b %Y')}"
                            except:
                                pass
                        
                        # Build result card
                        response += f"{status_emoji} **{name}**{date_str}\n"
                        response += f"   📈 Score: {obtained}/{total} marks ({percentage}%)\n"
                        response += f"   🎯 Status: {status_text}\n\n"
                    
                    # Add performance summary if available
                    if 'performance_summary' in student_context and student_context['performance_summary']:
                        summary = student_context['performance_summary']
                        avg_score = summary.get('average_score', 0)
                        pass_rate = summary.get('pass_rate', 0)
                        total_attempts = summary.get('total_attempts', 0)
                        
                        response += f"📊 **Overall Performance**\n"
                        response += f"• Total Assessments: {total_attempts}\n"
                        response += f"• Average Score: {avg_score}%\n"
                        response += f"• Pass Rate: {pass_rate}%\n"
                    
                    return response
                else:
                    # Student has NOT completed any tests
                    if 'available_assessments' in student_context and student_context['available_assessments']:
                        avail = student_context['available_assessments'][0]
                        name = avail.get('name', avail.get('title', 'Assessment'))
                        duration = avail.get('total_time', 30)
                        return f"📝 **No Results Yet**\n\nYou haven't completed any assessments yet.\n\nReady to start?\n• **{name}** ({duration} minutes)\n\n💡 Click 'View Assessments' to begin!"
                    return "📝 **No Results Yet**\n\nYou haven't completed any assessments yet. Start your first test from the Assessments page!"
            return "📊 You can view your assessment results in the history section of your dashboard."
        
        elif query_type == "greeting_query":
            # Warm greeting response
            if student_context and 'available_assessments' in student_context:
                count = len(student_context['available_assessments'])
                if count > 0:
                    return f"Hi there! 👋 I'm your placement assistant.\n\nI can help you with:\n• Viewing available assessments\n• Checking your results\n• Taking tests\n• Portal navigation\n\nYou have {count} assessment{'s' if count > 1 else ''} ready to take. Would you like to see {'them' if count > 1 else 'it'}?"
            return "Hi there! 👋 I'm your placement assistant.\n\nI can help you with:\n• Available assessments\n• Your test results\n• How to take tests\n• Portal navigation\n\nWhat would you like to know?"
        
        elif query_type == "help_query":
            return "I'm here to help! You can ask me about available assessments, your results, how to take tests, or general portal navigation. What would you like to know?"
        
        return "I'm your placement portal assistant. I can help you with assessments, results, and general questions. How can I assist you today?"
    
    def _generate_chromadb_response(
        self,
        query: str,
        relevant_docs: List[Dict[str, Any]],
        student_context: Optional[Dict[str, Any]],
        query_type: str
    ) -> str:
        """
        Generate response using only ChromaDB documents and student context
        without calling Groq API
        """
        response_parts = []
        
        # Add greeting for greeting queries
        if query_type == "greeting_query":
            if student_context and 'available_assessments' in student_context:
                count = len(student_context['available_assessments'])
                response_parts.append(f"Hello! 👋 I'm your placement assistant.\n\n")
                response_parts.append(f"You have {count} assessment{'s' if count > 1 else ''} available.")
            else:
                response_parts.append("Hello! 👋 I'm here to help with your placement portal queries.")
        
        # Add relevant document information
        if relevant_docs:
            response_parts.append("\n\nBased on our knowledge base:\n")
            for doc in relevant_docs[:2]:  # Use top 2 most relevant
                text = doc.get('text', '')
                # Extract key information from document
                lines = text.split('\n')
                for line in lines[:3]:  # First 3 lines usually most relevant
                    if line.strip():
                        response_parts.append(f"• {line.strip()}")
        
        # Add student-specific information based on query type
        if query_type == "assessment_query" and student_context:
            if 'available_assessments' in student_context and student_context['available_assessments']:
                response_parts.append("\n\nYour available assessments:")
                for assess in student_context['available_assessments'][:3]:
                    name = assess.get('name', assess.get('title', 'Assessment'))
                    duration = assess.get('total_time', '30')
                    category = assess.get('category', 'General')
                    response_parts.append(f"\n📝 **{name}**")
                    response_parts.append(f"- Category: {category}")
                    response_parts.append(f"- Duration: {duration} minutes")
        
        elif query_type == "result_query" and student_context:
            if 'completed_assessments' in student_context and student_context['completed_assessments']:
                response_parts.append("\n\nYour recent results:")
                for result in student_context['completed_assessments'][:3]:
                    name = result.get('assessment_title', result.get('assessment_name', 'Assessment'))
                    percentage = result.get('percentage', 0)
                    status = 'PASSED' if result.get('pass_status') == 'pass' else 'FAILED'
                    response_parts.append(f"\n✅ **{name}**: {percentage}% - {status}")
            else:
                response_parts.append("\n\nYou haven't completed any assessments yet. Start from the Assessments page!")
        
        # Add helpful navigation tip
        response_parts.append("\n\nFor more details, please check the relevant section from your dashboard.")
        
        return '\n'.join(response_parts).strip()
