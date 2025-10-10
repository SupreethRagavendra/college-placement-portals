"""
Comprehensive Testing Suite for Groq AI RAG System
Tests all components and scenarios
"""
import requests
import json
from groq import Groq
import os
from dotenv import load_dotenv
from colorama import init, Fore, Style
import time

# Initialize colorama for colored output
init()

# Load environment
load_dotenv()

# Configuration
RAG_SERVICE_URL = "http://localhost:8001"
GROQ_API_KEY = os.getenv('GROQ_API_KEY')
GROQ_MODEL = os.getenv('GROQ_MODEL', 'llama-3.3-70b-versatile')

# Test student ID (use a valid student ID from your database)
TEST_STUDENT_ID = 50


def print_header(text):
    """Print formatted header"""
    print("\n" + "=" * 70)
    print(f"{Fore.CYAN}{Style.BRIGHT}{text}{Style.RESET_ALL}")
    print("=" * 70)


def print_success(text):
    """Print success message"""
    print(f"{Fore.GREEN}✓ {text}{Style.RESET_ALL}")


def print_error(text):
    """Print error message"""
    print(f"{Fore.RED}✗ {text}{Style.RESET_ALL}")


def print_info(text):
    """Print info message"""
    print(f"{Fore.YELLOW}ℹ {text}{Style.RESET_ALL}")


def test_groq_direct():
    """Test 1: Direct Groq API connection"""
    print_header("TEST 1: Direct Groq API Connection")
    
    try:
        client = Groq(api_key=GROQ_API_KEY)
        
        response = client.chat.completions.create(
            messages=[
                {
                    "role": "user",
                    "content": "Say 'Hello from Groq AI!' in a friendly way."
                }
            ],
            model=GROQ_MODEL,
            max_tokens=50
        )
        
        result = response.choices[0].message.content
        print_success("Groq API connection successful")
        print_info(f"Response: {result}")
        print_info(f"Model: {GROQ_MODEL}")
        return True
    
    except Exception as e:
        print_error(f"Groq API connection failed: {e}")
        return False


def test_service_health():
    """Test 2: RAG Service health check"""
    print_header("TEST 2: RAG Service Health Check")
    
    try:
        response = requests.get(f"{RAG_SERVICE_URL}/health", timeout=10)
        
        if response.status_code == 200:
            data = response.json()
            print_success("RAG service is healthy")
            print_info(f"ChromaDB collections: {data.get('chromadb_collections', 0)}")
            print_info(f"Database status: {data.get('database', 'unknown')}")
            print_info(f"Groq model: {data.get('groq_model', 'unknown')}")
            return True
        else:
            print_error(f"Health check failed with status {response.status_code}")
            return False
    
    except requests.exceptions.ConnectionError:
        print_error("Cannot connect to RAG service. Is it running?")
        print_info("Start the service with: python main.py")
        return False
    except Exception as e:
        print_error(f"Health check error: {e}")
        return False


def test_service_status():
    """Test 3: Get detailed service status"""
    print_header("TEST 3: Service Status Details")
    
    try:
        response = requests.get(f"{RAG_SERVICE_URL}/status", timeout=10)
        
        if response.status_code == 200:
            data = response.json()
            print_success("Status retrieved successfully")
            
            collections = data.get('collections', [])
            print_info(f"Collections: {len(collections)}")
            for coll in collections:
                print(f"  - {coll['name']}: {coll['document_count']} documents")
            
            last_sync = data.get('last_sync', {})
            print_info(f"Last sync: {json.dumps(last_sync, indent=2)}")
            
            return True
        else:
            print_error(f"Status check failed: {response.status_code}")
            return False
    
    except Exception as e:
        print_error(f"Status check error: {e}")
        return False


