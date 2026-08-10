import { useEffect, useRef, useState } from 'react';
import { Button, Spinner, TextControl } from '@wordpress/components';
import { api } from '../api';

function makeSessionId() {
	return 'sess_' + Math.random().toString(36).slice(2, 14);
}

export default function Chat({ preview, onPreview, prefill, onPrefillConsumed }) {
	const [sessionId, setSessionId] = useState(
		() => localStorage.getItem('wpb_session') || ''
	);
	const [messages, setMessages] = useState([]);
	const [input, setInput] = useState('');
	const [loading, setLoading] = useState(false);
	const [error, setError] = useState('');
	const bottomRef = useRef(null);

	// Restore history for a persisted session.
	useEffect(() => {
		if (!sessionId) {
			return;
		}
		api('/chat/session/' + sessionId)
			.then((data) => {
				setMessages(data.messages || []);
				if (data.preview?.url) {
					onPreview(data.preview);
				}
			})
			.catch(() => {});
		// eslint-disable-next-line react-hooks/exhaustive-deps
	}, []);

	// Auto-scroll.
	useEffect(() => {
		bottomRef.current?.scrollIntoView({ behavior: 'smooth' });
	}, [messages, loading]);

	// Consume prefilled message from other tabs.
	useEffect(() => {
		if (prefill) {
			setInput(prefill);
			onPrefillConsumed();
		}
	}, [prefill, onPrefillConsumed]);

	const send = async () => {
		const text = input.trim();
		if (!text || loading) {
			return;
		}

		const sid = sessionId || makeSessionId();
		if (!sessionId) {
			setSessionId(sid);
			localStorage.setItem('wpb_session', sid);
		}

		setInput('');
		setError('');
		setMessages((m) => [...m, { role: 'user', content: text }]);
		setLoading(true);

		try {
			const data = await api('/chat', {
				method: 'POST',
				body: { session_id: sid, message: text },
			});
			setMessages((m) => [
				...m,
				{ role: 'tools', tools: data.events || [] },
				{ role: 'assistant', content: data.reply },
			]);
			if (data.preview?.url) {
				onPreview(data.preview);
			}
		} catch (e) {
			setError(e.message || 'Terjadi kesalahan.');
		} finally {
			setLoading(false);
		}
	};

	const reset = async () => {
		if (sessionId) {
			try {
				await api('/chat/session/' + sessionId, { method: 'DELETE' });
			} catch (e) {
				// ignore
			}
		}
		localStorage.removeItem('wpb_session');
		setSessionId('');
		setMessages([]);
		setError('');
		onPreview({ url: '', label: '' });
	};

	return (
		<div className="wpb-chat">
			<div className="wpb-chat-head">
				<span className="wpb-chat-title">Asisten Builder</span>
				<Button variant="tertiary" size="small" onClick={reset}>
					Reset
				</Button>
			</div>

			<div className="wpb-chat-body">
				{messages.length === 0 && (
					<div className="wpb-chat-empty">
						<p>
							Minta AI membuat halaman, mengedit section, atau mengubah
							header/footer lewat chat.
						</p>
						<p className="wpb-chat-hint">
							Contoh: «Buat landing page untuk jasa desain grafis», «Ubah
							judul hero», «Edit header: tambah logo dan menu».
						</p>
					</div>
				)}

				{messages.map((msg, i) => {
					if (msg.role === 'tools') {
						return (
							<div key={i} className="wpb-tools">
								{(msg.tools || []).map((tool, j) => (
									<span
										key={j}
										className={'wpb-tool-chip' + (tool.ok ? ' is-ok' : ' is-err')}
									>
										{tool.ok ? '✓' : '✗'} {tool.tool}
										{tool.error ? ` — ${tool.error}` : ''}
									</span>
								))}
							</div>
						);
					}
					return (
						<div
							key={i}
							className={
								'wpb-bubble wpb-bubble-' + (msg.role === 'user' ? 'user' : 'ai')
							}
						>
							{msg.content}
						</div>
					);
				})}

				{loading && (
					<div className="wpb-bubble wpb-bubble-ai">
						<Spinner />
					</div>
				)}

				{error && <div className="wpb-chat-error">{error}</div>}

				<div ref={bottomRef} />
			</div>

			<div className="wpb-composer">
				<TextControl
					value={input}
					onChange={setInput}
					onKeyDown={(e) => {
						if (e.key === 'Enter' && !e.shiftKey) {
							e.preventDefault();
							send();
						}
					}}
					placeholder="Tulis instruksi untuk membangun / mengedit halaman…"
					disabled={loading}
				/>
				<Button
					variant="primary"
					onClick={send}
					disabled={loading || !input.trim()}
				>
					Kirim
				</Button>
			</div>
		</div>
	);
}
