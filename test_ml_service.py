
#!/usr/bin/env python3
"""
Test script to verify ML service is working correctly
"""

import subprocess
import json

def test_ml_service(command, data):
    """Test the ML prediction service"""
    request = json.dumps({
        "command": command,
        "data": data
    })
    
    try:
        result = subprocess.run(
            ["python3", "ml_model/prediction_service.py", request],
            capture_output=True,
            text=True,
            timeout=30
        )
        
        if result.returncode != 0:
            print(f"❌ Error: {result.stderr}")
            return None
            
        response = json.loads(result.stdout)
        return response
    except Exception as e:
        print(f"❌ Exception: {e}")
        return None

# Test 1: Health Check
print("=" * 60)
print("TEST 1: Health Check")
print("=" * 60)
result = test_ml_service("health_check", {})
if result and result.get("success"):
    print("✅ ML Service is healthy!")
    print(f"   Models loaded: {result['result']['models_loaded']}")
    print(f"   Question bank size: {result['result']['question_bank_size']}")
    print(f"   Using ML: {result['result']['using_ml']}")
    print(f"   Available ML models: {result['result']['ml_models_available']}")
else:
    print("❌ Health check failed")

# Test 2: Generate Quiz
print("\n" + "=" * 60)
print("TEST 2: Generate Practice Quiz (Frontend, Medium)")
print("=" * 60)
result = test_ml_service("generate_quiz", {
    "career_path": "frontend",
    "difficulty": "medium",
    "category": "frontend",
    "level": 10,
    "count": 5
})

if result and result.get("success"):
    questions = result.get("result", [])
    print(f"✅ Generated {len(questions)} questions")
    for i, q in enumerate(questions[:2], 1):  # Show first 2 questions
        print(f"\n   Question {i}:")
        print(f"   Q: {q['question']}")
        print(f"   Category: {q['category']} | Difficulty: {q['difficulty']}")
        print(f"   Options: {q['options']}")
        print(f"   Correct: {q['correctAnswer']}")
else:
    print("❌ Quiz generation failed")

# Test 3: Career Recommendation
print("\n" + "=" * 60)
print("TEST 3: Career Path Recommendation")
print("=" * 60)
result = test_ml_service("recommend_career", {
    "visual_design": 4,
    "backend_pref": 2,
    "math_stats": 3,
    "web_apps": 4,
    "data_interest": 2,
    "cloud_interest": 3,
    "mobile_interest": 2,
    "security_interest": 2,
    "frontend_perf": 0.8,
    "backend_perf": 0.5,
    "data_perf": 0.6,
    "algo_perf": 0.7
})

if result and result.get("success"):
    rec = result.get("result", {})
    print(f"✅ Recommended Path: {rec.get('recommended_path')}")
    print(f"   Confidence: {rec.get('confidence', 0):.1%}")
    print("   Probabilities:")
    for path, prob in rec.get('probabilities', {}).items():
        print(f"      {path}: {prob:.1%}")
else:
    print("❌ Career recommendation failed")

# Test 4: Study Suggestions
print("\n" + "=" * 60)
print("TEST 4: Study Suggestions")
print("=" * 60)
result = test_ml_service("suggest_study", {
    "level": 10,
    "career_path": "fullstack",
    "frontend_score": 0.7,
    "backend_score": 0.5,
    "data_score": 0.4,
    "algo_score": 0.6,
    "security_score": 0.3,
    "weak_categories": ["security", "data"]
})

if result and result.get("success"):
    suggestions = result.get("result", [])
    print(f"✅ Generated {len(suggestions)} study suggestions")
    for i, s in enumerate(suggestions, 1):
        print(f"\n   {i}. {s['topic']} (Priority: {s['priority']})")
        print(f"      Reason: {s['reason']}")
        print(f"      Action: {s['recommendedAction']}")
else:
    print("❌ Study suggestions failed")

# Test 5: Question Classification
print("\n" + "=" * 60)
print("TEST 5: Question Classification")
print("=" * 60)
result = test_ml_service("classify_question", {
    "text": "What is the time complexity of binary search?"
})

if result and result.get("success"):
    classification = result.get("result", {})
    print(f"✅ Classified as: {classification.get('category')}")
    print(f"   Confidence: {classification.get('confidence', 0):.1%}")
else:
    print("❌ Question classification failed")

print("\n" + "=" * 60)
print("TESTS COMPLETE")
print("=" * 60)
