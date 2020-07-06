<?php

namespace Pervozdanniy\TranslationStorage\Contracts;

interface Parser
{
    /**
     * Returns array of items, compatible with your storage interface [insert()] method
     * @param array $response
     * @return array index => item pairs
     * @see DynamicStorage::set()
     * @see StaticStorage::set()
     */
    public function parseBody(array $response): array;

    /**
     * @param array $response
     * @return bool
     */
    public function hasMore(array $response): bool;
}
