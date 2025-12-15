import { describe, it, expect } from 'vitest';
import { 
  validatePasswordStrength, 
  getPasswordRequirements 
} from '../../client/src/lib/passwordValidation';

describe('Password Validation', () => {
  describe('validatePasswordStrength', () => {
    it('should return Very Weak for empty password', () => {
      const result = validatePasswordStrength('');
      expect(result.score).toBe(0);
      expect(result.feedback).toBe('Very Weak');
      expect(result.isValid).toBe(false);
    });

    it('should return Weak for simple short password', () => {
      const result = validatePasswordStrength('abc');
      expect(result.score).toBeLessThanOrEqual(2);
      expect(result.feedback).toBe('Weak');
      expect(result.isValid).toBe(false);
    });

    it('should return Fair for password with 3 criteria', () => {
      const result = validatePasswordStrength('Abcdefgh');
      expect(result.score).toBe(3);
      expect(result.feedback).toBe('Fair');
      expect(result.isValid).toBe(false);
    });

    it('should return Good for password with 4 criteria', () => {
      const result = validatePasswordStrength('Abcdefg1');
      expect(result.score).toBe(4);
      expect(result.feedback).toBe('Good');
      expect(result.isValid).toBe(true);
    });

    it('should return Strong for password with all 5 criteria', () => {
      const result = validatePasswordStrength('Abcdefg1!');
      expect(result.score).toBe(5);
      expect(result.feedback).toBe('Strong');
      expect(result.isValid).toBe(true);
    });

    describe('checks', () => {
      it('should correctly check minimum length', () => {
        expect(validatePasswordStrength('short').checks.minLength).toBe(false);
        expect(validatePasswordStrength('longpassword').checks.minLength).toBe(true);
      });

      it('should correctly check uppercase', () => {
        expect(validatePasswordStrength('lowercase').checks.hasUppercase).toBe(false);
        expect(validatePasswordStrength('Uppercase').checks.hasUppercase).toBe(true);
      });

      it('should correctly check lowercase', () => {
        expect(validatePasswordStrength('UPPERCASE').checks.hasLowercase).toBe(false);
        expect(validatePasswordStrength('lowercase').checks.hasLowercase).toBe(true);
      });

      it('should correctly check numbers', () => {
        expect(validatePasswordStrength('nodigits').checks.hasNumber).toBe(false);
        expect(validatePasswordStrength('has123').checks.hasNumber).toBe(true);
      });

      it('should correctly check special characters', () => {
        expect(validatePasswordStrength('nospecial').checks.hasSpecial).toBe(false);
        expect(validatePasswordStrength('special!').checks.hasSpecial).toBe(true);
        expect(validatePasswordStrength('special@').checks.hasSpecial).toBe(true);
        expect(validatePasswordStrength('special#').checks.hasSpecial).toBe(true);
      });
    });
  });

  describe('getPasswordRequirements', () => {
    it('should return 5 requirements', () => {
      const requirements = getPasswordRequirements();
      expect(requirements).toHaveLength(5);
    });

    it('should include all expected requirements', () => {
      const requirements = getPasswordRequirements();
      expect(requirements).toContain('At least 8 characters long');
      expect(requirements).toContain('Contains uppercase letter (A-Z)');
      expect(requirements).toContain('Contains lowercase letter (a-z)');
      expect(requirements).toContain('Contains number (0-9)');
      expect(requirements).toContain('Contains special character (!@#$%^&*)');
    });
  });
});
