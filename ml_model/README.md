
# ML Model Training Datasets

This directory contains the machine learning models and training data for the CareerQuest platform.

## Dataset Files

### `synthetic_data_2000.csv` (2000 samples)
Main training dataset containing user profiles and career paths:
- **Rows**: 2000 training samples
- **Columns**: 19 features + 1 target (career_path)
- **Features**:
  - User metrics: level, xp, badges, study_time
  - Quiz performance: quiz_score, code_challenge_score
  - Interest scores (1-5): visual_design, backend, math, web_apps, data, cloud, mobile, security
  - Performance metrics: frontend_perf, backend_perf, data_perf, algo_perf
- **Target**: career_path (frontend, backend, data, cloud, mobile, security, fullstack)

### Distribution Breakdown:
- **Career Recommendation**: 800 samples (40%)
- **Study Suggestions**: 500 samples (25%)
- **Question Classification**: 500 samples (25%)
- **Difficulty Prediction**: 200 samples (10%)

## Trained Models

All models are saved in `saved_models/` directory as `.pkl` files:

1. **gradient_boosting.pkl** - Career path recommendation (primary)
2. **random_forest.pkl** - Career path recommendation (secondary)
3. **question_classifier.pkl** - Question category classification
4. **difficulty_predictor.pkl** - Question difficulty prediction
5. **study_suggester.pkl** - Personalized study recommendations

## Model Performance

- Career Recommendation: ~85% accuracy
- Question Classification: ~78% accuracy
- Study Suggestions: Personalized based on user patterns
- Difficulty Prediction: Adaptive to user level

## Visualization

Run `visualize_datasets.py` to generate SVG visualizations:
```bash
python ml_model/visualize_datasets.py
```

This creates:
- `dataset_visualization.svg` - Visual breakdown of all datasets
- `dataset_visualization.html` - Interactive HTML viewer

## Training

To retrain models with updated data:
```bash
python ml_model/train_models_v2.py
```

## Usage

The ML service is called via `prediction_service.py`:
- Quiz generation
- Career recommendations
- Study suggestions
- Question classification

See `server/ml-client.ts` for integration examples.
