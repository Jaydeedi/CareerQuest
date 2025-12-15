# Testing Guide

This document provides comprehensive guidelines for unit testing in this project.

## Table of Contents

1. [Testing Framework](#testing-framework)
2. [Running Tests Locally](#running-tests-locally)
3. [CI/CD Pipeline](#cicd-pipeline)
4. [Test Structure](#test-structure)
5. [Best Practices](#best-practices)
6. [Coverage Requirements](#coverage-requirements)

## Testing Framework

This project uses **Vitest** as the testing framework. Vitest is chosen for its:

- Native TypeScript support
- Fast execution with Vite integration
- Jest-compatible API
- Built-in coverage reporting
- Watch mode for development

### Dependencies

```json
{
  "vitest": "^4.0.15",
  "@vitest/coverage-v8": "^4.0.15",
  "@testing-library/react": "^16.3.0",
  "@testing-library/jest-dom": "^6.9.1",
  "jsdom": "^27.3.0",
  "happy-dom": "^20.0.11"
}
```

## Running Tests Locally

### Basic Commands

```bash
# Run all tests once
npm run test

# Run tests in watch mode (recommended for development)
npm run test:watch

# Run tests with coverage report
npm run test:coverage

# Run tests with UI (interactive browser interface)
npm run test:ui
```

### Running Specific Tests

```bash
# Run tests matching a pattern
npx vitest run auth

# Run tests in a specific file
npx vitest run tests/server/auth.test.ts

# Run tests in a specific directory
npx vitest run tests/server/
```

### Coverage Report

After running `npm run test:coverage`, you can view the coverage report:

- **Terminal**: Summary printed to console
- **HTML Report**: Open `coverage/index.html` in a browser
- **JSON Report**: `coverage/coverage-summary.json`

## CI/CD Pipeline

The GitHub Actions workflow (`.github/workflows/ci.yml`) runs automatically on:

- Every push to `main`, `master`, or `develop` branches
- Every pull request to these branches

### Pipeline Jobs

1. **lint-and-typecheck**: TypeScript type checking
2. **test**: Runs all unit tests
3. **test-coverage**: Generates coverage reports
4. **build**: Builds the production application
5. **security-audit**: Scans for vulnerable dependencies

### Artifacts

The pipeline uploads:
- Test results and coverage reports (retained 30 days)
- Build artifacts (retained 7 days)

## Test Structure

### Directory Structure

```
tests/
├── setup.ts                      # Global test setup and mocks
├── server/                       # Server-side tests
│   ├── auth.test.ts             # Authentication tests
│   ├── naiveBayes.test.ts       # ML recommendation tests
│   └── learningIntelligence.test.ts
├── client/                       # Client-side tests
│   └── passwordValidation.test.ts
└── shared/                       # Shared module tests
    └── schema.test.ts
```

### File Naming Conventions

- Test files: `*.test.ts` or `*.spec.ts`
- Place tests near the code they test or in the `tests/` directory
- Mirror the source directory structure in tests

### Test Anatomy

```typescript
import { describe, it, expect, beforeEach, vi } from 'vitest';

describe('ModuleName', () => {
  // Setup/teardown
  beforeEach(() => {
    // Reset state before each test
  });

  describe('functionName', () => {
    it('should describe expected behavior', () => {
      // Arrange
      const input = 'test';
      
      // Act
      const result = functionUnderTest(input);
      
      // Assert
      expect(result).toBe('expected');
    });

    it('should handle edge case', () => {
      // Test edge cases
    });
  });
});
```

## Best Practices

### 1. Test Naming

Use descriptive names that explain behavior:

```typescript
// ✅ Good
it('should return 401 when user is not authenticated')
it('should hash password with bcrypt salt rounds')

// ❌ Bad
it('test auth')
it('works')
```

### 2. Arrange-Act-Assert (AAA) Pattern

Structure tests clearly:

```typescript
it('should calculate category performance', () => {
  // Arrange - Set up test data
  const attempts = [
    { category: 'frontend', isCorrect: true },
    { category: 'frontend', isCorrect: false },
  ];

  // Act - Execute the code under test
  const result = calculateCategoryPerformance(attempts);

  // Assert - Verify the results
  expect(result.frontend.accuracy).toBe(0.5);
});
```

### 3. Test Isolation

Each test should be independent:

```typescript
beforeEach(() => {
  vi.clearAllMocks();
  // Reset any shared state
});
```

### 4. Mock External Dependencies

Use Vitest's mocking capabilities:

```typescript
import { vi } from 'vitest';

// Mock a module
vi.mock('./storage-firestore', () => ({
  storage: {
    getUser: vi.fn(),
  },
}));

// Mock a function
const mockFn = vi.fn().mockReturnValue('mocked');
```

### 5. Test Edge Cases

Always test:
- Empty inputs
- Null/undefined values
- Boundary conditions
- Error scenarios

```typescript
it('should handle empty array', () => {
  const result = processArray([]);
  expect(result).toEqual([]);
});

it('should throw error for invalid input', () => {
  expect(() => processArray(null)).toThrow();
});
```

### 6. Avoid Test Interdependence

```typescript
// ❌ Bad - Tests depend on each other
let sharedState;
it('test 1', () => { sharedState = 'value'; });
it('test 2', () => { expect(sharedState).toBe('value'); });

// ✅ Good - Each test is independent
it('test 1', () => {
  const state = 'value';
  expect(state).toBe('value');
});
```

### 7. Keep Tests Fast

- Mock expensive operations (database, network)
- Avoid unnecessary setup
- Use `beforeAll` for one-time setup

## Coverage Requirements

The project enforces minimum coverage thresholds:

| Metric | Minimum |
|--------|---------|
| Statements | 60% |
| Branches | 50% |
| Functions | 60% |
| Lines | 60% |

### Files Included in Coverage

- `server/**/*.ts`
- `client/src/lib/**/*.ts`
- `shared/**/*.ts`

### Files Excluded from Coverage

- Test files (`*.test.ts`, `*.spec.ts`)
- Type definitions (`*.d.ts`)
- Configuration files
- Seed/migration scripts

## Writing New Tests

### For Server-Side Code

1. Create test file in `tests/server/`
2. Import functions/classes to test
3. Mock external dependencies (Firebase, storage)
4. Write tests following AAA pattern

### For Client-Side Code

1. Create test file in `tests/client/`
2. For React components, use `@testing-library/react`
3. For utilities, test pure functions directly

### For Shared Modules

1. Create test file in `tests/shared/`
2. Test schema validations with valid/invalid inputs
3. Test type exports and transformations

## Troubleshooting

### Tests Not Running

```bash
# Clear Vitest cache
npx vitest run --clearCache
```

### Coverage Not Generating

Ensure `@vitest/coverage-v8` is installed:
```bash
npm install -D @vitest/coverage-v8
```

### Mocks Not Working

Check mock placement and ensure they're before imports:
```typescript
vi.mock('./module'); // Must be before import
import { something } from './module';
```

## Resources

- [Vitest Documentation](https://vitest.dev/)
- [Testing Library](https://testing-library.com/)
- [Zod Schema Testing](https://zod.dev/)
