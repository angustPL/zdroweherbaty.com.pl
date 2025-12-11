@props(['headers' => [], 'class' => 'email-table'])

<table class="{{ $class }}">
    @if (!empty($headers))
        <thead>
            <tr>
                @foreach ($headers as $header)
                    <th>{{ $header }}</th>
                @endforeach
            </tr>
        </thead>
    @endif

    <tbody>
        {{ $slot }}
    </tbody>
</table>