def test_chat_assessment_query():
    """Test 4: Assessment query"""
    print_header("TEST 4: Assessment Query Test")
    
    query = "What assessments are available for me?"
    
    try:
        payload = {
            "student_id": TEST_STUDENT_ID,
            "query": query,
            "session_id": "test_session_1"
        }
        
        print_info(f"Query: '{query}'")
        print_info(f"Student ID: {TEST_STUDENT_ID}")
        
        start_time = time.time()
        response = requests.post(
            f"{RAG_SERVICE_URL}/chat",
            json=payload,
            timeout=30
        )
        elapsed = time.time() - start_time
        
        if response.status_code == 200:
            data = response.json()
            print_success(f"Query processed successfully in {elapsed:.2f}s")
            print_info(f"Response: {data.get('message', '')[:200]}...")
            print_info(f"Query type: {data.get('query_type', 'unknown')}")
            print_info(f"Actions: {len(data.get('actions', []))}")
            print_info(f"Follow-ups: {len(data.get('follow_up_questions', []))}")
            
            if data.get('data', {}).get('assessments'):
                print_info(f"Assessments found: {len(data['data']['assessments'])}")
            
            return True
        else:
            print_error(f"Chat request failed: {response.status_code}")
            print_error(response.text)
            return False
    
    except Exception as e:
        print_error(f"Chat test error: {e}")
        return False


def test_chat_result_query():
    """Test 5: Result query"""
    print_header("TEST 5: Result Query Test")
    
    query = "Show me my assessment results"
    
    try:
        payload = {
            "student_id": TEST_STUDENT_ID,
            "query": query,
            "session_id": "test_session_2"
        }
        
        print_info(f"Query: '{query}'")
        
        start_time = time.time()
        response = requests.post(
            f"{RAG_SERVICE_URL}/chat",
            json=payload,
            timeout=30
        )
        elapsed = time.time() - start_time
        
        if response.status_code == 200:
            data = response.json()
            print_success(f"Query processed successfully in {elapsed:.2f}s")
            print_info(f"Response: {data.get('message', '')[:200]}...")
            print_info(f"Query type: {data.get('query_type', 'unknown')}")
            
            if data.get('data', {}).get('results'):
                print_info(f"Results found: {len(data['data']['results'])}")
            
            return True
        else:
            print_error(f"Chat request failed: {response.status_code}")
            return False
    
    except Exception as e:
        print_error(f"Chat test error: {e}")
        return False


def test_chat_help_query():
    """Test 6: Help query"""
    print_header("TEST 6: Help Query Test")
    
    query = "How do I take an assessment?"
    
    try:
        payload = {
            "student_id": TEST_STUDENT_ID,
            "query": query,
            "session_id": "test_session_3"
        }
        
        print_info(f"Query: '{query}'")
        
        start_time = time.time()
        response = requests.post(
            f"{RAG_SERVICE_URL}/chat",
            json=payload,
            timeout=30
        )
        elapsed = time.time() - start_time
        
        if response.status_code == 200:
            data = response.json()
            print_success(f"Query processed successfully in {elapsed:.2f}s")
            print_info(f"Response: {data.get('message', '')[:300]}...")
            print_info(f"Query type: {data.get('query_type', 'unknown')}")
            
            return True
        else:
            print_error(f"Chat request failed: {response.status_code}")
            return False
    
    except Exception as e:
        print_error(f"Chat test error: {e}")
        return False


def test_knowledge_sync():
    """Test 7: Knowledge base sync"""
    print_header("TEST 7: Knowledge Base Sync Test")
    
    try:
        print_info("Triggering knowledge sync...")
        
        payload = {"force": False}
        response = requests.post(
            f"{RAG_SERVICE_URL}/sync-knowledge",
            json=payload,
            timeout=60
        )
        
        if response.status_code == 200:
            data = response.json()
            print_success("Knowledge sync completed")
            
            stats = data.get('stats', {})
            print_info(f"Assessments synced: {stats.get('assessments_synced', 0)}")
            print_info(f"Questions synced: {stats.get('questions_synced', 0)}")
            print_info(f"Categories synced: {stats.get('categories_synced', 0)}")
            
            if stats.get('errors'):
                print_error(f"Sync errors: {len(stats['errors'])}")
            
            return True
        else:
            print_error(f"Sync failed: {response.status_code}")
            return False
    
    except Exception as e:
        print_error(f"Sync test error: {e}")
        return False


