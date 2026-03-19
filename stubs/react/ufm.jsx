import React from 'react';
import { createRoot } from 'react-dom/client';
import FileManager from './FileManager.jsx';

const el = document.getElementById('ufm-app');
if (el) {
    createRoot(el).render(<FileManager />);
}
