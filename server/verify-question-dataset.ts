
import * as fs from "fs";
import * as path from "path";

async function verifyQuestionDataset() {
  const datasetPath = path.join(process.cwd(), "ml_model", "saved_models", "question_templates.json");
  
  if (!fs.existsSync(datasetPath)) {
    console.error("❌ Question dataset not found at:", datasetPath);
    console.log("Run: python ml_model/train_models_v2.py to generate the dataset");
    return;
  }

  const data = JSON.parse(fs.readFileSync(datasetPath, "utf-8"));
  
  if (!data.questions || !Array.isArray(data.questions)) {
    console.error("❌ Invalid dataset format");
    return;
  }

  const questions = data.questions;
  const categories = new Set(questions.map((q: any) => q.category));
  const difficulties = new Set(questions.map((q: any) => q.difficulty));

  console.log("\n📊 Question Dataset Statistics:");
  console.log("================================");
  console.log(`✓ Total Questions: ${questions.length}`);
  console.log(`✓ Categories: ${Array.from(categories).join(", ")}`);
  console.log(`✓ Difficulties: ${Array.from(difficulties).join(", ")}`);
  
  const categoryBreakdown: Record<string, number> = {};
  questions.forEach((q: any) => {
    categoryBreakdown[q.category] = (categoryBreakdown[q.category] || 0) + 1;
  });
  
  console.log("\n📈 Questions per Category:");
  Object.entries(categoryBreakdown).sort((a, b) => b[1] - a[1]).forEach(([cat, count]) => {
    console.log(`  ${cat}: ${count} questions`);
  });
  
  const difficultyBreakdown: Record<string, number> = {};
  questions.forEach((q: any) => {
    difficultyBreakdown[q.difficulty] = (difficultyBreakdown[q.difficulty] || 0) + 1;
  });
  
  console.log("\n📊 Questions per Difficulty:");
  Object.entries(difficultyBreakdown).forEach(([diff, count]) => {
    console.log(`  ${diff}: ${count} questions`);
  });
  
  // Sample a few questions
  console.log("\n🔍 Sample Questions:");
  questions.slice(0, 3).forEach((q: any, i: number) => {
    console.log(`\n${i + 1}. [${q.category}] [${q.difficulty}] ${q.question}`);
    console.log(`   Options: ${q.options?.length || 0}`);
  });
}

verifyQuestionDataset().catch(console.error);
