<form method="post" action="{{ route('profile.update') }}" enctype="multipart/form-data">
    @csrf
    @method('patch')

    <div class="row mb-4">
        <!-- Avatar Upload -->
        <div class="col-md-12 text-center mb-4">
            <div class="position-relative d-inline-block">
                <div class="avatar-preview rounded-circle shadow-sm" id="avatarPreview" style="
                    width: 120px; 
                    height: 120px; 
                    background-image: url('{{ Auth::user()->avatar ? asset('storage/'.Auth::user()->avatar) : '' }}');
                    background-color: #e2e8f0;
                    background-size: cover; 
                    background-position: center;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    overflow: hidden;
                    border: 4px solid #fff;
                ">
                    @if(!Auth::user()->avatar)
                        <span class="text-primary fw-bold fs-1">{{ strtoupper(substr(Auth::user()->name, 0, 2)) }}</span>
                    @endif
                </div>
                <label for="avatarUpload" class="btn btn-sm btn-primary position-absolute bottom-0 end-0 rounded-circle shadow-sm" style="width: 35px; height: 35px; display: flex; align-items: center; justify-content: center;">
                    <i class="fas fa-camera"></i>
                </label>
                <input type="file" id="avatarUpload" name="avatar" accept=".png, .jpg, .jpeg" class="d-none"/>
            </div>
            <p class="small text-muted mt-2">Cliquez sur la caméra pour modifier</p>
        </div>
    </div>

    <div class="row g-3">
        <!-- Nom Complet -->
        <div class="col-md-6">
            <label for="name" class="form-label fw-bold">Nom Complet</label>
            <input type="text" class="form-control" id="name" name="name" value="{{ old('name', Auth::user()->name) }}" required>
             @error('name') <small class="text-danger">{{ $message }}</small> @enderror
        </div>

        <!-- Email -->
        <div class="col-md-6">
            <label for="email" class="form-label fw-bold">Email</label>
            <input type="email" class="form-control" id="email" name="email" value="{{ old('email', Auth::user()->email) }}" required>
            @error('email') <small class="text-danger">{{ $message }}</small> @enderror
        </div>

        <!-- Téléphone -->
        <div class="col-md-6">
            <label for="phone" class="form-label fw-bold">Téléphone</label>
            <input type="text" class="form-control" id="phone" name="phone" value="{{ old('phone', Auth::user()->phone) }}">
            @error('phone') <small class="text-danger">{{ $message }}</small> @enderror
        </div>

        <!-- Profession -->
        <div class="col-md-6">
            <label for="profession" class="form-label fw-bold text-primary">Profession</label>
            <input type="text" class="form-control" id="profession" name="profession" value="{{ old('profession', Auth::user()->profession) }}" placeholder="Ex: Enseignant, Commerçant...">
            @error('profession') <small class="text-danger">{{ $message }}</small> @enderror
        </div>

        <!-- Adresse -->
        <div class="col-12">
            <label for="adresse" class="form-label fw-bold text-primary">Adresse de résidence</label>
            <input type="text" class="form-control" id="adresse" name="adresse" value="{{ old('adresse', Auth::user()->adresse) }}" placeholder="Quartier, Ville...">
            @error('adresse') <small class="text-danger">{{ $message }}</small> @enderror
        </div>

        <!-- CNI -->
        <div class="col-md-6">
            <label for="cni" class="form-label fw-bold text-primary">Numéro CNI</label>
            <input type="text" class="form-control" id="cni" name="cni" value="{{ old('cni', Auth::user()->cni) }}" placeholder="Numéro de Carte d'Identité">
            @error('cni') <small class="text-danger">{{ $message }}</small> @enderror
        </div>

        <!-- Bénéficiaire -->
        <div class="col-md-6">
            <label for="beneficiaire" class="form-label fw-bold text-primary">Bénéficiaire (Ayant-droit)</label>
            <input type="text" class="form-control" id="beneficiaire" name="beneficiaire" value="{{ old('beneficiaire', Auth::user()->beneficiaire) }}" placeholder="Nom Complet en cas de décès">
             @error('beneficiaire') <small class="text-danger">{{ $message }}</small> @enderror
        </div>
    </div>

    <!-- Actions -->
    <div class="d-flex justify-content-end mt-4 pt-3 border-top">
        @if (session('status') === 'profile-updated')
            <span class="text-success me-3 align-self-center fw-bold"><i class="fas fa-check-circle"></i> Enregistré</span>
        @endif
        <button type="submit" class="btn btn-primary fw-bold px-4">
            <i class="fas fa-save me-2"></i> Enregistrer
        </button>
    </div>
</form>

<script>
    document.getElementById('avatarUpload').addEventListener('change', function(e) {
        if (e.target.files && e.target.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const preview = document.getElementById('avatarPreview');
                preview.style.backgroundImage = `url(${e.target.result})`;
                preview.innerHTML = ''; // Clear initials
            }
            reader.readAsDataURL(e.target.files[0]);
        }
    });
</script>
    });
</script>
