@props(['url'])
<tr>
    <td class="header">
        <a href="{{ $url ?? config('app.url') }}" style="display: inline-block;">
            <div class="logo" style="height: 80px; width: 160px; display: inline-block;">
                <flux:image.logo variant="standard" size="md" />
            </div>
        </a>
    </td>
</tr>
