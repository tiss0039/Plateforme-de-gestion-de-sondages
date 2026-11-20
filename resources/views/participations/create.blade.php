<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ $sondage->titre }}</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <form method="POST" action="{{ route('participations.store', $sondage) }}" class="bg-white shadow-sm sm:rounded-lg p-6 space-y-6">
                @csrf

                @if ($sondage->description)
                    <p class="text-gray-700 whitespace-pre-line">{{ $sondage->description }}</p>
                @endif
                <p class="text-sm text-gray-500">Toutes les questions sont obligatoires. Votre réponse ne pourra plus être modifiée après l'envoi.</p>

                @if ($errors->any())
                    <div class="rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700" role="alert">
                        Certaines réponses sont manquantes ou invalides : vérifiez les questions signalées ci-dessous.
                    </div>
                @endif

                @foreach ($sondage->questions as $question)
                    @php
                        $cle = 'reponses.'.$question->id;
                        $ancienne = old($cle);
                        $ancienTexte = is_scalar($ancienne) ? (string) $ancienne : '';
                        $anciensChoix = array_map('strval', array_filter((array) $ancienne, 'is_scalar'));
                        $messages = array_merge($errors->get($cle), $errors->get($cle.'.*') ? array_merge(...array_values($errors->get($cle.'.*'))) : []);
                    @endphp
                    <fieldset class="rounded-lg border p-4 {{ $messages ? 'border-red-300' : 'border-gray-200' }}">
                        <legend class="px-1 font-medium text-gray-900">{{ $loop->iteration }}. {{ $question->title }}</legend>
                        @if ($question->question)
                            <p class="text-sm text-gray-600">{{ $question->question }}</p>
                        @endif

                        <div class="mt-3 space-y-2">
                            @switch($question->question_type)
                                @case('text')
                                    <textarea name="reponses[{{ $question->id }}]" rows="3" maxlength="2000" required
                                              aria-label="Réponse à la question {{ $loop->iteration }}"
                                              class="block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">{{ $ancienTexte }}</textarea>
                                    @break
                                @case('multiple')
                                    @foreach ($question->options as $option)
                                        <label class="flex items-center gap-2 text-gray-700">
                                            <input type="radio" name="reponses[{{ $question->id }}]" value="{{ $option->id }}" required
                                                   @checked($ancienTexte === (string) $option->id)
                                                   class="border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                            {{ $option->contenu }}
                                        </label>
                                    @endforeach
                                    @break
                                @case('checkbox')
                                    <p class="text-xs text-gray-500">Cochez une ou plusieurs réponses.</p>
                                    @foreach ($question->options as $option)
                                        <label class="flex items-center gap-2 text-gray-700">
                                            <input type="checkbox" name="reponses[{{ $question->id }}][]" value="{{ $option->id }}"
                                                   @checked(in_array((string) $option->id, $anciensChoix, true))
                                                   class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                            {{ $option->contenu }}
                                        </label>
                                    @endforeach
                                    @break
                            @endswitch
                        </div>

                        <x-input-error class="mt-2" :messages="array_unique($messages)" />
                    </fieldset>
                @endforeach

                <div class="flex items-center justify-between gap-4 border-t border-gray-100 pt-6">
                    <x-link-button :href="route('participations.index')">Annuler</x-link-button>
                    <x-primary-button>Envoyer mes réponses</x-primary-button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
