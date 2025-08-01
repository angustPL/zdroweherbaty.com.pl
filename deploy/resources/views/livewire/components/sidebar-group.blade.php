{{--
    $groups - tablica grup (children z modelu Group)
    $parentPath - pełna ścieżka nadrzędna (opcjonalnie do generowania href)
--}}
@foreach ($groups as $groupName => $groupData)
    @if (!empty($groupData['children']))
        @php
            // Sprawdź czy ta grupa powinna być rozwinięta
            $currentPath = ($parentPath ?? '') . ($parentPath ? '\\' : '') . $groupName;
            $shouldExpand = false;

            if (request()->routeIs('grupa')) {
                $currentGrupa = request()->route('grupa');
                if ($currentGrupa) {
                    $decodedGrupa = urldecode($currentGrupa);
                    $currentGroupPath = str_replace(config('enova.grupa_url_separator'), '\\', $decodedGrupa);

                    // Sprawdź czy aktualna ścieżka zawiera tę grupę jako rodzic
                    $shouldExpand = str_contains($currentGroupPath, $currentPath . '\\');
                }
            }
        @endphp
        <flux:navlist.group :heading="$groupName" expandable :expanded="$shouldExpand">
            @include('livewire.components.sidebar-group', [
                'groups' => $groupData['children'],
                'parentPath' => ($parentPath ?? '') . ($parentPath ? '\\' : '') . $groupName,
            ])
        </flux:navlist.group>
    @else
        @php
            // Budowanie pełnej ścieżki grupy dla linku
            $groupPath = ($parentPath ?? '') . ($parentPath ? '\\' : '') . $groupName;
            // Konwersja na format URL z użyciem spójnika z konfiguracji
            $urlPath = str_replace('\\', config('enova.grupa_url_separator'), $groupPath);
            // Kodowanie URL dla bezpieczeństwa
            $encodedPath = urlencode($urlPath);

            // Sprawdź czy to jest aktualna grupa
            $isCurrent = false;
            if (request()->routeIs('grupa')) {
                $currentGrupa = request()->route('grupa');
                if ($currentGrupa) {
                    // Porównaj po dekodowaniu dla polskich znaków
                    $decodedCurrent = urldecode($currentGrupa);
                    $isCurrent = $decodedCurrent === $urlPath;
                }
            }
        @endphp
        @if (empty($parentPath))
            @if (!empty($encodedPath))
                <flux:navlist.item href="{{ route('grupa', ['group' => $encodedPath]) }}" :current="$isCurrent"
                    icon="x-mark">
                    {{ $groupName }}
                </flux:navlist.item>
            @endif
        @else
            @if (!empty($encodedPath))
                <flux:navlist.item href="{{ route('grupa', ['group' => $encodedPath]) }}" :current="$isCurrent">
                    {{ $groupName }}
                </flux:navlist.item>
            @endif
        @endif
    @endif
@endforeach
