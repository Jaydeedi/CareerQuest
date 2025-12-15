import { defineConfig } from 'vitest/config';
import path from 'path';

export default defineConfig({
  test: {
    globals: true,
    environment: 'node',
    include: ['**/*.{test,spec}.{js,mjs,cjs,ts,mts,cts,jsx,tsx}'],
    exclude: ['**/node_modules/**', '**/dist/**'],
    coverage: {
      provider: 'v8',
      reporter: ['text', 'json', 'html', 'lcov'],
      reportsDirectory: './coverage',
      include: [
        'server/**/*.ts',
        'client/src/lib/**/*.ts',
        'shared/**/*.ts'
      ],
      exclude: [
        'node_modules',
        'dist',
        '**/*.d.ts',
        '**/*.test.ts',
        '**/*.spec.ts',
        '**/index.ts',
        'server/vite.ts',
        'server/firebase.ts',
        'server/seed*.ts',
        'server/migrate*.ts',
        'server/routes.ts',
        'server/storage*.ts',
        'server/db.ts',
        'server/judge0.ts',
        'server/huggingface*.ts',
        'client/src/lib/firebase.ts',
        'client/src/lib/queryClient.ts',
        'client/src/lib/utils.ts'
      ],
      thresholds: {
        statements: 20,
        branches: 15,
        functions: 15,
        lines: 20
      }
    },
    testTimeout: 10000,
    hookTimeout: 10000,
    setupFiles: ['./tests/setup.ts'],
  },
  resolve: {
    alias: {
      '@': path.resolve(__dirname, 'client/src'),
      '@shared': path.resolve(__dirname, 'shared'),
      '@assets': path.resolve(__dirname, 'attached_assets'),
    },
  },
});
