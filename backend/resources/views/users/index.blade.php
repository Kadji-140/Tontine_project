<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Gestion des Membres') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    
                    @if(session('success'))
                        <div class="alert alert-success mb-4">{{ session('success') }}</div>
                    @endif
                    @if(session('error'))
                        <div class="alert alert-danger mb-4">{{ session('error') }}</div>
                    @endif

                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Nom</th>
                                <th>Email</th>
                                <th>Rôle</th>
                                <th>Statut</th>

                                <th>Date d'inscription</th>
                                <th class="text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($users as $user)
                                <tr class="{{ !$user->is_active ? 'table-warning' : '' }}">
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="fw-bold">{{ $user->name }}</div>
                                        </div>
                                    </td>
                                    <td>{{ $user->email }}</td>
                                    <td>
                                        <span class="badge {{ $user->role === 'admin' ? 'bg-danger' : ($user->role === 'tresorier' ? 'bg-warning text-dark' : 'bg-secondary') }}">
                                            {{ ucfirst($user->role) }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($user->is_active)
                                            <span class="badge bg-success">Actif</span>
                                        @else
                                            <span class="badge bg-danger">En attente</span>
                                        @endif
                                    </td>

                                    <td>{{ $user->created_at->format('d/m/Y H:i') }}</td>
                                    <td class="text-end">
                                        @if($user->id !== auth()->id())
                                            <form action="{{ route('users.toggle', $user) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('PATCH')
                                                @if($user->is_active)
                                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Désactiver">
                                                        <i class="fas fa-ban"></i> Bloquer
                                                    </button>
                                                @else
                                                    <button type="submit" class="btn btn-sm btn-success" title="Activer">
                                                        <i class="fas fa-check"></i> Valider
                                                    </button>
                                                @endif
                                            </form>
                                            
                                            <form action="{{ route('users.destroy', $user) }}" method="POST" class="d-inline ms-1" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce dossier ?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-secondary" title="Supprimer / Annuler">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
