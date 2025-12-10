
import { HfInference } from "@huggingface/inference";

// Initialize Hugging Face client
const hf = new HfInference(process.env.HUGGINGFACE_API_KEY);

/**
 * Classify text using Hugging Face zero-shot classification
 */
export async function classifyQuestionHF(questionText: string): Promise<{
  category: string;
  confidence: number;
}> {
  try {
    const result = await hf.zeroShotClassification({
      model: "facebook/bart-large-mnli",
      inputs: questionText,
      parameters: {
        candidate_labels: ["frontend", "backend", "data science", "algorithms", "security", "cloud"],
      },
    });

    return {
      category: result.labels[0],
      confidence: result.scores[0],
    };
  } catch (error) {
    console.error("HF classification error:", error);
    throw error;
  }
}

/**
 * Generate study suggestions using Hugging Face text generation
 */
export async function generateStudySuggestionsHF(userContext: string): Promise<string> {
  try {
    const prompt = `Based on this student's performance: ${userContext}\n\nProvide 3-5 personalized study suggestions:`;
    
    const result = await hf.textGeneration({
      model: "mistralai/Mistral-7B-Instruct-v0.2",
      inputs: prompt,
      parameters: {
        max_new_tokens: 300,
        temperature: 0.7,
      },
    });

    return result.generated_text;
  } catch (error) {
    console.error("HF text generation error:", error);
    throw error;
  }
}

/**
 * Enhanced question generation using Hugging Face with ML-guided prompts
 */
export async function generateQuestionsHF(
  topic: string,
  difficulty: string,
  careerPath?: string,
  count: number = 5
): Promise<any[]> {
  try {
    // Create context-aware prompt based on difficulty and career path
    const difficultyContext = {
      easy: "beginner-friendly, foundational concepts",
      medium: "intermediate-level, practical application",
      hard: "advanced, in-depth technical knowledge",
      beginner: "beginner-friendly, foundational concepts",
      intermediate: "intermediate-level, practical application",
      advanced: "advanced, in-depth technical knowledge"
    };

    const careerContext = careerPath 
      ? `relevant to ${careerPath} career path` 
      : "covering general computer science concepts";

    const prompt = `You are an expert computer science educator. Generate ${count} high-quality multiple-choice questions about ${topic}.

Requirements:
- Difficulty: ${difficultyContext[difficulty as keyof typeof difficultyContext] || "intermediate-level"}
- Context: ${careerContext}
- Format: Each question must be a valid JSON object
- Quality: Questions should be clear, educational, and test real understanding

Output format (JSON array):
[
  {
    "question": "Clear, specific question text?",
    "options": ["Option A", "Option B", "Option C", "Option D"],
    "correctAnswer": 1,
    "category": "${topic}",
    "difficulty": "${difficulty}",
    "explanation": "Detailed explanation of why the answer is correct"
  }
]

Generate exactly ${count} questions following this format:`;
    
    const result = await hf.textGeneration({
      model: "mistralai/Mistral-7B-Instruct-v0.2",
      inputs: prompt,
      parameters: {
        max_new_tokens: 1500,
        temperature: 0.7,
        top_p: 0.9,
        return_full_text: false,
      },
    });

    // Try to extract JSON array from response
    const generatedText = result.generated_text;
    
    // Multiple extraction strategies
    let questions: any[] = [];
    
    // Strategy 1: Direct JSON array match
    const jsonArrayMatch = generatedText.match(/\[\s*\{[\s\S]*\}\s*\]/);
    if (jsonArrayMatch) {
      try {
        questions = JSON.parse(jsonArrayMatch[0]);
      } catch (e) {
        console.log("Strategy 1 failed, trying strategy 2");
      }
    }
    
    // Strategy 2: Multiple JSON objects
    if (questions.length === 0) {
      const objectMatches = generatedText.matchAll(/\{[\s\S]*?"question"[\s\S]*?\}/g);
      for (const match of objectMatches) {
        try {
          const obj = JSON.parse(match[0]);
          if (obj.question && obj.options && obj.correctAnswer !== undefined) {
            questions.push(obj);
          }
        } catch (e) {
          continue;
        }
      }
    }
    
    // Validate and clean questions
    questions = questions
      .filter(q => 
        q.question && 
        Array.isArray(q.options) && 
        q.options.length >= 4 &&
        (typeof q.correctAnswer === 'number' || typeof q.correctAnswer === 'string') &&
        q.explanation
      )
      .map(q => {
        // Convert correctAnswer to number if it's a string
        let correctAnswerIndex = q.correctAnswer;
        if (typeof correctAnswerIndex === 'string') {
          correctAnswerIndex = parseInt(correctAnswerIndex);
        }
        
        return {
          question: q.question.trim(),
          options: q.options.slice(0, 4).map((opt: string) => String(opt).trim()),
          correctAnswer: Math.min(Math.max(0, correctAnswerIndex), 3),
          category: q.category || topic,
          difficulty: q.difficulty || difficulty,
          explanation: q.explanation.trim()
        };
      })
      .slice(0, count);
    
    console.log(`[HF] Generated ${questions.length}/${count} valid questions for ${topic} (${difficulty})`);
    
    if (questions.length === 0) {
      throw new Error("No valid questions generated by Hugging Face");
    }
    
    return questions;
    
  } catch (error) {
    console.error("HF question generation error:", error);
    return [];
  }
}

export default {
  classifyQuestionHF,
  generateStudySuggestionsHF,
  generateQuestionsHF,
};
