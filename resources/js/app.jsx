import './bootstrap';
import React from 'react';
import { createRoot } from 'react-dom/client';
import Main from './Main.jsx';

const rootElement = document.getElementById('app');

if (rootElement) {
    const root = createRoot(rootElement);
    root.render(
        <React.StrictMode>
            <Main />
        </React.StrictMode>
    );
}
