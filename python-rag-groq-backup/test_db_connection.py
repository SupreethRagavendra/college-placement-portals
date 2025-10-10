#!/usr/bin/env python3
"""
Test database connection and fetch live assessments
"""
import os
from dotenv import load_dotenv
import psycopg2
from psycopg2.extras import RealDictCursor

# Load environment variables
load_dotenv()

def test_db_connection():
    """Test database connection and fetch assessments"""
    print("🔍 Testing database connection...")
    
    try:
        # Database configuration
        db_config = {
            'host': os.getenv('DB_HOST'),
            'port': os.getenv('DB_PORT'),
            'database': os.getenv('DB_NAME'),
            'user': os.getenv('DB_USER'),
            'password': os.getenv('DB_PASSWORD')
        }
        
        print(f"Connecting to: {db_config['host']}:{db_config['port']}")
        
        # Connect to database
        conn = psycopg2.connect(**db_config, cursor_factory=RealDictCursor)
        cursor = conn.cursor()
        
        print("✅ Database connection successful!")
        
        # Test query - fetch active assessments
        # First, let's check what columns exist
        check_query = """
            SELECT column_name 
            FROM information_schema.columns 
            WHERE table_name = 'assessments'
        """
        
        cursor.execute(check_query)
        columns = cursor.fetchall()
        print("\n📋 Available columns in assessments table:")
        for col in columns:
            print(f"  - {col['column_name']}")
        
        # Now fetch assessments with correct columns
        query = """
            SELECT 
                id, name, category, duration, 
                pass_percentage, start_date, end_date, is_active
            FROM assessments 
            WHERE deleted_at IS NULL 
            AND is_active = true
            ORDER BY created_at DESC
            LIMIT 10
        """
        
        cursor.execute(query)
        assessments = cursor.fetchall()
        
        print(f"\n📝 Found {len(assessments)} active assessments:")
        print("=" * 60)
        
        if assessments:
            for i, assessment in enumerate(assessments, 1):
                name = assessment.get('name') or assessment.get('title', 'Unnamed Assessment')
                category = assessment.get('category', 'General')
                duration = assessment.get('total_time', 30)
                pass_pct = assessment.get('pass_percentage', 60)
                
                print(f"{i}. {name}")
                print(f"   Category: {category}")
                print(f"   Duration: {duration} minutes")
                print(f"   Pass Score: {pass_pct}%")
                print(f"   Status: {assessment.get('status', 'active')}")
                print()
        else:
            print("❌ No active assessments found!")
            
        # Test student context query
        print("\n🎓 Testing student context query...")
        student_id = 1  # Test with student ID 1
        
        context_query = """
            SELECT 
                sa.id, sa.obtained_marks, sa.total_marks, 
                sa.percentage, sa.pass_status, sa.submit_time,
                a.name as assessment_name, a.name as assessment_title
            FROM student_assessments sa
            JOIN assessments a ON sa.assessment_id = a.id
            WHERE sa.student_id = %s 
            AND sa.status = 'completed'
            ORDER BY sa.submit_time DESC
            LIMIT 5
        """
        
        cursor.execute(context_query, (student_id,))
        results = cursor.fetchall()
        
        print(f"Found {len(results)} completed assessments for student {student_id}")
        
        cursor.close()
        conn.close()
        
        return True
        
    except Exception as e:
        print(f"❌ Database connection failed: {e}")
        return False

if __name__ == "__main__":
    print("=" * 60)
    print("DATABASE CONNECTION TEST")
    print("=" * 60)
    
    success = test_db_connection()
    
    print("\n" + "=" * 60)
    if success:
        print("✅ Database test completed successfully!")
        print("The RAG service should now be able to fetch live assessments.")
    else:
        print("❌ Database test failed!")
        print("Please check your database configuration in .env file.")
    print("=" * 60)
