<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Sondages ouverts</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm sm:rounded-lg">
                <div class="px-6 py-4 border-b border-gray-100">
                    <p class="text-sm text-gray-500">Sondages des autres utilisateurs actuellement ouverts aux réponses. Une seule réponse est acceptée par participant.</p>
                    @unless ($peutRepondre)
                        <p class="mt-2 text-sm text-amber-700">Votre compte n'a pas l'autorisation de répondre aux sondages.</p>
                    @endunless
                </div>

                @forelse ($sondages as $sondage)
                    <div class="px-6 py-5 border-b border-gray-100 last:border-0 flex flex-wrap items-center justify-between gap-4">
                        <div class="max-w-2xl">
                            <p class="font-medium text-gray-900">{{ $sondage->titre }}</p>
                            @if ($sondage->description)
                                <p class="text-sm text-gray-600">{{ Str::limit($sondage->description, 140) }}</p>
                            @endif
                            <p class="mt-1 text-xs text-gray-500">{{ $sondage->questions_count }} question(s) · jusqu'au {{ $sondage->date_fin->format('d/m/Y') }}</p>
                        </div>
                        @if ($sondage->deja_repondu)
                            <span class="inline-flex items-center rounded-full bg-green-50 px-3 py-1 text-sm font-medium text-green-700 ring-1 ring-inset ring-green-600/20">Déjà répondu ✓</span>
                        @elseif ($peutRepondre)
                            <x-link-button variant="primary" :href="route('participations.create', $sondage)">Répondre</x-link-button>
                        @endif
                    </div>
                @empty
                    <p class="px-6 py-10 text-center text-gray-500">Aucun sondage n'est ouvert aux réponses pour le moment.</p>
                @endforelse
            </div>
        </div>
    </div>
</x-app-layout>
