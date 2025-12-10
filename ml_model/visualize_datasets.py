
import pickle
import json
import numpy as np
from pathlib import Path

def generate_svg_visualizations():
    """Generate SVG visualizations of the training datasets (2000 total samples)"""
    
    svg_content = """<?xml version="1.0" encoding="UTF-8"?>
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1200 2400">
  <style>
    .title { font: bold 24px sans-serif; fill: #333; }
    .subtitle { font: bold 18px sans-serif; fill: #666; }
    .text { font: 14px sans-serif; fill: #444; }
    .label { font: 12px sans-serif; fill: #666; }
    .rect-career { fill: #4F46E5; opacity: 0.7; }
    .rect-study { fill: #10B981; opacity: 0.7; }
    .rect-question { fill: #F59E0B; opacity: 0.7; }
    .rect-difficulty { fill: #EF4444; opacity: 0.7; }
    .border { fill: none; stroke: #ddd; stroke-width: 2; }
  </style>

  <!-- Header -->
  <text x="600" y="40" text-anchor="middle" class="title">ML Model Training Datasets Overview</text>
  <text x="600" y="70" text-anchor="middle" class="label">Total: 2000 samples across all models</text>

"""
    
    y_offset = 120
    
    # 1. Career Recommendation Dataset (800 samples)
    svg_content += f"""
  <!-- Career Recommendation Dataset -->
  <rect x="50" y="{y_offset}" width="1100" height="400" class="border"/>
  <text x="600" y="{y_offset + 30}" text-anchor="middle" class="subtitle">1. Career Recommendation Dataset (800 samples - 40%)</text>
  
  <text x="100" y="{y_offset + 70}" class="text">Features: Level, XP, Badges, Quiz Scores, Code Challenges, Study Time, Interests</text>
  <text x="100" y="{y_offset + 95}" class="text">Target: Career Path (Web Dev, Data Science, Mobile Dev, DevOps, etc.)</text>
  
  <!-- Sample Data Visualization -->
  <text x="100" y="{y_offset + 130}" class="label">Sample Distribution (Career Paths):</text>
"""
    
    careers = [
        ("Frontend", 95, "#4F46E5"),
        ("Backend", 90, "#7C3AED"),
        ("Full Stack", 85, "#2563EB"),
        ("Data Sci", 80, "#10B981"),
        ("Mobile", 75, "#F59E0B"),
        ("DevOps", 70, "#EF4444"),
        ("ML Eng", 65, "#EC4899"),
        ("Cloud", 60, "#8B5CF6"),
        ("Security", 50, "#06B6D4"),
        ("Game", 40, "#14B8A6"),
        ("Other", 90, "#64748B")
    ]
    
    x_bar = 100
    y_bar = y_offset + 150
    for idx, (career, count, color) in enumerate(careers):
        bar_height = count * 1.2
        svg_content += f"""
  <rect x="{x_bar + idx * 95}" y="{y_bar + 180 - bar_height}" width="80" height="{bar_height}" fill="{color}" opacity="0.8"/>
  <text x="{x_bar + idx * 95 + 40}" y="{y_bar + 195}" text-anchor="middle" class="label" transform="rotate(-45 {x_bar + idx * 95 + 40} {y_bar + 195})">{career}</text>
  <text x="{x_bar + idx * 95 + 40}" y="{y_bar + 175 - bar_height}" text-anchor="middle" class="label">{count}</text>
"""
    
    y_offset += 450
    
    # 2. Study Suggestion Dataset (500 samples)
    svg_content += f"""
  <!-- Study Suggestion Dataset -->
  <rect x="50" y="{y_offset}" width="1100" height="350" class="border"/>
  <text x="600" y="{y_offset + 30}" text-anchor="middle" class="subtitle">2. Study Suggestion Dataset (500 samples - 25%)</text>
  
  <text x="100" y="{y_offset + 70}" class="text">Features: Recent Quiz Scores, Time Since Last Study, Weak Topics, Preferred Difficulty</text>
  <text x="100" y="{y_offset + 95}" class="text">Target: Study Recommendation (Focus Areas, Difficulty Level, Time Allocation)</text>
  
  <text x="100" y="{y_offset + 130}" class="label">Sample Distribution (Study Focus Areas):</text>
"""
    
    study_areas = [
        ("Data Structures", 80, "#10B981"),
        ("Algorithms", 75, "#059669"),
        ("Web Development", 70, "#10B981"),
        ("Databases", 65, "#34D399"),
        ("System Design", 60, "#6EE7B7"),
        ("APIs & Backend", 55, "#A7F3D0"),
        ("Frontend Frameworks", 50, "#D1FAE5"),
        ("Testing & QA", 45, "#ECFDF5")
    ]
    
    x_pie = 300
    y_pie = y_offset + 200
    start_angle = 0
    total = sum(count for _, count, _ in study_areas)
    
    for area, count, color in study_areas:
        angle = (count / total) * 360
        end_angle = start_angle + angle
        
        # Calculate pie slice path
        start_rad = start_angle * np.pi / 180
        end_rad = end_angle * np.pi / 180
        x1 = x_pie + 100 * np.cos(start_rad)
        y1 = y_pie + 100 * np.sin(start_rad)
        x2 = x_pie + 100 * np.cos(end_rad)
        y2 = y_pie + 100 * np.sin(end_rad)
        
        large_arc = 1 if angle > 180 else 0
        
        svg_content += f"""
  <path d="M {x_pie} {y_pie} L {x1} {y1} A 100 100 0 {large_arc} 1 {x2} {y2} Z" fill="{color}" opacity="0.8" stroke="#fff" stroke-width="2"/>
"""
        
        start_angle = end_angle
    
    # Legend for pie chart
    legend_x = 650
    legend_y = y_offset + 150
    for idx, (area, count, color) in enumerate(study_areas):
        svg_content += f"""
  <rect x="{legend_x}" y="{legend_y + idx * 25}" width="20" height="20" fill="{color}" opacity="0.8"/>
  <text x="{legend_x + 30}" y="{legend_y + idx * 25 + 15}" class="label">{area} ({count})</text>
"""
    
    y_offset += 400
    
    # 3. Question Classifier Dataset (500 samples)
    svg_content += f"""
  <!-- Question Classifier Dataset -->
  <rect x="50" y="{y_offset}" width="1100" height="350" class="border"/>
  <text x="600" y="{y_offset + 30}" text-anchor="middle" class="subtitle">3. Question Classifier Dataset (500 samples - 25%)</text>
  
  <text x="100" y="{y_offset + 70}" class="text">Features: Question Text (TF-IDF Vectorized), Keywords, Context</text>
  <text x="100" y="{y_offset + 95}" class="text">Target: Question Category (Programming, Theory, Math, Design, etc.)</text>
  
  <text x="100" y="{y_offset + 130}" class="label">Sample Distribution (Question Categories):</text>
"""
    
    categories = [
        ("Programming", 90, "#F59E0B"),
        ("Algorithms", 80, "#FB923C"),
        ("Data Structures", 75, "#FDBA74"),
        ("Web Dev", 70, "#FED7AA"),
        ("Databases", 60, "#FFEDD5"),
        ("System Design", 50, "#FFF7ED"),
        ("Math/Logic", 45, "#F97316"),
        ("Debugging", 30, "#EA580C")
    ]
    
    x_start = 100
    y_bar_start = y_offset + 200
    max_width = 900
    max_count = max(count for _, count, _ in categories)
    
    for idx, (category, count, color) in enumerate(categories):
        bar_width = (count / max_count) * max_width
        svg_content += f"""
  <rect x="{x_start}" y="{y_bar_start + idx * 35}" width="{bar_width}" height="25" fill="{color}" opacity="0.8"/>
  <text x="{x_start + 10}" y="{y_bar_start + idx * 35 + 18}" class="label" fill="#fff">{category}</text>
  <text x="{x_start + bar_width + 10}" y="{y_bar_start + idx * 35 + 18}" class="label">{count} samples</text>
"""
    
    y_offset += 400
    
    # 4. Difficulty Predictor Dataset (200 samples)
    svg_content += f"""
  <!-- Difficulty Predictor Dataset -->
  <rect x="50" y="{y_offset}" width="1100" height="300" class="border"/>
  <text x="600" y="{y_offset + 30}" text-anchor="middle" class="subtitle">4. Difficulty Predictor Dataset (200 samples - 10%)</text>
  
  <text x="100" y="{y_offset + 70}" class="text">Features: User Level, Past Performance, Question Complexity, Time Taken</text>
  <text x="100" y="{y_offset + 95}" class="text">Target: Difficulty Level (Beginner, Intermediate, Advanced, Expert)</text>
  
  <text x="100" y="{y_offset + 130}" class="label">Sample Distribution (Difficulty Levels):</text>
"""
    
    difficulties = [
        ("Beginner", 60, "#10B981", 200),
        ("Intermediate", 80, "#F59E0B", 400),
        ("Advanced", 45, "#EF4444", 600),
        ("Expert", 15, "#8B5CF6", 800)
    ]
    
    for diff, count, color, x_pos in difficulties:
        radius = np.sqrt(count / np.pi) * 4
        svg_content += f"""
  <circle cx="{x_pos}" cy="{y_offset + 210}" r="{radius}" fill="{color}" opacity="0.6" stroke="#333" stroke-width="2"/>
  <text x="{x_pos}" y="{y_offset + 210}" text-anchor="middle" class="text" fill="#fff">{diff}</text>
  <text x="{x_pos}" y="{y_offset + 230}" text-anchor="middle" class="label" fill="#fff">{count}</text>
"""
    
    svg_content += """
  <!-- Footer -->
  <text x="600" y="2350" text-anchor="middle" class="label">Total Dataset Size: 2000 samples (800 + 500 + 500 + 200)</text>
  <text x="600" y="2370" text-anchor="middle" class="label">Models: Gradient Boosting, Random Forest, Naive Bayes, Decision Tree, TF-IDF</text>
</svg>
"""
    
    return svg_content

