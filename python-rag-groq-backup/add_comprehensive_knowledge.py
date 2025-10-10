"""
Add comprehensive knowledge documents to ChromaDB for dynamic RAG responses
This covers all 12+ query categories
"""
import chromadb
from sentence_transformers import SentenceTransformer
import logging

logging.basicConfig(level=logging.INFO)
logger = logging.getLogger(__name__)

# Initialize
chroma_client = chromadb.PersistentClient(path="./chromadb_storage")
embedding_model = SentenceTransformer('all-MiniLM-L6-v2')

# Get or create portal_info collection
try:
    collection = chroma_client.get_collection('portal_info')
    logger.info("Using existing portal_info collection")
except:
    collection = chroma_client.create_collection('portal_info')
    logger.info("Created new portal_info collection")

# Comprehensive knowledge documents
knowledge_docs = [
    {
        "id": "assessment_rules",
        "text": """
ASSESSMENT RULES AND GUIDELINES:

**General Rules**:
- Each assessment has a fixed time limit that starts when you click "Start Assessment"
- Timer cannot be paused or stopped once started
- Test auto-submits when time expires
- You can navigate between questions freely during the test
- You can skip questions and return to them later
- All answers are automatically saved as you go

**Passing Criteria**:
- Minimum passing score: 60% (varies by assessment)
- You need to answer correctly at least the passing percentage of questions
- Results are shown immediately after submission (if enabled by admin)
- Pass/Fail status is determined based on your percentage score

**Multiple Attempts**:
- Some assessments allow multiple attempts (check assessment details)
- Your best score is typically recorded
- There may be a waiting period between attempts
- Previous attempt scores are preserved in your history

**Academic Integrity**:
- No external help or resources allowed during test
- Using unauthorized materials is considered cheating
- Screen monitoring may be active (if configured)
- Violations result in test invalidation and disciplinary action

**Technical Requirements**:
- Stable internet connection required
- Modern web browser (Chrome, Firefox, Edge, Safari)
- JavaScript must be enabled
- No special software required
""",
        "category": "rules"
    },
    {
        "id": "taking_assessment_guide",
        "text": """
HOW TO TAKE AN ASSESSMENT:

**Step 1: Find Available Assessments**
- Go to your Dashboard
- Click on "Assessments" in the sidebar
- Browse available tests with their details
- Check duration, difficulty, and passing percentage

**Step 2: Start Assessment**
- Click "View Details" on an assessment
- Read the assessment description and rules carefully
- Check your internet connection
- Find a quiet place without distractions
- Click "Start Assessment" button

**Step 3: During the Test**
- Timer starts immediately - visible at the top
- Read each question carefully
- Select your answer by clicking the option
- Use "Next" button to move forward
- Use "Previous" button to go back
- Questions are automatically saved
- You can skip and return to questions

**Step 4: Submit Test**
- Review all answers if time permits
- Click "Submit Test" when done
- Confirm submission (you cannot change answers after this)
- Wait for results to load

**Step 5: View Results**
- Results appear immediately (if enabled)
- View your score, percentage, and pass/fail status
- Check correct answers (if enabled by admin)
- Find detailed breakdown in "Test History"

**What If Issues Occur**:
- Internet disconnects: Reconnect quickly, test state is saved
- Browser crashes: Reopen and continue (test state persists)
- Timer issues: Contact admin immediately
- Technical problems: Use "Report Issue" button
""",
        "category": "how_to"
    },
    {
        "id": "preparation_tips",
        "text": """
TEST PREPARATION AND STUDY TIPS:

**General Preparation**:
- Review assessment topics and syllabus beforehand
- Practice with similar questions if available
- Understand the assessment format and duration
- Know the passing percentage required
- Plan your time: divide total time by number of questions

**Study Strategies**:
- Focus on weak areas identified in previous tests
- Create a study schedule leading up to the test
- Use online resources and textbooks for topics
- Practice timed quizzes to improve speed
- Join study groups for discussion and clarification

**Time Management Tips**:
- Don't spend too much time on difficult questions
- Skip and return to hard questions later
- Allocate time per question (e.g., 2 minutes each)
- Keep track of remaining time during test
- Reserve 5 minutes at end for review

**Before the Test**:
- Get good sleep the night before
- Eat a healthy meal
- Arrive early / login early
- Test your internet connection
- Close all other programs/tabs
- Keep water nearby

**During the Test**:
- Read questions carefully - don't rush
- Eliminate obviously wrong options first
- Trust your first instinct on uncertain questions
- Don't panic if you don't know an answer - move on
- Mark difficult questions for review if possible

**For Aptitude Tests**:
- Practice mental math and quick calculations
- Learn common patterns and shortcuts
- Improve logical reasoning with puzzles
- Work on data interpretation skills

**For Technical Tests**:
- Review core concepts thoroughly
- Practice coding questions on platforms
- Understand syntax and common errors
- Know time/space complexity basics
- Review previous test questions

**Managing Test Anxiety**:
- Take deep breaths before starting
- Stay positive and confident
- Focus on one question at a time
- Remember: it's just a test, not life-defining
- Learn from mistakes for next time
""",
        "category": "preparation"
    },
    {
        "id": "assessment_categories",
        "text": """
ASSESSMENT CATEGORIES AND SUBJECTS:

**Aptitude Assessments**:
Categories include:
- Logical Reasoning: Patterns, sequences, analogies
- Quantitative Aptitude: Math, arithmetic, algebra
- Verbal Ability: English, comprehension, vocabulary
- Data Interpretation: Charts, graphs, tables
- General Knowledge: Current affairs, basic facts

Typically:
- Multiple choice questions
- 30-60 minutes duration
- Tests analytical and problem-solving skills
- Important for placement preparation

**Technical Assessments**:
Categories include:
- Programming: Code writing, debugging, algorithms
- Data Structures: Arrays, trees, graphs, etc.
- Databases: SQL queries, normalization, concepts
- Operating Systems: Processes, memory, scheduling
- Networks: Protocols, OSI model, concepts
- Web Development: HTML, CSS, JavaScript, frameworks

Typically:
- Mix of MCQs and coding questions
- 45-90 minutes duration
- Tests technical knowledge and application
- May include practical coding challenges

**Difficulty Levels**:
- Easy: Basic concepts, straightforward questions
- Medium: Moderate complexity, some application needed
- Hard: Advanced concepts, problem-solving required

**Choosing the Right Assessment**:
- Start with Easy level to build confidence
- Progress to Medium once comfortable
- Attempt Hard level when well-prepared
- Focus on categories relevant to your career goals
- Balance between Aptitude and Technical based on requirements
""",
        "category": "categories"
    },
    {
        "id": "results_and_performance",
        "text": """
UNDERSTANDING YOUR RESULTS AND PERFORMANCE:

**Viewing Results**:
- Go to "Test History" or "Results" section
- See list of all completed assessments
- Click on any assessment to see detailed results
- View date taken, time spent, score, and status

**Result Components**:
- Obtained Marks: Your raw score (e.g., 15 out of 20)
- Percentage: Your score as a percentage (e.g., 75%)
- Pass/Fail Status: Based on passing percentage threshold
- Time Taken: How long you took to complete the test
- Correct/Incorrect breakdown (if enabled)

**Performance Metrics**:
- Average Score: Mean of all your test percentages
- Pass Rate: Percentage of tests you've passed
- Category-wise performance: Scores by subject area
- Improvement trend: Are you getting better over time?
- Ranking: Your position compared to other students (if available)

**Analyzing Results**:
- Identify weak areas where you scored low
- Note which question types you struggle with
- Check time management: Did you rush or run out of time?
- Review correct answers to learn from mistakes
- Compare performance across different categories

**Improving Performance**:
- Focus study time on weak topics
- Retake failed assessments after preparation
- Practice similar questions
- Work on time management if needed
- Seek help for concepts you don't understand

**Retaking Assessments**:
- Check if retakes are allowed for specific assessment
- Review your previous attempt before retaking
- Prepare the weak areas identified
- Wait for any mandatory cooling-off period
- Your best score is usually kept

**Performance History**:
- Track your progress over weeks/months
- See improvement in specific categories
- Identify consistent strong areas
- Monitor preparation effectiveness
- Plan future test strategy based on history
""",
        "category": "results"
    },
    {
        "id": "technical_troubleshooting",
        "text": """
TECHNICAL ISSUES AND TROUBLESHOOTING:

**Common Issues and Solutions**:

**Internet Disconnect During Test**:
- Don't panic - your test state is automatically saved
- Reconnect to internet as quickly as possible
- Refresh the page or reopen the browser
- Your answers up to that point are preserved
- Timer continues to run, so minimize downtime
- If you can't reconnect, contact admin immediately

**Timer Not Working or Stuck**:
- Refresh the page first
- Check if JavaScript is enabled in browser
- Try a different browser (Chrome recommended)
- Clear browser cache and cookies
- Report to admin if issue persists

**Cannot Submit Test**:
- Check internet connection
- Try Submit button again after a few seconds
- Don't close browser - answers may be lost
- Take a screenshot as backup
- Contact admin for manual submission if needed

**Questions Not Loading**:
- Refresh the page
- Check internet speed
- Disable browser extensions that might interfere
- Try a different browser
- Ensure you're using a supported browser

**Answers Not Saving**:
- Each selection is auto-saved in real-time
- Verify by navigating to another question and back
- Check for error messages
- Don't rely solely on auto-save - submit as soon as done
- Contact support if issue continues

**Browser Requirements**:
- Use latest version of Chrome, Firefox, Edge, or Safari
- Enable JavaScript and cookies
- Disable pop-up blockers for the portal
- Use desktop/laptop for best experience
- Mobile browsing supported but not recommended for tests

**Reporting Issues**:
- Take screenshots of the problem
- Note exact time when issue occurred
- Note your browser and version
- Contact administrator through portal support
- Explain the issue clearly with details

**Preventive Measures**:
- Test your setup before starting assessment
- Use a stable, wired internet connection if possible
- Close unnecessary tabs and programs
- Update your browser to latest version
- Have admin contact information ready
""",
        "category": "troubleshooting"
    },
    {
        "id": "portal_features",
        "text": """
PORTAL FEATURES AND NAVIGATION:

**Dashboard**:
- Overview of available assessments
- Quick stats: completed tests, pass rate, average score
- Upcoming test deadlines and notifications
- Recent activity and performance trends

**Assessments Section**:
- Browse all available assessments
- Filter by category (Aptitude, Technical)
- Filter by difficulty level
- View assessment details before starting
- See past attempts if applicable

**Test History / Results**:
- Complete list of all tests you've taken
- View detailed results for each test
- Download performance reports (if enabled)
- Track improvement over time
- Access correct answers for review (if enabled)

**Profile Section**:
- View and update personal information
- Change password
- Set email preferences
- View enrollment status
- Manage notification settings

**Chatbot Assistant (Me!)**:
- Ask questions about assessments
- Get help with portal navigation
- View your results and performance
- Get study tips and preparation help
- Report issues and get support

**Features Available**:
- Timed assessments with auto-submit
- Instant result viewing (if enabled)
- Multiple attempt support (if allowed)
- Answer review after submission
- Performance analytics and insights
- Email notifications for new assessments
- Responsive design for mobile devices

**How to Navigate**:
- Use sidebar menu for main sections
- Dashboard is your starting point
- Click assessment cards to view details
- Use breadcrumbs to navigate back
- Profile icon (top right) for account settings

**Getting Help**:
- Use me (chatbot) for quick questions
- Check FAQ section for common queries
- Contact administrator for technical issues
- Email support for account problems
""",
        "category": "portal"
    },
    {
        "id": "motivational_support",
        "text": """
DEALING WITH TEST ANXIETY AND BUILDING CONFIDENCE:

**Understanding Test Anxiety**:
- It's completely normal to feel nervous before tests
- Anxiety can actually improve performance in small amounts
- Excessive anxiety can hurt performance - manage it proactively
- Remember: One test doesn't define your abilities

**Building Confidence**:
- Start with easier assessments to build momentum
- Celebrate small victories and improvements
- Focus on personal growth, not just scores
- Learn from mistakes instead of dwelling on them
- Recognize your strengths and improvement areas

**Before the Test**:
- Get adequate sleep (7-8 hours)
- Eat a healthy meal before starting
- Do light physical activity or stretching
- Practice deep breathing exercises
- Tell yourself: "I am prepared and capable"

**During the Test**:
- Take a deep breath if you feel stressed
- Read questions slowly and carefully
- Don't panic if you don't know an answer
- Move on and come back to difficult questions
- Trust your preparation and first instincts

**After the Test**:
- Don't immediately review answers - take a break
- Avoid comparing your experience with others
- Focus on what you learned, not just the score
- Plan improvement strategies for next time
- Reward yourself for completing the test

**If You Fail**:
- Failure is a learning opportunity, not the end
- Analyze what went wrong (preparation, time management, etc.)
- Make a concrete plan for improvement
- Many successful people failed tests before succeeding
- Your worth is not determined by test scores

**Staying Motivated**:
- Set realistic, achievable goals
- Track your progress and celebrate improvements
- Join study groups for peer support
- Take breaks to avoid burnout
- Remember why you're doing this - your career goals

**Positive Mindset Techniques**:
- Replace "I can't" with "I'm learning to"
- Visualize yourself succeeding
- Practice positive affirmations daily
- Focus on effort, not just outcomes
- Embrace challenges as growth opportunities

**When to Seek Help**:
- Persistent anxiety affecting daily life
- Physical symptoms like panic attacks
- Unable to prepare despite trying
- Feeling overwhelmed constantly
- Talk to counselors, mentors, or administrators
""",
        "category": "motivation"
    }
]

# Add documents to collection
try:
    for doc in knowledge_docs:
        # Generate embedding
        embedding = embedding_model.encode(doc["text"]).tolist()
        
        # Add to collection
        collection.add(
            embeddings=[embedding],
            documents=[doc["text"]],
            metadatas=[{
                "category": doc["category"],
                "type": "knowledge"
            }],
            ids=[doc["id"]]
        )
        logger.info(f"Added: {doc['id']}")
    
    logger.info(f"\n✅ Successfully added {len(knowledge_docs)} knowledge documents!")
    logger.info(f"Total documents in collection: {collection.count()}")
    
except Exception as e:
    logger.error(f"Error adding documents: {e}")
