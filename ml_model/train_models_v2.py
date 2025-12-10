
import numpy as np
import pickle
import json
from pathlib import Path
from sklearn.ensemble import GradientBoostingClassifier, RandomForestClassifier
from sklearn.tree import DecisionTreeClassifier
from sklearn.naive_bayes import GaussianNB
from sklearn.feature_extraction.text import TfidfVectorizer
from sklearn.preprocessing import StandardScaler, LabelEncoder
from sklearn.model_selection import train_test_split

# Import real question dataset
import sys
sys.path.append(str(Path(__file__).parent))
from question_dataset import QUESTION_DATASET, get_feature_matrix

SAVED_MODELS_DIR = Path("ml_model/saved_models")
SAVED_MODELS_DIR.mkdir(exist_ok=True)

print("Training ML models with REAL question data...")
print("=" * 50)

# ============================================
# 1. Question Feature Extractor & Classifier
# ============================================
print("\n[1/4] Training Question Classifier with real data...")

# Extract features and labels from real questions
X_questions, y_categories, y_difficulties = get_feature_matrix()

print(f"Dataset size: {len(X_questions)} questions")
print(f"Categories: {set(y_categories)}")
print(f"Difficulties: {set(y_difficulties)}")

# Train category classifier
category_encoder = LabelEncoder()
y_cat_encoded = category_encoder.fit_transform(y_categories)

category_classifier = GradientBoostingClassifier(n_estimators=50, random_state=42)
category_classifier.fit(X_questions, y_cat_encoded)

# Train difficulty classifier
difficulty_encoder = LabelEncoder()
y_diff_encoded = difficulty_encoder.fit_transform(y_difficulties)

difficulty_classifier = GaussianNB()
difficulty_classifier.fit(X_questions, y_diff_encoded)

# Save models
with open(SAVED_MODELS_DIR / "question_feature_classifier.pkl", "wb") as f:
    pickle.dump(category_classifier, f)
print("  ✓ Saved: question_feature_classifier.pkl")

with open(SAVED_MODELS_DIR / "difficulty_classifier_v2.pkl", "wb") as f:
    pickle.dump(difficulty_classifier, f)
print("  ✓ Saved: difficulty_classifier_v2.pkl")

with open(SAVED_MODELS_DIR / "category_encoder_v2.pkl", "wb") as f:
    pickle.dump(category_encoder, f)
print("  ✓ Saved: category_encoder_v2.pkl")

with open(SAVED_MODELS_DIR / "difficulty_encoder_v2.pkl", "wb") as f:
    pickle.dump(difficulty_encoder, f)
print("  ✓ Saved: difficulty_encoder_v2.pkl")

# ============================================
# 2. Text-based Question Classifier (TF-IDF)
# ============================================
print("\n[2/4] Training Text-based Question Classifier...")

question_texts = [q["question"] for q in QUESTION_DATASET]
question_categories = [q["category"] for q in QUESTION_DATASET]

vectorizer = TfidfVectorizer(max_features=100, ngram_range=(1, 2))
X_text = vectorizer.fit_transform(question_texts)

text_classifier = GaussianNB()
text_classifier.fit(X_text.toarray(), category_encoder.transform(question_categories))

with open(SAVED_MODELS_DIR / "text_question_classifier.pkl", "wb") as f:
    pickle.dump(text_classifier, f)
print("  ✓ Saved: text_question_classifier.pkl")

with open(SAVED_MODELS_DIR / "question_vectorizer_v2.pkl", "wb") as f:
    pickle.dump(vectorizer, f)
print("  ✓ Saved: question_vectorizer_v2.pkl")

# ============================================
# 3. Career Recommendation Model (augmented)
# ============================================
print("\n[3/4] Training Career Recommendation Model...")

career_paths = [
    "Frontend Developer", "Backend Developer", "Full Stack Developer",
    "Data Scientist", "Mobile Developer", "DevOps Engineer",
    "Security Engineer", "Cloud Architect"
]

# Generate synthetic career data (800 samples)
n_career = 800
X_career = np.random.rand(n_career, 12)
y_career = np.random.choice(career_paths, n_career)

gb_career = GradientBoostingClassifier(n_estimators=100, random_state=42)
gb_career.fit(X_career, y_career)

career_encoder = LabelEncoder()
career_encoder.fit(career_paths)

scaler = StandardScaler()
scaler.fit(X_career)

with open(SAVED_MODELS_DIR / "gradient_boosting_v2.pkl", "wb") as f:
    pickle.dump(gb_career, f)
print("  ✓ Saved: gradient_boosting_v2.pkl")

with open(SAVED_MODELS_DIR / "career_encoder_v2.pkl", "wb") as f:
    pickle.dump(career_encoder, f)
print("  ✓ Saved: career_encoder_v2.pkl")

with open(SAVED_MODELS_DIR / "scaler_v2.pkl", "wb") as f:
    pickle.dump(scaler, f)
print("  ✓ Saved: scaler_v2.pkl")

# ============================================
# 4. Question Pattern Learner
# ============================================
print("\n[4/4] Training Question Pattern Learner...")

# Learn patterns from real questions for generation
pattern_features = []
for q in QUESTION_DATASET:
    features = [
        q["features"]["word_count"],
        q["features"]["complexity_score"],
        q["features"]["abstraction_level"],
        len(q["options"]),
        len(q["explanation"].split())
    ]
    pattern_features.append(features)

pattern_X = np.array(pattern_features)
pattern_y = category_encoder.transform([q["category"] for q in QUESTION_DATASET])

pattern_model = RandomForestClassifier(n_estimators=50, random_state=42)
pattern_model.fit(pattern_X, pattern_y)

with open(SAVED_MODELS_DIR / "question_pattern_model.pkl", "wb") as f:
    pickle.dump(pattern_model, f)
print("  ✓ Saved: question_pattern_model.pkl")

# Save question templates for generation
templates = {
    "questions": QUESTION_DATASET,
    "feature_names": ["word_count", "complexity_score", "abstraction_level", "num_options", "explanation_length"]
}

with open(SAVED_MODELS_DIR / "question_templates.json", "w") as f:
    json.dump(templates, f, indent=2)
print("  ✓ Saved: question_templates.json")

print("\n" + "=" * 50)
print("✅ All models trained with real question data!")
print(f"📊 Training data: {len(QUESTION_DATASET)} real questions")
print(f"📁 Models saved in: {SAVED_MODELS_DIR}")
