#!/usr/bin/env python3
"""
ML Prediction Service for CareerQuest
Handles quiz generation, study suggestions, career recommendations using pre-trained models
"""

import sys
import json
import pickle
import numpy as np
import random
from pathlib import Path
from datetime import datetime

SAVED_MODELS_DIR = Path(__file__).parent / "saved_models"

_models_cache = None
_models_status = {"loaded": False, "errors": []}

def log_startup():
    """Log application startup information"""
    timestamp = datetime.now().strftime("%Y-%m-%d %H:%M:%S")
    print(f"\n{'='*60}", file=sys.stderr)
    print(f"Application Startup at {timestamp}", file=sys.stderr)
    print(f"{'='*60}\n", file=sys.stderr)
    print("🚀 Starting CareerQuest ML Prediction Service...\n", file=sys.stderr)

def log_models_loaded(models):
    """Log successfully loaded models"""
    print("✅ Models loaded successfully!", file=sys.stderr)
    
    ml_models = []
    if "question_classifier" in models:
        ml_models.append("Question Classifier")
    if "difficulty_predictor" in models:
        ml_models.append("Difficulty Predictor")
    if "random_forest" in models:
        ml_models.append("Career Recommender (Random Forest)")
    if "study_suggester" in models:
        ml_models.append("Study Suggester")
    if "scaler" in models:
        ml_models.append("Feature Scaler")
    if "features" in models:
        feature_count = len(models["features"]) if isinstance(models["features"], list) else 12
        print(f"   Features: {feature_count} features", file=sys.stderr)
    
    for model_name in ml_models:
        print(f"   ✓ {model_name}", file=sys.stderr)
    
    print(f"   Question Bank: {len(QUESTION_BANK)} questions across 5 categories\n", file=sys.stderr)

def log_prediction_request(command, data, result=None, error=None):
    """Log prediction requests with results"""
    timestamp = datetime.now().strftime("%Y-%m-%d %I:%M:%S %p")
    
    if command == "generate_quiz":
        print(f"\n🎯 Quiz Generation Request | {timestamp}", file=sys.stderr)
        print(f"   Category: {data.get('category', 'mixed')}", file=sys.stderr)
        print(f"   Difficulty: {data.get('difficulty', 'medium')}", file=sys.stderr)
        print(f"   Career Path: {data.get('career_path', 'fullstack')}", file=sys.stderr)
        print(f"   Count: {data.get('count', 5)} questions", file=sys.stderr)
        print(f"   User Level: {data.get('level', 10)}", file=sys.stderr)
        if result:
            print(f"   ✅ Generated: {len(result)} unique questions", file=sys.stderr)
    
    elif command == "recommend_career":
        print(f"\n🎓 Career Recommendation Request | {timestamp}", file=sys.stderr)
        print(f"   Visual Design: {data.get('visual_design', 3)}/5", file=sys.stderr)
        print(f"   Backend Preference: {data.get('backend_pref', 3)}/5", file=sys.stderr)
        print(f"   Data Interest: {data.get('data_interest', 3)}/5", file=sys.stderr)
        if result:
            print(f"   ✅ Recommendation: {result.get('recommended_path', 'N/A')}", file=sys.stderr)
            print(f"   Confidence: {int(result.get('confidence', 0) * 100)}%", file=sys.stderr)
    
    elif command == "suggest_study":
        print(f"\n📚 Study Suggestion Request | {timestamp}", file=sys.stderr)
        print(f"   User Level: {data.get('level', 1)}", file=sys.stderr)
        print(f"   Career Path: {data.get('career_path', 'fullstack')}", file=sys.stderr)
        print(f"   Weak Categories: {data.get('weak_categories', [])}", file=sys.stderr)
        if result:
            print(f"   ✅ Suggestions: {len(result)} personalized recommendations", file=sys.stderr)
    
    elif command == "classify_question":
        print(f"\n🔍 Question Classification Request | {timestamp}", file=sys.stderr)
        print(f"   Text: {data.get('text', '')[:50]}...", file=sys.stderr)
        if result:
            print(f"   ✅ Category: {result.get('category', 'N/A')}", file=sys.stderr)
            print(f"   Confidence: {int(result.get('confidence', 0) * 100)}%", file=sys.stderr)
    
    elif command == "health_check":
        print(f"\n❤️  Health Check Request | {timestamp}", file=sys.stderr)
        if result:
            print(f"   ✅ Status: {result.get('status', 'N/A')}", file=sys.stderr)
            print(f"   Models Loaded: {result.get('models_loaded', 0)}", file=sys.stderr)
    
    if error:
        print(f"   ❌ Error: {error}", file=sys.stderr)

def load_models():
    """Load all pre-trained ML models with validation"""
    global _models_cache, _models_status
    if _models_cache is not None:
        return _models_cache
    
    # Log startup on first load
    if _models_cache is None:
        log_startup()
    
    models = {}
    errors = []
    
    model_files = {
        "question_classifier": "question_classifier.pkl",
        "question_vectorizer": "question_vectorizer.pkl",
        "question_label_encoder": "question_label_encoder.pkl",
        "difficulty_predictor": "difficulty_predictor.pkl",
        "difficulty_label_encoder": "difficulty_label_encoder.pkl",
        "study_suggester": "study_suggester.pkl",
        "study_label_encoder": "study_label_encoder.pkl",
        "career_label_encoder": "career_label_encoder.pkl",
        "random_forest": "random_forest.pkl",
        "scaler": "scaler.pkl",
        "features": "features.pkl",
    }
    
    for key, filename in model_files.items():
        filepath = SAVED_MODELS_DIR / filename
        if filepath.exists():
            try:
                with open(filepath, "rb") as f:
                    models[key] = pickle.load(f)
            except Exception as e:
                errors.append(f"Failed to load {key}: {e}")
                print(f"Warning: Failed to load {key}: {e}", file=sys.stderr)
        else:
            errors.append(f"Model file not found: {filename}")
    
    career_features_path = SAVED_MODELS_DIR / "career_features.json"
    if career_features_path.exists():
        try:
            with open(career_features_path, "r") as f:
                models["career_features"] = json.load(f)
        except Exception as e:
            errors.append(f"Failed to load career_features: {e}")
    
    _models_cache = models
    _models_status = {"loaded": True, "errors": errors, "model_count": len(models)}
    
    # Log successful model loading
    log_models_loaded(models)
    
    if errors:
        print(f"⚠️  ML Models loaded with {len(errors)} warnings:", file=sys.stderr)
        for error in errors[:3]:  # Show first 3 errors
            print(f"   - {error}", file=sys.stderr)
    
    return models

