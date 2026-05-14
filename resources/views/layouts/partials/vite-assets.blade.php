@php
    $assets = $assets ?? [];
    $viteIsAvailable = file_exists(public_path('hot')) || file_exists(public_path('build/manifest.json'));
@endphp

@if($viteIsAvailable && ! empty($assets))
    @vite($assets)
@endif
