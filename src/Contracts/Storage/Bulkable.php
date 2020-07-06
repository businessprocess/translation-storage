<?php

namespace Pervozdanniy\TranslationStorage\Contracts\Storage;

interface Bulkable
{
    /**
     * @param array $data Compatible with [TranslationStorage::insert()]
     * @return bool
     * @see DynamicStorage::set()
     */
    public function bulkInsert(array $data): bool;
}