def get_default_level_range(difficulty):
    """Get default level range based on difficulty"""
    defaults = {
        "easy": [1, 10],
        "medium": [5, 20],
        "hard": [15, 30]
    }
    return defaults.get(difficulty, [1, 30])

QUESTION_BANK = [
    # =============== ALGORITHMS - EASY ===============
    {
        "question": "What is the time complexity of binary search?",
        "options": ["O(n)", "O(log n)", "O(n^2)", "O(1)"],
        "correctAnswer": 1,
        "category": "algorithms",
        "difficulty": "easy",
        "explanation": "Binary search has O(log n) complexity because it halves the search space each iteration.",
        "level_range": [1, 8]
    },
    {
        "question": "What is an array?",
        "options": ["A function type", "A collection of elements stored at contiguous memory locations", "A type of loop", "A database table"],
        "correctAnswer": 1,
        "category": "algorithms",
        "difficulty": "easy",
        "explanation": "An array is a data structure that stores elements of the same type in contiguous memory locations.",
        "level_range": [1, 5]
    },
    {
        "question": "What does FIFO stand for in queue data structures?",
        "options": ["First In First Out", "First In Final Output", "Fast Input Fast Output", "Fixed Input Fixed Output"],
        "correctAnswer": 0,
        "category": "algorithms",
        "difficulty": "easy",
        "explanation": "FIFO means First In First Out - the first element added is the first one removed.",
        "level_range": [1, 6]
    },
    {
        "question": "What is the time complexity of accessing an element in an array by index?",
        "options": ["O(n)", "O(log n)", "O(n^2)", "O(1)"],
        "correctAnswer": 3,
        "category": "algorithms",
        "difficulty": "easy",
        "explanation": "Array access by index is O(1) constant time because elements are stored at contiguous memory locations.",
        "level_range": [1, 7]
    },
    {
        "question": "What is a stack data structure?",
        "options": ["FIFO structure", "LIFO structure", "Random access structure", "Sorted structure"],
        "correctAnswer": 1,
        "category": "algorithms",
        "difficulty": "easy",
        "explanation": "A stack is a LIFO (Last In First Out) data structure where the last element added is the first removed.",
        "level_range": [1, 6]
    },
    # =============== ALGORITHMS - MEDIUM ===============
    {
        "question": "What is recursion?",
        "options": ["A loop type", "A function that calls itself", "A data structure", "An error type"],
        "correctAnswer": 1,
        "category": "algorithms",
        "difficulty": "medium",
        "explanation": "Recursion is a programming technique where a function calls itself to solve smaller instances of the same problem.",
        "level_range": [6, 15]
    },
    {
        "question": "What is Big O notation used for?",
        "options": ["Measuring code length", "Describing algorithm efficiency", "Formatting output", "Managing memory"],
        "correctAnswer": 1,
        "category": "algorithms",
        "difficulty": "medium",
        "explanation": "Big O notation describes the upper bound of an algorithm's time or space complexity.",
        "level_range": [5, 14]
    },
    {
        "question": "What is a hash table?",
        "options": ["A type of database", "A data structure using key-value pairs with O(1) average lookup", "A sorting algorithm", "A file format"],
        "correctAnswer": 1,
        "category": "algorithms",
        "difficulty": "medium",
        "explanation": "A hash table is a data structure that stores key-value pairs and provides O(1) average time complexity for lookups.",
        "level_range": [7, 16]
    },
    {
        "question": "What is the worst-case time complexity of quicksort?",
        "options": ["O(n)", "O(n log n)", "O(n^2)", "O(log n)"],
        "correctAnswer": 2,
        "category": "algorithms",
        "difficulty": "medium",
        "explanation": "Quicksort's worst case is O(n^2) when the pivot selection consistently results in unbalanced partitions.",
        "level_range": [8, 17]
    },
    {
        "question": "What is a linked list?",
        "options": ["An array variant", "A sequence of nodes where each node points to the next", "A tree structure", "A hash function"],
        "correctAnswer": 1,
        "category": "algorithms",
        "difficulty": "medium",
        "explanation": "A linked list is a linear data structure where elements are stored in nodes that contain data and a reference to the next node.",
        "level_range": [5, 14]
    },
    {
        "question": "What is the time complexity of inserting at the beginning of a linked list?",
        "options": ["O(n)", "O(log n)", "O(n^2)", "O(1)"],
        "correctAnswer": 3,
        "category": "algorithms",
        "difficulty": "medium",
        "explanation": "Inserting at the beginning of a linked list is O(1) because you only need to update the head pointer.",
        "level_range": [6, 15]
    },
    # =============== ALGORITHMS - HARD ===============
    {
        "question": "What is memoization?",
        "options": ["A memory type", "Caching function results to avoid redundant calculations", "A debugging technique", "A testing method"],
        "correctAnswer": 1,
        "category": "algorithms",
        "difficulty": "hard",
        "explanation": "Memoization is an optimization technique that caches the results of expensive function calls to avoid redundant calculations.",
        "level_range": [15, 30]
    },
    {
        "question": "What is the time complexity of Dijkstra's algorithm with a binary heap?",
        "options": ["O(V)", "O(E log V)", "O(V^2)", "O(E + V)"],
        "correctAnswer": 1,
        "category": "algorithms",
        "difficulty": "hard",
        "explanation": "Dijkstra's algorithm with a binary heap has O(E log V) complexity where E is edges and V is vertices.",
        "level_range": [18, 30]
    },
    {
        "question": "What is dynamic programming?",
        "options": ["Writing code dynamically", "Breaking problems into overlapping subproblems and storing their solutions", "Runtime code generation", "Automatic memory management"],
        "correctAnswer": 1,
        "category": "algorithms",
        "difficulty": "hard",
        "explanation": "Dynamic programming solves complex problems by breaking them into simpler overlapping subproblems and storing results to avoid recomputation.",
        "level_range": [16, 30]
    },
    {
        "question": "What is the amortized time complexity of adding to a dynamic array?",
        "options": ["O(n)", "O(log n)", "O(n^2)", "O(1)"],
        "correctAnswer": 3,
        "category": "algorithms",
        "difficulty": "hard",
        "explanation": "While individual insertions may trigger O(n) resizing, the amortized cost across many insertions is O(1).",
        "level_range": [17, 30]
    },
    {
        "question": "What is a balanced binary search tree?",
        "options": ["A tree with equal values", "A BST where height difference between subtrees is bounded", "A complete binary tree", "A tree with no duplicates"],
        "correctAnswer": 1,
        "category": "algorithms",
        "difficulty": "hard",
        "explanation": "A balanced BST maintains a bounded height difference between left and right subtrees, ensuring O(log n) operations.",
        "level_range": [16, 30]
    },
    
    # =============== FRONTEND - EASY ===============
    {
        "question": "What does CSS stand for?",
        "options": ["Creative Style Sheets", "Cascading Style Sheets", "Computer Style Sheets", "Colorful Style Sheets"],
        "correctAnswer": 1,
        "category": "frontend",
        "difficulty": "easy",
        "explanation": "CSS stands for Cascading Style Sheets, used for styling web pages.",
        "level_range": [1, 6]
    },
    {
        "question": "What is the difference between let and const in JavaScript?",
        "options": ["No difference", "let can be reassigned, const cannot", "const is faster", "let is deprecated"],
        "correctAnswer": 1,
        "category": "frontend",
        "difficulty": "easy",
        "explanation": "let allows variable reassignment while const creates a read-only reference that cannot be reassigned.",
        "level_range": [1, 7]
    },
    {
        "question": "What does HTML stand for?",
        "options": ["Hyper Text Markup Language", "High Tech Modern Language", "Hyper Transfer Markup Language", "Home Tool Markup Language"],
        "correctAnswer": 0,
        "category": "frontend",
        "difficulty": "easy",
        "explanation": "HTML stands for HyperText Markup Language, the standard language for creating web pages.",
        "level_range": [1, 5]
    },
    {
        "question": "Which HTML tag is used for the largest heading?",
        "options": ["<h6>", "<heading>", "<h1>", "<head>"],
        "correctAnswer": 2,
        "category": "frontend",
        "difficulty": "easy",
        "explanation": "<h1> is used for the largest heading in HTML, with <h6> being the smallest.",
        "level_range": [1, 5]
    },
    {
        "question": "What is the CSS property to change text color?",
        "options": ["text-color", "font-color", "color", "foreground"],
        "correctAnswer": 2,
        "category": "frontend",
        "difficulty": "easy",
        "explanation": "The 'color' property in CSS is used to change the text color of an element.",
        "level_range": [1, 6]
    },
    # =============== FRONTEND - MEDIUM ===============
    {
        "question": "What is React's virtual DOM?",
        "options": ["A browser API", "An in-memory representation of the real DOM", "A database", "A server component"],
        "correctAnswer": 1,
        "category": "frontend",
        "difficulty": "medium",
        "explanation": "React's virtual DOM is an in-memory representation that React uses to optimize updates to the real DOM.",
        "level_range": [8, 18]
    },
    {
        "question": "What is a closure in JavaScript?",
        "options": ["A way to close files", "A function that has access to its outer scope", "A type of loop", "An error handler"],
        "correctAnswer": 1,
        "category": "frontend",
        "difficulty": "medium",
        "explanation": "A closure is a function that retains access to variables from its outer (enclosing) scope even after that scope has finished executing.",
        "level_range": [7, 16]
    },
    {
        "question": "What is the purpose of the useEffect hook in React?",
        "options": ["State management", "Performing side effects", "Routing", "Form validation"],
        "correctAnswer": 1,
        "category": "frontend",
        "difficulty": "medium",
        "explanation": "useEffect is a React hook for performing side effects like data fetching, subscriptions, or DOM manipulation.",
        "level_range": [9, 18]
    },
    {
        "question": "What is CSS Flexbox used for?",
        "options": ["Animation", "One-dimensional layout", "Database queries", "Server rendering"],
        "correctAnswer": 1,
        "category": "frontend",
        "difficulty": "medium",
        "explanation": "Flexbox is a CSS layout model designed for one-dimensional layouts, making it easy to align and distribute space among items.",
        "level_range": [6, 15]
    },
    {
        "question": "What is event bubbling in JavaScript?",
        "options": ["Creating events", "Events propagating from child to parent elements", "Canceling events", "Event scheduling"],
        "correctAnswer": 1,
        "category": "frontend",
        "difficulty": "medium",
        "explanation": "Event bubbling is when an event triggered on a child element propagates up through its parent elements in the DOM tree.",
        "level_range": [8, 17]
    },
    {
        "question": "What is the purpose of useState in React?",
        "options": ["Routing", "Managing component state", "Making API calls", "Styling components"],
        "correctAnswer": 1,
        "category": "frontend",
        "difficulty": "medium",
        "explanation": "useState is a React hook that allows functional components to have state variables.",
        "level_range": [7, 16]
    },
    # =============== FRONTEND - HARD ===============
    {
        "question": "What is React's reconciliation algorithm?",
        "options": ["A sorting algorithm", "The process of comparing virtual DOM trees to update the real DOM efficiently", "A security feature", "A routing mechanism"],
        "correctAnswer": 1,
        "category": "frontend",
        "difficulty": "hard",
        "explanation": "Reconciliation is React's diffing algorithm that compares virtual DOM trees to determine the minimal set of changes needed for the real DOM.",
        "level_range": [17, 30]
    },
    {
        "question": "What is tree shaking in webpack?",
        "options": ["Reordering code", "Removing unused code from bundles", "Code compression", "Syntax checking"],
        "correctAnswer": 1,
        "category": "frontend",
        "difficulty": "hard",
        "explanation": "Tree shaking is a technique used to eliminate dead code by analyzing import/export statements to remove unused modules.",
        "level_range": [16, 30]
    },
    {
        "question": "What is the purpose of React.memo?",
        "options": ["Memory allocation", "Memoizing component rendering to prevent unnecessary re-renders", "State persistence", "Error logging"],
        "correctAnswer": 1,
        "category": "frontend",
        "difficulty": "hard",
        "explanation": "React.memo is a higher-order component that memoizes the rendered output, preventing re-renders if props haven't changed.",
        "level_range": [15, 30]
    },
    {
        "question": "What is hydration in server-side rendering?",
        "options": ["Adding water to servers", "Attaching event handlers to server-rendered HTML", "Database optimization", "Cache warming"],
        "correctAnswer": 1,
        "category": "frontend",
        "difficulty": "hard",
        "explanation": "Hydration is the process where client-side JavaScript takes over server-rendered HTML by attaching event handlers and making it interactive.",
        "level_range": [18, 30]
    },
    
    # =============== BACKEND - EASY ===============
    {
        "question": "Which HTTP method is used to update a resource?",
        "options": ["GET", "POST", "PUT", "DELETE"],
        "correctAnswer": 2,
        "category": "backend",
        "difficulty": "easy",
        "explanation": "PUT is the HTTP method used to update or replace an existing resource.",
        "level_range": [1, 7]
    },
    {
        "question": "What is a primary key in a database?",
        "options": ["Any column", "A unique identifier for each row", "The first column", "A foreign key reference"],
        "correctAnswer": 1,
        "category": "backend",
        "difficulty": "easy",
        "explanation": "A primary key uniquely identifies each record in a database table.",
        "level_range": [1, 6]
    },
    {
        "question": "What is a REST API?",
        "options": ["A database type", "An architectural style for web services", "A programming language", "A testing framework"],
        "correctAnswer": 1,
        "category": "backend",
        "difficulty": "easy",
        "explanation": "REST (Representational State Transfer) is an architectural style for designing networked applications using HTTP methods.",
        "level_range": [1, 8]
    },
    {
        "question": "What HTTP status code indicates success?",
        "options": ["404", "500", "200", "301"],
        "correctAnswer": 2,
        "category": "backend",
        "difficulty": "easy",
        "explanation": "HTTP status code 200 indicates that the request was successful.",
        "level_range": [1, 6]
    },
    {
        "question": "What does JSON stand for?",
        "options": ["JavaScript Object Notation", "Java Standard Object Notation", "JavaScript Online Notation", "Java Serialized Object Notation"],
        "correctAnswer": 0,
        "category": "backend",
        "difficulty": "easy",
        "explanation": "JSON stands for JavaScript Object Notation, a lightweight data interchange format.",
        "level_range": [1, 5]
    },
    # =============== BACKEND - MEDIUM ===============
    {
        "question": "What is the purpose of middleware in Express.js?",
        "options": ["Database connection", "Process requests before reaching routes", "Render views", "Manage sessions only"],
        "correctAnswer": 1,
        "category": "backend",
        "difficulty": "medium",
        "explanation": "Middleware functions in Express.js process requests before they reach route handlers.",
        "level_range": [7, 16]
    },
    {
        "question": "What is a foreign key in a database?",
        "options": ["A key from another country", "A column that references a primary key in another table", "An encryption key", "A backup key"],
        "correctAnswer": 1,
        "category": "backend",
        "difficulty": "medium",
        "explanation": "A foreign key is a column that creates a relationship between two tables by referencing the primary key of another table.",
        "level_range": [6, 15]
    },
    {
        "question": "What is connection pooling?",
        "options": ["Swimming pool management", "Reusing database connections instead of creating new ones", "Network load balancing", "Thread management"],
        "correctAnswer": 1,
        "category": "backend",
        "difficulty": "medium",
        "explanation": "Connection pooling maintains a cache of database connections that can be reused, improving performance.",
        "level_range": [9, 18]
    },
    {
        "question": "What is rate limiting?",
        "options": ["Speed optimization", "Restricting the number of requests a user can make in a time period", "Database throttling", "Memory management"],
        "correctAnswer": 1,
        "category": "backend",
        "difficulty": "medium",
        "explanation": "Rate limiting controls the rate of requests a user can make to an API to prevent abuse and ensure fair usage.",
        "level_range": [8, 17]
    },
    {
        "question": "What is an ORM?",
        "options": ["Object Relational Mapping", "Online Resource Manager", "Output Render Module", "Object Runtime Memory"],
        "correctAnswer": 0,
        "category": "backend",
        "difficulty": "medium",
        "explanation": "ORM (Object Relational Mapping) is a technique that maps database tables to classes, allowing developers to interact with databases using objects.",
        "level_range": [7, 16]
    },
    # =============== BACKEND - HARD ===============
    {
        "question": "What is database sharding?",
        "options": ["Deleting old data", "Horizontally partitioning data across multiple databases", "Data encryption", "Backup strategy"],
        "correctAnswer": 1,
        "category": "backend",
        "difficulty": "hard",
        "explanation": "Sharding is a database architecture pattern that horizontally partitions data across multiple database instances for scalability.",
        "level_range": [17, 30]
    },
    {
        "question": "What is eventual consistency in distributed systems?",
        "options": ["Immediate data sync", "Data will become consistent given enough time without new updates", "Data validation", "Error handling"],
        "correctAnswer": 1,
        "category": "backend",
        "difficulty": "hard",
        "explanation": "Eventual consistency guarantees that, given enough time without new updates, all replicas will converge to the same value.",
        "level_range": [18, 30]
    },
    {
        "question": "What is the CAP theorem?",
        "options": ["A coding standard", "States that a distributed system can only guarantee two of three: Consistency, Availability, Partition tolerance", "A security protocol", "A testing methodology"],
        "correctAnswer": 1,
        "category": "backend",
        "difficulty": "hard",
        "explanation": "CAP theorem states that a distributed data store can only provide two of three guarantees: Consistency, Availability, and Partition tolerance.",
        "level_range": [19, 30]
    },
    {
        "question": "What is a message queue?",
        "options": ["An email system", "A system for asynchronous communication between services using messages", "A database type", "A logging mechanism"],
        "correctAnswer": 1,
        "category": "backend",
        "difficulty": "hard",
        "explanation": "A message queue enables asynchronous communication between services by storing messages until they can be processed.",
        "level_range": [16, 30]
    },
    
    # =============== DATA - EASY ===============
    {
        "question": "What is SQL used for?",
        "options": ["Styling web pages", "Managing and querying relational databases", "Creating animations", "Building mobile apps"],
        "correctAnswer": 1,
        "category": "data",
        "difficulty": "easy",
        "explanation": "SQL (Structured Query Language) is used for managing and querying data in relational database systems.",
        "level_range": [1, 7]
    },
    {
        "question": "What is a database table?",
        "options": ["A furniture piece", "A structured collection of data organized in rows and columns", "A type of graph", "A programming function"],
        "correctAnswer": 1,
        "category": "data",
        "difficulty": "easy",
        "explanation": "A database table is a collection of related data organized in rows (records) and columns (fields).",
        "level_range": [1, 5]
    },
    {
        "question": "What does SELECT do in SQL?",
        "options": ["Deletes data", "Retrieves data from a database", "Creates tables", "Updates records"],
        "correctAnswer": 1,
        "category": "data",
        "difficulty": "easy",
        "explanation": "The SELECT statement is used to retrieve data from one or more tables in a database.",
        "level_range": [1, 6]
    },
    # =============== DATA - MEDIUM ===============
    {
        "question": "What is the difference between SQL and NoSQL databases?",
        "options": ["SQL is faster", "SQL uses structured tables, NoSQL uses flexible schemas", "NoSQL is always better", "They are the same"],
        "correctAnswer": 1,
        "category": "data",
        "difficulty": "medium",
        "explanation": "SQL databases use structured tables with predefined schemas, while NoSQL databases offer flexible, schema-less data storage.",
        "level_range": [7, 16]
    },
    {
        "question": "What is the purpose of indexes in databases?",
        "options": ["Store data", "Speed up data retrieval", "Encrypt data", "Delete records"],
        "correctAnswer": 1,
        "category": "data",
        "difficulty": "medium",
        "explanation": "Indexes are data structures that speed up data retrieval operations by providing quick access paths to rows.",
        "level_range": [8, 17]
    },
    {
        "question": "What is a JOIN in SQL?",
        "options": ["Connecting to a database", "Combining rows from two or more tables based on a related column", "Creating a backup", "Sorting data"],
        "correctAnswer": 1,
        "category": "data",
        "difficulty": "medium",
        "explanation": "A JOIN clause combines rows from two or more tables based on a related column between them.",
        "level_range": [6, 15]
    },
    {
        "question": "What is data aggregation?",
        "options": ["Deleting data", "Combining multiple data points into a summary", "Encrypting data", "Backing up data"],
        "correctAnswer": 1,
        "category": "data",
        "difficulty": "medium",
        "explanation": "Data aggregation is the process of gathering and summarizing data, often using functions like COUNT, SUM, AVG.",
        "level_range": [7, 16]
    },
    # =============== DATA - HARD ===============
    {
        "question": "What is normalization in databases?",
        "options": ["Making data smaller", "Organizing data to reduce redundancy", "Encrypting data", "Deleting duplicates"],
        "correctAnswer": 1,
        "category": "data",
        "difficulty": "hard",
        "explanation": "Normalization is the process of organizing a database to reduce data redundancy and improve data integrity.",
        "level_range": [15, 30]
    },
    {
        "question": "What is ACID in database transactions?",
        "options": ["A chemical property", "Atomicity, Consistency, Isolation, Durability", "A query language", "A backup method"],
        "correctAnswer": 1,
        "category": "data",
        "difficulty": "hard",
        "explanation": "ACID (Atomicity, Consistency, Isolation, Durability) is a set of properties that guarantee reliable database transactions.",
        "level_range": [16, 30]
    },
    {
        "question": "What is a data warehouse?",
        "options": ["A physical storage facility", "A system for reporting and analysis using data from multiple sources", "A backup system", "A type of NoSQL database"],
        "correctAnswer": 1,
        "category": "data",
        "difficulty": "hard",
        "explanation": "A data warehouse is a central repository that aggregates data from multiple sources for analysis and reporting.",
        "level_range": [17, 30]
    },
    
    # =============== SECURITY - EASY ===============
    {
        "question": "What is SQL injection?",
        "options": ["A database optimization technique", "An attack that inserts malicious SQL code", "A way to speed up queries", "A backup method"],
        "correctAnswer": 1,
        "category": "security",
        "difficulty": "easy",
        "explanation": "SQL injection is an attack where malicious SQL code is inserted into application queries to manipulate the database.",
        "level_range": [1, 8]
    },
    {
        "question": "What is HTTPS?",
        "options": ["A programming language", "A secure version of HTTP using encryption", "A database type", "A file format"],
        "correctAnswer": 1,
        "category": "security",
        "difficulty": "easy",
        "explanation": "HTTPS is the secure version of HTTP that encrypts communication between the browser and server using TLS/SSL.",
        "level_range": [1, 6]
    },
    {
        "question": "What is a password hash?",
        "options": ["An encrypted password", "A one-way transformation of a password for secure storage", "A password hint", "A temporary password"],
        "correctAnswer": 1,
        "category": "security",
        "difficulty": "easy",
        "explanation": "Password hashing transforms passwords into fixed-length strings that cannot be reversed, providing secure storage.",
        "level_range": [1, 7]
    },
    # =============== SECURITY - MEDIUM ===============
    {
        "question": "What is XSS (Cross-Site Scripting)?",
        "options": ["A CSS framework", "An attack that injects malicious scripts into web pages", "A browser feature", "A server configuration"],
        "correctAnswer": 1,
        "category": "security",
        "difficulty": "medium",
        "explanation": "XSS is a security vulnerability that allows attackers to inject malicious scripts into web pages viewed by others.",
        "level_range": [7, 16]
    },
    {
        "question": "What is CORS?",
        "options": ["A programming language", "Cross-Origin Resource Sharing security mechanism", "A database type", "A CSS property"],
        "correctAnswer": 1,
        "category": "security",
        "difficulty": "medium",
        "explanation": "CORS (Cross-Origin Resource Sharing) is a security mechanism that controls how web pages can request resources from different domains.",
        "level_range": [8, 17]
    },
    {
        "question": "What is the difference between authentication and authorization?",
        "options": ["They are the same", "Authentication verifies identity, authorization grants access", "Authorization comes first", "Neither is important"],
        "correctAnswer": 1,
        "category": "security",
        "difficulty": "medium",
        "explanation": "Authentication verifies who you are, while authorization determines what you're allowed to do.",
        "level_range": [6, 15]
    },
    {
        "question": "What is CSRF?",
        "options": ["A file format", "Cross-Site Request Forgery attack", "A compression algorithm", "A caching mechanism"],
        "correctAnswer": 1,
        "category": "security",
        "difficulty": "medium",
        "explanation": "CSRF is an attack that tricks authenticated users into submitting unwanted requests to a web application.",
        "level_range": [9, 18]
    },
    {
        "question": "What is input validation?",
        "options": ["User interface design", "Checking user input for correctness and safety before processing", "Database indexing", "Network monitoring"],
        "correctAnswer": 1,
        "category": "security",
        "difficulty": "medium",
        "explanation": "Input validation ensures that user-provided data meets expected criteria and is safe to process, preventing many attacks.",
        "level_range": [5, 14]
    },
    # =============== SECURITY - HARD ===============
    {
        "question": "What is JWT token hijacking?",
        "options": ["Creating tokens", "Stealing and misusing authentication tokens", "Token refresh", "Token generation"],
        "correctAnswer": 1,
        "category": "security",
        "difficulty": "hard",
        "explanation": "JWT hijacking occurs when an attacker steals a valid JWT token and uses it to impersonate the legitimate user.",
        "level_range": [16, 30]
    },
    {
        "question": "What is defense in depth?",
        "options": ["Deep learning security", "Using multiple layers of security controls throughout a system", "Network depth analysis", "Code obfuscation"],
        "correctAnswer": 1,
        "category": "security",
        "difficulty": "hard",
        "explanation": "Defense in depth is a security strategy that uses multiple layers of controls, so if one fails, others provide protection.",
        "level_range": [17, 30]
    },
    {
        "question": "What is a zero-day vulnerability?",
        "options": ["A minor bug", "A vulnerability unknown to software vendors and without a patch", "A testing technique", "A backup strategy"],
        "correctAnswer": 1,
        "category": "security",
        "difficulty": "hard",
        "explanation": "A zero-day vulnerability is a software flaw unknown to the vendor and has no available patch, making it highly dangerous.",
        "level_range": [18, 30]
    },
    {
        "question": "What is the OWASP Top 10?",
        "options": ["A ranking of websites", "A list of the most critical web application security risks", "A testing framework", "A coding standard"],
        "correctAnswer": 1,
        "category": "security",
        "difficulty": "hard",
        "explanation": "The OWASP Top 10 is a regularly updated list of the most critical security risks to web applications.",
        "level_range": [15, 30]
    }
]

