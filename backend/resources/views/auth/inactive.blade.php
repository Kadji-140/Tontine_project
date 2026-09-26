<x-guest-layout>
    <div class="mb-4 text-sm text-gray-600 dark:text-gray-400">
        {{ __('Votre compte a été créé avec succès ! Cependant, il est actuellement en attente de validation par un administrateur.') }}
        <br><br>
        {{ __('Vous recevrez une notification une fois votre compte activé. Merci de votre patience.') }}
    </div>

    <div class="mt-4 flex items-center justify-between">
        <form method="POST" action="{{ route('logout') }}">
            @csrf

            <button type="submit" class="underline text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800">
                {{ __('Se déconnecter') }}
            </button>
        </form>
    </div>
</x-guest-layout>
