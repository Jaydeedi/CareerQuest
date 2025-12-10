
# ML-Powered Question Generation System Guide

## Overview

Your learning platform uses a **hybrid approach** combining:
1. **Custom scikit-learn ML models** (trained on real question data)
2. **Hugging Face AI models** (for advanced question generation)
3. **Template-based fallbacks** (for reliability)

This document explains how everything works together.

---

## System Architecture

### 1. Real Question Dataset (`ml_model/question_dataset.py`)

**Purpose**: Store real questions with extracted features for ML training.

**Structure**:
```python
{
  "question": "What does the 'virtual DOM' in React primarily help with?",
  "options": ["Database connections", "Performance optimization...", ...],
  "correct_answer": 1,
  "category": "frontend",
  "difficulty": "easy",
  "explanation": "The virtual DOM is a lightweight copy...",
  "features": {
    "word_count": 9,
    "has_code_term": 1,
    "complexity_score": 0.3,
    "topic_keywords": ["react", "dom", "virtual", "performance"],
    "requires_practical_knowledge": 1,
    "abstraction_level": 0.4
  }
}
```

**Current Dataset Size**: 15 real questions across 6 categories:
- Frontend (4 questions)
- Backend (3 questions)
- Data Science (3 questions)
- Algorithms (2 questions)
- Security (2 questions)
- Cloud (1 question)

**How Features Are Extracted**:
- `word_count`: Length of question text
- `has_code_term`: 1 if contains programming keywords, 0 otherwise
- `complexity_score`: 0-1 scale (easy=0.2, medium=0.5, hard=0.9)
- `abstraction_level`: How abstract vs. concrete the question is
- `topic_keywords`: Important words that identify the topic

---

### 2. ML Model Training (`ml_model/train_models_v2.py`)

**4 Models Are Trained**:

#### Model 1: Question Feature Classifier
- **Algorithm**: Gradient Boosting Classifier
- **Input**: 6 numerical features from questions
- **Output**: Category prediction (frontend, backend, data, etc.)
- **Training Data**: 15 real questions with extracted features
- **Purpose**: Classify new questions into categories

#### Model 2: Difficulty Classifier
- **Algorithm**: Naive Bayes
- **Input**: Same 6 features
- **Output**: Difficulty level (easy, medium, hard)
- **Training Data**: 15 real questions
- **Purpose**: Predict question difficulty

#### Model 3: Text-Based Question Classifier
- **Algorithm**: Naive Bayes + TF-IDF
- **Input**: Question text (converted to TF-IDF vectors)
- **Output**: Category prediction
- **Training Data**: 15 question texts
- **Purpose**: Classify questions based on text content alone

#### Model 4: Question Pattern Learner
- **Algorithm**: Random Forest
- **Input**: Question structure features (word count, complexity, etc.)
- **Output**: Category prediction
- **Training Data**: 15 questions
- **Purpose**: Learn patterns in how questions are structured

**Training Output**:
```
[1/4] Training Question Classifier with real data...
Dataset size: 15 questions
Categories: {'frontend', 'backend', 'data', 'algorithms', 'security'}
Difficulties: {'easy', 'medium', 'hard'}
✓ Saved: question_feature_classifier.pkl
✓ Saved: difficulty_classifier_v2.pkl
```

---

### 3. Hybrid Question Generator (`ml_model/hybrid_generator.py`)

**Purpose**: Bridge between ML models and AI generation.

**Two Main Functions**:

#### A. Generate ML-Guided Prompts
```python
def generate_ml_guided_prompt(category, difficulty, career_path):
    # 1. Load ML models
    models = load_models()
    
    # 2. Find similar questions from templates (real dataset)
    category_questions = [q for q in templates if q["category"] == category]
    difficulty_questions = [q for q in category_questions if q["difficulty"] == difficulty]
    
    # 3. Create example-based prompt with style guide
    return {
        "category": category,
        "difficulty": difficulty,
        "examples": difficulty_questions[:2],  # Use 2 real examples
        "style_guide": {
            "word_count_range": [8, 15],
            "complexity": examples[0]["features"]["complexity_score"],
            "requires_code": examples[0]["features"]["has_code_term"]
        }
    }
```

**What This Does**:
- Finds 2 real questions matching the requested category/difficulty
- Extracts their style characteristics
- Creates a prompt template for Hugging Face to follow

#### B. Validate Generated Questions
```python
def validate_generated_question(question, expected_category, expected_difficulty):
    # 1. Extract features from the generated question
    features = extract_question_features(question)
    
    # 2. Use ML model to predict category
    predicted_cat = models["feature_classifier"].predict([features])[0]
    
    # 3. Check if prediction matches expectation
    category_match = predicted_cat == expected_category
    
    # 4. Check quality criteria
    has_question = len(question.get("question", "")) > 10
    has_options = len(question.get("options", [])) == 4
    has_answer = "correctAnswer" in question
    has_explanation = len(question.get("explanation", "")) > 20
    
    return category_match and has_question and has_options and has_answer and has_explanation
```

