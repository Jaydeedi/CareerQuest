import { storage } from "./storage-firestore";
import type { Question, QuestionAttempt } from "@shared/schema";
import * as fs from "fs";
import * as path from "path";

export interface GeneratedQuestion {
  question: string;
  options: string[];
  correctAnswer: number;
  category: string;
  difficulty: "easy" | "medium" | "hard";
  explanation: string;
}

export interface StudySuggestion {
  topic: string;
  reason: string;
  recommendedAction: string;
  priority: "high" | "medium" | "low";
}

interface CategoryPerformance {
  [category: string]: {
    correct: number;
    total: number;
    accuracy: number;
  };
}

interface QuestionBank {
  questions: GeneratedQuestion[];
  categories: Set<string>;
  difficulties: Set<string>;
}

// Load questions from ML model dataset (500+ questions)
function loadQuestionsFromMLDataset(): GeneratedQuestion[] {
  try {
    const datasetPath = path.join(process.cwd(), "ml_model", "saved_models", "question_templates.json");
    if (fs.existsSync(datasetPath)) {
      const data = JSON.parse(fs.readFileSync(datasetPath, "utf-8"));
      if (data.questions && Array.isArray(data.questions)) {
        console.log(`[Learning Intelligence] Loaded ${data.questions.length} questions from ML dataset`);
        return data.questions.map((q: any) => ({
          question: q.question,
          options: q.options || [],
          correctAnswer: q.correct_answer || q.correctAnswer || 0,
          category: q.category || "algorithms",
          difficulty: q.difficulty || "medium",
          explanation: q.explanation || "No explanation provided"
        }));
      }
    }
  } catch (error) {
    console.warn("[Learning Intelligence] Could not load ML dataset, using fallback questions:", error);
  }
  
  // Fallback to hardcoded questions if ML dataset not available
  return FALLBACK_QUESTIONS;
}

