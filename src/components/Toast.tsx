import React, { useEffect } from 'react';

interface ToastProps {
  message: string;
  subMessage?: string;
  type?: 'success' | 'info' | 'error';
  onClose: () => void;
}

export const Toast: React.FC<ToastProps> = ({
  message,
  subMessage = 'Maraña Crochet Artesanal',
  type = 'success',
  onClose
}) => {
  useEffect(() => {
    const timer = setTimeout(onClose, 3200);
    return () => clearTimeout(timer);
  }, [onClose]);

  const iconName = type === 'success' ? 'check_circle' : type === 'error' ? 'error' : 'info';
  const iconColor = type === 'success' ? 'text-status-stock-success' : type === 'error' ? 'text-status-out-error' : 'text-primary';

  return (
    <div className="fixed bottom-6 right-6 z-50 animate-bounce-in flex items-center gap-3.5 bg-neutral-ink-card text-on-primary px-5 py-4 rounded-2xl shadow-2xl border border-white/10 backdrop-blur-md">
      <div className={`w-9 h-9 rounded-xl bg-white/10 flex items-center justify-center ${iconColor}`}>
        <span className="material-symbols-outlined text-2xl">{iconName}</span>
      </div>
      <div className="flex flex-col">
        <span className="font-label-md text-label-md font-bold text-white">{message}</span>
        {subMessage && (
          <span className="font-label-sm text-label-sm text-outline-variant">{subMessage}</span>
        )}
      </div>
      <button
        onClick={onClose}
        className="ml-2 text-white/50 hover:text-white transition-colors p-1"
        aria-label="Cerrar notificación"
      >
        <span className="material-symbols-outlined text-lg">close</span>
      </button>
    </div>
  );
};