def calculate_question_score(question, user_level, career_path, target_difficulty=None):
    """Calculate a relevance score for a question based on user profile using ML models"""
    models = load_models()
    score = 0.0
    
    level_range = question.get("level_range")
    if not level_range:
        level_range = get_default_level_range(question.get("difficulty", "medium"))
    if level_range[0] <= user_level <= level_range[1]:
        score += 2.0
        optimal_level = (level_range[0] + level_range[1]) / 2
        level_distance = abs(user_level - optimal_level)
        max_distance = (level_range[1] - level_range[0]) / 2
        score += 1.0 * (1 - level_distance / max(max_distance, 1))
    else:
        distance = min(abs(user_level - level_range[0]), abs(user_level - level_range[1]))
        score -= distance * 0.1
    
    category_weights = {
        "frontend": {"frontend": 2.0, "algorithms": 1.0, "security": 0.5},
        "backend": {"backend": 2.0, "data": 1.5, "algorithms": 1.0, "security": 1.0},
        "data": {"data": 2.0, "algorithms": 1.5, "backend": 1.0},
        "cloud": {"backend": 1.5, "security": 1.5, "data": 1.0},
        "mobile": {"frontend": 1.5, "backend": 1.0, "algorithms": 1.0},
        "security": {"security": 2.0, "backend": 1.0, "algorithms": 0.5},
        "fullstack": {"frontend": 1.5, "backend": 1.5, "algorithms": 1.0, "data": 1.0, "security": 0.5}
    }
    
    weights = category_weights.get(career_path, category_weights["fullstack"])
    category_score = weights.get(question["category"], 0.3)
    score += category_score
    
    if target_difficulty:
        difficulty_map = {"easy": 1, "medium": 2, "hard": 3}
        target_val = difficulty_map.get(target_difficulty, 2)
        question_val = difficulty_map.get(question["difficulty"], 2)
        diff_match = 1.0 - abs(target_val - question_val) * 0.3
        score += diff_match
    
    if models:
        try:
            if "question_classifier" in models and "question_vectorizer" in models:
                vectorizer = models["question_vectorizer"]
                classifier = models["question_classifier"]
                text_vector = vectorizer.transform([question["question"]])
                prediction_proba = classifier.predict_proba(text_vector)
                max_confidence = np.max(prediction_proba)
                score += max_confidence * 0.5
        except Exception:
            pass
    
    score += random.uniform(0, 0.3)
    
    return score

