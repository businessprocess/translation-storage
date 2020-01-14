<?php

namespace Translate\StorageManager\Contracts;

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