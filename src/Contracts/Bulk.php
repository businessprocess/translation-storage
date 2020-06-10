<?php

namespace Pervozdanniy\TranslationStorage\Contracts;

interface Bulk
{
    /**
     * @param array $data Compatible with [TranslationStorage::insert()]
     * @return bool
     * @see DynamicStorage::insert()
     */
    public function bulkInsert(array $data): bool;
}
