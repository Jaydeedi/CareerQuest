import { describe, it, expect, beforeEach } from 'vitest';

interface CategoryPerformance {
  [category: string]: { correct: number; total: number };
}

interface QuestionAttempt {
  category?: string;
  isCorrect: boolean;
}

interface InterestResponse {
  questionId: number;
  response: string;
}

class NaiveBayesRecommender {
  private calculateCategoryPerformance(attempts: QuestionAttempt[]): CategoryPerformance {
    const performance: CategoryPerformance = {};

    for (const attempt of attempts) {
      if (!attempt.category) continue;

      if (!performance[attempt.category]) {
        performance[attempt.category] = { correct: 0, total: 0 };
      }

      performance[attempt.category].total++;
      if (attempt.isCorrect) {
        performance[attempt.category].correct++;
      }
    }

    return performance;
  }

  private analyzeInterestResponses(responses: InterestResponse[]): Record<string, number> {
    const affinities: Record<string, number> = {
      frontend: 0,
      backend: 0,
      data: 0,
      cloud: 0,
      mobile: 0,
      security: 0,
    };

    for (const response of responses) {
      const r = response.response.toString();

      switch (response.questionId) {
        case 1:
          if (r === '5' || r === '4') {
            affinities.frontend += 2;
            affinities.mobile += 1;
          }
          break;

        case 2:
          if (r.includes('Backend')) {
            affinities.backend += 3;
            affinities.cloud += 1;
          } else if (r.includes('Frontend')) {
            affinities.frontend += 3;
            affinities.mobile += 1;
          } else if (r.includes('Both')) {
            affinities.frontend += 1;
            affinities.backend += 1;
          }
          break;

        case 3:
          if (r === '5' || r === '4') {
            affinities.data += 3;
            affinities.backend += 1;
          }
          break;

        case 4:
          if (r.includes('web applications')) {
            affinities.frontend += 2;
            affinities.backend += 2;
          } else if (r.includes('data')) {
            affinities.data += 3;
          } else if (r.includes('cloud')) {
            affinities.cloud += 3;
          } else if (r.includes('mobile')) {
            affinities.mobile += 3;
          } else if (r.includes('security')) {
            affinities.security += 3;
          }
          break;
      }
    }

    return affinities;
  }

  public testCalculateCategoryPerformance(attempts: QuestionAttempt[]) {
    return this.calculateCategoryPerformance(attempts);
  }

  public testAnalyzeInterestResponses(responses: InterestResponse[]) {
    return this.analyzeInterestResponses(responses);
  }
}

describe('NaiveBayesRecommender', () => {
  let recommender: NaiveBayesRecommender;

  beforeEach(() => {
    recommender = new NaiveBayesRecommender();
  });

  describe('calculateCategoryPerformance', () => {
    it('should calculate performance correctly for single category', () => {
      const attempts: QuestionAttempt[] = [
        { category: 'frontend', isCorrect: true },
        { category: 'frontend', isCorrect: true },
        { category: 'frontend', isCorrect: false },
      ];

      const result = recommender.testCalculateCategoryPerformance(attempts);

      expect(result.frontend).toEqual({ correct: 2, total: 3 });
    });

    it('should calculate performance correctly for multiple categories', () => {
      const attempts: QuestionAttempt[] = [
        { category: 'frontend', isCorrect: true },
        { category: 'backend', isCorrect: false },
        { category: 'frontend', isCorrect: true },
        { category: 'data', isCorrect: true },
        { category: 'backend', isCorrect: true },
      ];

      const result = recommender.testCalculateCategoryPerformance(attempts);

      expect(result.frontend).toEqual({ correct: 2, total: 2 });
      expect(result.backend).toEqual({ correct: 1, total: 2 });
      expect(result.data).toEqual({ correct: 1, total: 1 });
    });

    it('should ignore attempts without category', () => {
      const attempts: QuestionAttempt[] = [
        { category: 'frontend', isCorrect: true },
        { isCorrect: false },
        { category: undefined, isCorrect: true },
      ];

      const result = recommender.testCalculateCategoryPerformance(attempts);

      expect(result.frontend).toEqual({ correct: 1, total: 1 });
      expect(Object.keys(result)).toHaveLength(1);
    });

    it('should return empty object for empty array', () => {
      const result = recommender.testCalculateCategoryPerformance([]);
      expect(result).toEqual({});
    });
  });

  describe('analyzeInterestResponses', () => {
    it('should increase frontend affinity for visual design preference', () => {
      const responses: InterestResponse[] = [
        { questionId: 1, response: '5' },
      ];

      const result = recommender.testAnalyzeInterestResponses(responses);

      expect(result.frontend).toBe(2);
      expect(result.mobile).toBe(1);
    });

    it('should increase backend affinity for backend preference', () => {
      const responses: InterestResponse[] = [
        { questionId: 2, response: 'Backend development' },
      ];

      const result = recommender.testAnalyzeInterestResponses(responses);

      expect(result.backend).toBe(3);
      expect(result.cloud).toBe(1);
    });

    it('should increase data affinity for math/statistics preference', () => {
      const responses: InterestResponse[] = [
        { questionId: 3, response: '5' },
      ];

      const result = recommender.testAnalyzeInterestResponses(responses);

      expect(result.data).toBe(3);
      expect(result.backend).toBe(1);
    });

    it('should handle multiple responses correctly', () => {
      const responses: InterestResponse[] = [
        { questionId: 1, response: '5' },
        { questionId: 2, response: 'Frontend design' },
        { questionId: 4, response: 'Building web applications' },
      ];

      const result = recommender.testAnalyzeInterestResponses(responses);

      expect(result.frontend).toBeGreaterThan(0);
    });

    it('should start with zero affinities', () => {
      const result = recommender.testAnalyzeInterestResponses([]);

      expect(result).toEqual({
        frontend: 0,
        backend: 0,
        data: 0,
        cloud: 0,
        mobile: 0,
        security: 0,
      });
    });
  });
});
