import { describe, it, expect } from 'vitest';

interface CategoryPerformance {
  [category: string]: { correct: number; total: number; accuracy: number };
}

interface QuestionAttempt {
  category?: string;
  isCorrect: boolean;
}

function shuffleArray<T>(array: T[]): T[] {
  const shuffled = [...array];
  for (let i = shuffled.length - 1; i > 0; i--) {
    const j = Math.floor(Math.random() * (i + 1));
    [shuffled[i], shuffled[j]] = [shuffled[j], shuffled[i]];
  }
  return shuffled;
}

function calculateCategoryPerformance(attempts: QuestionAttempt[]): CategoryPerformance {
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

function selectDifficultyForLevel(level: number): 'easy' | 'medium' | 'hard' {
  if (level <= 3) return 'easy';
  if (level <= 7) return 'medium';
  return 'hard';
}

function mapDifficultyLabel(difficulty?: string): 'easy' | 'medium' | 'hard' {
  if (difficulty === 'beginner') return 'easy';
  if (difficulty === 'intermediate') return 'medium';
  if (difficulty === 'advanced') return 'hard';
  return 'medium';
}

describe('Learning Intelligence Service', () => {
  describe('shuffleArray', () => {
    it('should return array with same length', () => {
      const arr = [1, 2, 3, 4, 5];
      const shuffled = shuffleArray(arr);
      expect(shuffled).toHaveLength(arr.length);
    });

    it('should contain all original elements', () => {
      const arr = [1, 2, 3, 4, 5];
      const shuffled = shuffleArray(arr);
      expect(shuffled.sort()).toEqual(arr.sort());
    });

    it('should not modify original array', () => {
      const arr = [1, 2, 3, 4, 5];
      const originalCopy = [...arr];
      shuffleArray(arr);
      expect(arr).toEqual(originalCopy);
    });

    it('should handle empty array', () => {
      const result = shuffleArray([]);
      expect(result).toEqual([]);
    });

    it('should handle single element array', () => {
      const result = shuffleArray([1]);
      expect(result).toEqual([1]);
    });
  });

  describe('calculateCategoryPerformance', () => {
    it('should calculate accuracy correctly', () => {
      const attempts: QuestionAttempt[] = [
        { category: 'frontend', isCorrect: true },
        { category: 'frontend', isCorrect: false },
      ];

      const result = calculateCategoryPerformance(attempts);

      expect(result.frontend.accuracy).toBe(0.5);
      expect(result.frontend.correct).toBe(1);
      expect(result.frontend.total).toBe(2);
    });

    it('should handle 100% accuracy', () => {
      const attempts: QuestionAttempt[] = [
        { category: 'backend', isCorrect: true },
        { category: 'backend', isCorrect: true },
        { category: 'backend', isCorrect: true },
      ];

      const result = calculateCategoryPerformance(attempts);

      expect(result.backend.accuracy).toBe(1);
    });

    it('should handle 0% accuracy', () => {
      const attempts: QuestionAttempt[] = [
        { category: 'data', isCorrect: false },
        { category: 'data', isCorrect: false },
      ];

      const result = calculateCategoryPerformance(attempts);

      expect(result.data.accuracy).toBe(0);
    });

    it('should skip attempts without category', () => {
      const attempts: QuestionAttempt[] = [
        { category: 'frontend', isCorrect: true },
        { isCorrect: true },
        { category: undefined, isCorrect: false },
      ];

      const result = calculateCategoryPerformance(attempts);

      expect(Object.keys(result)).toHaveLength(1);
      expect(result.frontend).toBeDefined();
    });
  });

  describe('selectDifficultyForLevel', () => {
    it('should return easy for levels 1-3', () => {
      expect(selectDifficultyForLevel(1)).toBe('easy');
      expect(selectDifficultyForLevel(2)).toBe('easy');
      expect(selectDifficultyForLevel(3)).toBe('easy');
    });

    it('should return medium for levels 4-7', () => {
      expect(selectDifficultyForLevel(4)).toBe('medium');
      expect(selectDifficultyForLevel(5)).toBe('medium');
      expect(selectDifficultyForLevel(6)).toBe('medium');
      expect(selectDifficultyForLevel(7)).toBe('medium');
    });

    it('should return hard for levels 8+', () => {
      expect(selectDifficultyForLevel(8)).toBe('hard');
      expect(selectDifficultyForLevel(10)).toBe('hard');
      expect(selectDifficultyForLevel(20)).toBe('hard');
    });
  });

  describe('mapDifficultyLabel', () => {
    it('should map beginner to easy', () => {
      expect(mapDifficultyLabel('beginner')).toBe('easy');
    });

    it('should map intermediate to medium', () => {
      expect(mapDifficultyLabel('intermediate')).toBe('medium');
    });

    it('should map advanced to hard', () => {
      expect(mapDifficultyLabel('advanced')).toBe('hard');
    });

    it('should default to medium for unknown values', () => {
      expect(mapDifficultyLabel('unknown')).toBe('medium');
      expect(mapDifficultyLabel('')).toBe('medium');
    });

    it('should default to medium for undefined', () => {
      expect(mapDifficultyLabel(undefined)).toBe('medium');
    });
  });
});
