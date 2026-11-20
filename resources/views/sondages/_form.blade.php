{{-- Formulaire commun à la création et à la modification d'un sondage. --}}
@php
    $types = App\Models\Question::TYPES;
    $maxQuestions = App\Http\Requests\SondageRequest::MAX_QUESTIONS;
    $maxOptions = App\Http\Requests\SondageRequest::MAX_OPTIONS;
@endphp

@if ($errors->any())
    <div class="mb-6 rounded-md border border-red-200 bg-red-50 p-4 text-sm text-red-700" role="alert">
        <p class="font-semibold">Le sondage n'a pas été enregistré :</p>
        <ul class="mt-2 list-disc list-inside space-y-1">
            @foreach (collect($errors->all())->unique() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="space-y-6">
    <div>
        <x-input-label for="titre" value="Titre du sondage" />
        <x-text-input id="titre" name="titre" type="text" class="mt-1 block w-full" maxlength="255"
                      :value="old('titre', $sondage->titre)" required autofocus />
        <x-input-error class="mt-2" :messages="$errors->get('titre')" />
    </div>

    <div>
        <x-input-label for="description" value="Description (facultative)" />
        <textarea id="description" name="description" rows="3" maxlength="2000"
                  class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">{{ old('description', $sondage->description) }}</textarea>
        <x-input-error class="mt-2" :messages="$errors->get('description')" />
    </div>

    <div class="grid gap-6 sm:grid-cols-2">
        <div>
            <x-input-label for="date_debut" value="Date de début" />
            <x-text-input id="date_debut" name="date_debut" type="date" class="mt-1 block w-full"
                          :value="old('date_debut', $sondage->date_debut?->format('Y-m-d'))" required />
            <x-input-error class="mt-2" :messages="$errors->get('date_debut')" />
        </div>
        <div>
            <x-input-label for="date_fin" value="Date de fin (incluse)" />
            <x-text-input id="date_fin" name="date_fin" type="date" class="mt-1 block w-full"
                          :value="old('date_fin', $sondage->date_fin?->format('Y-m-d'))" required />
            <x-input-error class="mt-2" :messages="$errors->get('date_fin')" />
        </div>
    </div>

    <div x-data="formulaireQuestions(@js($questions), @js($errors->getMessages()))" class="border-t border-gray-100 pt-6">
        <div class="flex items-center justify-between">
            <h3 class="text-lg font-semibold text-gray-900">Questions</h3>
            <span class="text-sm text-gray-500" x-text="questions.length + ' / {{ $maxQuestions }}'"></span>
        </div>
        <p class="mt-1 text-sm text-gray-500">Toutes les questions sont obligatoires pour les participants. Une question à choix propose au moins deux options.</p>

        <template x-for="(question, i) in questions" :key="question.cle">
            <fieldset class="mt-4 rounded-lg border border-gray-200 p-4 space-y-4">
                <div class="flex items-center justify-between">
                    <legend class="font-medium text-gray-900" x-text="'Question ' + (i + 1)"></legend>
                    <button type="button" class="text-sm text-red-600 hover:text-red-800 disabled:opacity-40"
                            x-on:click="retirerQuestion(i)" x-bind:disabled="questions.length === 1">
                        Retirer la question
                    </button>
                </div>

                <div>
                    <label class="block font-medium text-sm text-gray-700" x-bind:for="'question-' + i + '-titre'">Intitulé</label>
                    <input type="text" maxlength="255" required x-model="question.title"
                           x-bind:id="'question-' + i + '-titre'" x-bind:name="'questions[' + i + '][title]'"
                           class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                    <p class="mt-2 text-sm text-red-600" x-show="erreur(i, 'title')" x-text="erreur(i, 'title')"></p>
                </div>

                <div>
                    <label class="block font-medium text-sm text-gray-700" x-bind:for="'question-' + i + '-precision'">Précision (facultative)</label>
                    <input type="text" maxlength="1000" x-model="question.question"
                           x-bind:id="'question-' + i + '-precision'" x-bind:name="'questions[' + i + '][question]'"
                           class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                </div>

                <div>
                    <label class="block font-medium text-sm text-gray-700" x-bind:for="'question-' + i + '-type'">Type de réponse</label>
                    <select x-model="question.type_question" x-bind:id="'question-' + i + '-type'"
                            x-bind:name="'questions[' + i + '][type_question]'" x-on:change="preparerOptions(question)"
                            class="mt-1 block w-full sm:w-72 border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                        @foreach ($types as $valeur => $libelle)
                            <option value="{{ $valeur }}">{{ $libelle }}</option>
                        @endforeach
                    </select>
                </div>

                <div x-show="question.type_question !== 'text'" class="space-y-2">
                    <p class="font-medium text-sm text-gray-700">Options</p>
                    <template x-for="(option, j) in question.options" :key="j">
                        <div class="flex items-center gap-2">
                            <input type="text" maxlength="255" x-model="question.options[j]"
                                   x-bind:name="question.type_question !== 'text' ? 'questions[' + i + '][options][]' : null"
                                   x-bind:required="question.type_question !== 'text'"
                                   x-bind:aria-label="'Option ' + (j + 1) + ' de la question ' + (i + 1)"
                                   class="block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                            <button type="button" class="text-sm text-gray-500 hover:text-red-600 disabled:opacity-40"
                                    x-on:click="question.options.splice(j, 1)" x-bind:disabled="question.options.length <= 2">
                                Retirer
                            </button>
                        </div>
                    </template>
                    <button type="button" class="text-sm font-medium text-indigo-600 hover:text-indigo-800"
                            x-on:click="question.options.push('')" x-show="question.options.length < {{ $maxOptions }}">
                        + Ajouter une option
                    </button>
                    <p class="text-sm text-red-600" x-show="erreur(i, 'options')" x-text="erreur(i, 'options')"></p>
                </div>
            </fieldset>
        </template>

        <button type="button" class="mt-4 text-sm font-medium text-indigo-600 hover:text-indigo-800"
                x-on:click="ajouterQuestion()" x-show="questions.length < {{ $maxQuestions }}">
            + Ajouter une question
        </button>
    </div>
</div>

@once
    @push('scripts')
        <script>
            function formulaireQuestions(questions, erreurs) {
                let compteur = 0;

                return {
                    questions: questions.map((question) => ({ ...question, options: [...question.options], cle: compteur++ })),
                    erreursInitiales: erreurs,

                    ajouterQuestion() {
                        this.erreursInitiales = {};
                        this.questions.push({ title: '', question: '', type_question: 'text', options: [], cle: compteur++ });
                    },
                    retirerQuestion(index) {
                        this.erreursInitiales = {};
                        this.questions.splice(index, 1);
                    },
                    preparerOptions(question) {
                        while (question.type_question !== 'text' && question.options.length < 2) {
                            question.options.push('');
                        }
                    },
                    erreur(index, champ) {
                        const messages = Object.entries(this.erreursInitiales)
                            .filter(([cle]) => cle === `questions.${index}.${champ}` || cle.startsWith(`questions.${index}.${champ}.`))
                            .flatMap(([, liste]) => liste);

                        return messages[0] ?? '';
                    },
                };
            }
        </script>
    @endpush
@endonce
