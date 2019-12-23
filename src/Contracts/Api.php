<?php

namespace Translate\StorageManager\Contracts;

interface Api
{
    /**
     * @param string|null $langs
     * @param int $page
     * @return array
     * ```php
     *  [
     *      'items' => [
     *          'key' => 'string|required',
     *          'value' => 'string|required',
     *          'lang' => 'string|required',
     *          'group' => 'string'
     *       ],
     *      'meta' => [
     *          'totalPages' => 'int|required',
     *          'pageNum' => 'int|required'
     *      ]
     *  ]
     * ```
     */
    public function fetch(string $langs = null, int $page = 1): array;
}
