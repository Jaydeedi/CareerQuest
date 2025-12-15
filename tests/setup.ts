import { vi } from 'vitest';

vi.mock('./server/firebase', () => ({
  db: {},
  auth: {},
}));

vi.mock('./server/storage-firestore', () => ({
  storage: {
    getUser: vi.fn(),
    getUserByEmail: vi.fn(),
    getUserByUsername: vi.fn(),
    createUser: vi.fn(),
    updateUser: vi.fn(),
    getUserBadges: vi.fn(),
    awardBadge: vi.fn(),
    getBadge: vi.fn(),
    createNotification: vi.fn(),
    getUserChallengeAttempts: vi.fn(),
    getUserQuizAttempts: vi.fn(),
    getCareerPaths: vi.fn(),
  },
}));
