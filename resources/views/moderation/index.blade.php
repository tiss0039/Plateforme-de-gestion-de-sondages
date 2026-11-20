<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Validation des sondages</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white shadow-sm sm:rounded-lg">
                <div class="px-6 py-4 border-b border-gray-100">
                    <h3 class="font-semibold text-gray-900">En attente de validation ({{ $enAttente->count() }})</h3>
                    <p class="text-sm text-gray-500">Un sondage validé peut ensuite être ouvert aux réponses par son créateur.</p>
                </div>

                @forelse ($enAttente as $sondage)
                    <div id="sondage-{{ $sondage->id }}" class="px-6 py-5 border-b border-gray-100 last:border-0">
                        <div class="flex flex-wrap items-start justify-between gap-4">
                            <div>
                                <a href="{{ route('sondages.show', $sondage) }}" class="font-medium text-gray-900 hover:text-indigo-600">{{ $sondage->titre }}</a>
                                <p class="text-sm text-gray-500">
                                    Par {{ $sondage->user->name }} · {{ $sondage->questions_count }} question(s) ·
                                    du {{ $sondage->date_debut->format('d/m/Y') }} au {{ $sondage->date_fin->format('d/m/Y') }}
                                </p>
                            </div>
                            <div class="flex flex-wrap items-start gap-3">
                                <x-link-button :href="route('sondages.show', $sondage)">Examiner</x-link-button>
                                <form method="POST" action="{{ route('moderation.approve', $sondage) }}">
                                    @csrf
                                    <x-primary-button>Valider</x-primary-button>
                                </form>
                            </div>
                        </div>
                        <div class="mt-3">
                            <x-formulaire-refus :sondage="$sondage" />
                        </div>
                    </div>
                @empty
                    <p class="px-6 py-10 text-center text-gray-500">Aucun sondage en attente de validation.</p>
                @endforelse
            </div>

            @if ($recents->isNotEmpty())
                <div class="bg-white shadow-sm sm:rounded-lg">
                    <div class="px-6 py-4 border-b border-gray-100">
                        <h3 class="font-semibold text-gray-900">Dernières décisions</h3>
                    </div>
                    <ul class="divide-y divide-gray-100 text-sm">
                        @foreach ($recents as $sondage)
                            <li class="px-6 py-3 flex flex-wrap items-center justify-between gap-2">
                                <a href="{{ route('sondages.show', $sondage) }}" class="text-gray-900 hover:text-indigo-600">{{ $sondage->titre }}</a>
                                <span class="flex items-center gap-3 text-gray-500">
                                    <x-statut-badge :sondage="$sondage" />
                                    {{ $sondage->modere_le->format('d/m/Y H:i') }}{{ $sondage->moderateur ? ' · '.$sondage->moderateur->name : '' }}
                                </span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
