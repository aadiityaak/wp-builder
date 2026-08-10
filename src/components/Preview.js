import { useEffect, useState } from 'react';

export default function Preview({ preview }) {
	const [key, setKey] = useState(0);

	useEffect(() => {
		if (preview?.url) {
			setKey((k) => k + 1);
		}
	}, [preview?.url]);

	return (
		<div className="wpb-preview">
			<div className="wpb-preview-head">
				<span>{preview?.label || 'Belum ada target preview'}</span>
				{preview?.url && (
					<a href={preview.url} target="_blank" rel="noreferrer">
						Buka di tab baru
					</a>
				)}
			</div>
			{preview?.url ? (
				<iframe
					key={key}
					src={preview.url}
					title="Preview"
					className="wpb-preview-frame"
				/>
			) : (
				<div className="wpb-preview-empty">
					Preview halaman, header atau footer akan muncul di sini.
				</div>
			)}
		</div>
	);
}
