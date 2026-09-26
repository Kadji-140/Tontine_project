<x-app-layout>
    <div class="container py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>Registre des Sanctions</h1>
            <a href="{{ route('sanctions.create') }}" class="btn btn-danger">Nouvelle Sanction</a>
        </div>

        @if(session('success')) <div class="alert alert-success">{{ session('success') }}</div> @endif
        @if(session('error')) <div class="alert alert-danger">{{ session('error') }}</div> @endif

        <div class="card shadow-sm" style="overflow-x: auto">
            <div class="card-body">
                <table class="table table-hover align-middle">
                    <thead>
                    <tr>
                        <th>Membre</th>
                        <th>Motif</th>
                        <th>Montant</th>
                        <th>Date</th>
                        <th>État</th>
                        <th>Action</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($sanctions as $sanction)
                        <tr>
                            <td class="fw-bold">{{ $sanction->user->name }}</td>
                            <td>{{ $sanction->motif }}</td>
                            <td class="text-danger fw-bold">{{ number_format($sanction->montant, 0, ',', ' ') }} FCFA</td>
                            <td>{{ $sanction->created_at->format('d/m/Y') }}</td>
                            <td>
                                @if($sanction->est_reglee)
                                    <span class="badge bg-success">Payée</span>
                                @else
                                    <span class="badge bg-warning text-dark">Impayée</span>
                                @endif
                            </td>
                            <td>
                                @if(!$sanction->est_reglee)
                                    <form action="{{ route('sanctions.payer', $sanction) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('PUT')
                                        <button type="submit" class="btn btn-sm btn-success" title="Encaisser maintenant">
                                            Encaisser
                                        </button>
                                    </form>

                                    <form action="{{ route('sanctions.destroy', $sanction) }}" method="POST" class="d-inline" onsubmit="return confirm('Annuler cette sanction ?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-secondary">Annuler</button>
                                    </form>
                                @else
                                    <span class="text-muted small">Archivée</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
