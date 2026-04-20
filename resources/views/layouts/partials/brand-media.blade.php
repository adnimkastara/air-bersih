@php
    $imageUrl = $imageUrl ?? null;
    $appName = $appName ?? 'Brand';
    $initials = $initials ?? 'BR';
    $imgClass = $imgClass ?? '';
    $fallbackClass = $fallbackClass ?? '';
    $fallbackTextClass = $fallbackTextClass ?? '';
@endphp

@if(!empty($imageUrl))
    <img src="{{ $imageUrl }}" alt="Logo {{ $appName }}" class="{{ $imgClass }}" loading="lazy">
@elseif(!empty($initials))
    <span class="{{ $fallbackClass }}">{{ $initials }}</span>
@else
    <span class="{{ $fallbackTextClass }}">{{ $appName }}</span>
@endif
