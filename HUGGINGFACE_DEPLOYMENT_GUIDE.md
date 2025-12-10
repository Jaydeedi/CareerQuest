# Deploying CareerQuest ML Models to Hugging Face

This guide explains how to deploy your trained ML models to Hugging Face and configure the application to use them.

## Overview

The system supports two modes:
1. **Local Mode** (default) - Uses the local Python prediction service
2. **Hugging Face Mode** - Calls your deployed models via Hugging Face Inference API

## Step 1: Prepare Your Models for Hugging Face

### Option A: Hugging Face Spaces (Gradio/FastAPI)

Create a new Hugging Face Space with your models:

1. Go to [Hugging Face Spaces](https://huggingface.co/spaces)
2. Create a new Space with Python SDK
3. Upload your model files from `ml_model/saved_models/`
4. Create an `app.py` file:

```python
import gradio as gr
import pickle
import json
import numpy as np
from pathlib import Path

MODELS_DIR = Path("saved_models")

def load_models():
    models = {}
    model_files = [
        "question_classifier.pkl",
        "difficulty_predictor.pkl",
        "career_label_encoder.pkl",
        "study_suggester.pkl",
        "gradient_boosting.pkl",
        "random_forest.pkl",
        "scaler.pkl",
    ]
    for filename in model_files:
        filepath = MODELS_DIR / filename
        if filepath.exists():
            with open(filepath, "rb") as f:
                key = filename.replace(".pkl", "")
                models[key] = pickle.load(f)
    return models

MODELS = load_models()

def generate_quiz(category, difficulty, career_path, count):
    # Your quiz generation logic here
    questions = [
        {
            "question": "What is the time complexity of binary search?",
            "options": ["O(n)", "O(log n)", "O(n^2)", "O(1)"],
            "correctAnswer": 1,
            "category": "algorithms",
            "difficulty": "medium",
            "explanation": "Binary search has O(log n) complexity."
        }
    ]
    return {"questions": questions[:count]}

def recommend_career(user_data):
    # Your career recommendation logic here
    return {
        "recommended_path": "fullstack",
        "probabilities": {"frontend": 0.3, "backend": 0.3, "data": 0.2, "cloud": 0.1, "mobile": 0.1},
        "confidence": 0.75
    }

def suggest_study(user_data):
    # Your study suggestion logic here
    return {
        "suggestions": [
            {"topic": "Algorithms", "reason": "Improve problem-solving", "recommendedAction": "Practice coding challenges", "priority": "high"}
        ]
    }

# Create Gradio interface
with gr.Blocks() as demo:
    gr.Markdown("# CareerQuest ML API")
    
    with gr.Tab("Quiz Generation"):
        category_input = gr.Dropdown(["algorithms", "frontend", "backend", "data", "security"], label="Category")
        difficulty_input = gr.Dropdown(["easy", "medium", "hard"], label="Difficulty")
        career_input = gr.Textbox(label="Career Path")
        count_input = gr.Number(value=5, label="Count")
        quiz_output = gr.JSON(label="Generated Quiz")
        gr.Button("Generate").click(generate_quiz, [category_input, difficulty_input, career_input, count_input], quiz_output)
    
    with gr.Tab("Career Recommendation"):
        career_data = gr.JSON(label="User Data")
        career_output = gr.JSON(label="Recommendation")
        gr.Button("Recommend").click(recommend_career, [career_data], career_output)
    
    with gr.Tab("Study Suggestions"):
        study_data = gr.JSON(label="User Performance")
        study_output = gr.JSON(label="Suggestions")
        gr.Button("Suggest").click(suggest_study, [study_data], study_output)

demo.launch()
```

### Option B: Hugging Face Inference Endpoints (Production)

For production use, deploy as an Inference Endpoint:

1. Go to [Hugging Face Inference Endpoints](https://huggingface.co/inference-endpoints)
2. Create a new endpoint
3. Upload your model repository with the required files:
   - `handler.py` - Custom inference handler
   - `requirements.txt` - Python dependencies
   - Model files (`.pkl` files)

Create `handler.py`:

```python
import pickle
import json
import numpy as np
from pathlib import Path

class EndpointHandler:
    def __init__(self, path=""):
        self.models = {}
        models_dir = Path(path) / "saved_models"
        
        model_files = {
            "question_classifier": "question_classifier.pkl",
            "career_label_encoder": "career_label_encoder.pkl",
            "study_suggester": "study_suggester.pkl",
        }
        
        for key, filename in model_files.items():
            filepath = models_dir / filename
            if filepath.exists():
                with open(filepath, "rb") as f:
                    self.models[key] = pickle.load(f)
    
    def __call__(self, data):
        inputs = data.get("inputs", {})
        command = inputs.get("command", "")
        request_data = inputs.get("data", {})
        
        if command == "generate_quiz":
            return self.generate_quiz(request_data)
        elif command == "recommend_career":
            return self.recommend_career(request_data)
        elif command == "suggest_study":
            return self.suggest_study(request_data)
        elif command == "health_check":
            return {"status": "healthy", "models_loaded": len(self.models)}
        else:
            return {"error": f"Unknown command: {command}"}
    
    def generate_quiz(self, data):
        # Implement your quiz generation logic
        return {"questions": []}
    
    def recommend_career(self, data):
        # Implement your career recommendation logic
        return {"recommended_path": "fullstack", "probabilities": {}, "confidence": 0.5}
    
    def suggest_study(self, data):
        # Implement your study suggestion logic
        return {"suggestions": []}
```

Create `requirements.txt`:

```
numpy==1.24.0
scikit-learn==1.3.0
joblib==1.3.0
```

## Step 2: Configure Environment Variables

Add these environment variables in Replit:

### Required Variables

| Variable | Description | Example |
|----------|-------------|---------|
| `HUGGINGFACE_API_TOKEN` | Your Hugging Face API token | `hf_xxxxxxxxxxxxxxxx` |
| `USE_HUGGINGFACE` | Enable Hugging Face mode | `true` |

### Optional Variables

| Variable | Description | Default |
|----------|-------------|---------|
| `HF_MODEL_ENDPOINT` | Custom endpoint URL | Hugging Face Inference API |
| `HF_QUIZ_MODEL` | Quiz model ID | `your-username/careerquest-quiz` |
| `HF_CAREER_MODEL` | Career model ID | `your-username/careerquest-career` |
| `HF_STUDY_MODEL` | Study model ID | `your-username/careerquest-study` |

## Step 3: Get Your Hugging Face API Token

1. Go to [Hugging Face Settings](https://huggingface.co/settings/tokens)
2. Create a new token with `read` permissions
3. Copy the token (starts with `hf_`)
4. Add it as `HUGGINGFACE_API_TOKEN` in Replit Secrets

## Step 4: Enable Hugging Face Mode

Set `USE_HUGGINGFACE=true` in your environment variables to switch from local ML to Hugging Face.

## How It Works

The system uses a **fallback architecture**:

```
Request → Check USE_HUGGINGFACE
              ↓
         [true] → Try Hugging Face API
                      ↓
                 [success] → Return result
                 [error] → Fall back to local ML
              ↓
         [false] → Use local ML directly
```

This ensures the app always works, even if Hugging Face is unavailable.

## API Request/Response Formats

### Quiz Generation

**Request:**
```json
{
  "inputs": {
    "command": "generate_quiz",
    "data": {
      "category": "algorithms",
      "difficulty": "medium",
      "career_path": "backend",
      "count": 5
    }
  }
}
```

**Response:**
```json
{
  "questions": [
    {
      "question": "What is the time complexity of binary search?",
      "options": ["O(n)", "O(log n)", "O(n^2)", "O(1)"],
      "correctAnswer": 1,
      "category": "algorithms",
      "difficulty": "medium",
      "explanation": "Binary search has O(log n) complexity."
    }
  ]
}
```

### Career Recommendation

**Request:**
```json
{
  "inputs": {
    "command": "recommend_career",
    "data": {
      "visual_design": 4,
      "backend_pref": 5,
      "math_stats": 3,
      "web_apps": 5,
      "data_interest": 2,
      "cloud_interest": 3,
      "mobile_interest": 2,
      "security_interest": 3,
      "frontend_perf": 0.7,
      "backend_perf": 0.85,
      "data_perf": 0.5,
      "algo_perf": 0.75
    }
  }
}
```

**Response:**
```json
{
  "recommended_path": "backend",
  "probabilities": {
    "frontend": 0.15,
    "backend": 0.35,
    "data": 0.1,
    "cloud": 0.15,
    "mobile": 0.1,
    "security": 0.15
  },
  "confidence": 0.82
}
```

### Study Suggestions

**Request:**
```json
{
  "inputs": {
    "command": "suggest_study",
    "data": {
      "level": 15,
      "career_path": "frontend",
      "frontend_score": 0.6,
      "backend_score": 0.4,
      "data_score": 0.5,
      "algo_score": 0.7,
      "security_score": 0.3,
      "weak_categories": ["security", "backend"]
    }
  }
}
```

**Response:**
```json
{
  "suggestions": [
    {
      "topic": "Security Best Practices",
      "reason": "Your security score (30%) needs improvement.",
      "recommendedAction": "Learn about OWASP top 10 vulnerabilities.",
      "priority": "high"
    }
  ]
}
```

## Testing Your Deployment

After deploying to Hugging Face, test with curl:

```bash
curl -X POST \
  -H "Authorization: Bearer YOUR_HF_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"inputs": {"command": "health_check", "data": {}}}' \
  https://api-inference.huggingface.co/models/your-username/your-model
```

## Files to Upload to Hugging Face

From `ml_model/saved_models/`:
- `question_classifier.pkl`
- `question_vectorizer.pkl`
- `question_label_encoder.pkl`
- `difficulty_predictor.pkl`
- `difficulty_label_encoder.pkl`
- `study_suggester.pkl`
- `study_label_encoder.pkl`
- `career_label_encoder.pkl`
- `gradient_boosting.pkl`
- `random_forest.pkl`
- `scaler.pkl`
- `features.pkl`
- `career_features.json`

## Troubleshooting

### Model not responding
- Check if the model is still loading (cold start can take 30-60 seconds)
- Verify your API token has correct permissions

### Falling back to local ML
- Check Hugging Face API status
- Verify `USE_HUGGINGFACE=true` is set
- Check the model endpoint URL is correct

### Type errors in responses
- Ensure your Hugging Face model returns the exact response format expected
- Check the API documentation for your endpoint type
