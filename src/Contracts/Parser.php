<?php

namespace Translate\StorageManager\Contracts;

interface Parser
{
    /**
     * Returns array of items, compatible with [TranslationStorage::insert()]
     * @param array $response
     * @return array index => item pairs
     * @see Storage::insert()
     */
    public function parseBody(array $response): array;

    /**
     * @param array $response
     * @return bool
     */
    public function hasMore(array $response): bool;
}