def generate_quiz(data):
    """Generate quiz questions using ML-guided selection with randomization"""
    models = load_models()
    
    category = data.get("category", "mixed")
    difficulty = data.get("difficulty", "medium")
    career_path = data.get("career_path", "fullstack")
    count = data.get("count", 5)
    level = data.get("level", 10)
    
    level_to_difficulty = {
        (1, 5): "easy",
        (6, 12): "medium" if difficulty == "medium" else difficulty,
        (13, 19): "medium",
        (20, 30): "hard"
    }
    
    if difficulty == "medium":
        for range_tuple, diff in level_to_difficulty.items():
            if range_tuple[0] <= level <= range_tuple[1]:
                difficulty = diff
                break
    
    # Filter available questions
    available_questions = []
    for q in QUESTION_BANK:
        if category != "mixed" and q["category"] != category:
            continue
        available_questions.append(q)
    
    if not available_questions:
        available_questions = QUESTION_BANK.copy()
    
    # Score and sort questions
    scored_questions = []
    for q in available_questions:
        score = calculate_question_score(q, level, career_path, difficulty)
        scored_questions.append((score, q))
    
    scored_questions.sort(key=lambda x: x[0], reverse=True)
    
    # Add strong randomization to prevent repetition
    # Take top 50% of scored questions and shuffle them
    top_half_count = max(count * 3, len(scored_questions) // 2)
    top_candidates = [q for score, q in scored_questions[:top_half_count]]
    random.shuffle(top_candidates)
    
    selected_questions = []
    used_questions = set()  # Track by question text to avoid exact duplicates
    used_categories = set()
    
    if category == "mixed":
        # Ensure diversity across categories
        for q in top_candidates:
            if len(selected_questions) >= count:
                break
            
            # Skip if we've seen this exact question
            if q["question"] in used_questions:
                continue
            
            cat = q["category"]
            if cat not in used_categories or len(used_categories) >= 4:
                selected_questions.append(q)
                used_questions.add(q["question"])
                used_categories.add(cat)
        
        # Fill remaining slots
        while len(selected_questions) < count:
            for q in top_candidates:
                if q["question"] not in used_questions:
                    selected_questions.append(q)
                    used_questions.add(q["question"])
                    if len(selected_questions) >= count:
                        break
            break
    else:
        # Single category: select from shuffled top candidates
        for q in top_candidates[:count * 2]:  # Take 2x to ensure variety
            if len(selected_questions) >= count:
                break
            if q["question"] not in used_questions:
                selected_questions.append(q)
                used_questions.add(q["question"])
    
    # Final shuffle
    random.shuffle(selected_questions)
    
    result = []
    for q in selected_questions[:count]:
        result.append({
            "question": q["question"],
            "options": q["options"],
            "correctAnswer": q["correctAnswer"],
            "category": q["category"],
            "difficulty": q["difficulty"],
            "explanation": q["explanation"]
        })
    
    print(f"[ML Quiz] Generated {len(result)} unique questions from {len(available_questions)} available", file=sys.stderr)
    
    return result

def suggest_study(data):
    """Generate study suggestions using ML model"""
    models = load_models()
    
    level = data.get("level", 1)
    career_path = data.get("career_path", "fullstack")
    frontend_score = data.get("frontend_score", 0.5)
    backend_score = data.get("backend_score", 0.5)
    data_score = data.get("data_score", 0.5)
    algo_score = data.get("algo_score", 0.5)
    security_score = data.get("security_score", 0.5)
    weak_categories = data.get("weak_categories", [])
    
    suggestions = []
    
    scores = {
        "frontend": frontend_score,
        "backend": backend_score,
        "data": data_score,
        "algorithms": algo_score,
        "security": security_score
    }
    
    career_focus = {
        "frontend": ["frontend", "algorithms"],
        "backend": ["backend", "data", "algorithms"],
        "data": ["data", "algorithms", "backend"],
        "cloud": ["backend", "security"],
        "mobile": ["frontend", "backend"],
        "security": ["security", "backend"],
        "fullstack": ["frontend", "backend", "algorithms"]
    }
    
    focus_areas = career_focus.get(career_path, ["algorithms"])
    
    if models and "study_suggester" in models:
        try:
            suggester = models["study_suggester"]
            features = np.array([[level, frontend_score, backend_score, data_score, algo_score, security_score]])
            predictions = suggester.predict(features)
        except Exception:
            pass
    
    sorted_scores = sorted(scores.items(), key=lambda x: x[1])
    
    for category, score in sorted_scores[:3]:
        priority = "high" if score < 0.4 else ("medium" if score < 0.6 else "low")
        
        if category in focus_areas or category in weak_categories:
            priority = "high" if priority != "high" else priority
        
        topic_map = {
            "frontend": "Career Path Development",
            "backend": "Backend Architecture",
            "data": "Data Structures & Analysis",
            "algorithms": "Algorithm Optimization",
            "security": "Security Best Practices"
        }
        
        reason_map = {
            "frontend": f"Your frontend skills ({int(score*100)}%) could use improvement for your {career_path} career path.",
            "backend": f"Backend knowledge ({int(score*100)}%) is essential for building robust applications.",
            "data": f"Data skills ({int(score*100)}%) will help you make better data-driven decisions.",
            "algorithms": f"Algorithm understanding ({int(score*100)}%) is fundamental for technical interviews.",
            "security": f"Security knowledge ({int(score*100)}%) is critical for building safe applications."
        }
        
        action_map = {
            "frontend": "Practice React components and CSS layouts",
            "backend": "Build a REST API with Node.js and Express",
            "data": "Work through SQL exercises and data modeling",
            "algorithms": "Solve algorithm challenges on practice platforms",
            "security": "Learn about OWASP top 10 vulnerabilities"
        }
        
        suggestions.append({
            "topic": topic_map.get(category, category.title()),
            "reason": reason_map.get(category, f"Improve your {category} skills."),
            "recommendedAction": action_map.get(category, f"Practice {category} exercises."),
            "priority": priority
        })
    
    suggestions.sort(key=lambda x: {"high": 0, "medium": 1, "low": 2}[x["priority"]])
    
    return suggestions

def recommend_career(data):
    """Recommend career path using ML model"""
    models = load_models()
    
    visual_design = data.get("visual_design", 3)
    backend_pref = data.get("backend_pref", 3)
    math_stats = data.get("math_stats", 3)
    web_apps = data.get("web_apps", 3)
    data_interest = data.get("data_interest", 3)
    cloud_interest = data.get("cloud_interest", 3)
    mobile_interest = data.get("mobile_interest", 3)
    security_interest = data.get("security_interest", 3)
    frontend_perf = data.get("frontend_perf", 0.5)
    backend_perf = data.get("backend_perf", 0.5)
    data_perf = data.get("data_perf", 0.5)
    algo_perf = data.get("algo_perf", 0.5)
    
    # ML gradient-boosting recommender removed; fallback heuristic used below.
    
    career_scores = {
        "frontend": (visual_design * 0.4) + (web_apps * 0.3) + (frontend_perf * 5 * 0.3),
        "backend": (backend_pref * 0.4) + (web_apps * 0.2) + (backend_perf * 5 * 0.4),
        "data": (math_stats * 0.4) + (data_interest * 0.3) + (data_perf * 5 * 0.3),
        "cloud": (backend_pref * 0.3) + (cloud_interest * 0.4) + (backend_perf * 5 * 0.3),
        "mobile": (visual_design * 0.3) + (mobile_interest * 0.4) + (frontend_perf * 5 * 0.3),
        "security": (security_interest * 0.5) + (backend_perf * 5 * 0.3) + (algo_perf * 5 * 0.2),
    }
    
    fullstack_score = (career_scores["frontend"] + career_scores["backend"]) / 2
    career_scores["fullstack"] = fullstack_score
    
    total_score = sum(career_scores.values())
    probabilities = {k: v / total_score for k, v in career_scores.items()}
    
    recommended = max(career_scores.items(), key=lambda x: x[1])
    recommended_path = recommended[0]
    
    max_prob = max(probabilities.values())
    confidence = min(0.95, max_prob * 1.5)
    
    return {
        "recommended_path": recommended_path,
        "probabilities": probabilities,
        "confidence": confidence
    }

def classify_question(data):
    """Classify a question into a category using ML model"""
    models = load_models()
    text = data.get("text", "")
    
    if models and "question_classifier" in models and "question_vectorizer" in models:
        try:
            vectorizer = models["question_vectorizer"]
            classifier = models["question_classifier"]
            label_encoder = models.get("question_label_encoder")
            
            text_vector = vectorizer.transform([text])
            prediction = classifier.predict(text_vector)[0]
            prediction_proba = classifier.predict_proba(text_vector)[0]
            confidence = float(np.max(prediction_proba))
            
            if label_encoder:
                category = label_encoder.inverse_transform([prediction])[0]
            else:
                category = str(prediction)
            
            return {
                "category": category,
                "confidence": min(0.95, confidence)
            }
        except Exception as e:
            print(f"ML classification failed: {e}", file=sys.stderr)
    
    keywords = {
        "frontend": ["css", "html", "react", "vue", "angular", "dom", "ui", "ux", "style", "component", "javascript", "browser"],
        "backend": ["api", "server", "database", "rest", "http", "node", "express", "endpoint", "request", "response", "middleware"],
        "data": ["sql", "query", "table", "schema", "index", "normalization", "data", "analytics", "pandas", "numpy"],
        "algorithms": ["complexity", "sort", "search", "tree", "graph", "recursion", "dynamic", "hash", "array", "linked"],
        "security": ["xss", "sql injection", "csrf", "authentication", "authorization", "encryption", "https", "cors", "vulnerability"]
    }
    
    text_lower = text.lower()
    
    scores = {}
    for category, words in keywords.items():
        score = sum(1 for word in words if word in text_lower)
        scores[category] = score
    
    if max(scores.values()) == 0:
        return {"category": "algorithms", "confidence": 0.3}
    
    best_category = max(scores.items(), key=lambda x: x[1])
    total = sum(scores.values())
    confidence = best_category[1] / total if total > 0 else 0.3
    
    return {
        "category": best_category[0],
        "confidence": min(0.95, confidence)
    }

def health_check(data):
    """Check if the ML service is healthy"""
    models = load_models()
    model_count = len(models)
    
    ml_models_available = []
    if "question_classifier" in models:
        ml_models_available.append("question_classifier")
    if "difficulty_predictor" in models:
        ml_models_available.append("difficulty_predictor")
    if "random_forest" in models:
        ml_models_available.append("random_forest")
    if "study_suggester" in models:
        ml_models_available.append("study_suggester")
    
    return {
        "status": "healthy",
        "models_loaded": model_count,
        "ml_models_available": ml_models_available,
        "question_bank_size": len(QUESTION_BANK),
        "using_ml": len(ml_models_available) > 0
    }

def main():
    if len(sys.argv) < 2:
        print(json.dumps({"success": False, "error": "No command provided"}))
        sys.exit(1)
    
    try:
        request = json.loads(sys.argv[1])
        command = request.get("command")
        data = request.get("data", {})
        
        handlers = {
            "generate_quiz": generate_quiz,
            "suggest_study": suggest_study,
            "recommend_career": recommend_career,
            "classify_question": classify_question,
            "health_check": health_check
        }
        
        if command not in handlers:
            error_msg = f"Unknown command: {command}"
            log_prediction_request(command, data, error=error_msg)
            print(json.dumps({"success": False, "error": error_msg}))
            sys.exit(1)
        
        # Execute handler
        result = handlers[command](data)
        
        # Log the request with result
        log_prediction_request(command, data, result=result)
        
        print(json.dumps({"success": True, "result": result}))
        
    except json.JSONDecodeError as e:
        error_msg = f"Invalid JSON: {str(e)}"
        log_prediction_request("unknown", {}, error=error_msg)
        print(json.dumps({"success": False, "error": error_msg}))
        sys.exit(1)
    except Exception as e:
        error_msg = str(e)
        log_prediction_request(request.get("command", "unknown") if 'request' in locals() else "unknown", 
                             request.get("data", {}) if 'request' in locals() else {}, 
                             error=error_msg)
        print(json.dumps({"success": False, "error": error_msg}))
        sys.exit(1)

if __name__ == "__main__":
    main()
