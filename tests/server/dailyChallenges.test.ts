import { describe, it, expect } from 'vitest';

function getDifficultyOptions(level: number): string[] {
  if (level < 3) return ['beginner', 'easy'];
  if (level < 7) return ['easy', 'beginner', 'mediu'];
  if (level < 12) return ['easy', 'medium'];
  if (level < 18) return ['medium', 'intermediate'];
  return ['medium', 'intermediate', 'hard', 'advanced'];
}

function shouldPreferEasier(dayOfWeek: number): boolean {
  return dayOfWeek === 1 || dayOfWeek === 6;
}

function shouldPreferHarder(dayOfWeek: number): boolean {
  return dayOfWeek === 3 || dayOfWeek === 5;
}

describe('Daily Challenges Generator', () => {
  describe('getDifficultyOptions', () => {
    it('should return beginner/easy for low level users (< 3)', () => {
      const result = getDifficultyOptions(1);
      expect(result).toContain('beginner');
      expect(result).toContain('easy');
      expect(result).not.toContain('hard');
    });

    it('should return easy/beginner/medium for level 3-6', () => {
      const result = getDifficultyOptions(5);
      expect(result).toContain('easy');
      expect(result).toContain('beginner');
      expect(result).toContain('medium');
    });

    it('should return easy/medium for level 7-11', () => {
      const result = getDifficultyOptions(10);
      expect(result).toContain('easy');
      expect(result).toContain('medium');
      expect(result).not.toContain('beginner');
    });

    it('should return medium/intermediate for level 12-17', () => {
      const result = getDifficultyOptions(15);
      expect(result).toContain('medium');
      expect(result).toContain('intermediate');
      expect(result).not.toContain('easy');
    });

    it('should return all harder difficulties for level 18+', () => {
      const result = getDifficultyOptions(20);
      expect(result).toContain('medium');
      expect(result).toContain('intermediate');
      expect(result).toContain('hard');
      expect(result).toContain('advanced');
    });

    it('should handle edge cases at boundaries', () => {
      expect(getDifficultyOptions(2)).toEqual(['beginner', 'easy']);
      expect(getDifficultyOptions(3)).toEqual(['easy', 'beginner', 'medium']);
      expect(getDifficultyOptions(6)).toEqual(['easy', 'beginner', 'medium']);
      expect(getDifficultyOptions(7)).toEqual(['easy', 'medium']);
      expect(getDifficultyOptions(11)).toEqual(['easy', 'medium']);
      expect(getDifficultyOptions(12)).toEqual(['medium', 'intermediate']);
      expect(getDifficultyOptions(17)).toEqual(['medium', 'intermediate']);
      expect(getDifficultyOptions(18)).toEqual(['medium', 'intermediate', 'hard', 'advanced']);
    });
  });

  describe('shouldPreferEasier', () => {
    it('should prefer easier on Monday (1)', () => {
      expect(shouldPreferEasier(1)).toBe(true);
    });

    it('should prefer easier on Saturday (6)', () => {
      expect(shouldPreferEasier(6)).toBe(true);
    });

    it('should not prefer easier on other days', () => {
      expect(shouldPreferEasier(0)).toBe(false);
      expect(shouldPreferEasier(2)).toBe(false);
      expect(shouldPreferEasier(3)).toBe(false);
      expect(shouldPreferEasier(4)).toBe(false);
      expect(shouldPreferEasier(5)).toBe(false);
    });
  });

  describe('shouldPreferHarder', () => {
    it('should prefer harder on Wednesday (3)', () => {
      expect(shouldPreferHarder(3)).toBe(true);
    });

    it('should prefer harder on Friday (5)', () => {
      expect(shouldPreferHarder(5)).toBe(true);
    });

    it('should not prefer harder on other days', () => {
      expect(shouldPreferHarder(0)).toBe(false);
      expect(shouldPreferHarder(1)).toBe(false);
      expect(shouldPreferHarder(2)).toBe(false);
      expect(shouldPreferHarder(4)).toBe(false);
      expect(shouldPreferHarder(6)).toBe(false);
    });
  });

  describe('difficulty scaling logic', () => {
    it('should progressively increase difficulty with level', () => {
      const level1Options = getDifficultyOptions(1);
      const level10Options = getDifficultyOptions(10);
      const level20Options = getDifficultyOptions(20);

      expect(level1Options).toContain('beginner');
      expect(level10Options).not.toContain('beginner');
      expect(level20Options).toContain('advanced');
    });

    it('should have overlapping difficulties for smooth progression', () => {
      const level6Options = getDifficultyOptions(6);
      const level7Options = getDifficultyOptions(7);

      const commonDifficulties = level6Options.filter((d) =>
        level7Options.includes(d)
      );
      expect(commonDifficulties.length).toBeGreaterThan(0);
    });
  });
});
