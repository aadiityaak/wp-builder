import './style.scss';
import { createRoot } from 'react-dom/client';
import App from './app';

const mount = document.getElementById('wpb-app');
if (mount) {
	createRoot(mount).render(<App />);
}
