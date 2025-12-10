const ML_BASE = process.env.ML_SERVICE_URL || "http://127.0.0.1:5001";

export type MLResponse<T> = {
	success: boolean;
	result?: T;
	error?: string;
};

export async function callMLService<T = any>(command: string, data?: any, timeoutMs = 10_000): Promise<MLResponse<T>> {
	const url = `${ML_BASE}/predict`;
	const controller = new AbortController();
	const id = setTimeout(() => controller.abort(), timeoutMs);

	try {
		const resp = await fetch(url, {
			method: "POST",
			headers: { "Content-Type": "application/json" },
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
			return { success: false, error: `ML service error: ${resp.status} ${resp.statusText} - ${JSON.stringify(payload)}` };
		}

		return { success: true, result: payload };
	} catch (err: any) {
		if (err.name === 'AbortError') {
			return { success: false, error: `Request timed out after ${timeoutMs}ms` };
		}
		return { success: false, error: String(err) };
	}
}

export async function healthCheck(timeoutMs = 3000): Promise<boolean> {
	const url = `${ML_BASE}/health`;
	const controller = new AbortController();
	const id = setTimeout(() => controller.abort(), timeoutMs);

	try {
		const resp = await fetch(url, { method: 'GET', signal: controller.signal });
		clearTimeout(id);
		return resp.ok;
	} catch (_) {
		return false;
	}
}

export default callMLService;
