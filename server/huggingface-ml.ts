import { HfInference } from "@huggingface/inference";

const HF_API_TOKEN = process.env.HUGGINGFACE_API_TOKEN;
const HF_MODEL_ENDPOINT = process.env.HF_MODEL_ENDPOINT;

let hfClient: HfInference | null = null;

function getHfClient(): HfInference | null {
  if (!HF_API_TOKEN) {
    return null;
  }
  if (!hfClient) {
    hfClient = new HfInference(HF_API_TOKEN);
  }
  return hfClient;
}

export interface HFQuizRequest {
  category: string;
  difficulty: string;
  career_path: string;
  count: number;
}

export interface HFCareerRequest {
  visual_design: number;
  backend_pref: number;
  math_stats: number;
  web_apps: number;
  data_interest: number;
  cloud_interest: number;
  mobile_interest: number;
  security_interest: number;
  frontend_perf: number;
  backend_perf: number;
  data_perf: number;
  algo_perf: number;
}

export interface HFStudyRequest {
  level: number;
  career_path: string;
  frontend_score: number;
  backend_score: number;
  data_score: number;
  algo_score: number;
  security_score: number;
  weak_categories: string[];
}

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

export interface CareerRecommendation {
  recommended_path: string;
  probabilities: Record<string, number>;
  confidence: number;
}

export function isHuggingFaceConfigured(): boolean {
  return !!(HF_API_TOKEN && HF_MODEL_ENDPOINT);
}

async function callHuggingFaceEndpoint<T>(
  endpoint: string,
  payload: Record<string, unknown>
): Promise<T> {
  if (!HF_API_TOKEN) {
    throw new Error("Hugging Face API token not configured");
  }

  const baseUrl = HF_MODEL_ENDPOINT || "https://api-inference.huggingface.co/models";
  const url = `${baseUrl}/${endpoint}`;

  const response = await fetch(url, {
    method: "POST",
    headers: {
      "Authorization": `Bearer ${HF_API_TOKEN}`,
      "Content-Type": "application/json",
    },
    body: JSON.stringify({ inputs: payload }),
  });

  if (!response.ok) {
    const error = await response.text();
    throw new Error(`Hugging Face API error: ${response.status} - ${error}`);
  }

  const result = await response.json();
  return result as T;
}

export async function callHuggingFaceInference<T>(
  modelId: string,
  command: string,
  data: object
): Promise<T> {
  if (!HF_API_TOKEN) {
    throw new Error("Hugging Face API token not configured");
  }

  const baseUrl = HF_MODEL_ENDPOINT || `https://api-inference.huggingface.co/models/${modelId}`;
  
  const response = await fetch(baseUrl, {
    method: "POST",
    headers: {
      "Authorization": `Bearer ${HF_API_TOKEN}`,
      "Content-Type": "application/json",
    },
    body: JSON.stringify({
      inputs: {
        command,
        data,
      },
    }),
  });

  if (!response.ok) {
    const errorText = await response.text();
    throw new Error(`Hugging Face API error: ${response.status} - ${errorText}`);
  }

  const result = await response.json();
  
  if (Array.isArray(result) && result.length > 0) {
    return result[0] as T;
  }
  
  return result as T;
}

export async function generateQuizFromHuggingFace(
  request: HFQuizRequest,
  modelId?: string
): Promise<GeneratedQuestion[]> {
  const model = modelId || process.env.HF_QUIZ_MODEL || "your-username/careerquest-quiz-model";
  
  console.log(`Calling Hugging Face model: ${model} for quiz generation`);
  
  const result = await callHuggingFaceInference<{ questions: GeneratedQuestion[] }>(
    model,
    "generate_quiz",
    request
  );
  
  return result.questions || [];
}

export async function recommendCareerFromHuggingFace(
  request: HFCareerRequest,
  modelId?: string
): Promise<CareerRecommendation> {
  const model = modelId || process.env.HF_CAREER_MODEL || "your-username/careerquest-career-model";
  
  console.log(`Calling Hugging Face model: ${model} for career recommendation`);
  
  const result = await callHuggingFaceInference<CareerRecommendation>(
    model,
    "recommend_career",
    request
  );
  
  return result;
}

export async function suggestStudyFromHuggingFace(
  request: HFStudyRequest,
  modelId?: string
): Promise<StudySuggestion[]> {
  const model = modelId || process.env.HF_STUDY_MODEL || "your-username/careerquest-study-model";
  
  console.log(`Calling Hugging Face model: ${model} for study suggestions`);
  
  const result = await callHuggingFaceInference<{ suggestions: StudySuggestion[] }>(
    model,
    "suggest_study",
    request
  );
  
  return result.suggestions || [];
}

export async function healthCheckHuggingFace(): Promise<boolean> {
  if (!isHuggingFaceConfigured()) {
    return false;
  }

  try {
    const client = getHfClient();
    if (!client) return false;
    
    return true;
  } catch (error) {
    console.error("Hugging Face health check failed:", error);
    return false;
  }
}
