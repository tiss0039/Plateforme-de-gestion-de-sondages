@props(['sondage'])

@php
    $sac = $errors->getBag('refus'.$sondage->id);
@endphp

<details class="group" @if ($sac->any()) open @endif>
    <summary class="inline-flex cursor-pointer list-none items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50">
        Refuser…
    </summary>
    <form method="POST" action="{{ route('moderation.reject', $sondage) }}" class="mt-3 space-y-2">
        @csrf
        <label for="motif-{{ $sondage->id }}" class="block text-sm font-medium text-gray-700">Motif du refus (communiqué au créateur)</label>
        <textarea id="motif-{{ $sondage->id }}" name="motif_refus" rows="2" maxlength="1000" required
                  class="block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm">{{ $sac->any() ? old('motif_refus') : '' }}</textarea>
        <x-input-error :messages="$sac->get('motif_refus')" />
        <x-danger-button>Confirmer le refus</x-danger-button>
    </form>
</details>
