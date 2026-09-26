import React, { createContext, useContext, useEffect, useState } from 'react';

export type ModeTheme = 'clair' | 'sombre' | 'systeme';

interface ContexteThemeType {
  theme: ModeTheme;
  setTheme: (mode: ModeTheme) => void;
  estSombre: boolean;
  basculerTheme: () => void;
}

const ContexteTheme = createContext<ContexteThemeType | undefined>(undefined);

const CLE_STOCKAGE_THEME = 'theme_tontine';

export const FournisseurTheme: React.FC<{ children: React.ReactNode }> = ({ children }) => {
  const [theme, setThemeState] = useState<ModeTheme>(() => {
    const stocke = localStorage.getItem(CLE_STOCKAGE_THEME) as ModeTheme | null;
    return stocke || 'systeme';
  });

  const [estSombre, setEstSombre] = useState<boolean>(() => {
    if (typeof window === 'undefined') return false;
    const stocke = localStorage.getItem(CLE_STOCKAGE_THEME);
    if (stocke === 'sombre') return true;
    if (stocke === 'clair') return false;
    return window.matchMedia('(prefers-color-scheme: dark)').matches;
  });

  const appliquerTheme = (mode: ModeTheme) => {
    const racine = document.documentElement;
    const prefereSombre = window.matchMedia('(prefers-color-scheme: dark)').matches;
    const actifEstSombre = mode === 'sombre' || (mode === 'systeme' && prefereSombre);

    setEstSombre(actifEstSombre);

    if (actifEstSombre) {
      racine.classList.add('dark');
    } else {
      racine.classList.remove('dark');
    }
  };

  const setTheme = (mode: ModeTheme) => {
    setThemeState(mode);
    localStorage.setItem(CLE_STOCKAGE_THEME, mode);
    appliquerTheme(mode);
  };

  const basculerTheme = () => {
    setTheme(estSombre ? 'clair' : 'sombre');
  };

  useEffect(() => {
    appliquerTheme(theme);

    const mediaQuery = window.matchMedia('(prefers-color-scheme: dark)');
    const gererChangementSysteme = (e: MediaQueryListEvent) => {
      if (theme === 'systeme') {
        appliquerTheme('systeme');
      }
    };

    mediaQuery.addEventListener('change', gererChangementSysteme);
    return () => mediaQuery.removeEventListener('change', gererChangementSysteme);
  }, [theme]);

  return (
    <ContexteTheme.Provider value={{ theme, setTheme, estSombre, basculerTheme }}>
      {children}
    </ContexteTheme.Provider>
  );
};

export const useTheme = (): ContexteThemeType => {
  const contexte = useContext(ContexteTheme);
  if (!contexte) {
    throw new Error("useTheme doit être utilisé à l'intérieur d'un FournisseurTheme");
  }
  return contexte;
};
