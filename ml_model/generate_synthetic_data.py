
import csv
import random
import numpy as np

# Career paths and their typical patterns
CAREER_PATTERNS = {
    "frontend": {
        "interests": {"visual": (3, 5), "web": (4, 5), "mobile": (2, 4)},
        "performance": {"frontend_perf": (0.7, 0.95), "backend_perf": (0.4, 0.6)},
    },
    "backend": {
        "interests": {"backend": (4, 5), "data": (3, 4), "cloud": (3, 4)},
        "performance": {"backend_perf": (0.75, 0.95), "data_perf": (0.6, 0.8)},
    },
    "data": {
        "interests": {"data": (4, 5), "math": (4, 5), "backend": (3, 4)},
        "performance": {"data_perf": (0.8, 0.95), "algo_perf": (0.7, 0.9)},
    },
    "cloud": {
        "interests": {"cloud": (4, 5), "backend": (3, 4), "security": (3, 4)},
        "performance": {"backend_perf": (0.65, 0.85), "data_perf": (0.6, 0.75)},
    },
    "mobile": {
        "interests": {"mobile": (4, 5), "visual": (4, 5), "web": (3, 4)},
        "performance": {"frontend_perf": (0.8, 0.95), "backend_perf": (0.45, 0.6)},
    },
    "security": {
        "interests": {"security": (4, 5), "backend": (3, 4), "math": (3, 4)},
        "performance": {"backend_perf": (0.6, 0.8), "algo_perf": (0.7, 0.85)},
    },
    "fullstack": {
        "interests": {"web": (4, 5), "backend": (4, 5), "visual": (3, 4)},
        "performance": {"frontend_perf": (0.75, 0.9), "backend_perf": (0.7, 0.9)},
    },
}

def generate_user_data(career_path, user_id):
    """Generate synthetic user data for a specific career path"""
    pattern = CAREER_PATTERNS[career_path]
    
    # Level correlates with XP and badges
    level = random.randint(1, 25)
    xp = level * 100 + random.randint(-50, 200)
    badges = max(0, level // 3 + random.randint(-1, 2))
    
    # Quiz and code scores
    quiz_score = round(random.uniform(0.55, 0.95), 2)
    code_score = round(random.uniform(0.45, 0.90), 2)
    
    # Study time correlates with level
    study_time = level * 20 + random.randint(-50, 100)
    
    # Interest scores (1-5 scale)
    interests = {
        "visual": random.randint(1, 5),
        "backend": random.randint(1, 5),
        "math": random.randint(1, 5),
        "web": random.randint(1, 5),
        "data": random.randint(1, 5),
        "cloud": random.randint(1, 5),
        "mobile": random.randint(1, 5),
        "security": random.randint(1, 5),
    }
    
    # Apply career-specific interest patterns
    for interest, (min_val, max_val) in pattern.get("interests", {}).items():
        interests[interest] = random.randint(min_val, max_val)
    
    # Performance scores (0-1 scale)
    performance = {
        "frontend_perf": round(random.uniform(0.4, 0.7), 2),
        "backend_perf": round(random.uniform(0.4, 0.7), 2),
        "data_perf": round(random.uniform(0.4, 0.7), 2),
        "algo_perf": round(random.uniform(0.5, 0.8), 2),
    }
    
    # Apply career-specific performance patterns
    for perf, (min_val, max_val) in pattern.get("performance", {}).items():
        performance[perf] = round(random.uniform(min_val, max_val), 2)
    
    return [
        level, xp, badges, quiz_score, code_score, study_time,
        interests["visual"], interests["backend"], interests["math"], interests["web"],
        interests["data"], interests["cloud"], interests["mobile"], interests["security"],
        performance["frontend_perf"], performance["backend_perf"],
        performance["data_perf"], performance["algo_perf"],
        career_path
    ]

# Generate 2000 samples with balanced distribution
samples = []
careers = list(CAREER_PATTERNS.keys())
samples_per_career = 2000 // len(careers)
remainder = 2000 % len(careers)

for i, career in enumerate(careers):
    count = samples_per_career + (1 if i < remainder else 0)
    for j in range(count):
        samples.append(generate_user_data(career, len(samples)))

# Shuffle to avoid sequential patterns
random.shuffle(samples)

# Write to CSV
with open("ml_model/synthetic_data_2000.csv", "w", newline="") as f:
    writer = csv.writer(f)
    writer.writerow([
        "level", "xp", "badges", "quiz_score", "code_challenge_score", "study_time",
        "interest_visual", "interest_backend", "interest_math", "interest_web",
        "interest_data", "interest_cloud", "interest_mobile", "interest_security",
        "frontend_perf", "backend_perf", "data_perf", "algo_perf", "career_path"
    ])
    writer.writerows(samples)

print(f"✅ Generated 2000 synthetic training samples")
print(f"Distribution: {samples_per_career} samples per career path")
