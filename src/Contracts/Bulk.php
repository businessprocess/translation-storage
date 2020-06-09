<?php

namespace Translate\StorageManager\Contracts;

interface Bulk
{
    /**
     * @param array $data Compatible with [TranslationStorage::insert()]
     * @return bool
     * @see Storage::insert()
     */
    public function bulkInsert(array $data): bool;
}
