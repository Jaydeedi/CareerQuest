
# CareerQuest ML API - Hugging Face Deployment

This is the ML prediction service for CareerQuest, deployed on Hugging Face Spaces.

## Deployment Steps

1. Create a new Space on Hugging Face:
   - Go to https://huggingface.co/spaces
   - Click "Create new Space"
   - Choose "Gradio" or "Docker" SDK
   - Name it: `careerquest-ml-api`

2. Upload these files to your Space:
   ```
   ml_model/
   ├── app.py
   ├── prediction_service.py
   ├── requirements.txt
   ├── saved_models/
   │   ├── *.pkl files
   │   └── *.json files
   └── README.md
   ```

3. The service will automatically start and show logs like:
   ```
   ===== Application Startup at 2025-01-XX XX:XX:XX =====
   
   🚀 Starting CareerQuest ML API (HuggingFace Spaces)...
   
   ✅ Models loaded successfully!
      Features: 12 features
      ✓ Question Classifier
      ✓ Difficulty Predictor
      ✓ Career Recommender (Gradient Boosting)
      ✓ Study Suggester
      Question Bank: 80+ questions across 5 categories
   
   🌐 Starting server on 0.0.0.0:7860
   ```

## API Endpoints

### POST /predict
Main prediction endpoint for all ML operations.

**Request:**
```json
{
  "command": "generate_quiz",
  "data": {
    "category": "algorithms",
    "difficulty": "medium",
    "career_path": "backend",
    "count": 5,
    "level": 10
  }
}
```

**Response:**
```json
{
  "success": true,
  "result": [...]
}
```

### GET /health
Health check endpoint.

## Connecting from Your App

Update your `.env` in Replit:
```
HUGGINGFACE_API_TOKEN=hf_xxxxxxxxxxxxx
USE_HUGGINGFACE=true
HF_MODEL_ENDPOINT=https://your-username-careerquest-ml-api.hf.space
```

## Logs Example

When the service receives requests, you'll see:
```
🔬 Prediction Request | 2025-01-XX 01:30:45 PM
   Command: recommend_career
   ✅ Recommendation: backend
   Confidence: 85%
```
