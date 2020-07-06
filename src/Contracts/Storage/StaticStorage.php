<?php

namespace Pervozdanniy\TranslationStorage\Contracts\Storage;

interface StaticStorage
{
    /**
     * @param string $key
     * @param string $value
     * @param string $lang
     * @param string $group
     * @return bool
     */
    public function set(string $key, string $value, string $lang, string $group): bool;

    /**
     * @param string $key
     * @param string $lang
     * @param string $group
     * @return string|null
     */
    public function find(string $key, string $lang, string $group): ?string;

    /**
     * @param string $group
     * @param string $lang
     * @return array
     */
    public function findByGroup(string $group, string $lang): array;

    /**
     * @param string $group
     * @param string[]|null $langs Set null to drop all languages for specified group
     * @return bool
     */
    public function clearGroup(string $group, array $langs = null): bool;

    /**
     * @param string[]|null $langs Set null to drop ALL languages
     * @return bool
     */
    public function clear(array $langs = null): bool;
}
