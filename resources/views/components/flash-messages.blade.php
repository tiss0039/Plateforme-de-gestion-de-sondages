@php
    $messages = array_filter([
        'success' => session('success'),
        'info' => session('info'),
        'error' => session('error'),
    ]);
    $styles = [
        'success' => 'border-green-200 bg-green-50 text-green-800',
        'info' => 'border-blue-200 bg-blue-50 text-blue-800',
        'error' => 'border-red-200 bg-red-50 text-red-800',
    ];
@endphp

@if ($messages)
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-6 space-y-2">
        @foreach ($messages as $type => $message)
            <div role="status" class="rounded-md border px-4 py-3 text-sm {{ $styles[$type] }}">{{ $message }}</div>
        @endforeach
    </div>
@endif
