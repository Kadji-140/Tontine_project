<div class="profile-card border-danger">
    <div class="profile-card-header bg-danger">
        <div class="d-flex align-items-center">
            <div class="bg-white bg-opacity-25 rounded-circle p-2 me-3">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <div>
                <h5 class="mb-0 fw-bold">Supprimer le Compte</h5>
                <small class="opacity-75">Action irréversible</small>
            </div>
        </div>
    </div>

    <div class="profile-card-body">
        <div class="profile-alert profile-alert-danger">
            <i class="fas fa-exclamation-circle me-3"></i>
            <div>
                <strong>Attention :</strong> Une fois votre compte supprimé, toutes vos données seront
                <span class="fw-bold">effacées définitivement</span>. Cette action ne peut pas être annulée.
            </div>
        </div>

        <p class="text-muted mb-4">
            <i class="fas fa-info-circle me-2"></i>
            Avant de supprimer votre compte, assurez-vous d'avoir sauvegardé toutes les données
            que vous souhaitez conserver.
        </p>

        <button type="button" class="profile-button profile-button-danger mb-4"
                data-bs-toggle="modal" data-bs-target="#confirmDeleteModal">
            <i class="fas fa-trash-alt me-2"></i> Supprimer définitivement mon compte
        </button>

        <!-- Modal de confirmation -->
        <div class="modal fade" id="confirmDeleteModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content" style="border-radius: 12px; overflow: hidden;">
                    <div class="modal-header bg-danger text-white">
                        <h5 class="modal-title fw-bold">
                            <i class="fas fa-exclamation-circle me-2"></i> Confirmer la suppression
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>

                    <form method="post" action="{{ route('profile.destroy') }}">
                        @csrf
                        @method('delete')

                        <div class="modal-body p-4">
                            <div class="text-center mb-4">
                                <div class="bg-danger bg-opacity-10 rounded-circle d-inline-flex p-4 mb-3">
                                    <i class="fas fa-user-slash text-danger fa-3x"></i>
                                </div>
                                <h4 class="fw-bold text-danger mb-3">Êtes-vous absolument sûr ?</h4>
                                <p class="text-muted">
                                    Toutes vos données, vos cotisations, vos prêts et votre historique
                                    seront <span class="fw-bold text-danger">supprimés définitivement</span>.
                                </p>
                            </div>

                            <div class="profile-form-group">
                                <label for="password" class="profile-label">
                                    <i class="fas fa-key me-2"></i> Confirmez avec votre mot de passe
                                </label>
                                <input type="password" name="password" id="password"
                                       class="profile-input @error('password', 'userDeletion') profile-input-error @enderror"
                                       placeholder="Votre mot de passe actuel" required>
                                @error('password', 'userDeletion')
                                <div class="profile-error-message">
                                    <i class="fas fa-exclamation-circle"></i> {{ $message }}
                                </div>
                                @enderror
                            </div>

                            <div class="profile-alert profile-alert-warning">
                                <i class="fas fa-lightbulb me-2"></i>
                                <strong>Conseil :</strong> Contactez l'administrateur avant de procéder si vous avez des doutes.
                            </div>
                        </div>

                        <div class="modal-footer border-0">
                            <button type="button" class="profile-button profile-button-secondary"
                                    data-bs-dismiss="modal">
                                <i class="fas fa-times me-2"></i> Annuler
                            </button>
                            <button type="submit" class="profile-button profile-button-danger">
                                <i class="fas fa-trash-alt me-2"></i> Oui, supprimer définitivement
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Style spécifique pour le modal -->
<style>
    #confirmDeleteModal .modal-content {
        border: none;
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.15);
    }

    #confirmDeleteModal .modal-header {
        background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
        padding: 20px 24px;
    }

    #confirmDeleteModal .modal-body {
        padding: 24px;
    }

    #confirmDeleteModal .modal-footer {
        padding: 20px 24px;
        background-color: #f8f9fa;
    }

    @media (max-width: 576px) {
        #confirmDeleteModal .modal-dialog {
            margin: 10px;
        }

        #confirmDeleteModal .profile-button {
            width: 100%;
            margin-bottom: 8px;
        }
    }
</style>
