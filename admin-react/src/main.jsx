import React from 'react';
import { createRoot } from 'react-dom/client';
import App from './App';

const rootEl = document.getElementById('plugin-admin-root');

if (rootEl) {
  const bootstrap = window.VelocityOptionsData || {};
  const root = createRoot(rootEl);
  root.render(<App bootstrap={bootstrap} />);
}
