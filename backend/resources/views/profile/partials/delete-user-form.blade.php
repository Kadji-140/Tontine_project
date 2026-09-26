<div class="card border-danger shadow-sm">
    <div class="card-header bg-white border-bottom border-danger py-3">
        <div class="d-flex align-items-center">
            <div class="bg-danger bg-opacity-10 rounded-circle p-2 me-3">
                <i class="fas fa-exclamation-triangle text-danger"></i>
            </div>
            <div>
                <h5 class="mb-0 fw-bold text-danger">Supprimer le Compte</h5>
                <small class="text-muted">Action irréversible</small>
            </div>
        </div>
    </div>

    <div class="card-body p-4">
        <div class="alert alert-light border-danger text-danger mb-4 d-flex align-items-center">
            <i class="fas fa-exclamation-circle me-3 fs-4"></i>
            <div>
                <strong>Attention :</strong> Cette action effacera définitivement toutes vos données.
            </div>
        </div>

        <p class="text-muted mb-4">
            Avant de supprimer votre compte, assurez-vous de sauvegarder tout ce qui est important.
        </p>

        <button type="button" class="btn btn-outline-danger"
                data-bs-toggle="modal" data-bs-target="#confirmDeleteModal">
            <i class="fas fa-trash-alt me-2"></i> Je veux supprimer mon compte
        </button>

        <!-- Modal de confirmation -->
        <div class="modal fade" id="confirmDeleteModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow-lg">
                    <div class="modal-header bg-white text-danger border-bottom">
                        <h5 class="modal-title fw-bold">
                            <i class="fas fa-exclamation-circle me-2"></i> Confirmer la suppression
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>

                    <form method="post" action="{{ route('profile.destroy') }}">
                        @csrf
                        @method('delete')

                        <div class="modal-body p-4">
                            <p class="text-muted mb-4">
                                Pour confirmer, veuillez entrer votre mot de passe.
                                <br>
                                <span class="fw-bold text-danger">Cette action est définitive.</span>
                            </p>

                            <div class="mb-3">
                                <label for="password" class="form-label fw-bold">Mot de passe actuel</label>
                                <input type="password" name="password" id="password"
                                       class="form-control"
                                       placeholder="Entrez votre mot de passe" required>
                                @error('password', 'userDeletion')
                                <div class="text-danger small mt-1">
                                    <i class="fas fa-times-circle me-1"></i> {{ $message }}
                                </div>
                                @enderror
                            </div>
                        </div>

                        <div class="modal-footer border-0 bg-light">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                            <button type="submit" class="btn btn-danger">
                                <i class="fas fa-trash mb-1 me-1"></i> Supprimer définitivement
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
