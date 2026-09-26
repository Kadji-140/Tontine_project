import React from 'react';
import ReactDOM from 'react-dom/client';
import { BrowserRouter } from 'react-router-dom';
import { FournisseurTheme } from './contexts/ThemeContext';
import App from './App';
import './styles/global.css';

ReactDOM.createRoot(document.getElementById('root')!).render(
  <React.StrictMode>
    <FournisseurTheme>
      <BrowserRouter>
        <App />
      </BrowserRouter>
    </FournisseurTheme>
  </React.StrictMode>
);