const FALLBACK_QUESTIONS: GeneratedQuestion[] = [
  // Frontend Questions
  {
    question: "What does the 'virtual DOM' in React primarily help with?",
    options: ["Database connections", "Performance optimization through efficient updates", "Server-side rendering", "CSS styling"],
    correctAnswer: 1,
    category: "frontend",
    difficulty: "easy",
    explanation: "The virtual DOM is a lightweight copy of the actual DOM that React uses to optimize rendering by only updating what has changed."
  },
  {
    question: "Which CSS property is used to create a flexible container in Flexbox?",
    options: ["display: block", "display: flex", "display: grid", "display: inline"],
    correctAnswer: 1,
    category: "frontend",
    difficulty: "easy",
    explanation: "The 'display: flex' property creates a flex container and enables flexbox layout for its children."
  },
  {
    question: "What is the purpose of the useEffect hook in React?",
    options: ["Managing component state", "Handling side effects like API calls", "Rendering JSX elements", "Styling components"],
    correctAnswer: 1,
    category: "frontend",
    difficulty: "medium",
    explanation: "useEffect is used to perform side effects in functional components, such as data fetching, subscriptions, or DOM manipulation."
  },
  {
    question: "What is the purpose of the 'key' prop when rendering lists in React?",
    options: ["To style list items", "To help React identify which items have changed", "To sort the list", "To filter the list"],
    correctAnswer: 1,
    category: "frontend",
    difficulty: "medium",
    explanation: "The key prop helps React identify which items have changed, been added, or removed, enabling efficient updates."
  },
  {
    question: "What is tree shaking in modern JavaScript bundlers?",
    options: ["A debugging technique", "Removing unused code from the bundle", "Organizing folder structure", "Caching dependencies"],
    correctAnswer: 1,
    category: "frontend",
    difficulty: "hard",
    explanation: "Tree shaking is a dead code elimination technique that removes unused exports from the final bundle to reduce size."
  },
  // Backend Questions
  {
    question: "What does REST stand for?",
    options: ["Remote Execution Standard Transfer", "Representational State Transfer", "Request-Response System Transfer", "Reliable Server Technology"],
    correctAnswer: 1,
    category: "backend",
    difficulty: "easy",
    explanation: "REST stands for Representational State Transfer, an architectural style for designing networked applications."
  },
  {
    question: "What is middleware in Express.js?",
    options: ["A database connector", "Functions that have access to request and response objects", "A templating engine", "A testing framework"],
    correctAnswer: 1,
    category: "backend",
    difficulty: "medium",
    explanation: "Middleware functions in Express.js have access to the request and response objects and can modify them or end the request-response cycle."
  },
  {
    question: "What is the difference between SQL and NoSQL databases?",
    options: ["SQL is faster than NoSQL", "SQL uses structured schemas while NoSQL is schema-flexible", "NoSQL cannot handle transactions", "SQL databases are always cloud-based"],
    correctAnswer: 1,
    category: "backend",
    difficulty: "medium",
    explanation: "SQL databases use structured schemas with tables and relationships, while NoSQL databases offer flexible schemas for various data models."
  },
  {
    question: "What is connection pooling in database management?",
    options: ["Storing passwords securely", "Reusing database connections to reduce overhead", "Backing up data regularly", "Distributing data across servers"],
    correctAnswer: 1,
    category: "backend",
    difficulty: "hard",
    explanation: "Connection pooling maintains a cache of database connections that can be reused, reducing the overhead of creating new connections."
  },
  {
    question: "What is the N+1 query problem in ORMs?",
    options: ["A security vulnerability", "Inefficient queries that fetch related data one at a time", "A database sizing issue", "A network latency problem"],
    correctAnswer: 1,
    category: "backend",
    difficulty: "hard",
    explanation: "The N+1 problem occurs when code fetches a collection and then makes individual queries for each item's relationships, leading to poor performance."
  },
  // Data Science Questions
  {
    question: "What is the purpose of data normalization?",
    options: ["To increase data size", "To scale features to a common range", "To remove all data", "To add random noise"],
    correctAnswer: 1,
    category: "data",
    difficulty: "easy",
    explanation: "Data normalization scales features to a common range (often 0-1) to ensure equal contribution to machine learning algorithms."
  },
  {
    question: "What is overfitting in machine learning?",
    options: ["When a model is too simple", "When a model memorizes training data but fails on new data", "When data is missing", "When training takes too long"],
    correctAnswer: 1,
    category: "data",
    difficulty: "medium",
    explanation: "Overfitting occurs when a model learns the training data too well, including noise, and performs poorly on unseen data."
  },
  {
    question: "What is the purpose of cross-validation?",
    options: ["To speed up training", "To reliably estimate model performance on unseen data", "To reduce model size", "To visualize data"],
    correctAnswer: 1,
    category: "data",
    difficulty: "medium",
    explanation: "Cross-validation splits data into multiple folds to evaluate model performance more reliably than a single train-test split."
  },
  {
    question: "What is the bias-variance tradeoff?",
    options: ["Choosing between speed and accuracy", "Balancing underfitting and overfitting", "Selecting hardware resources", "Managing data storage"],
    correctAnswer: 1,
    category: "data",
    difficulty: "hard",
    explanation: "The bias-variance tradeoff describes the balance between underfitting (high bias) and overfitting (high variance) in model complexity."
  },
  // Security Questions
  {
    question: "What is SQL injection?",
    options: ["A database optimization technique", "An attack that inserts malicious SQL code", "A way to speed up queries", "A backup method"],
    correctAnswer: 1,
    category: "security",
    difficulty: "easy",
    explanation: "SQL injection is an attack where malicious SQL code is inserted into application queries to manipulate the database."
  },
  {
    question: "What is XSS (Cross-Site Scripting)?",
    options: ["A CSS framework", "An attack that injects malicious scripts into web pages", "A browser feature", "A server configuration"],
    correctAnswer: 1,
    category: "security",
    difficulty: "medium",
    explanation: "XSS attacks inject malicious scripts into trusted websites to execute in victims' browsers and steal data."
  },
  {
    question: "What is HTTPS and why is it important?",
    options: ["A faster version of HTTP", "HTTP with encryption for secure data transmission", "A new web protocol replacing HTTP", "A compression algorithm"],
    correctAnswer: 1,
    category: "security",
    difficulty: "easy",
    explanation: "HTTPS encrypts data between browser and server using TLS/SSL, protecting sensitive information from interception."
  },
  {
    question: "What is CSRF (Cross-Site Request Forgery)?",
    options: ["A code review process", "An attack that tricks users into performing unwanted actions", "A testing methodology", "A deployment strategy"],
    correctAnswer: 1,
    category: "security",
    difficulty: "hard",
    explanation: "CSRF attacks trick authenticated users into submitting unwanted requests, exploiting the trust a site has in the user's browser."
  },
  // Algorithms Questions
  {
    question: "What is the time complexity of binary search?",
    options: ["O(n)", "O(log n)", "O(n²)", "O(1)"],
    correctAnswer: 1,
    category: "algorithms",
    difficulty: "easy",
    explanation: "Binary search has O(log n) time complexity because it halves the search space with each comparison."
  },
  {
    question: "What data structure uses LIFO (Last In, First Out)?",
    options: ["Queue", "Stack", "Array", "Linked List"],
    correctAnswer: 1,
    category: "algorithms",
    difficulty: "easy",
    explanation: "A stack uses LIFO ordering where the last element added is the first one removed."
  },
  {
    question: "What is the difference between BFS and DFS?",
    options: ["BFS uses a stack, DFS uses a queue", "BFS explores level by level, DFS goes deep first", "They are identical", "BFS is faster than DFS"],
    correctAnswer: 1,
    category: "algorithms",
    difficulty: "medium",
    explanation: "BFS explores nodes level by level using a queue, while DFS explores as deep as possible first using a stack or recursion."
  },
  {
    question: "What is dynamic programming used for?",
    options: ["Runtime code changes", "Solving problems by breaking them into overlapping subproblems", "Memory allocation", "Multi-threading"],
    correctAnswer: 1,
    category: "algorithms",
    difficulty: "hard",
    explanation: "Dynamic programming solves complex problems by breaking them into simpler overlapping subproblems and storing results to avoid recomputation."
  },
  // Cloud Questions
  {
    question: "What is containerization in cloud computing?",
    options: ["Physical server isolation", "Packaging applications with their dependencies", "Network segmentation", "Data compression"],
    correctAnswer: 1,
    category: "cloud",
    difficulty: "medium",
    explanation: "Containerization packages an application with all its dependencies into a container for consistent deployment across environments."
  },
  {
    question: "What is the difference between IaaS, PaaS, and SaaS?",
    options: ["They are different programming languages", "They represent different levels of cloud service abstraction", "They are security protocols", "They are database types"],
    correctAnswer: 1,
    category: "cloud",
    difficulty: "medium",
    explanation: "IaaS provides infrastructure, PaaS provides platform for development, and SaaS provides complete software applications."
  },
  {
    question: "What is horizontal scaling vs vertical scaling?",
    options: ["Horizontal means adding color, vertical means adding size", "Horizontal adds more machines, vertical adds more power to existing machines", "They are the same thing", "Horizontal is cheaper than vertical"],
    correctAnswer: 1,
    category: "cloud",
    difficulty: "medium",
    explanation: "Horizontal scaling adds more machines to handle load, while vertical scaling increases the power of existing machines."
  },
  // Mobile Questions
  {
    question: "What is the purpose of React Native?",
    options: ["Server-side rendering", "Building cross-platform mobile apps with JavaScript", "Database management", "Web scraping"],
    correctAnswer: 1,
    category: "mobile",
    difficulty: "easy",
    explanation: "React Native allows developers to build native mobile apps for iOS and Android using JavaScript and React."
  },
  {
    question: "What is the difference between native and hybrid mobile apps?",
    options: ["Native apps are always faster", "Native apps are built for specific platforms, hybrid use web technologies", "Hybrid apps are more expensive", "There is no difference"],
    correctAnswer: 1,
    category: "mobile",
    difficulty: "medium",
    explanation: "Native apps are built specifically for iOS or Android platforms, while hybrid apps use web technologies wrapped in a native container."
  }
];