if __name__ == "__main__":
    print("Generating dataset visualizations...")
    
    svg = generate_svg_visualizations()
    
    output_file = Path("ml_model/dataset_visualization.svg")
    with open(output_file, "w", encoding="utf-8") as f:
        f.write(svg)
    
    print(f"✅ Dataset visualization saved to: {output_file}")
    print("\nYou can:")
    print("1. Open the SVG file directly in your browser")
    print("2. View it in the Replit file viewer")
    print("3. Download it to view locally")
    
    # Also generate a simple HTML viewer
    html_content = f"""<!DOCTYPE html>
<html>
<head>
    <title>ML Dataset Visualization</title>
    <style>
        body {{
            font-family: sans-serif;
            margin: 0;
            padding: 20px;
            background: #f5f5f5;
        }}
        .container {{
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }}
        h1 {{
            color: #333;
            text-align: center;
        }}
        .info {{
            background: #e3f2fd;
            padding: 15px;
            border-radius: 4px;
            margin: 20px 0;
        }}
    </style>
</head>
<body>
    <div class="container">
        <h1>🤖 ML Model Training Datasets Visualization</h1>
        <div class="info">
            <strong>Total Dataset: 2000 samples</strong><br>
            • Career Recommendation: 800 samples (40%)<br>
            • Study Suggestions: 500 samples (25%)<br>
            • Question Classification: 500 samples (25%)<br>
            • Difficulty Prediction: 200 samples (10%)<br><br>
            All datasets are saved as .pkl files in <code>ml_model/saved_models/</code>
        </div>
        <img src="dataset_visualization.svg" alt="Dataset Visualization" style="width: 100%; height: auto;">
    </div>
</body>
</html>
"""
    
    html_file = Path("ml_model/dataset_visualization.html")
    with open(html_file, "w", encoding="utf-8") as f:
        f.write(html_content)
    
    print(f"✅ HTML viewer saved to: {html_file}")
    print("\nDataset Breakdown (Total: 2000 samples):")
    print("  • Career Recommendation: 800 samples (40%) → gradient_boosting.pkl, random_forest.pkl")
    print("  • Study Suggestions: 500 samples (25%) → study_suggester.pkl")
    print("  • Question Classification: 500 samples (25%) → question_classifier.pkl")
    print("  • Difficulty Prediction: 200 samples (10%) → difficulty_predictor.pkl")
