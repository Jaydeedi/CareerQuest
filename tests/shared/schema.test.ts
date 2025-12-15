import { describe, it, expect } from 'vitest';
import { insertUserSchema, registerRequestSchema } from '../../shared/schema';

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

    it('should require username field', () => {
      const invalidUser = {
        email: 'test@example.com',
        password: 'password123',
        displayName: 'Test User',
      };

      const result = insertUserSchema.safeParse(invalidUser);
      expect(result.success).toBe(false);
    });

    it('should require email field', () => {
      const invalidUser = {
        username: 'testuser',
        password: 'password123',
        displayName: 'Test User',
      };

      const result = insertUserSchema.safeParse(invalidUser);
      expect(result.success).toBe(false);
    });

    it('should require password field', () => {
      const invalidUser = {
        username: 'testuser',
        email: 'test@example.com',
        displayName: 'Test User',
      };

      const result = insertUserSchema.safeParse(invalidUser);
      expect(result.success).toBe(false);
    });

    it('should require displayName field', () => {
      const invalidUser = {
        username: 'testuser',
        email: 'test@example.com',
        password: 'password123',
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
        pathSelectionMode: 'ai-guided',
      };

      const result = insertUserSchema.safeParse(userWithMode);
      expect(result.success).toBe(true);
    });

    it('should accept manual pathSelectionMode', () => {
      const userWithMode = {
        username: 'testuser',
        email: 'test@example.com',
        password: 'password123',
        displayName: 'Test User',
        pathSelectionMode: 'manual',
      };

      const result = insertUserSchema.safeParse(userWithMode);
      expect(result.success).toBe(true);
    });

    it('should reject non-string username', () => {
      const invalidUser = {
        username: 123,
        email: 'test@example.com',
        password: 'password123',
        displayName: 'Test User',
      };

      const result = insertUserSchema.safeParse(invalidUser);
      expect(result.success).toBe(false);
    });

    it('should reject non-string email', () => {
      const invalidUser = {
        username: 'testuser',
        email: 123,
        password: 'password123',
        displayName: 'Test User',
      };

      const result = insertUserSchema.safeParse(invalidUser);
      expect(result.success).toBe(false);
    });

    it('should reject null values for required fields', () => {
      const invalidUser = {
        username: null,
        email: 'test@example.com',
        password: 'password123',
        displayName: 'Test User',
      };

      const result = insertUserSchema.safeParse(invalidUser);
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

    it('should require all base user fields', () => {
      const invalidRegistration = {
        password: 'password123',
        confirmPassword: 'password123',
      };

      const result = registerRequestSchema.safeParse(invalidRegistration);
      expect(result.success).toBe(false);
    });

    it('should validate with optional fields included', () => {
      const validRegistration = {
        username: 'testuser',
        email: 'test@example.com',
        password: 'password123',
        confirmPassword: 'password123',
        displayName: 'Test User',
        pathSelectionMode: 'manual',
      };

      const result = registerRequestSchema.safeParse(validRegistration);
      expect(result.success).toBe(true);
    });
  });
});
