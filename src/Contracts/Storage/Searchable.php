<?php

namespace Pervozdanniy\TranslationStorage\Contracts\Storage;

interface Searchable
{
    /**
     * @param string $query
     * @param string $lang
     * @param string|null $group
     * @return string[] Translation keys
     */
    public function search(string $query, string $lang, string $group = null): array;
}
