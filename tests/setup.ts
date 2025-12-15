import { vi } from 'vitest';

vi.mock('../server/firebase', () => ({
  db: {},
  auth: {},
}));

vi.mock('../server/storage-firestore', () => ({
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

vi.mock('firebase/firestore', () => ({
  collection: vi.fn(),
  doc: vi.fn(),
  getDoc: vi.fn(),
  getDocs: vi.fn(),
  setDoc: vi.fn(),
  updateDoc: vi.fn(),
  deleteDoc: vi.fn(),
  query: vi.fn(),
  where: vi.fn(),
  orderBy: vi.fn(),
  limit: vi.fn(),
  Timestamp: {
    now: vi.fn(() => ({ toDate: () => new Date() })),
    fromDate: vi.fn((d) => ({ toDate: () => d })),
  },
}));
