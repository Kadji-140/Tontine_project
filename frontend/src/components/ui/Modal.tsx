import React, { useEffect } from 'react';
import { X } from 'lucide-react';

interface PropsModal {
  estOuvert: boolean;
  surFermer: () => void;
  titre: string;
  children: React.ReactNode;
}

export const Modal: React.FC<PropsModal> = ({
  estOuvert,
  surFermer,
  titre,
  children,
}) => {
  useEffect(() => {
    const handleKeyDown = (e: KeyboardEvent) => {
      if (e.key === 'Escape') surFermer();
    };
    if (estOuvert) {
      document.body.style.overflow = 'hidden';
      window.addEventListener('keydown', handleKeyDown);
    } else {
      document.body.style.overflow = 'unset';
    }
    return () => {
      document.body.style.overflow = 'unset';
      window.removeEventListener('keydown', handleKeyDown);
    };
  }, [estOuvert, surFermer]);

  if (!estOuvert) return null;

  return (
    <div className="fixed inset-0 z-50 flex items-center justify-center p-4">
      {/* Backdrop */}
      <div 
        className="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity"
        onClick={surFermer}
      />

      {/* Modal Dialog */}
      <div className="relative w-full max-w-lg bg-white dark:bg-sombre-surface border border-slate-200 dark:border-sombre-bordure rounded-2xl shadow-2xl p-6 z-10 max-h-[90vh] overflow-y-auto animate-in fade-in zoom-in-95 duration-200">
        <div className="flex items-center justify-between pb-3 mb-4 border-b border-slate-100 dark:border-sombre-bordure">
          <h3 className="text-lg font-bold text-slate-900 dark:text-white">
            {titre}
          </h3>
          <button
            onClick={surFermer}
            className="p-1 rounded-lg text-slate-400 hover:text-slate-600 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-sombre-survol transition-colors"
          >
            <X className="w-5 h-5" />
          </button>
        </div>

        <div>{children}</div>
      </div>
    </div>
  );
};

export default Modal;
