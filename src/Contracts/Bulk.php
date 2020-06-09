<?php

namespace Translate\StorageManager\Contracts;

interface BulkActions
{
    /**
     * @param array $data Compatible with [TranslationStorage::insert()]
     * @return bool
     * @see TranslationStorage::insert()
     */
    public function bulkInsert(array $data): bool;
}