def test_student_context_init():
    """Test 8: Student context initialization"""
    print_header("TEST 8: Student Context Initialization")
    
    try:
        response = requests.post(
            f"{RAG_SERVICE_URL}/init-student-context",
            params={"student_id": TEST_STUDENT_ID},
            timeout=10
        )
        
        if response.status_code == 200:
            data = response.json()
            print_success("Student context initialized")
            
            summary = data.get('context_summary', {})
            print_info(f"Available assessments: {summary.get('available_assessments', 0)}")
            print_info(f"Completed assessments: {summary.get('completed_assessments', 0)}")
            print_info(f"Has performance data: {summary.get('has_performance_data', False)}")
            
            return True
        else:
            print_error(f"Context init failed: {response.status_code}")
            return False
    
    except Exception as e:
        print_error(f"Context init error: {e}")
        return False


def test_response_time():
    """Test 9: Response time benchmark"""
    print_header("TEST 9: Response Time Benchmark")
    
    queries = [
        "What assessments are available?",
        "Show my results",
        "How to take a test?",
        "What is the passing score?",
        "Tell me about technical assessments"
    ]
    
    times = []
    
    for i, query in enumerate(queries, 1):
        try:
            print_info(f"Query {i}/{len(queries)}: '{query}'")
            
            payload = {
                "student_id": TEST_STUDENT_ID,
                "query": query,
                "session_id": f"benchmark_{i}"
            }
            
            start_time = time.time()
            response = requests.post(
                f"{RAG_SERVICE_URL}/chat",
                json=payload,
                timeout=30
            )
            elapsed = time.time() - start_time
            
            if response.status_code == 200:
                times.append(elapsed)
                print(f"  ⏱ {elapsed:.2f}s")
            else:
                print(f"  ✗ Failed")
        
        except Exception as e:
            print(f"  ✗ Error: {e}")
    
    if times:
        avg_time = sum(times) / len(times)
        min_time = min(times)
        max_time = max(times)
        
        print_success(f"Benchmark completed: {len(times)}/{len(queries)} queries")
        print_info(f"Average: {avg_time:.2f}s")
        print_info(f"Min: {min_time:.2f}s")
        print_info(f"Max: {max_time:.2f}s")
        
        if avg_time < 3.0:
            print_success("✓ Performance is excellent (< 3s average)")
            return True
        elif avg_time < 5.0:
            print_info("⚠ Performance is acceptable (< 5s average)")
            return True
        else:
            print_error("✗ Performance needs improvement (> 5s average)")
            return False
    else:
        print_error("Benchmark failed")
        return False


def run_all_tests():
    """Run all tests"""
    print_header("GROQ AI RAG SYSTEM - COMPREHENSIVE TEST SUITE")
    print_info(f"RAG Service: {RAG_SERVICE_URL}")
    print_info(f"Groq Model: {GROQ_MODEL}")
    print_info(f"Test Student ID: {TEST_STUDENT_ID}")
    
    tests = [
        ("Groq API Connection", test_groq_direct),
        ("Service Health", test_service_health),
        ("Service Status", test_service_status),
        ("Assessment Query", test_chat_assessment_query),
        ("Result Query", test_chat_result_query),
        ("Help Query", test_chat_help_query),
        ("Knowledge Sync", test_knowledge_sync),
        ("Student Context", test_student_context_init),
        ("Response Time", test_response_time),
    ]
    
    results = []
    
    for test_name, test_func in tests:
        try:
            result = test_func()
            results.append((test_name, result))
        except Exception as e:
            print_error(f"Test '{test_name}' crashed: {e}")
            results.append((test_name, False))
        
        time.sleep(0.5)  # Small delay between tests
    
    # Summary
    print_header("TEST SUMMARY")
    
    passed = sum(1 for _, result in results if result)
    total = len(results)
    
    for test_name, result in results:
        if result:
            print_success(f"{test_name}")
        else:
            print_error(f"{test_name}")
    
    print("\n" + "=" * 70)
    print(f"{Fore.CYAN}Total: {passed}/{total} tests passed{Style.RESET_ALL}")
    
    if passed == total:
        print(f"{Fore.GREEN}{Style.BRIGHT}✓ ALL TESTS PASSED!{Style.RESET_ALL}")
    elif passed >= total * 0.8:
        print(f"{Fore.YELLOW}⚠ Most tests passed, but some issues detected{Style.RESET_ALL}")
    else:
        print(f"{Fore.RED}✗ Multiple tests failed - system needs attention{Style.RESET_ALL}")
    
    print("=" * 70)


if __name__ == "__main__":
    run_all_tests()
