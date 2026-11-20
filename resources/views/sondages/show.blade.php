<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center gap-3">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ $sondage->titre }}</h2>
            <x-statut-badge :sondage="$sondage" />
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if ($sondage->statut === App\Enums\SondageStatus::REFUSE && $sondage->motif_refus)
                <div class="rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                    <p class="font-semibold">Sondage refusé{{ $sondage->moderateur ? ' par '.$sondage->moderateur->name : '' }}</p>
                    <p class="mt-1">{{ $sondage->motif_refus }}</p>
                    <p class="mt-2">Modifiez le sondage pour le soumettre de nouveau à validation.</p>
                </div>
            @endif

            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                @if ($sondage->description)
                    <p class="text-gray-700 whitespace-pre-line">{{ $sondage->description }}</p>
                @endif

                <dl class="mt-4 grid gap-4 text-sm sm:grid-cols-4">
                    <div>
                        <dt class="text-gray-500">Période de réponse</dt>
                        <dd class="font-medium text-gray-900">{{ $sondage->date_debut->format('d/m/Y') }} – {{ $sondage->date_fin->format('d/m/Y') }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500">Créé par</dt>
                        <dd class="font-medium text-gray-900">{{ $sondage->user->name }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500">Questions</dt>
                        <dd class="font-medium text-gray-900">{{ $sondage->questions->count() }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500">Réponses reçues</dt>
                        <dd class="font-medium text-gray-900">{{ $sondage->participations_count }}</dd>
                    </div>
                </dl>

                <div class="mt-6 flex flex-wrap items-center gap-3 border-t border-gray-100 pt-6">
                    @can('moderate', $sondage)
                        <form method="POST" action="{{ route('moderation.approve', $sondage) }}">
                            @csrf
                            <x-primary-button>Valider</x-primary-button>
                        </form>
                        <x-formulaire-refus :sondage="$sondage" />
                    @endcan

                    @can('open', $sondage)
                        <form method="POST" action="{{ route('sondages.open', $sondage) }}">
                            @csrf
                            <x-primary-button>Ouvrir aux réponses</x-primary-button>
                        </form>
                    @endcan

                    @can('close', $sondage)
                        <form method="POST" action="{{ route('sondages.close', $sondage) }}"
                              onsubmit="return confirm('Clôturer ce sondage ? Il n\'acceptera plus de réponses.')">
                            @csrf
                            <x-primary-button>Clôturer</x-primary-button>
                        </form>
                    @endcan

                    @can('viewResults', $sondage)
                        <x-link-button :href="route('sondages.resultats', $sondage)">Voir les résultats</x-link-button>
                    @endcan

                    @can('update', $sondage)
                        <x-link-button :href="route('sondages.edit', $sondage)">Modifier</x-link-button>
                    @endcan

                    @can('delete', $sondage)
                        <form method="POST" action="{{ route('sondages.destroy', $sondage) }}" class="sm:ms-auto"
                              onsubmit="return confirm('Supprimer définitivement ce sondage et ses réponses ?')">
                            @csrf
                            @method('DELETE')
                            <x-danger-button>Supprimer</x-danger-button>
                        </form>
                    @endcan
                </div>

                @if ($sondage->statut === App\Enums\SondageStatus::EN_ATTENTE && auth()->id() === $sondage->user_id)
                    <p class="mt-4 text-sm text-gray-500">Ce sondage attend la validation d'un administrateur.</p>
                @elseif ($sondage->statut === App\Enums\SondageStatus::VALIDE && auth()->id() === $sondage->user_id && auth()->user()->cannot('open', $sondage))
                    <p class="mt-4 text-sm text-amber-700">La date de fin est dépassée : modifiez les dates pour pouvoir ouvrir le sondage.</p>
                @endif
            </div>

            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <h3 class="text-lg font-semibold text-gray-900">Questions</h3>
                <ol class="mt-4 space-y-4">
                    @foreach ($sondage->questions as $question)
                        <li class="rounded-lg border border-gray-200 p-4">
                            <div class="flex flex-wrap items-baseline justify-between gap-2">
                                <p class="font-medium text-gray-900">{{ $loop->iteration }}. {{ $question->title }}</p>
                                <span class="text-xs uppercase tracking-wide text-gray-500">{{ $question->libelleType() }}</span>
                            </div>
                            @if ($question->question)
                                <p class="mt-1 text-sm text-gray-600">{{ $question->question }}</p>
                            @endif
                            @if ($question->estAChoix())
                                <ul class="mt-2 list-disc list-inside text-sm text-gray-700">
                                    @foreach ($question->options as $option)
                                        <li>{{ $option->contenu }}</li>
                                    @endforeach
                                </ul>
                            @endif
                        </li>
                    @endforeach
                </ol>
            </div>
        </div>
    </div>
</x-app-layout>
