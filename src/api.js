const cfg = window.wpBuilder || { restUrl: '', nonce: '', homeUrl: '' };

/**
 * Minimal fetch wrapper against our REST namespace.
 *
 * @param {string} path    API path (after /wp-builder/v1).
 * @param {Object} options Fetch options.
 * @return {Promise} Parsed JSON.
 */
export function api(path, options = {}) {
	const { body, headers, ...rest } = options;

	return fetch(cfg.restUrl + path, {
		...rest,
		headers: {
			'X-WP-Nonce': cfg.nonce,
			'Content-Type': 'application/json',
			...(headers || {}),
		},
		body: body ? JSON.stringify(body) : undefined,
	}).then(async (response) => {
		const data = await response.json().catch(() => ({}));
		if (!response.ok) {
			const message = data?.message || data?.code || 'Request failed';
			throw new Error(message);
		}
		return data;
	});
}

export const homeUrl = cfg.homeUrl;