**What This Does**:
- Uses trained ML models to verify generated questions
- Ensures questions match the requested category
- Validates question structure and quality

---

### 4. Hugging Face AI Integration (`server/huggingface-client.ts`)

**Models Used**:
- **Question Generation**: `mistralai/Mistral-7B-Instruct-v0.2`
- **Question Classification**: `facebook/bart-large-mnli`

#### Question Generation Flow:

```typescript
export async function generateQuestionsHF(
  topic: string,
  difficulty: string,
  careerPath?: string,
  count: number = 5
): Promise<any[]> {
  
  // 1. Create context-aware prompt
  const prompt = `You are an expert computer science educator. Generate ${count} high-quality multiple-choice questions about ${topic}.

Requirements:
- Difficulty: ${difficulty}
- Context: relevant to ${careerPath} career path
- Format: Each question must be a valid JSON object

Output format (JSON array):
[
  {
    "question": "Clear, specific question text?",
    "options": ["Option A", "Option B", "Option C", "Option D"],
    "correctAnswer": 1,
    "category": "${topic}",
    "difficulty": "${difficulty}",
    "explanation": "Detailed explanation..."
  }
]`;
  
  // 2. Call Hugging Face API
  const result = await hf.textGeneration({
    model: "mistralai/Mistral-7B-Instruct-v0.2",
    inputs: prompt,
    parameters: {
      max_new_tokens: 1500,
      temperature: 0.7,
      top_p: 0.9,
    },
  });
  
  // 3. Extract and validate JSON questions
  const questions = extractQuestionsFromText(result.generated_text);
  
  // 4. Clean and validate each question
  return questions
    .filter(q => validateQuestionStructure(q))
    .map(q => cleanQuestion(q))
    .slice(0, count);
}
```

**Key Features**:
- Uses ML-guided prompts for consistency
- Validates generated JSON structure
- Falls back to templates if AI fails

---

### 5. Practice Quiz Generation Flow (`server/routes.ts`)

**Complete Flow When User Clicks "Generate Quiz"**:

```
User Request
    ↓
1. Check if AI generation requested (useAI flag)
    ↓
2A. If useAI = true:
    ├─→ Get ML-guided prompt from hybrid_generator.py
    ├─→ Call Hugging Face with prompt + examples
    ├─→ Validate generated questions using ML models
    └─→ Return validated AI questions
    
2B. If useAI = false (default):
    ├─→ Use template-based selection
    ├─→ Filter questions by category/difficulty
    ├─→ Score questions based on user performance
    └─→ Return top 5 questions
    ↓
3. Cache quiz server-side (prevent answer tampering)
    ↓
4. Return quiz to frontend (without correct answers)
    ↓
5. User submits answers
    ↓
6. Validate answers server-side using cached data
    ↓
7. Award XP only on first completion (prevent farming)
```

**Code Implementation**:
```typescript
app.post("/api/quizzes/generate-practice", async (req, res) => {
  const { careerPath, topic, difficulty, useAI } = req.body;
  
  let questions;
  
  if (useAI && topic) {
    // AI-Powered Generation
    const { generateQuestionsHF } = await import("./huggingface-client");
    
    try {
      questions = await generateQuestionsHF(
        topic,
        difficulty || "medium",
        careerPath,
        5
      );
    } catch (aiError) {
      // Fallback to templates if AI fails
      questions = await generatePracticeQuiz(careerPath, userLevel, topic, difficulty);
    }
  } else {
    // Template-Based Selection (Default)
    questions = await generatePracticeQuiz(careerPath, userLevel, topic, difficulty);
  }
  
  // Cache quiz server-side
  const quizId = `practice-${Date.now()}-${userId}`;
  practiceQuizCache.set(quizId, { questions, userId, createdAt: Date.now() });
  
  return { quiz: { id: quizId, questions } };
});
```

---

## How It All Works Together

### Example: User Generates a "Frontend" Quiz

**Step 1: User Request**
```
Topic: "React Hooks"
Difficulty: "medium"
Career Path: "Frontend Developer"
Use AI: true
```

**Step 2: ML Model Guidance**
```python
# hybrid_generator.py finds similar questions
similar_questions = [
  {
    "question": "What is the purpose of the useEffect hook in React?",
    "difficulty": "medium",
    "complexity_score": 0.5
  }
]

# Creates style guide
style_guide = {
  "word_count_range": [8, 12],
  "complexity": 0.5,
  "requires_code_terms": true
}
```

**Step 3: Hugging Face Generation**
```
Prompt: "Generate 5 medium-difficulty questions about React Hooks for Frontend Developers.
Follow this style: [examples from ML dataset]"

AI Response: [
  {
    "question": "Which hook should you use to perform side effects in React functional components?",
    "options": ["useState", "useEffect", "useContext", "useMemo"],
    "correctAnswer": 1,
    "explanation": "useEffect is specifically designed for side effects..."
  },
  ...
]
```

