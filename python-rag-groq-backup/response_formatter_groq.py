"""
Response Formatter for Groq AI RAG System
Formats AI responses consistently and adds contextual actions
"""
from typing import Dict, List, Any, Optional
from datetime import datetime
import re


class ResponseFormatter:
    """Format Groq AI responses with structured data and actions"""
    
    def __init__(self):
        self.action_patterns = {
            'view_assessment': r'assessment[s]?\s+(?:titled|named|called)?\s*["\']?([^"\']+)["\']?',
            'view_results': r'(?:your|my)\s+(?:results?|scores?|performance)',
            'take_assessment': r'(?:take|start|begin)\s+(?:the\s+)?assessment',
        }
    
    def format_response(
        self,
        message: str,
        data: Optional[Dict[str, Any]] = None,
        query_type: str = "general",
        student_id: Optional[int] = None,
        service_status: Optional[str] = None,
        fallback_used: Optional[str] = None
    ) -> Dict[str, Any]:
        """
        Format a complete response with actions and follow-ups
        
        Args:
            message: AI generated response
            data: Additional structured data
            query_type: Type of query (assessment_query, result_query, help_query, etc.)
            student_id: Student ID for personalized actions
            service_status: Current service status (groq_ai, chromadb, offline)
            fallback_used: Which fallback was used if any
        
        Returns:
            Formatted response dictionary
        """
        # Add status indicator to message
        status_indicator = self._get_status_indicator(service_status, fallback_used)
        formatted_message = self._format_message_with_status(message, status_indicator)
        
        response = {
            "success": True,
            "message": formatted_message,
            "data": data or {},
            "actions": self._generate_actions(message, data, query_type, student_id),
            "follow_up_questions": self._generate_follow_ups(query_type, data),
            "timestamp": datetime.utcnow().isoformat() + "Z",
            "query_type": query_type,
            "service_info": {
                "status": service_status or "unknown",
                "fallback": fallback_used,
                "indicator": status_indicator
            }
        }
        
        return response
    
    def _get_status_indicator(self, service_status: Optional[str], fallback_used: Optional[str]) -> str:
        """Get status indicator emoji and text"""
        if service_status == "operational" or fallback_used == "groq_ai":
            return "🟢 Groq AI"
        elif fallback_used == "chromadb":
            return "🟡 ChromaDB"
        elif fallback_used == "context_based":
            return "🟠 Context"
        elif service_status == "error" or fallback_used == "final":
            return "🔴 Offline"
        else:
            return "⚪ Unknown"
    
    def _format_message_with_status(self, message: str, status_indicator: str) -> str:
        """Add status indicator to message if not already present"""
        # Don't add status if message already has formatting
        if message.startswith(("📚", "📊", "📝", "⚠️", "👋")):
            return message
        
        # Add subtle status at the end
        return message
    
    def format_error(
        self,
        error_message: str,
        error_type: str = "general_error",
        fallback_response: Optional[str] = None
    ) -> Dict[str, Any]:
        """Format error response"""
        return {
            "success": False,
            "message": fallback_response or "I encountered an issue processing your request. Please try again.",
            "error": {
                "type": error_type,
                "message": error_message
            },
            "actions": [
                {
                    "label": "Try Again",
                    "action": "retry",
                    "icon": "refresh"
                },
                {
                    "label": "Get Help",
                    "url": "/student/help",
                    "icon": "help-circle"
                }
            ],
            "timestamp": datetime.utcnow().isoformat() + "Z"
        }
    
    def _clean_message(self, message: str) -> str:
        """Clean and format the message"""
        # Remove excessive whitespace
        message = re.sub(r'\s+', ' ', message).strip()
        
        # Ensure proper capitalization for sentences
        sentences = message.split('. ')
        sentences = [s.capitalize() if s else s for s in sentences]
        message = '. '.join(sentences)
        
        return message
    
    def _generate_actions(
        self,
        message: str,
        data: Optional[Dict[str, Any]],
        query_type: str,
        student_id: Optional[int]
    ) -> List[Dict[str, Any]]:
        """Generate contextual actions based on response"""
        actions = []
        
        # Add actions based on query type
        if query_type == "assessment_query" and data:
            if "assessments" in data and data["assessments"]:
                for assessment in data["assessments"][:3]:  # Limit to 3
                    actions.append({
                        "label": f"View {assessment.get('title', 'Assessment')}",
                        "url": f"/student/assessments/{assessment.get('id')}",
                        "icon": "eye",
                        "type": "view"
                    })
            
            # Add "View All Assessments" action
            actions.append({
                "label": "View All Assessments",
                "url": "/student/assessments",
                "icon": "list",
                "type": "navigation"
            })
        
        elif query_type == "result_query" and data:
            if "results" in data and data["results"]:
                for result in data["results"][:2]:  # Limit to 2
                    actions.append({
                        "label": f"View Result Details",
                        "url": f"/student/assessments/result/{result.get('id')}",
                        "icon": "file-text",
                        "type": "view"
                    })
            
            actions.append({
                "label": "View All Results",
                "url": "/student/history",
                "icon": "clock",
                "type": "navigation"
            })
        
        elif query_type == "help_query":
            actions.append({
                "label": "View Dashboard",
                "url": "/student/dashboard",
                "icon": "home",
                "type": "navigation"
            })
            actions.append({
                "label": "Browse Assessments",
                "url": "/student/assessments",
                "icon": "book-open",
                "type": "navigation"
            })
        
        return actions
    
    def _generate_follow_ups(
        self,
        query_type: str,
        data: Optional[Dict[str, Any]]
    ) -> List[str]:
        """Generate relevant follow-up questions"""
        follow_ups = []
        
        if query_type == "assessment_query":
            follow_ups = [
                "Would you like to see your past performance?",
                "Do you need tips for taking assessments?",
                "Want to know about assessment categories?"
            ]
        
        elif query_type == "result_query":
            follow_ups = [
                "Would you like study recommendations?",
                "Want to see available practice assessments?",
                "Need help understanding your results?"
            ]
        
        elif query_type == "help_query":
            follow_ups = [
                "Do you need help with a specific assessment?",
                "Want to know how to improve your scores?",
                "Need technical support?"
            ]
        
        else:  # general
            follow_ups = [
                "Would you like to see available assessments?",
                "Want to check your results?",
                "Need help with something specific?"
            ]
        
        return follow_ups
    
    def format_assessment_data(self, assessments: List[Dict]) -> Dict[str, Any]:
        """Format assessment data for response"""
        formatted = {
            "total_count": len(assessments),
            "assessments": []
        }
        
        for assessment in assessments:
            formatted["assessments"].append({
                "id": assessment.get("id"),
                "title": assessment.get("title") or assessment.get("name"),
                "category": assessment.get("category"),
                "duration": assessment.get("total_time"),
                "status": assessment.get("status"),
                "start_date": assessment.get("start_date"),
                "end_date": assessment.get("end_date"),
                "pass_percentage": assessment.get("pass_percentage", 60)
            })
        
        return formatted
    
    def format_result_data(self, results: List[Dict]) -> Dict[str, Any]:
        """Format result data for response"""
        formatted = {
            "total_attempts": len(results),
            "results": []
        }
        
        for result in results:
            formatted["results"].append({
                "id": result.get("id"),
                "assessment_title": result.get("assessment_title"),
                "score": result.get("obtained_marks"),
                "total": result.get("total_marks"),
                "percentage": result.get("percentage"),
                "status": result.get("pass_status"),
                "date": result.get("submit_time") or result.get("end_time")
            })
        
        return formatted
    
    def format_streaming_chunk(self, chunk: str) -> str:
        """Format a streaming response chunk"""
        return f"data: {chunk}\n\n"
