import React, { useState } from 'react';
import { useAuthStore } from '../../stores/authStore';
import { Bouton } from '../../components/ui/Bouton';
import { Clock, RefreshCw, LogOut, CheckCircle2 } from 'lucide-react';
import { useNavigate } from 'react-router-dom';

export const CompteInactif: React.FC = () => {
  const { utilisateur, initialiser, deconnexion } = useAuthStore();
  const [actualisationEnCours, setActualisationEnCours] = useState(false);
  const navigate = useNavigate();

  const verifierStatut = async () => {
    setActualisationEnCours(true);
    await initialiser();
    setActualisationEnCours(false);

    const storeActuel = useAuthStore.getState();
    if (storeActuel.estActif) {
      navigate('/tableau-de-bord');
    }
  };

  return (
    <div className="space-y-6 text-center">
      <div className="w-16 h-16 rounded-full bg-amber-100 dark:bg-amber-950/60 text-amber-600 dark:text-amber-400 mx-auto flex items-center justify-center shadow-lg shadow-amber-500/10">
        <Clock className="w-8 h-8 animate-pulse" />
      </div>

      <div className="space-y-2">
        <h3 className="text-xl font-bold text-slate-900 dark:text-white">
          Compte en attente de validation
        </h3>
        <p className="text-xs text-slate-600 dark:text-slate-400 leading-relaxed max-w-sm mx-auto">
          Bonjour <strong className="text-slate-800 dark:text-slate-200">{utilisateur?.name}</strong>. Votre inscription a bien été enregistrée.
        </p>
      </div>

      <div className="p-4 rounded-2xl bg-amber-50/70 dark:bg-amber-950/30 border border-amber-200/80 dark:border-amber-900/40 text-left space-y-2.5 text-xs text-amber-900 dark:text-amber-300">
        <div className="flex items-center gap-2 font-bold">
          <CheckCircle2 className="w-4 h-4 text-amber-600 dark:text-amber-400" />
          <span>Procédure d'admission en cours</span>
        </div>
        <p className="text-[11px] leading-relaxed opacity-90">
          Un administrateur ou trésorier de votre tontine doit approuver votre dossier d'adhésion avant que vous puissiez consulter le solde, participer aux séances et solliciter des prêts.
        </p>
      </div>

      <div className="flex flex-col gap-2.5 pt-2">
        <Bouton
          variante="primaire"
          chargement={actualisationEnCours}
          onClick={verifierStatut}
          className="w-full justify-center"
          icone={<RefreshCw className={`w-4 h-4 mr-1 ${actualisationEnCours ? 'animate-spin' : ''}`} />}
        >
          Vérifier l'activation
        </Bouton>

        <Bouton
          variante="contour"
          onClick={() => deconnexion()}
          className="w-full justify-center"
          icone={<LogOut className="w-4 h-4 mr-1" />}
        >
          Se déconnecter
        </Bouton>
      </div>
    </div>
  );
};
