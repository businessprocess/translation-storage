<?php

namespace Translate\StorageManager\Contracts;

interface TranslationStorage
{
    /**
     * @param string $key
     * @param string $value
     * @param string $lang
     * @param string|null $group
     * @return bool
     */
    public function insert(string $key, string $value, string $lang, string $group = null): bool;

    /**
     * @param string $key
     * @param string $lang
     * @return string|null
     */
    public function find(string $key, string $lang): ?string;

    /**
     * @param string $key
     * @return bool
     */
    public function delete(string $key): bool;

    /**
     * @param string $group
     * @param string $lang
     * @return array|null
     */
    public function group(string $group, string $lang): ?array;

    /**
     * @param string $group
     * @return bool
     */
    public function deleteGroup(string $group): bool;
}