class LearningIntelligenceService {
  private questionBank: QuestionBank;

  constructor() {
    // Load questions from ML dataset (500+) instead of hardcoded bank (40)
    const allQuestions = loadQuestionsFromMLDataset();
    
    this.questionBank = {
      questions: allQuestions,
      categories: new Set(allQuestions.map(q => q.category)),
      difficulties: new Set(allQuestions.map(q => q.difficulty))
    };
    
    console.log(`[Learning Intelligence] Initialized with ${allQuestions.length} questions across ${this.questionBank.categories.size} categories`);
  }

  private shuffleArray<T>(array: T[]): T[] {
    const shuffled = [...array];
    for (let i = shuffled.length - 1; i > 0; i--) {
      const j = Math.floor(Math.random() * (i + 1));
      [shuffled[i], shuffled[j]] = [shuffled[j], shuffled[i]];
    }
    return shuffled;
  }

  private calculateCategoryPerformance(attempts: QuestionAttempt[]): CategoryPerformance {
    const performance: CategoryPerformance = {};

    for (const attempt of attempts) {
      if (!attempt.category) continue;

      if (!performance[attempt.category]) {
        performance[attempt.category] = { correct: 0, total: 0, accuracy: 0 };
      }

      performance[attempt.category].total++;
      if (attempt.isCorrect) {
        performance[attempt.category].correct++;
      }
    }

    for (const category of Object.keys(performance)) {
      const cat = performance[category];
      cat.accuracy = cat.total > 0 ? cat.correct / cat.total : 0;
    }

    return performance;
  }

