# CareerQuest

## Overview

CareerQuest is a gamified career path recommendation and learning platform for Computer Science students. It combines interactive coding challenges, quizzes, and AI-powered career recommendations to help students discover and progress toward their ideal tech career paths. The platform features XP-based leveling, badges, leaderboards, daily challenges, and streak tracking to create an engaging learning experience.

## User Preferences

Preferred communication style: Simple, everyday language.

## System Architecture

### Frontend Architecture
- **Framework**: React with TypeScript, using Vite as the build tool
- **Routing**: Wouter for client-side routing
- **State Management**: TanStack React Query for server state and caching
- **UI Components**: Radix UI primitives with shadcn/ui component library
- **Styling**: Tailwind CSS with custom design tokens defined in CSS variables
- **Code Editor**: Monaco Editor integration for code challenges
- **Fonts**: Inter (UI), Space Grotesk (display), JetBrains Mono (code)

### Backend Architecture
- **Runtime**: Node.js with Express.js
- **Language**: TypeScript compiled with tsx for development, esbuild for production
- **Authentication**: Passport.js with local strategy, session-based auth using express-session with memorystore
- **Security**: Helmet for HTTP headers, rate limiting with express-rate-limit, bcrypt for password hashing

### Data Storage
- **Primary Database**: Firebase Firestore (NoSQL document database)
- **Schema Definition**: Drizzle ORM schema in `shared/schema.ts` (PostgreSQL schema exists but Firebase is the active storage)
- **Storage Layer**: Custom storage abstraction in `server/storage-firestore.ts` implementing the `IStorage` interface

### Machine Learning Service
- **Framework**: Python Flask service with scikit-learn models
- **Models**: Gradient Boosting, Random Forest, Naive Bayes classifiers for career recommendations, question classification, and difficulty prediction
- **Location**: `ml_model/` directory with trained models in `saved_models/`
- **Integration**: Called from Node.js via HTTP requests to Flask service or subprocess execution

### Code Execution
- **Service**: Judge0 CE via RapidAPI for secure code compilation and execution
- **Supported Languages**: JavaScript, Python, Java, C++, C, TypeScript, Go, Rust

### AI Integrations
- **OpenAI**: Used for generating study suggestions (optional, with fallbacks)
- **Hugging Face**: Alternative AI provider for text generation and classification

### Key Design Patterns
- **Monorepo Structure**: Client, server, and shared code in single repository
- **Shared Schema**: Type definitions in `shared/schema.ts` used by both frontend and backend
- **Path Aliases**: `@/` for client source, `@shared/` for shared code
- **Error Boundaries**: React error boundary for graceful frontend error handling
- **Gamification Core**: XP system (1000 XP per level), badge rarities, streak tracking, daily challenges

## External Dependencies

### Required Services
- **Firebase**: Firestore database and authentication infrastructure
  - Project ID: `careerquest-7a741`
  - Collections: users, careerPaths, badges, quizzes, questions, codeChallenges, etc.

- **Judge0 RapidAPI**: Code execution service
  - Host: `judge0-ce.p.rapidapi.com`
  - Required env: `JUDGE0_API_KEY` or `RAPIDAPI_KEY`

### Optional Services
- **OpenAI API**: For AI-powered study suggestions
  - Env: `OPENAI_API_KEY`
  - Falls back to template-based suggestions if unavailable

- **Hugging Face**: Alternative AI inference
  - Env: `HUGGINGFACE_API_KEY` or `HUGGINGFACE_API_TOKEN`
  - Models: facebook/bart-large-mnli, mistralai/Mistral-7B-Instruct-v0.2

- **Neon Database**: PostgreSQL serverless (configured but Firebase is primary)
  - Env: `DATABASE_URL`

### Environment Variables
```
FIREBASE_API_KEY, FIREBASE_AUTH_DOMAIN, FIREBASE_PROJECT_ID, FIREBASE_STORAGE_BUCKET, FIREBASE_MESSAGING_SENDER_ID, FIREBASE_APP_ID
RAPIDAPI_KEY or JUDGE0_API_KEY
OPENAI_API_KEY (optional)
HUGGINGFACE_API_KEY (optional)
SESSION_SECRET
DATABASE_URL (optional, for PostgreSQL)
ML_SERVICE_URL (default: http://127.0.0.1:5001)
```

### Testing
- **Framework**: Vitest with v8 coverage provider
- **Setup**: Mocks for Firebase and Firestore in `tests/setup.ts`
- **Commands**: `npm test`, `npm run test:watch`, `npm run test:coverage`