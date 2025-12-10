
#!/usr/bin/env python3
import sys
import json
import pickle
import numpy as np
from pathlib import Path

SAVED_MODELS_DIR = Path("ml_model/saved_models")

def load_models():
    """Load all ML models"""
    models = {}
    try:
        with open(SAVED_MODELS_DIR / "question_feature_classifier.pkl", "rb") as f:
            models["feature_classifier"] = pickle.load(f)
        with open(SAVED_MODELS_DIR / "category_encoder_v2.pkl", "rb") as f:
            models["category_encoder"] = pickle.load(f)
        with open(SAVED_MODELS_DIR / "difficulty_encoder_v2.pkl", "rb") as f:
            models["difficulty_encoder"] = pickle.load(f)
        with open(SAVED_MODELS_DIR / "question_templates.json", "r") as f:
            models["templates"] = json.load(f)
        return models
    except Exception as e:
        print(f"Error loading models: {e}", file=sys.stderr)
        return None

def extract_question_features(question_data):
    """Extract features from a question for classification"""
    return [
        len(question_data.get("question", "").split()),  # word_count
        1 if any(term in question_data.get("question", "").lower() 
                 for term in ["api", "function", "method", "class", "code"]) else 0,  # has_code_term
        question_data.get("complexity_score", 0.5),  # complexity_score
        1,  # requires_practical_knowledge
        question_data.get("abstraction_level", 0.5),  # abstraction_level
        len(question_data.get("topic_keywords", []))  # num_keywords
    ]

def generate_ml_guided_prompt(category, difficulty, career_path=None):
    """Generate prompt based on ML model predictions"""
    models = load_models()
    if not models:
        return None
    
    # Find similar questions from templates
    templates = models["templates"]["questions"]
    category_questions = [q for q in templates if q["category"] == category]
    difficulty_questions = [q for q in category_questions if q["difficulty"] == difficulty]
    
    if not difficulty_questions:
        difficulty_questions = category_questions[:3] if category_questions else templates[:3]
    
    # Create example-based prompt
    examples = difficulty_questions[:2]
    
    return {
        "category": category,
        "difficulty": difficulty,
        "examples": examples,
        "style_guide": {
            "word_count_range": [8, 15],
            "complexity": examples[0]["features"]["complexity_score"] if examples else 0.5,
            "requires_code": examples[0]["features"]["has_code_term"] if examples else 1
        }
    }

def validate_generated_question(question, expected_category, expected_difficulty):
    """Validate a generated question against learned patterns"""
    models = load_models()
    if not models:
        return True  # Allow if models not loaded
    
    try:
        features = extract_question_features(question)
        X = np.array([features])
        
        # Predict category
        predicted_cat = models["category_encoder"].inverse_transform(
            models["feature_classifier"].predict(X)
        )[0]
        
        # Check if category matches
        category_match = predicted_cat == expected_category
        
        # Check basic quality criteria
        has_question = len(question.get("question", "")) > 10
        has_options = len(question.get("options", [])) == 4
        has_answer = "correctAnswer" in question
        has_explanation = len(question.get("explanation", "")) > 20
        
        is_valid = category_match and has_question and has_options and has_answer and has_explanation
        
        return is_valid
        
    except Exception as e:
        print(f"Validation error: {e}", file=sys.stderr)
        return True  # Allow on error

def main():
    if len(sys.argv) < 2:
        print(json.dumps({"error": "No command provided"}))
        sys.exit(1)
    
    try:
        request = json.loads(sys.argv[1])
        command = request.get("command")
        data = request.get("data", {})
        
        if command == "get_prompt_guidance":
            category = data.get("category", "algorithms")
            difficulty = data.get("difficulty", "medium")
            career_path = data.get("career_path")
            
            guidance = generate_ml_guided_prompt(category, difficulty, career_path)
            print(json.dumps({"success": True, "result": guidance}))
            
        elif command == "validate_questions":
            questions = data.get("questions", [])
            category = data.get("category")
            difficulty = data.get("difficulty")
            
            validated = []
            for q in questions:
                if validate_generated_question(q, category, difficulty):
                    validated.append(q)
            
            print(json.dumps({
                "success": True,
                "result": {
                    "validated_count": len(validated),
                    "total_count": len(questions),
                    "questions": validated
                }
            }))
            
        else:
            print(json.dumps({"success": False, "error": f"Unknown command: {command}"}))
            
    except Exception as e:
        print(json.dumps({"success": False, "error": str(e)}))
        sys.exit(1)

if __name__ == "__main__":
    main()