  private selectDifficultyForLevel(level: number): "easy" | "medium" | "hard" {
    if (level <= 3) return "easy";
    if (level <= 7) return "medium";
    return "hard";
  }

  private mapDifficultyLabel(difficulty?: string): "easy" | "medium" | "hard" {
    if (difficulty === "beginner") return "easy";
    if (difficulty === "intermediate") return "medium";
    if (difficulty === "advanced") return "hard";
    return "medium";
  }

  async selectQuestionsFromSyllabus(
    syllabusText: string,
    careerPath: string,
    count: number = 10
  ): Promise<GeneratedQuestion[]> {
    const keywords = syllabusText.toLowerCase().split(/\s+/);
    
    const categoryMapping: Record<string, string[]> = {
      frontend: ["react", "css", "html", "javascript", "ui", "ux", "dom", "component", "vue", "angular"],
      backend: ["server", "api", "database", "express", "node", "rest", "graphql", "sql", "mongodb"],
      data: ["data", "machine", "learning", "analytics", "statistics", "python", "pandas", "numpy", "model"],
      security: ["security", "authentication", "encryption", "csrf", "xss", "sql injection", "https", "oauth"],
      algorithms: ["algorithm", "data structure", "complexity", "sort", "search", "tree", "graph", "recursion"],
      cloud: ["cloud", "aws", "azure", "docker", "kubernetes", "container", "serverless", "devops"],
      mobile: ["mobile", "ios", "android", "react native", "flutter", "app", "swift", "kotlin"]
    };

    const matchedCategories = new Set<string>();
    
    for (const [category, categoryKeywords] of Object.entries(categoryMapping)) {
      for (const keyword of keywords) {
        if (categoryKeywords.some(ck => keyword.includes(ck))) {
          matchedCategories.add(category);
          break;
        }
      }
    }

    if (matchedCategories.size === 0) {
      matchedCategories.add("algorithms");
      matchedCategories.add("backend");
    }

    let candidateQuestions = this.questionBank.questions.filter(
      q => matchedCategories.has(q.category)
    );

    if (candidateQuestions.length < count) {
      candidateQuestions = [...this.questionBank.questions];
    }

    const selectedQuestions: GeneratedQuestion[] = [];
    const usedCategories = new Set<string>();
    const shuffled = this.shuffleArray(candidateQuestions);

    for (const difficulty of ["easy", "medium", "hard"] as const) {
      const diffQuestions = shuffled.filter(q => q.difficulty === difficulty);
      const targetCount = Math.ceil(count / 3);
      
      for (const q of diffQuestions) {
        if (selectedQuestions.length >= count) break;
        if (!selectedQuestions.some(sq => sq.question === q.question)) {
          selectedQuestions.push(q);
          usedCategories.add(q.category);
        }
        if (selectedQuestions.filter(sq => sq.difficulty === difficulty).length >= targetCount) break;
      }
    }

    while (selectedQuestions.length < count && shuffled.length > selectedQuestions.length) {
      const remaining = shuffled.filter(q => !selectedQuestions.some(sq => sq.question === q.question));
      if (remaining.length === 0) break;
      selectedQuestions.push(remaining[0]);
    }

    return this.shuffleArray(selectedQuestions.slice(0, count));
  }

