<?php

namespace Translate\StorageManager\Contracts;

interface Api
{
    /**
     * @param array $params
     * @param int $page
     * @return array
     * ```php
     *  [
     *      'items' => [
     *          'key' => 'string|required',
     *          'value' => 'string|required',
     *          'lang' => 'string|required',
     *          'group' => 'string|required'
     *       ],
     *      'meta' => [
     *          'totalPages' => 'int|required',
     *          'pageNum' => 'int|required'
     *      ]
     *  ]
     * ```
     */
    public function fetch(array $params = [], int $page = 1): array;
}
