<?php

namespace Translate\StorageManager\Contracts;

interface BulkActions
{
    /**
     * @param array $data Compatible with [TranslationStorage::insert()]
     * @see TranslationStorage::insert()
     * ```php
     *  [
     *      'key' => 'string|required',
     *      'value' => 'string|required',
     *      'lang' => 'string|required',
     *      'group' => 'string'
     *  ]
     * ```
     * @return bool
     */
    public function bulkInsert(array $data): bool;
}