  async generatePracticeQuiz(
    careerPath: string,
    userLevel: number,
    topic?: string,
    difficulty?: "beginner" | "intermediate" | "advanced",
    userId?: string
  ): Promise<{ questions: GeneratedQuestion[]; usedTrainedModel: boolean }> {
    const targetDifficulty = difficulty 
      ? this.mapDifficultyLabel(difficulty) 
      : this.selectDifficultyForLevel(userLevel);

    let userPerformance: CategoryPerformance = {};
    if (userId) {
      try {
        const attempts = await storage.getUserQuestionAttemptsByCategory(userId);
        userPerformance = this.calculateCategoryPerformance(attempts);
      } catch (err) {
        console.warn("Could not fetch user performance for adaptive selection:", err);
      }
    }

    const careerPathCategories: Record<string, string[]> = {
      fullstack: ["frontend", "backend", "algorithms"],
      datascience: ["data", "algorithms", "backend"],
      cloud: ["cloud", "backend", "security"],
      mobile: ["mobile", "frontend", "algorithms"],
      security: ["security", "backend", "algorithms"],
      default: ["algorithms", "backend", "frontend"]
    };

    const relevantCategories = careerPathCategories[careerPath.toLowerCase()] || careerPathCategories.default;

    // Try to use ML model first
    try {
      const { callMLService } = await import("./ml-client");
      
      console.log(`[AI Quiz] Requesting from trained ML model (Level ${userLevel}, ${careerPath}, ${targetDifficulty})`);
      
      const mlResponse = await callMLService<GeneratedQuestion[]>("generate_quiz", {
        category: topic || "mixed",
        difficulty: targetDifficulty,
        career_path: careerPath,
        count: 5,
        level: userLevel
      });

      if (mlResponse.success && mlResponse.result && mlResponse.result.length >= 5) {
        console.log(`[AI Quiz] ✅ Trained model generated ${mlResponse.result.length} questions successfully`);
        return { questions: this.shuffleArray(mlResponse.result), usedTrainedModel: true };
      } else {
        console.warn(`\n⚠️ ML model returned insufficient questions (${mlResponse.result?.length || 0}), falling back to template selection\n`);
      }
    } catch (mlError) {
      console.error("\n❌ ML generation failed:", mlError, "\n");
    }

    // Fallback to template-based selection with scoring
    console.log(`[Practice Quiz] Using template-based selection (fallback)`);
    
    let candidates = this.questionBank.questions.filter(q => {
      const matchesTopic = !topic || q.category.toLowerCase().includes(topic.toLowerCase()) || 
                          q.question.toLowerCase().includes(topic.toLowerCase());
      const matchesCategory = relevantCategories.includes(q.category);
      return matchesTopic || matchesCategory;
    });

    if (candidates.length < 5) {
      candidates = [...this.questionBank.questions];
    }

    const scored = candidates.map(q => {
      let score = Math.random() * 10;

      if (q.difficulty === targetDifficulty) {
        score += 20;
      } else if (
        (q.difficulty === "easy" && targetDifficulty === "medium") ||
        (q.difficulty === "medium" && targetDifficulty === "hard")
      ) {
        score += 10;
      }

      if (relevantCategories.includes(q.category)) {
        score += 15;
      }

      const catPerf = userPerformance[q.category];
      if (catPerf && catPerf.accuracy < 0.6) {
        score += 10;
      }

      return { question: q, score };
    });

    scored.sort((a, b) => b.score - a.score);

    const selected: GeneratedQuestion[] = [];
    const usedCategories = new Set<string>();

    for (const { question } of scored) {
      if (selected.length >= 5) break;
      
      if (selected.length < 3 || !usedCategories.has(question.category)) {
        selected.push(question);
        usedCategories.add(question.category);
      }
    }

    while (selected.length < 5 && scored.length > selected.length) {
      const remaining = scored.filter(s => !selected.includes(s.question));
      if (remaining.length === 0) break;
      selected.push(remaining[0].question);
    }

    return { questions: this.shuffleArray(selected), usedTrainedModel: false };
  }

