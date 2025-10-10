"""
Fallback Responses for Groq RAG System
Hardcoded responses when all other systems fail
"""
import re
from typing import Optional


def get_hardcoded_response(query: str) -> str:
    """
    Get hardcoded response based on query patterns
    This is the last line of defense when everything else fails
    """
    query_lower = query.lower().strip()
    
    # Greetings
    if any(word in query_lower for word in ['hi', 'hello', 'hey', 'good morning', 'good afternoon']):
        return (
            "Hello! 👋 I'm your placement portal assistant.\n\n"
            "I can help you with:\n"
            "• Available assessments\n"
            "• Your test results\n"
            "• How to take tests\n"
            "• Portal navigation\n\n"
            "What would you like to know?"
        )
    
    # Assessment queries
    if any(word in query_lower for word in ['assessment', 'test', 'exam', 'quiz']):
        if any(word in query_lower for word in ['how', 'take', 'start', 'begin']):
            return (
                "To take an assessment:\n\n"
                "1. Go to 'Assessments' from the sidebar\n"
                "2. Click 'Start Assessment' on any available test\n"
                "3. Read the instructions carefully\n"
                "4. Click 'Begin Test' when ready\n"
                "5. Answer all questions within the time limit\n"
                "6. Submit when done\n\n"
                "⚠️ Note: The timer cannot be paused once started!"
            )
        
        if any(word in query_lower for word in ['available', 'show', 'list', 'what']):
            return (
                "To view available assessments:\n\n"
                "• Click on 'Assessments' in the sidebar\n"
                "• You'll see all available tests with their details\n"
                "• Each assessment shows duration, category, and difficulty\n"
                "• Click 'Start Assessment' to begin any test\n\n"
                "Note: I'm currently unable to fetch the live list. Please check the Assessments page."
            )
    
    # Results queries
    if any(word in query_lower for word in ['result', 'score', 'mark', 'performance', 'grade']):
        if any(word in query_lower for word in ['check', 'view', 'see', 'show', 'my']):
            return (
                "To view your results:\n\n"
                "• Go to 'Test History' or 'Results' from the sidebar\n"
                "• You'll see all your completed assessments\n"
                "• Each result shows your score, percentage, and pass/fail status\n"
                "• Click on any result for detailed breakdown\n\n"
                "Note: I'm currently unable to fetch your live results. Please check the Results page."
            )
    
    # History queries
    if any(word in query_lower for word in ['history', 'past', 'previous', 'completed']):
        return (
            "To view your test history:\n\n"
            "• Navigate to 'Test History' from the sidebar\n"
            "• You'll see all your past attempts\n"
            "• Each entry shows date, score, and status\n"
            "• You can filter by date or assessment type\n\n"
            "Check the Test History page for your complete assessment record."
        )
    
    # Help queries
    if any(word in query_lower for word in ['help', 'assist', 'support', 'problem', 'issue']):
        return (
            "I can help you with:\n\n"
            "📝 **Assessments**\n"
            "• Finding available tests\n"
            "• Understanding test rules\n"
            "• Starting assessments\n\n"
            "📊 **Results**\n"
            "• Viewing your scores\n"
            "• Understanding pass criteria\n"
            "• Checking test history\n\n"
            "🔧 **Technical Issues**\n"
            "• If you're facing issues, try refreshing the page\n"
            "• Clear your browser cache if problems persist\n"
            "• Contact your administrator for urgent issues\n\n"
            "What specific help do you need?"
        )
    
    # Preparation/Tips queries
    if any(word in query_lower for word in ['prepare', 'study', 'tips', 'improve', 'practice']):
        return (
            "Here are some preparation tips:\n\n"
            "📚 **Before the Test**\n"
            "• Review the assessment description and requirements\n"
            "• Ensure stable internet connection\n"
            "• Find a quiet environment\n\n"
            "⏱️ **During the Test**\n"
            "• Read questions carefully\n"
            "• Manage your time wisely\n"
            "• Don't spend too long on difficult questions\n"
            "• Review answers if time permits\n\n"
            "💡 **General Tips**\n"
            "• Practice with available mock tests\n"
            "• Focus on your weak areas\n"
            "• Stay calm and confident\n\n"
            "Good luck with your preparation!"
        )
    
    # Pass/Fail criteria
    if any(word in query_lower for word in ['pass', 'passing', 'fail', 'criteria', 'percentage']):
        return (
            "Assessment Pass Criteria:\n\n"
            "• Most assessments require **60% or above** to pass\n"
            "• Some specialized tests may have different criteria\n"
            "• Check the assessment details for specific requirements\n"
            "• Your result will clearly show Pass/Fail status\n\n"
            "Each assessment page shows its passing percentage before you start."
        )
    
    # Navigation queries
    if any(word in query_lower for word in ['navigate', 'where', 'find', 'location', 'dashboard']):
        return (
            "Portal Navigation Guide:\n\n"
            "📍 **Main Sections**\n"
            "• **Dashboard** - Overview of your progress\n"
            "• **Assessments** - Available tests to take\n"
            "• **Test History** - Your completed assessments\n"
            "• **Profile** - Your account settings\n\n"
            "All sections are accessible from the sidebar menu on the left.\n"
            "Click on any section to navigate there."
        )
    
    # Time/Duration queries
    if any(word in query_lower for word in ['time', 'duration', 'long', 'minutes', 'hours']):
        return (
            "Assessment Time Information:\n\n"
            "• Test durations vary from 15 to 120 minutes\n"
            "• Each assessment shows its duration before you start\n"
            "• Timer starts when you begin the test\n"
            "• Timer cannot be paused once started\n"
            "• Remaining time is shown during the test\n\n"
            "⚠️ Make sure you have enough time before starting an assessment!"
        )
    
    # Rules/Instructions queries
    if any(word in query_lower for word in ['rule', 'instruction', 'guideline', 'policy']):
        return (
            "Assessment Rules & Guidelines:\n\n"
            "✅ **Do's**\n"
            "• Read all instructions carefully\n"
            "• Complete the test in one sitting\n"
            "• Submit before time expires\n"
            "• Use a stable internet connection\n\n"
            "❌ **Don'ts**\n"
            "• Don't refresh the page during test\n"
            "• Don't use multiple tabs/windows\n"
            "• Don't close browser without submitting\n"
            "• Don't attempt to pause the timer\n\n"
            "Following these rules ensures fair assessment for everyone."
        )
    
    # Profile/Account queries
    if any(word in query_lower for word in ['profile', 'account', 'password', 'email', 'settings']):
        return (
            "Account Management:\n\n"
            "• Go to 'Profile' from the sidebar\n"
            "• Update your personal information\n"
            "• Change password if needed\n"
            "• View your account status\n\n"
            "For account issues, contact your administrator."
        )
    
    # Default response
    return (
        "I'm here to help! I can assist you with:\n\n"
        "• 📝 Available assessments\n"
        "• 📊 Your test results\n"
        "• ❓ How to take tests\n"
        "• 🔍 Portal navigation\n"
        "• 💡 Preparation tips\n\n"
        "Please ask me a specific question, or navigate to the relevant section from the sidebar.\n\n"
        "Note: I'm currently operating in offline mode. For real-time data, please check the respective pages directly."
    )


