<x-guest-layout>
    <div class="space-y-4 py-2">
        <h1 class="text-xl font-semibold text-gray-900">{{ config('app.name') }}</h1>
        <p class="text-sm text-gray-600">
            Créez des sondages, faites-les valider par un administrateur, ouvrez-les aux réponses
            puis consultez les résultats.
        </p>
        <ol class="list-decimal list-inside space-y-1 text-sm text-gray-600">
            <li>Création du sondage et de ses questions</li>
            <li>Validation par un administrateur</li>
            <li>Ouverture aux participants</li>
            <li>Clôture et résultats</li>
        </ol>

        <div class="flex flex-wrap gap-3 pt-2">
            @auth
                <x-link-button variant="primary" :href="route('dashboard')">Tableau de bord</x-link-button>
            @else
                <x-link-button variant="primary" :href="route('login')">Se connecter</x-link-button>
                @if (Route::has('register'))
                    <x-link-button :href="route('register')">Créer un compte</x-link-button>
                @endif
            @endauth
        </div>
    </div>
</x-guest-layout>
