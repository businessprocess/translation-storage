<?php

namespace Translate\StorageManager\Contracts;

interface Storage
{
    /**
     * @param string $index
     * @param array $fields Associative array of key => value pairs
     * @return bool
     */
    public function insert(string $index, array $fields): bool;

    /**
     * @param string $id
     * @param string $lang
     * @param string|null $index
     * @return array|null
     */
    public function find(string $id, string $lang, string $index = null): ?array;

    /**
     * @param array|null $langs
     * @param string|null $index
     * @return iterable
     */
    public function fetch(array $langs = null, string $index = null): iterable;

    /**
     * @param array|null $langs
     * @param string|null $index
     */
    public function clear(array $langs = null, string $index = null): void;
}