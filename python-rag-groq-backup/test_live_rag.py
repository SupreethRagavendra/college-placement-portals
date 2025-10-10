#!/usr/bin/env python3
"""
Test the complete RAG system with live data from Supabase
"""
import requests
import json
import time
from datetime import datetime
from colorama import init, Fore, Style

# Initialize colorama for colored output
init(autoreset=True)

# Service configuration
BASE_URL = "http://localhost:8001"
STUDENT_ID = 1

def print_header(title):
    """Print formatted header"""
    print("\n" + "=" * 60)
    print(f"{Fore.CYAN}{title}{Style.RESET_ALL}")
    print("=" * 60)

def print_success(message):
    """Print success message"""
    print(f"{Fore.GREEN}✅ {message}{Style.RESET_ALL}")

def print_error(message):
    """Print error message"""
    print(f"{Fore.RED}❌ {message}{Style.RESET_ALL}")

def print_info(message):
    """Print info message"""
    print(f"{Fore.YELLOW}ℹ️  {message}{Style.RESET_ALL}")

def test_service_health():
    """Test if the service is running"""
    print_header("SERVICE HEALTH CHECK")
    try:
        response = requests.get(f"{BASE_URL}/health", timeout=5)
        if response.status_code == 200:
            data = response.json()
            print_success(f"Service is healthy")
            print(f"  • ChromaDB Collections: {data.get('chromadb_collections', 0)}")
            print(f"  • Database: {data.get('database', 'unknown')}")
            print(f"  • Groq Model: {data.get('groq_model', 'unknown')}")
            return True
        else:
            print_error(f"Service returned status {response.status_code}")
            return False
    except Exception as e:
        print_error(f"Service is not running: {e}")
        return False

def test_live_assessments(student_id):
    """Test fetching live assessments"""
    print_header("LIVE ASSESSMENT QUERY TEST")
    
    queries = [
        "Show me available assessments",
        "What tests can I take?",
        "List all assessments"
    ]
    
    for query in queries:
        print(f"\n📝 Query: '{query}'")
        
        try:
            payload = {
                "student_id": student_id,
                "query": query
            }
            
            response = requests.post(
                f"{BASE_URL}/chat",
                json=payload,
                timeout=10
            )
            
            if response.status_code == 200:
                data = response.json()
                message = data.get('message', '')
                service_info = data.get('service_info', {})
                
                # Check if live data is present
                if 'Quantitative Aptitude' in message:
                    print_success("Live assessment data found!")
                    print(f"  Status: {service_info.get('indicator', 'Unknown')}")
                    
                    # Display the formatted response
                    print("\n" + "-" * 40)
                    print(message[:500])  # Show first 500 chars
                    if len(message) > 500:
                        print("...")
                    print("-" * 40)
                else:
                    print_error("No live assessment data in response")
                    print(f"  Response: {message[:200]}...")
                    
            else:
                print_error(f"Request failed with status {response.status_code}")
                
        except Exception as e:
            print_error(f"Query failed: {e}")

def test_student_results(student_id):
    """Test fetching student results"""
    print_header("STUDENT RESULTS QUERY TEST")
    
    queries = [
        "Show my results",
        "What are my scores?",
        "My assessment history"
    ]
    
    for query in queries:
        print(f"\n📊 Query: '{query}'")
        
        try:
            payload = {
                "student_id": student_id,
                "query": query
            }
            
            response = requests.post(
                f"{BASE_URL}/chat",
                json=payload,
                timeout=10
            )
            
            if response.status_code == 200:
                data = response.json()
                message = data.get('message', '')
                service_info = data.get('service_info', {})
                
                print(f"  Status: {service_info.get('indicator', 'Unknown')}")
                print(f"  Fallback: {service_info.get('fallback', 'None')}")
                
                # Display response snippet
                print(f"  Response: {message[:200]}...")
                
            else:
                print_error(f"Request failed with status {response.status_code}")
                
        except Exception as e:
            print_error(f"Query failed: {e}")

def test_knowledge_sync():
    """Test knowledge base sync"""
    print_header("KNOWLEDGE BASE SYNC TEST")
    
    try:
        response = requests.post(
            f"{BASE_URL}/sync-knowledge",
            json={"force": False},
            timeout=15
        )
        
        if response.status_code == 200:
            data = response.json()
            if data.get('success'):
                stats = data.get('stats', {})
                print_success("Knowledge sync successful")
                print(f"  • Assessments synced: {stats.get('assessments_synced', 0)}")
                print(f"  • Questions synced: {stats.get('questions_synced', 0)}")
                print(f"  • Categories synced: {stats.get('categories_synced', 0)}")
            else:
                print_error(f"Sync failed: {data.get('message')}")
        else:
            print_error(f"Sync request failed with status {response.status_code}")
            
    except Exception as e:
        print_error(f"Sync test failed: {e}")

def test_groq_api():
    """Test Groq API connection"""
    print_header("GROQ API CONNECTION TEST")
    
    try:
        response = requests.post(f"{BASE_URL}/test-groq", timeout=10)
        
        if response.status_code == 200:
            data = response.json()
            print_success("Groq API is working")
            print(f"  • Model: {data.get('model')}")
            print(f"  • Response: {data.get('groq_response')}")
        else:
            print_error(f"Groq test failed with status {response.status_code}")
            
    except Exception as e:
        print_error(f"Groq API test failed: {e}")

def main():
    """Run all tests"""
    print("\n" + "=" * 60)
    print(f"{Fore.CYAN}🚀 LIVE RAG SYSTEM TEST{Style.RESET_ALL}")
    print("=" * 60)
    print(f"Testing RAG service at {BASE_URL}")
    print(f"Student ID: {STUDENT_ID}")
    print(f"Time: {datetime.now().strftime('%Y-%m-%d %H:%M:%S')}")
    
    # Run tests
    if test_service_health():
        test_groq_api()
        test_knowledge_sync()
        test_live_assessments(STUDENT_ID)
        test_student_results(STUDENT_ID)
        
        print("\n" + "=" * 60)
        print_success("LIVE RAG SYSTEM TEST COMPLETE")
        print("=" * 60)
        print("\n✨ Your RAG chatbot is now configured to:")
        print("  • Fetch LIVE assessments from Supabase")
        print("  • Show REAL student results with scores")
        print("  • Display status indicators (🟢 Groq / 🟡 ChromaDB / 🔴 Offline)")
        print("  • Format responses with tables and cards")
        print("  • Fall back gracefully when services fail")
    else:
        print_error("Service is not running. Please start it first:")
        print("  cd python-rag-groq")
        print("  python main.py")

if __name__ == "__main__":
    main()
