<?php

namespace Translate\StorageManager\Contracts;

interface Parser
{
    /**
     * Returns array of items, compatible with [TranslationStorage::insert()]
     * @param array $response
     * @return array
     * @see TranslationStorage::insert()
     * ```php
     *  [
     *      'key' => 'string|required',
     *      'value' => 'string|required',
     *      'lang' => 'string|required',
     *      'group' => 'string'
     *  ]
     * ```
     */
    public function parseBody(array $response): array;

    /**
     * @param array $response
     * @return bool
     */
    public function hasMore(array $response): bool;
}