  async generateStudySuggestions(
    userPerformance: {
      level: number;
      totalXp: number;
      careerPath: string | null;
      recentQuizScores: Array<{ category: string; score: number; total: number }>;
      weakCategories: string[];
    }
  ): Promise<StudySuggestion[]> {
    const suggestions: StudySuggestion[] = [];

    const categoryDescriptions: Record<string, { topic: string; action: string }> = {
      frontend: { 
        topic: "Frontend Development", 
        action: "Practice React hooks, CSS layouts, and DOM manipulation" 
      },
      backend: { 
        topic: "Backend Development", 
        action: "Study REST APIs, database queries, and server architecture" 
      },
      data: { 
        topic: "Data Science Fundamentals", 
        action: "Review statistics, data preprocessing, and ML algorithms" 
      },
      security: { 
        topic: "Cybersecurity Basics", 
        action: "Learn about XSS, SQL injection prevention, and HTTPS" 
      },
      algorithms: { 
        topic: "Algorithms & Data Structures", 
        action: "Practice sorting algorithms, trees, and complexity analysis" 
      },
      cloud: { 
        topic: "Cloud Infrastructure", 
        action: "Study containerization, cloud services, and deployment" 
      },
      mobile: { 
        topic: "Mobile Development", 
        action: "Practice React Native or native development concepts" 
      }
    };

    for (let i = 0; i < Math.min(userPerformance.weakCategories.length, 2); i++) {
      const weakCat = userPerformance.weakCategories[i];
      const catInfo = categoryDescriptions[weakCat] || { 
        topic: weakCat, 
        action: `Focus on improving your ${weakCat} skills` 
      };

      const weakScore = userPerformance.recentQuizScores.find(s => s.category === weakCat);
      const accuracy = weakScore ? Math.round((weakScore.score / weakScore.total) * 100) : 0;

      suggestions.push({
        topic: catInfo.topic,
        reason: `Your accuracy in ${weakCat} is ${accuracy}%. This is below average and improving here will boost your overall performance.`,
        recommendedAction: catInfo.action,
        priority: accuracy < 40 ? "high" : "medium"
      });
    }

    if (userPerformance.level < 5) {
      suggestions.push({
        topic: "Complete Daily Challenges",
        reason: "Regular practice helps build consistency and earn XP faster",
        recommendedAction: "Complete both quiz and code challenges daily to maintain your streak",
        priority: "high"
      });
    }

    if (userPerformance.careerPath) {
      suggestions.push({
        topic: "Career Path Progress",
        reason: `Continue advancing on your ${userPerformance.careerPath} career path`,
        recommendedAction: "Complete the next module in your career path to unlock new content",
        priority: "medium"
      });
    }

    if (userPerformance.level >= 5) {
      suggestions.push({
        topic: "Practice Code Challenges",
        reason: "At your level, hands-on coding practice is essential for growth",
        recommendedAction: "Attempt more difficult code challenges to improve problem-solving skills",
        priority: "medium"
      });
    }

    if (!userPerformance.careerPath) {
      suggestions.push({
        topic: "Complete Interest Assessment",
        reason: "A career path helps focus your learning and provides personalized content",
        recommendedAction: "Complete the interest questionnaire to get AI-powered career recommendations",
        priority: "high"
      });
    }

    if (suggestions.length < 3) {
      suggestions.push({
        topic: "Explore New Topics",
        reason: "Broadening your knowledge makes you a more versatile developer",
        recommendedAction: "Try a quiz or module in a category you haven't explored yet",
        priority: "low"
      });
    }

    return suggestions.slice(0, 5);
  }

  addQuestionsToBank(questions: GeneratedQuestion[]): void {
    for (const q of questions) {
      if (!this.questionBank.questions.some(existing => existing.question === q.question)) {
        this.questionBank.questions.push(q);
        this.questionBank.categories.add(q.category);
        this.questionBank.difficulties.add(q.difficulty);
      }
    }
  }

  getQuestionBankStats(): { totalQuestions: number; categories: string[]; difficulties: string[] } {
    return {
      totalQuestions: this.questionBank.questions.length,
      categories: Array.from(this.questionBank.categories),
      difficulties: Array.from(this.questionBank.difficulties)
    };
  }
}

export const learningIntelligence = new LearningIntelligenceService();

export async function generateQuestionsFromText(
  syllabusText: string,
  careerPath: string,
  count: number = 10
): Promise<GeneratedQuestion[]> {
  return learningIntelligence.selectQuestionsFromSyllabus(syllabusText, careerPath, count);
}

export async function generatePracticeQuiz(
  careerPath: string,
  userLevel: number,
  topic?: string,
  difficulty?: "beginner" | "intermediate" | "advanced",
  userId?: string
): Promise<GeneratedQuestion[]> {
  return learningIntelligence.generatePracticeQuiz(careerPath, userLevel, topic, difficulty, userId);
}

export async function generateStudySuggestions(
  userPerformance: {
    level: number;
    totalXp: number;
    careerPath: string | null;
    recentQuizScores: Array<{ category: string; score: number; total: number }>;
    weakCategories: string[];
  }
): Promise<StudySuggestion[]> {
  return learningIntelligence.generateStudySuggestions(userPerformance);
}
