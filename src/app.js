import { useState } from 'react';
import { Button } from '@wordpress/components';
import Chat from './components/Chat';
import Preview from './components/Preview';
import Pages from './components/Pages';
import Settings from './components/Settings';

const TABS = [
	{ key: 'chat', label: 'Chat' },
	{ key: 'pages', label: 'Halaman' },
	{ key: 'settings', label: 'Pengaturan' },
];

export default function App() {
	const [tab, setTab] = useState('chat');
	const [preview, setPreview] = useState({ url: '', label: '' });
	const [prefill, setPrefill] = useState('');

	const editPage = (page) => {
		setPrefill(
			`Edit halaman «${page.title}» (id ${page.id}). Tampilkan daftar section-nya, lalu ikuti instruksi saya.`
		);
		setTab('chat');
	};

	const editPart = (part) => {
		setPrefill(`Edit ${part.label.toLowerCase()}.`);
		setPreview(part);
		setTab('chat');
	};

	return (
		<div className="wpb-app">
			<nav className="wpb-nav">
				{TABS.map((t) => (
					<Button
						key={t.key}
						variant={tab === t.key ? 'primary' : 'tertiary'}
						onClick={() => setTab(t.key)}
					>
						{t.label}
					</Button>
				))}
			</nav>

			{tab === 'chat' && (
				<div className="wpb-layout">
					<div className="wpb-layout-chat">
						<Chat
							preview={preview}
							onPreview={setPreview}
							prefill={prefill}
							onPrefillConsumed={() => setPrefill('')}
						/>
					</div>
					<div className="wpb-layout-preview">
						<Preview preview={preview} />
					</div>
				</div>
			)}

			{tab === 'pages' && (
				<Pages onEditPage={editPage} onEditPart={editPart} />
			)}

			{tab === 'settings' && <Settings />}
		</div>
	);
}
