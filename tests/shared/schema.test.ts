import { describe, it, expect } from 'vitest';
import { z } from 'zod';

const insertUserSchema = z.object({
  username: z.string().min(1),
  email: z.string().email(),
  password: z.string().min(1),
  displayName: z.string().min(1),
  pathSelectionMode: z.enum(['ai-guided', 'manual']).optional(),
  currentCareerPathId: z.string().nullable().optional(),
  currentStreak: z.number().optional(),
  lastLoginDate: z.date().nullable().optional(),
});

const registerRequestSchema = insertUserSchema
  .extend({
    confirmPassword: z.string().min(1, 'Please confirm your password'),
  })
  .superRefine((data, ctx) => {
    if (data.password !== data.confirmPassword) {
      ctx.addIssue({
        code: z.ZodIssueCode.custom,
        message: 'Passwords do not match',
        path: ['confirmPassword'],
      });
    }
  });

describe('Schema Validation', () => {
  describe('insertUserSchema', () => {
    it('should validate correct user data', () => {
      const validUser = {
        username: 'testuser',
        email: 'test@example.com',
        password: 'password123',
        displayName: 'Test User',
      };

      const result = insertUserSchema.safeParse(validUser);
      expect(result.success).toBe(true);
    });

    it('should reject invalid email', () => {
      const invalidUser = {
        username: 'testuser',
        email: 'invalid-email',
        password: 'password123',
        displayName: 'Test User',
      };

      const result = insertUserSchema.safeParse(invalidUser);
      expect(result.success).toBe(false);
    });

    it('should reject empty username', () => {
      const invalidUser = {
        username: '',
        email: 'test@example.com',
        password: 'password123',
        displayName: 'Test User',
      };

      const result = insertUserSchema.safeParse(invalidUser);
      expect(result.success).toBe(false);
    });

    it('should reject empty password', () => {
      const invalidUser = {
        username: 'testuser',
        email: 'test@example.com',
        password: '',
        displayName: 'Test User',
      };

      const result = insertUserSchema.safeParse(invalidUser);
      expect(result.success).toBe(false);
    });

    it('should accept optional pathSelectionMode', () => {
      const userWithMode = {
        username: 'testuser',
        email: 'test@example.com',
        password: 'password123',
        displayName: 'Test User',
        pathSelectionMode: 'ai-guided' as const,
      };

      const result = insertUserSchema.safeParse(userWithMode);
      expect(result.success).toBe(true);
    });

    it('should reject invalid pathSelectionMode', () => {
      const userWithInvalidMode = {
        username: 'testuser',
        email: 'test@example.com',
        password: 'password123',
        displayName: 'Test User',
        pathSelectionMode: 'invalid',
      };

      const result = insertUserSchema.safeParse(userWithInvalidMode);
      expect(result.success).toBe(false);
    });
  });

  describe('registerRequestSchema', () => {
    it('should validate matching passwords', () => {
      const validRegistration = {
        username: 'testuser',
        email: 'test@example.com',
        password: 'password123',
        confirmPassword: 'password123',
        displayName: 'Test User',
      };

      const result = registerRequestSchema.safeParse(validRegistration);
      expect(result.success).toBe(true);
    });

    it('should reject non-matching passwords', () => {
      const invalidRegistration = {
        username: 'testuser',
        email: 'test@example.com',
        password: 'password123',
        confirmPassword: 'differentpassword',
        displayName: 'Test User',
      };

      const result = registerRequestSchema.safeParse(invalidRegistration);
      expect(result.success).toBe(false);
      if (!result.success) {
        const confirmPasswordError = result.error.issues.find(
          (issue) => issue.path.includes('confirmPassword')
        );
        expect(confirmPasswordError).toBeDefined();
        expect(confirmPasswordError?.message).toBe('Passwords do not match');
      }
    });

    it('should reject empty confirmPassword', () => {
      const invalidRegistration = {
        username: 'testuser',
        email: 'test@example.com',
        password: 'password123',
        confirmPassword: '',
        displayName: 'Test User',
      };

      const result = registerRequestSchema.safeParse(invalidRegistration);
      expect(result.success).toBe(false);
    });

    it('should reject missing confirmPassword', () => {
      const invalidRegistration = {
        username: 'testuser',
        email: 'test@example.com',
        password: 'password123',
        displayName: 'Test User',
      };

      const result = registerRequestSchema.safeParse(invalidRegistration);
      expect(result.success).toBe(false);
    });
  });
});
