@props(['route' => '', 'showMemberFilter' => false, 'members' => []])

<div class="card shadow-sm mb-4">
    <div class="card-header bg-light">
        <h5 class="mb-0"><i class="bi bi-funnel"></i> Filtres</h5>
    </div>
    <div class="card-body">
        <form method="GET" action="{{ $route }}" class="row g-3">
            {{ $slot }}

            <div class="col-md-12">
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-funnel"></i> Appliquer
                    </button>
                    <a href="{{ $route }}" class="btn btn-outline-secondary">
                        <i class="bi bi-x-circle"></i> Réinitialiser
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>
