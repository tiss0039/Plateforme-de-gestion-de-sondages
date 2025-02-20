<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Modifier le Sondage') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">

                    <h3 class="text-2xl font-bold mb-6 text-center">✏️ Modifier le Sondage</h3>

                    <!-- Affichage des erreurs -->
                    @if ($errors->any())
                        <div class="mb-4 p-4 bg-red-100 text-red-700 rounded-lg">
                            <ul class="list-disc list-inside">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <!-- Formulaire de modification -->
                    <form action="{{ route('sondages.update', $sondage) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="mb-4">
                            <label for="titre" class="block text-gray-700 font-semibold mb-2">Titre du sondage</label>
                            <input type="text" id="titre" name="titre" value="{{ old('titre', $sondage->titre) }}"
                                   class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                        </div>

                        <div class="mb-4">
                            <label for="description" class="block text-gray-700 font-semibold mb-2">Description</label>
                            <textarea id="description" name="description" rows="3"
                                      class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">{{ old('description', $sondage->description) }}</textarea>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="date_debut" class="block text-gray-700 font-semibold mb-2">Date de début</label>
                                <input type="date" id="date_debut" name="date_debut" value="{{ old('date_debut', $sondage->date_debut) }}"
                                       class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                            </div>

                            <div>
                                <label for="date_fin" class="block text-gray-700 font-semibold mb-2">Date de fin</label>
                                <input type="date" id="date_fin" name="date_fin" value="{{ old('date_fin', $sondage->date_fin) }}"
                                       class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                            </div>
                        </div>

                        <div class="mt-6 flex justify-between">
                            <a href="{{ route('sondages.index') }}" 
                               class="px-6 py-3 bg-gray-500 text-white rounded-lg shadow-md hover:bg-gray-700 transition">
                                Annuler
                            </a>
                            
                            <button type="submit" 
                                    class="px-6 py-3 bg-blue-500 text-black font-semibold rounded-lg shadow-md hover:bg-blue-700 transition">
                                 Sauvegarder les modifications
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>

