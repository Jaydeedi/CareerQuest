import { describe, it, expect, vi, beforeEach } from 'vitest';
import bcrypt from 'bcryptjs';

const SALT_ROUNDS = 10;

async function hashPassword(password: string): Promise<string> {
  return bcrypt.hash(password, SALT_ROUNDS);
}

async function comparePassword(password: string, hash: string): Promise<boolean> {
  return bcrypt.compare(password, hash);
}

function requireAuth(req: any, res: any, next: any) {
  if (req.isAuthenticated()) {
    return next();
  }
  res.status(401).send({ error: 'Not authenticated' });
}

function requireAdmin(req: any, res: any, next: any) {
  if (req.isAuthenticated() && req.user?.isAdmin) {
    return next();
  }
  res.status(403).send({ error: 'Admin access required' });
}

describe('Auth Module', () => {
  describe('hashPassword', () => {
    it('should hash a password successfully', async () => {
      const password = 'testPassword123!';
      const hash = await hashPassword(password);
      
      expect(hash).toBeDefined();
      expect(hash).not.toBe(password);
      expect(hash.length).toBeGreaterThan(0);
    });

    it('should create different hashes for the same password', async () => {
      const password = 'testPassword123!';
      const hash1 = await hashPassword(password);
      const hash2 = await hashPassword(password);
      
      expect(hash1).not.toBe(hash2);
    });

    it('should create a valid bcrypt hash format', async () => {
      const password = 'testPassword123!';
      const hash = await hashPassword(password);
      
      expect(hash).toMatch(/^\$2[aby]?\$\d{1,2}\$.{53}$/);
    });
  });

  describe('comparePassword', () => {
    it('should return true for matching password', async () => {
      const password = 'testPassword123!';
      const hash = await hashPassword(password);
      
      const result = await comparePassword(password, hash);
      
      expect(result).toBe(true);
    });

    it('should return false for non-matching password', async () => {
      const password = 'testPassword123!';
      const wrongPassword = 'wrongPassword456!';
      const hash = await hashPassword(password);
      
      const result = await comparePassword(wrongPassword, hash);
      
      expect(result).toBe(false);
    });

    it('should return false for empty password', async () => {
      const password = 'testPassword123!';
      const hash = await hashPassword(password);
      
      const result = await comparePassword('', hash);
      
      expect(result).toBe(false);
    });
  });

  describe('requireAuth middleware', () => {
    let mockReq: any;
    let mockRes: any;
    let mockNext: any;

    beforeEach(() => {
      mockReq = {
        isAuthenticated: vi.fn(),
      };
      mockRes = {
        status: vi.fn().mockReturnThis(),
        send: vi.fn(),
      };
      mockNext = vi.fn();
    });

    it('should call next() when user is authenticated', () => {
      mockReq.isAuthenticated.mockReturnValue(true);

      requireAuth(mockReq, mockRes, mockNext);

      expect(mockNext).toHaveBeenCalled();
      expect(mockRes.status).not.toHaveBeenCalled();
    });

    it('should return 401 when user is not authenticated', () => {
      mockReq.isAuthenticated.mockReturnValue(false);

      requireAuth(mockReq, mockRes, mockNext);

      expect(mockNext).not.toHaveBeenCalled();
      expect(mockRes.status).toHaveBeenCalledWith(401);
      expect(mockRes.send).toHaveBeenCalledWith({ error: 'Not authenticated' });
    });
  });

  describe('requireAdmin middleware', () => {
    let mockReq: any;
    let mockRes: any;
    let mockNext: any;

    beforeEach(() => {
      mockReq = {
        isAuthenticated: vi.fn(),
        user: { isAdmin: false },
      };
      mockRes = {
        status: vi.fn().mockReturnThis(),
        send: vi.fn(),
      };
      mockNext = vi.fn();
    });

    it('should call next() when user is authenticated and admin', () => {
      mockReq.isAuthenticated.mockReturnValue(true);
      mockReq.user = { isAdmin: true };

      requireAdmin(mockReq, mockRes, mockNext);

      expect(mockNext).toHaveBeenCalled();
      expect(mockRes.status).not.toHaveBeenCalled();
    });

    it('should return 403 when user is authenticated but not admin', () => {
      mockReq.isAuthenticated.mockReturnValue(true);
      mockReq.user = { isAdmin: false };

      requireAdmin(mockReq, mockRes, mockNext);

      expect(mockNext).not.toHaveBeenCalled();
      expect(mockRes.status).toHaveBeenCalledWith(403);
      expect(mockRes.send).toHaveBeenCalledWith({ error: 'Admin access required' });
    });

    it('should return 403 when user is not authenticated', () => {
      mockReq.isAuthenticated.mockReturnValue(false);

      requireAdmin(mockReq, mockRes, mockNext);

      expect(mockNext).not.toHaveBeenCalled();
      expect(mockRes.status).toHaveBeenCalledWith(403);
    });
  });
});
