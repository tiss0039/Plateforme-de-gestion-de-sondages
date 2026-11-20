<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Modifier le sondage</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <form method="POST" action="{{ route('sondages.update', $sondage) }}" class="bg-white shadow-sm sm:rounded-lg p-6">
                @csrf
                @method('PUT')

                @if ($sondage->statut !== App\Enums\SondageStatus::EN_ATTENTE)
                    <div class="mb-6 rounded-md border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
                        Après modification, le sondage repassera « En attente » et devra de nouveau être validé par un administrateur.
                    </div>
                @endif

                @include('sondages._form')

                <div class="mt-8 flex items-center justify-between gap-4 border-t border-gray-100 pt-6">
                    <x-link-button :href="route('sondages.show', $sondage)">Annuler</x-link-button>
                    <x-primary-button>Enregistrer les modifications</x-primary-button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
