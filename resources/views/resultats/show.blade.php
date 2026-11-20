<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center gap-3">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Résultats : {{ $sondage->titre }}</h2>
            <x-statut-badge :sondage="$sondage" />
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="grid gap-4 sm:grid-cols-3 px-4 sm:px-0">
                <div class="bg-white shadow-sm sm:rounded-lg p-5">
                    <p class="text-sm text-gray-500">Participants</p>
                    <p class="mt-1 text-2xl font-semibold text-gray-900">{{ $total }}</p>
                </div>
                <div class="bg-white shadow-sm sm:rounded-lg p-5">
                    <p class="text-sm text-gray-500">Questions</p>
                    <p class="mt-1 text-2xl font-semibold text-gray-900">{{ $questions->count() }}</p>
                </div>
                <div class="bg-white shadow-sm sm:rounded-lg p-5">
                    <p class="text-sm text-gray-500">Période</p>
                    <p class="mt-1 text-lg font-semibold text-gray-900">{{ $sondage->date_debut->format('d/m/Y') }} – {{ $sondage->date_fin->format('d/m/Y') }}</p>
                </div>
            </div>

            @if ($total === 0)
                <div class="bg-white shadow-sm sm:rounded-lg p-6 text-center text-gray-500">Aucune réponse pour le moment.</div>
            @endif

            @foreach ($questions as $question)
                <section class="bg-white shadow-sm sm:rounded-lg p-6">
                    <div class="flex flex-wrap items-baseline justify-between gap-2">
                        <h3 class="font-semibold text-gray-900">{{ $loop->iteration }}. {{ $question->title }}</h3>
                        <span class="text-xs uppercase tracking-wide text-gray-500">{{ $question->libelleType() }}</span>
                    </div>

                    @if ($question->estAChoix())
                        @if ($question->question_type === 'checkbox')
                            <p class="mt-1 text-xs text-gray-500">Plusieurs réponses possibles : les pourcentages, calculés sur le nombre de participants, peuvent dépasser 100 % au total.</p>
                        @endif
                        <ul class="mt-4 space-y-3">
                            @foreach ($question->statistiques as $ligne)
                                <li>
                                    <div class="flex items-baseline justify-between gap-4 text-sm">
                                        <span class="text-gray-800">{{ $ligne['contenu'] }}</span>
                                        <span class="whitespace-nowrap text-gray-600 tabular-nums">
                                            {{ $ligne['effectif'] }} · {{ number_format($ligne['pourcentage'], 1, ',', ' ') }} %
                                        </span>
                                    </div>
                                    <div class="mt-1 h-2 w-full rounded bg-gray-100" aria-hidden="true">
                                        <div class="h-2 rounded bg-indigo-600" style="width: {{ min(100, $ligne['pourcentage']) }}%"></div>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <p class="mt-1 text-sm text-gray-500">{{ $question->reponses->count() }} réponse(s) libre(s), présentées sans le nom des participants.</p>
                        @if ($question->reponses->isNotEmpty())
                            <ul class="mt-4 max-h-80 overflow-y-auto divide-y divide-gray-100 rounded-md border border-gray-200 text-sm">
                                @foreach ($question->reponses as $reponse)
                                    <li class="px-4 py-2 text-gray-700 whitespace-pre-line">{{ $reponse->texte }}</li>
                                @endforeach
                            </ul>
                        @endif
                    @endif
                </section>
            @endforeach

            <div class="px-4 sm:px-0">
                <x-link-button :href="route('sondages.show', $sondage)">Retour au sondage</x-link-button>
            </div>
        </div>
    </div>
</x-app-layout>
