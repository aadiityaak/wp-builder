import { useEffect, useState } from 'react';
import { Button, Spinner } from '@wordpress/components';
import { api, homeUrl } from '../api';

export default function Pages({ onEditPage, onEditPart }) {
	const [data, setData] = useState(null);

	const load = () => {
		api('/pages')
			.then(setData)
			.catch(() => setData({ pages: [], header: {}, footer: {} }));
	};

	useEffect(load, []);

	const publish = async (id) => {
		try {
			await api('/pages/' + id + '/publish', { method: 'POST' });
			load();
		} catch (e) {
			// eslint-disable-next-line no-alert
			alert(e.message);
		}
	};

	const remove = async (id) => {
		// eslint-disable-next-line no-alert
		if (!window.confirm('Hapus halaman ini?')) {
			return;
		}
		try {
			await api('/pages/' + id, { method: 'DELETE' });
			load();
		} catch (e) {
			// eslint-disable-next-line no-alert
			alert(e.message);
		}
	};

	if (!data) {
		return (
			<div className="wpb-page-loading">
				<Spinner />
			</div>
		);
	}

	const partRow = (part) => (
		<tr key={part.label}>
			<td className="wpb-part-label">
				<strong>{part.label}</strong>
			</td>
			<td>{part.set ? 'Tersedia' : 'Belum dibuat'}</td>
			<td>—</td>
			<td>—</td>
			<td className="wpb-actions">
				<Button
					variant="primary"
					size="small"
					onClick={() => onEditPart(part)}
				>
					Edit di Chat
				</Button>
			</td>
		</tr>
	);

	return (
		<div className="wpb-pages">
			<h2>Halaman Builder</h2>
			<table className="widefat striped">
				<thead>
					<tr>
						<th>Judul</th>
						<th>Status</th>
						<th>Section</th>
						<th>Diperbarui</th>
						<th>Aksi</th>
					</tr>
				</thead>
				<tbody>
					{data.pages.length === 0 && (
						<tr>
							<td colSpan="5" className="wpb-empty-row">
								Belum ada halaman. Buat lewat tab Chat.
							</td>
						</tr>
					)}
					{data.pages.map((p) => (
						<tr key={p.id}>
							<td>{p.title}</td>
							<td>{p.status}</td>
							<td>{p.section_count}</td>
							<td>{p.modified || '—'}</td>
							<td className="wpb-actions">
								<Button
									variant="link"
									size="small"
									onClick={() => onEditPage(p)}
								>
									Edit
								</Button>
								{p.status === 'draft' && (
									<Button variant="link" size="small" onClick={() => publish(p.id)}>
										Publish
									</Button>
								)}
								<Button variant="link" size="small" href={p.url} target="_blank">
									Buka
								</Button>
								<Button
									variant="link"
									size="small"
									isDestructive
									onClick={() => remove(p.id)}
								>
									Hapus
								</Button>
							</td>
						</tr>
					))}
				</tbody>
			</table>

			<h2>Header &amp; Footer</h2>
			<table className="widefat striped">
				<thead>
					<tr>
						<th>Bagian</th>
						<th>Status</th>
						<th>Section</th>
						<th>Diperbarui</th>
						<th>Aksi</th>
					</tr>
				</thead>
				<tbody>
					{partRow(data.header)}
					{partRow(data.footer)}
				</tbody>
			</table>

			<p className="wpb-note">
				Header/Footer berlaku untuk halaman yang dibuat dengan Builder. Situs
				beranda: {homeUrl}
			</p>
		</div>
	);
}
