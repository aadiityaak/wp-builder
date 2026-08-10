import { useEffect, useState } from 'react';
import { Button, Notice, TextControl } from '@wordpress/components';
import { api } from '../api';

export default function Settings() {
	const [form, setForm] = useState({
		base_url: 'https://api.openai.com/v1',
		model: 'gpt-4o-mini',
		temperature: 0.3,
		api_key: '',
	});
	const [masked, setMasked] = useState('');
	const [saving, setSaving] = useState(false);
	const [saved, setSaved] = useState(false);
	const [testing, setTesting] = useState(false);
	const [testMsg, setTestMsg] = useState(null); // {ok, text}

	useEffect(() => {
		api('/settings')
			.then((d) => {
				setForm((f) => ({
					...f,
					base_url: d.base_url,
					model: d.model,
					temperature: d.temperature,
				}));
				setMasked(d.api_key_masked);
			})
			.catch(() => {});
	}, []);

	const set = (key) => (value) => {
		setForm((f) => ({ ...f, [key]: value }));
		setSaved(false);
	};

	const save = async () => {
		setSaving(true);
		try {
			await api('/settings', { method: 'POST', body: form });
			setSaved(true);
			setMasked(form.api_key ? form.api_key.slice(0, 4) + '••••••••••••' + form.api_key.slice(-4) : masked);
		} catch (e) {
			setTestMsg({ ok: false, text: e.message });
		} finally {
			setSaving(false);
		}
	};

	const test = async () => {
		setTesting(true);
		try {
			await api('/settings/test', { method: 'POST' });
			setTestMsg({ ok: true, text: 'Koneksi berhasil. Provider merespons dengan baik.' });
		} catch (e) {
			setTestMsg({ ok: false, text: e.message });
		} finally {
			setTesting(false);
		}
	};

	return (
		<div className="wpb-settings">
			<h2>Pengaturan AI Provider</h2>
			<p className="wpb-note">
				Isi kredensial provider OpenAI-compatible (OpenAI, OpenRouter,
				DeepSeek, Groq, Ollama, dll). Kunci disimpan sebagai teks biasa di
				wp_options.
			</p>

			<TextControl
				label="Base URL"
				help="Contoh: https://api.openai.com/v1 atau https://openrouter.ai/api/v1"
				value={form.base_url}
				onChange={set('base_url')}
			/>
			<TextControl
				label="Model"
				help="Contoh: gpt-4o-mini, claude-3-5-haiku, deepseek-chat"
				value={form.model}
				onChange={set('model')}
			/>
			<TextControl
				label="API Key"
				type="password"
				help={masked ? `Kunci tersimpan (${masked}). Kosongkan untuk mempertahankan.` : 'Belum ada kunci tersimpan.'}
				value={form.api_key}
				onChange={set('api_key')}
			/>
			<TextControl
				label="Temperature"
				type="number"
				step="0.1"
				min="0"
				max="2"
				value={String(form.temperature)}
				onChange={set('temperature')}
			/>

			<div className="wpb-settings-actions">
				<Button variant="primary" onClick={save} disabled={saving || testing}>
					{saving ? 'Menyimpan…' : 'Simpan'}
				</Button>
				<Button variant="secondary" onClick={test} disabled={saving || testing}>
					{testing ? 'Menguji…' : 'Uji Koneksi'}
				</Button>
			</div>

			{saved && <Notice status="success" isDismissible={false}>Pengaturan tersimpan.</Notice>}
			{testMsg && (
				<Notice status={testMsg.ok ? 'success' : 'error'} isDismissible={false}>
					{testMsg.text}
				</Notice>
			)}
		</div>
	);
}
