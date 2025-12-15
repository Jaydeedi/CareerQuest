import { describe, it, expect, vi, beforeEach, afterEach } from 'vitest';

interface MLResponse<T = any> {
  success: boolean;
  result?: T;
  error?: string;
}

const ML_BASE = process.env.ML_SERVICE_URL || 'http://localhost:5001';

async function callMLService<T = any>(
  command: string,
  data?: any,
  timeoutMs = 10_000
): Promise<MLResponse<T>> {
  const url = `${ML_BASE}/predict`;
  const controller = new AbortController();
  const id = setTimeout(() => controller.abort(), timeoutMs);

  try {
    const resp = await fetch(url, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ command, data }),
      signal: controller.signal,
    });

    clearTimeout(id);

    let payload: any;
    try {
      payload = await resp.json();
    } catch (jsonErr) {
      return { success: false, error: `Invalid JSON response: ${jsonErr}` };
    }

    if (!resp.ok) {
      return {
        success: false,
        error: `ML service error: ${resp.status} ${resp.statusText} - ${JSON.stringify(payload)}`,
      };
    }

    return { success: true, result: payload };
  } catch (err: any) {
    clearTimeout(id);
    if (err.name === 'AbortError') {
      return { success: false, error: `Request timed out after ${timeoutMs}ms` };
    }
    return { success: false, error: String(err) };
  }
}

describe('ML Client', () => {
  const originalFetch = global.fetch;

  beforeEach(() => {
    vi.useFakeTimers();
  });

  afterEach(() => {
    global.fetch = originalFetch;
    vi.useRealTimers();
  });

  describe('callMLService', () => {
    it('should return success response on valid request', async () => {
      const mockResponse = { prediction: 'frontend', confidence: 0.85 };
      
      global.fetch = vi.fn().mockResolvedValue({
        ok: true,
        json: () => Promise.resolve(mockResponse),
      });

      const promise = callMLService('predict_career', { userId: '123' });
      await vi.runAllTimersAsync();
      const result = await promise;

      expect(result.success).toBe(true);
      expect(result.result).toEqual(mockResponse);
    });

    it('should return error on non-ok response', async () => {
      global.fetch = vi.fn().mockResolvedValue({
        ok: false,
        status: 500,
        statusText: 'Internal Server Error',
        json: () => Promise.resolve({ error: 'Server error' }),
      });

      const promise = callMLService('predict_career', { userId: '123' });
      await vi.runAllTimersAsync();
      const result = await promise;

      expect(result.success).toBe(false);
      expect(result.error).toContain('ML service error');
      expect(result.error).toContain('500');
    });

    it('should return error on invalid JSON response', async () => {
      global.fetch = vi.fn().mockResolvedValue({
        ok: true,
        json: () => Promise.reject(new Error('Invalid JSON')),
      });

      const promise = callMLService('predict_career', { userId: '123' });
      await vi.runAllTimersAsync();
      const result = await promise;

      expect(result.success).toBe(false);
      expect(result.error).toContain('Invalid JSON response');
    });

    it('should return error on network failure', async () => {
      global.fetch = vi.fn().mockRejectedValue(new Error('Network error'));

      const promise = callMLService('predict_career', { userId: '123' });
      await vi.runAllTimersAsync();
      const result = await promise;

      expect(result.success).toBe(false);
      expect(result.error).toContain('Network error');
    });

    it('should handle timeout correctly', async () => {
      const abortError = new Error('Aborted');
      abortError.name = 'AbortError';
      
      global.fetch = vi.fn().mockRejectedValue(abortError);

      vi.useRealTimers();
      const result = await callMLService('predict_career', { userId: '123' }, 100);
      vi.useFakeTimers();

      expect(result.success).toBe(false);
      expect(result.error).toContain('timed out');
    });

    it('should send correct request format', async () => {
      const mockFetch = vi.fn().mockResolvedValue({
        ok: true,
        json: () => Promise.resolve({ result: 'success' }),
      });
      global.fetch = mockFetch;

      const command = 'classify_question';
      const data = { question: 'What is React?' };

      const promise = callMLService(command, data);
      await vi.runAllTimersAsync();
      await promise;

      expect(mockFetch).toHaveBeenCalledWith(
        expect.stringContaining('/predict'),
        expect.objectContaining({
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ command, data }),
        })
      );
    });

    it('should work without data parameter', async () => {
      const mockFetch = vi.fn().mockResolvedValue({
        ok: true,
        json: () => Promise.resolve({ result: 'success' }),
      });
      global.fetch = mockFetch;

      const promise = callMLService('health_check');
      await vi.runAllTimersAsync();
      await promise;

      expect(mockFetch).toHaveBeenCalledWith(
        expect.any(String),
        expect.objectContaining({
          body: JSON.stringify({ command: 'health_check', data: undefined }),
        })
      );
    });
  });
});