**Step 4: ML Validation**
```python
# Validate each generated question
for question in ai_questions:
    features = extract_features(question)
    predicted_category = ml_model.predict(features)  # Should be "frontend"
    predicted_difficulty = difficulty_model.predict(features)  # Should be "medium"
    
    if predicted_category == "frontend" and predicted_difficulty == "medium":
        validated_questions.append(question)
```

**Step 5: Return to User**
```json
{
  "quiz": {
    "id": "practice-1234567890-user123",
    "title": "React Hooks Practice Quiz",
    "questions": [5 validated AI-generated questions],
    "generatedByAI": true
  }
}
```

---

## Key Advantages of This System

### 1. **Quality Assurance**
- ML models validate AI-generated questions
- Questions must match expected category and difficulty
- Structural validation ensures proper format

### 2. **Consistency**
- Real question dataset provides templates
- ML models learn patterns from real data
- AI follows style guide from similar questions

### 3. **Reliability**
- Multiple fallback layers:
  1. AI generation (Hugging Face)
  2. Template-based selection (ML-guided)
  3. Hardcoded question bank (last resort)

### 4. **Adaptability**
- System learns from 15 real questions
- Can expand dataset by adding more questions
- ML models retrain automatically with new data

### 5. **Security**
- Server-side answer validation
- XP awarded only once per quiz
- Cached quizzes prevent tampering

---

## Current Limitations & Future Improvements

### Limitations:
1. **Small Dataset**: Only 15 real questions for training
2. **Category Coverage**: Not all topics have equal representation
3. **AI Consistency**: Hugging Face responses can vary in quality

### Recommended Improvements:

#### 1. Expand Real Question Dataset
```python
# Add 100+ real questions across all categories
QUESTION_DATASET = [
  # Add more frontend questions (target: 30)
  # Add more backend questions (target: 30)
  # Add more data science questions (target: 30)
  # Add more algorithms questions (target: 30)
  # Add more security questions (target: 30)
  # Add more cloud questions (target: 30)
]
```

#### 2. Fine-Tune ML Models
```python
# Retrain with larger dataset
n_questions = 200  # Instead of 15
X, y = get_feature_matrix()  # Larger feature matrix
model.fit(X, y)  # Better predictions
```

#### 3. Implement Question Quality Scoring
```python
def score_question_quality(question):
    scores = {
        "clarity": check_clarity(question),
        "difficulty": check_difficulty_match(question),
        "educational_value": check_learning_objective(question),
        "answer_distribution": check_option_quality(question)
    }
    return sum(scores.values()) / len(scores)
```

#### 4. Add User Feedback Loop
```typescript
// Allow users to rate generated questions
app.post("/api/questions/:id/feedback", async (req, res) => {
  const { rating, issues } = req.body;
  
  // Store feedback
  await storage.createQuestionFeedback(questionId, userId, rating, issues);
  
  // Use feedback to improve future generation
  if (rating < 3) {
    await flagQuestionForReview(questionId);
  }
});
```

---

## Testing Your System

### 1. Test ML Model Accuracy
```bash
cd ml_model
python3 train_models_v2.py
# Check console output for accuracy scores
```

### 2. Test Question Generation
```bash
# Start your server
npm run dev

# Test AI generation endpoint
curl -X POST http://localhost:5000/api/quizzes/generate-practice \
  -H "Content-Type: application/json" \
  -d '{
    "topic": "frontend",
    "difficulty": "medium",
    "useAI": true
  }'
```

### 3. Test Validation
```python
# Run hybrid generator directly
python3 ml_model/hybrid_generator.py '{"command":"validate_questions","data":{"questions":[...],"category":"frontend","difficulty":"medium"}}'
```

---

## Conclusion

Your system combines:
- **Real question data** (15 curated examples)
- **ML models** (trained on real patterns)
- **Hugging Face AI** (for generation)
- **Validation layers** (quality assurance)
- **Template fallbacks** (reliability)

This creates a robust, adaptive question generation system that maintains quality while scaling to user needs.

**Next Steps**:
1. Expand question dataset to 200+ real questions
2. Retrain ML models with larger dataset
3. Test AI generation across all categories
4. Implement user feedback loop
5. Monitor question quality metrics

---

## File Reference

**Core Files**:
- `ml_model/question_dataset.py` - Real question data
- `ml_model/train_models_v2.py` - ML model training
- `ml_model/hybrid_generator.py` - ML-AI bridge
- `server/huggingface-client.ts` - AI generation
- `server/routes.ts` - API endpoints

**Saved Models** (`.pkl` files in `ml_model/saved_models/`):
- `question_feature_classifier.pkl`
- `difficulty_classifier_v2.pkl`
- `category_encoder_v2.pkl`
- `text_question_classifier.pkl`
- `question_pattern_model.pkl`

**Training Data**:
- `question_templates.json` - Question patterns
- `question_dataset.json` - Exported dataset
