import QuizCard from '../QuizCard';

const mockQuestions = [
  {
    id: "q1",
    question: "What is the time complexity of binary search?",
    options: ["O(n)", "O(log n)", "O(n²)", "O(1)"],
    correctAnswer: 1
  },
  {
    id: "q2",
    question: "Which data structure uses LIFO (Last In First Out)?",
    options: ["Queue", "Stack", "Array", "Tree"],
    correctAnswer: 1
  },
  {
    id: "q3",
    question: "What does SQL stand for?",
    options: [
      "Structured Query Language",
      "Simple Question Language",
      "System Query List",
      "Standard Quality Logic"
    ],
    correctAnswer: 0
  }
];

export default function QuizCardExample() {
  return <QuizCard questions={mockQuestions} timeLimit={180} />;
}
