<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Tableau de bord</h2>
            @can('create', App\Models\Sondage::class)
                <x-link-button variant="primary" :href="route('sondages.create')">Créer un sondage</x-link-button>
            @endcan
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="grid gap-4 sm:grid-cols-3 px-4 sm:px-0">
                <div class="bg-white shadow-sm sm:rounded-lg p-5">
                    <p class="text-sm text-gray-500">Mes sondages</p>
                    <p class="mt-1 text-2xl font-semibold text-gray-900">{{ $sondages->count() }}</p>
                </div>
                <a href="{{ route('participations.index') }}" class="bg-white shadow-sm sm:rounded-lg p-5 hover:ring-2 hover:ring-indigo-200 transition">
                    <p class="text-sm text-gray-500">Sondages auxquels répondre</p>
                    <p class="mt-1 text-2xl font-semibold text-gray-900">{{ $sondagesOuverts }}</p>
                </a>
                @if (! is_null($enAttenteDeValidation))
                    <a href="{{ route('moderation.index') }}" class="bg-white shadow-sm sm:rounded-lg p-5 hover:ring-2 hover:ring-indigo-200 transition">
                        <p class="text-sm text-gray-500">En attente de validation</p>
                        <p class="mt-1 text-2xl font-semibold text-gray-900">{{ $enAttenteDeValidation }}</p>
                    </a>
                @else
                    <div class="bg-white shadow-sm sm:rounded-lg p-5">
                        <p class="text-sm text-gray-500">Réponses reçues</p>
                        <p class="mt-1 text-2xl font-semibold text-gray-900">{{ $sondages->sum('participations_count') }}</p>
                    </div>
                @endif
            </div>

            <div class="bg-white shadow-sm sm:rounded-lg">
                <div class="px-6 py-4 border-b border-gray-100">
                    <h3 class="font-semibold text-gray-900">Mes sondages</h3>
                </div>

                @if ($sondages->isEmpty())
                    <div class="px-6 py-10 text-center text-gray-500">
                        @can('create', App\Models\Sondage::class)
                            <p>Vous n'avez encore créé aucun sondage.</p>
                            <x-link-button class="mt-4" :href="route('sondages.create')">Créer mon premier sondage</x-link-button>
                        @else
                            <p>Votre compte permet de répondre aux sondages ouverts, mais pas d'en créer.</p>
                        @endcan
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 text-sm">
                            <thead class="bg-gray-50 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                <tr>
                                    <th class="px-6 py-3">Titre</th>
                                    <th class="px-6 py-3">Statut</th>
                                    <th class="px-6 py-3">Période</th>
                                    <th class="px-6 py-3 text-right">Questions</th>
                                    <th class="px-6 py-3 text-right">Réponses</th>
                                    <th class="px-6 py-3 text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach ($sondages as $sondage)
                                    <tr class="align-top">
                                        <td class="px-6 py-4">
                                            <a href="{{ route('sondages.show', $sondage) }}" class="font-medium text-gray-900 hover:text-indigo-600">{{ $sondage->titre }}</a>
                                            @if ($sondage->description)
                                                <p class="text-gray-500">{{ Str::limit($sondage->description, 70) }}</p>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4"><x-statut-badge :sondage="$sondage" /></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-gray-600">
                                            {{ $sondage->date_debut->format('d/m/Y') }} – {{ $sondage->date_fin->format('d/m/Y') }}
                                        </td>
                                        <td class="px-6 py-4 text-right text-gray-600">{{ $sondage->questions_count }}</td>
                                        <td class="px-6 py-4 text-right text-gray-600">{{ $sondage->participations_count }}</td>
                                        <td class="px-6 py-4 text-right whitespace-nowrap space-x-3">
                                            <a href="{{ route('sondages.show', $sondage) }}" class="text-indigo-600 hover:text-indigo-800">Voir</a>
                                            @can('update', $sondage)
                                                <a href="{{ route('sondages.edit', $sondage) }}" class="text-indigo-600 hover:text-indigo-800">Modifier</a>
                                            @endcan
                                            @can('viewResults', $sondage)
                                                <a href="{{ route('sondages.resultats', $sondage) }}" class="text-indigo-600 hover:text-indigo-800">Résultats</a>
                                            @endcan
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
