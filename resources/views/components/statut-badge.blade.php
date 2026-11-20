@props(['sondage'])

<span class="inline-flex items-center gap-1 whitespace-nowrap rounded-full px-2.5 py-0.5 text-xs font-medium ring-1 ring-inset {{ $sondage->statut->couleur() }}">
    {{ $sondage->statut->libelle() }}
</span>
@if ($precision = $sondage->precisionStatut())
    <span class="block text-xs text-gray-500 mt-1">{{ $precision }}</span>
@endif
