<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Créer un sondage</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <form method="POST" action="{{ route('sondages.store') }}" class="bg-white shadow-sm sm:rounded-lg p-6">
                @csrf

                @include('sondages._form')

                <div class="mt-8 flex items-center justify-between gap-4 border-t border-gray-100 pt-6">
                    <x-link-button :href="route('dashboard')">Annuler</x-link-button>
                    <x-primary-button>Créer le sondage</x-primary-button>
                </div>
                <p class="mt-4 text-sm text-gray-500">Le sondage sera soumis à la validation d'un administrateur avant de pouvoir être ouvert aux réponses.</p>
            </form>
        </div>
    </div>
</x-app-layout>
