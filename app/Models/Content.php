<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

/**
 * Model reprezentujący treści w systemie.
 *
 * Treści mogą być różnych typów:
 * - 'terms' - regulamin, polityka prywatności (identyfikowane po nazwie, np. 'regulamin')
 * - 'product_group' - treści dla grup produktów (identyfikowane po URL grupy, np. 'Bi+fix+herbaty+czarne' lub zdekodowany 'Bi fix herbaty czarne')
 * - 'custom' - dowolne treści (identyfikowane po własnym identyfikatorze)
 *
 * Uwaga: Strony z dynamiczną treścią (np. /dostawa) nie są przechowywane w bazie.
 */
class Content extends Model
{
    /**
     * Pola, które mogą być masowo przypisane.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'type',
        'identifier',
        'title',
        'content',
        'is_active',
        'meta',
    ];

    /**
     * Typy danych, które powinny być rzutowane.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_active' => 'boolean',
        'meta' => 'array',
    ];

    /**
     * Scope dla aktywnych treści.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope dla typu treści.
     */
    public function scopeOfType(Builder $query, string $type): Builder
    {
        return $query->where('type', $type);
    }

    /**
     * Pobierz treść po typie i identyfikatorze.
     *
     * @param string $type Typ treści
     * @param string $identifier Identyfikator treści
     * @return Content|null
     */
    public static function findByTypeAndIdentifier(string $type, string $identifier): ?self
    {
        return static::where('type', $type)
            ->where('identifier', $identifier)
            ->where('is_active', true)
            ->first();
    }

    /**
     * Pobierz regulamin.
     *
     * @param string $identifier Identyfikator regulaminu (domyślnie 'regulamin')
     * @return Content|null
     */
    public static function getTerms(string $identifier = 'regulamin'): ?self
    {
        return static::findByTypeAndIdentifier('terms', $identifier);
    }

    /**
     * Pobierz treść dla grupy produktów.
     *
     * Używa clean_name z Group (np. 'Bi fix herbaty czarne\Podgrupa') i sprawdza różne warianty:
     * - Oryginalny format (z \)
     * - Format URL (z -- zamiast \)
     * - Format URL z plusami (z + zamiast spacji)
     * - Case-insensitive search
     *
     * @param string $groupIdentifier Identyfikator grupy (clean_name z Group lub URL grupy)
     * @return Content|null
     */
    public static function getForProductGroup(string $groupIdentifier): ?self
    {
        // Cache key dla treści grupy
        $cacheKey = 'content_product_group_' . md5($groupIdentifier);
        $cacheTtl = 3600; // 1 godzina
        
        // Sprawdź cache - użyj Cache::get() zamiast Cache::has() + Cache::get() aby uniknąć podwójnych zapytań
        // Używamy specjalnej wartości '__NOT_IN_CACHE__' jako domyślnej, aby odróżnić "brak w cache" od "zapisaliśmy '__NULL__'"
        $cached = \Illuminate\Support\Facades\Cache::get($cacheKey, '__NOT_IN_CACHE__');
        
        // Jeśli cache istnieje (nie jest '__NOT_IN_CACHE__'), zwróć wartość
        if ($cached !== '__NOT_IN_CACHE__') {
            // Cache istnieje - zwróć wartość (może być '__NULL__', co oznacza że sprawdziliśmy i nie ma treści)
            return $cached === '__NULL__' ? null : $cached;
        }
        
        // Cache nie istnieje - wykonaj zapytanie
        // Lista wariantów do sprawdzenia (używamy clean_name z Group, więc sprawdzamy różne formaty)
        $variants = [
            $groupIdentifier, // Oryginalny identyfikator (clean_name z Group)
            str_replace('\\', '--', $groupIdentifier), // Zamiana \ na -- (format URL)
            str_replace('\\', '+', $groupIdentifier), // Zamiana \ na + (format URL zakodowany)
            str_replace(' ', '+', $groupIdentifier), // Zamiana spacji na + (format URL zakodowany)
            str_replace('\\', ' ', $groupIdentifier), // Zamiana \ na spacje
        ];
        
        // Usuń duplikaty
        $variants = array_unique($variants);
        
        // Wykonaj jedno zapytanie sprawdzające wszystkie warianty (exact match + case-insensitive)
        // Używamy case-insensitive search, który znajdzie również exact match
        $lowerVariants = array_map('strtolower', $variants);
        
        $content = static::where('type', 'product_group')
            ->where('is_active', true)
            ->where(function ($query) use ($lowerVariants) {
                foreach ($lowerVariants as $index => $variant) {
                    if ($index === 0) {
                        $query->whereRaw('LOWER(identifier) = ?', [$variant]);
                    } else {
                        $query->orWhereRaw('LOWER(identifier) = ?', [$variant]);
                    }
                }
            })
            ->first();

        // Zapisz wynik w cache (nawet jeśli to null) - użyj specjalnej wartości '__NULL__' dla null
        // aby odróżnić "brak w cache" od "sprawdziliśmy i nie ma"
        $valueToCache = $content ?: '__NULL__';
        \Illuminate\Support\Facades\Cache::put($cacheKey, $valueToCache, $cacheTtl);
        
        // Zwróć null jeśli to '__NULL__' (oznacza że nie znaleziono)
        return $content;
    }
}
