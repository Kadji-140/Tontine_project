# Documentation Système Tontine Pro

## 1. Besoins Fonctionnels et Non Fonctionnels

### Besoins Fonctionnels

1.  **Gestion des Membres (Utilisateurs)**
    *   Inscription et authentification sécurisée.
    *   **Sécurité d'accès :** Validation obligatoire des nouveaux comptes par un administrateur avant toute connexion ("Sas d'entrée").
    *   Gestion de profil (consultation, modification).
    *   Attribution de rôles : Membre simple, Trésorier (gestion financière), Administrateur (gestion globale).
    *   Gestion administrative : Liste des inscrits, Activation/Blocage des comptes.

2.  **Gestion des Cycles de Tontine**
    *   Création de nouveaux cycles (définition de la période, taux d'intérêt, etc.).
    *   Configuration des paramètres financiers (Montant cotisation, Montant Mange-Mille).
    *   Clôture automatique ou manuelle des cycles.

3.  **Gestion des Séances (Réunions)**
    *   Planification des séances.
    *   Ouverture et Fermeture des séances.
    *   Enregistrement des présences et des opérations financières du jour.
    *   Upload et validation des preuves de versement bancaire.

4.  **Gestion Financière (Cotisations & Fonds)**
    *   Enregistrement des cotisations par type : Tontine (Épargne), Secours (Caisse), Banque (Placement).
    *   **Automatisme Mange-Mille** : Déduction automatique des frais de session lors de la première cotisation.
    *   Gestion des Fonds Spéciaux : Suivi des montants collectés pour les sanctions, frais d'inscription, etc.
    *   Gestion des Dépenses : Enregistrement et validation des sorties d'argent (collation, transport...).

5.  **Gestion des Prêts & Intérêts**
    *   Demande de prêt par les membres.
    *   Validation des prêts par le bureau (règles d'éligibilité : ancienneté, épargne disponible).
    *   Calcul des intérêts (logique configurable : intérêt simple, composé, ou montant fixe).
    *   Suivi des échéances de remboursement.
    *   **Remboursement** : Enregistrement des remboursements (partiels ou totaux) lors des séances.
    *   Distribution des gains : Calcul automatique des dividendes en fin de cycle basé sur les intérêts générés.

6.  **Reporting & Archives**
    *   Génération de rapports PDF par séance (Bilan des entrées/sorties).
    *   Tableau de bord membre (suivi personnel).
    *   Tableau de bord trésorier (vue d'ensemble de la caisse).

### Besoins Non Fonctionnels

1.  **Sécurité**
    *   Protection des données sensibles (mots de passe hachés).
    *   Contrôle d'accès strict (Middleware) selon les rôles.
    *   **Protection contre les intrusions :** Middleware `EnsureUserIsActive` pour bloquer les comptes non validés.
    *   Validation des entrées pour prévenir les injections SQL et XSS.

2.  **Fiabilité & Intégrité**
    *   Transactions atomiques pour les opérations financières (tout ou rien).
    *   Traçabilité des actions (qui a enregistré telle cotisation ?).

3.  **Performance & Scalabilité**
    *   Architecture MVC modulaire (Laravel).
    *   Base de données relationnelle optimisée avec indexation.
    *   Préparation pour une architecture Multi-tenant (SaaS) : Séparation logique des données.

4.  **Ergonomie (UI/UX)**
    *   Interface responsive (Mobile-first).
    *   Feedback utilisateur immédiat (Notifications toast, messages d'erreur clairs).
    *   Design moderne et épuré.

---

## 2. Modèle de Données (MCD Simplifié)

Voici la structure des principales tables et leurs relations.

**Users (Utilisateurs)**
*   `id` (PK), `name`, `email`, `password`, `role` (enum: membre, tresorier, admin).
*   `is_active` (boolean) : Définit si l'utilisateur a le droit de se connecter. Défaut : `false`.
*   *Cardinalité : 1 User peut avoir N Cotisations, N Prêts, etc.*

**Cycles**
*   `id` (PK), `nom`, `date_debut`, `date_fin`, `taux_interet`, `montant_mange_mille`...
*   *Relation : 1 Cycle contient N Séances.*

**Seances**
*   `id` (PK), `cycle_id` (FK), `date_seance`, `statut` (ouverte/fermee), `total_encaisse`, `preuve_versement`...
*   *Relation : 1 Séance appartient à 1 Cycle.*

**Cotisations**
*   `id` (PK), `user_id` (FK), `seance_id` (FK), `mt_total` (Montant), `type` (Tontine/Secours/Banque)...
*   *Relation : Lier un User à une Séance avec un montant.*

**Prets**
*   `id` (PK), `user_id` (FK), `seance_id` (FK), `montant_demande`, `interet_total`, `date_echeance`, `statut`...
*   *Relation : Un membre contracte un prêt lors d'une séance.*

**Remboursements**
*   `id` (PK), `pret_id` (FK), `seance_id` (FK), `user_id` (FK), `montant`, `created_at`...
*   *Relation : Un remboursement est lié à un prêt spécifique et enregistré lors d'une séance.*

**FondsDepenses (Mange-Mille / Sanctions)**
*   `id` (PK), `cycle_id` (FK), `seance_id` (FK), `user_id` (FK), `type` (mange-mille, sanction...), `montant`, `description`...
*   *Relation : Trace les entrées d'argent qui ne sont PAS des épargnes.*

**Depenses (Sorties)**
*   `id` (PK), `seance_id` (FK), `user_id` (FK - Auteur), `motif`, `montant`, `statut`, `recu_path`...
*   *Relation : Les dépenses de fonctionnement enregistrées lors d'une séance.*

---

## 3. Scénario Clé : Inscription et Déroulement d'une Séance

**Titre :** Inscription, Validation et Participation d'un nouveau membre

**Acteurs :** Trésorier (Principal), Nouveau Membre (Peter)

**Pré-conditions :** Le Cycle est actif.

**Déroulement :**

1.  **Inscription (Sas d'entrée) :**
    *   Peter s'inscrit sur le site public.
    *   Il est redirigé vers une page "En attente de validation". Il ne voit rien d'autre.
    *   Le Trésorier reçoit une notification (ou consulte la liste `/utilisateurs`).
    *   Le Trésorier clique sur "Valider" pour le compte de Peter.
    *   Peter peut maintenant accéder au tableau de bord.

2.  **Séance (Mange-Mille) :**
    *   Peter participe à sa première séance.
    *   Il verse 10 000 FCFA.
    *   **Système :** Déduit automatiquement 1 000 FCFA (Mange-Mille) et enregistre 9 000 FCFA en épargne.

3.  **Remboursement (si dette) :**
    *   Le membre B (qui a un prêt en cours) se présente.
    *   Le Trésorier clique sur "Remboursement", cherche "Membre B".
    *   Il sélectionne le prêt et saisit le montant remboursé.
    *   **Système :** Met à jour le reste à payer du prêt et augmente le solde de la séance.
4.  **Sortie d'argent (Prêt) :**
    *   Le membre A demande un prêt.
    *   Le Trésorier vérifie l'éligibilité (automatisé) et enregistre la demande.
    *   Si validé immédiatement, l'argent est sorti de la caisse (Décaissement).
5.  **Clôture :**
    *   Le Trésorier ferme la séance et imprime le Rapport PDF.

---

## 5. Notes Techniques & Code Snippets Importants

### 🔴 Comment rétablir la restriction d'ancienneté (3 mois) pour les prêts ?

À la demande du client, la règle empêchant les nouveaux membres (moins de 3 mois) de demander un prêt a été désactivée.
Pour la rétablir, décommentez le bloc de code suivant dans le fichier : `app/Http/Controllers/PretController.php`, méthode `store()`.

```php
// Fichier : app/Http/Controllers/PretController.php
// Méthode : store(StorePretRequest $request)

// 2. VÉRIFIER L'ANCIENNETÉ (3 MOIS MINIMUM)
$user = \App\Models\User::findOrFail($userId);
$accountAge = \Carbon\Carbon::parse($user->created_at)->diffInMonths(now());

if ($accountAge < 3) {
    $monthsRemaining = 3 - $accountAge;
    return back(); // Avec message d'erreur
}
```
Une fois décommenté, ce bloc bloquera automatiquement toute demande.

## 4. Diagrammes de Cas d'Utilisation

Pour réaliser vos diagrammes UML (avec draw.io, Lucidchart ou StarUML) :

**Acteur : Membre**
*   Cases :
    *   Se connecter
    *   Consulter mon solde
    *   Consulter mes dettes
    *   Demander un prêt
    *   Voir l'historique des séances

**Acteur : Trésorier**
*   Cases (Hérite de Membre) :
    *   Gérer les Séances (Créer, Ouvrir, Fermer)
    *   Enregistrer une Cotisation
    *   Enregistrer un Remboursement
    *   Enregistrer une Dépense
    *   Valider un Prêt
    *   Télécharger les Rapports
    *   Uploader la Preuve de Versement

**Acteur : Administrateur**
*   Cases (Hérite de Trésorier) :
    *   Gérer les Utilisateurs (Ajouter, Bannir)
    *   Gérer les Cycles (Créer, Configurer)
    *   Valider les Dépenses spéciales
    *   Valider les Preuves de Versement

**Relations :**
*   Utilisez des relations `<<include>>` pour l'authentification (ex: "Enregistrer Cotisation" `<<include>>` "Se Connecter").
*   Utilisez des relations `<<extend>>` pour les cas optionnels (ex: "Enregistrer Cotisation" `<<extend>>` "Déduire Mange-Mille").

---








