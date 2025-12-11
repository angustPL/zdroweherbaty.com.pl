@props(['title' => null])

<div class="panel">
    @if ($title)
        <h3>{{ $title }}</h3>
    @endif

    {{ $slot }}
</div>