def get_error_response(error_type: str = "general") -> str:
    """
    Get error-specific response
    """
    if error_type == "groq_api":
        return (
            "I'm having trouble connecting to the AI service right now.\n\n"
            "You can still:\n"
            "• Navigate to Assessments to see available tests\n"
            "• Check your Results page for scores\n"
            "• View Test History for past attempts\n\n"
            "The portal is fully functional - only the chatbot is temporarily limited."
        )
    
    elif error_type == "database":
        return (
            "I'm unable to fetch your data right now.\n\n"
            "Please navigate directly to:\n"
            "• Assessments - for available tests\n"
            "• Results - for your scores\n"
            "• Test History - for past attempts\n\n"
            "All portal features are working normally."
        )
    
    elif error_type == "chromadb":
        return (
            "I'm having trouble accessing my knowledge base.\n\n"
            "I can still help with basic questions about:\n"
            "• How to take assessments\n"
            "• Portal navigation\n"
            "• General guidelines\n\n"
            "What would you like to know?"
        )
    
    else:
        return (
            "I'm experiencing a temporary issue.\n\n"
            "While I work on this, you can:\n"
            "• Use the sidebar to navigate\n"
            "• Access all portal features directly\n"
            "• Try asking your question again\n\n"
            "Sorry for the inconvenience!"
        )
