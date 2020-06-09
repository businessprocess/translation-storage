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
     *      'items' => 'array|required',
     *      'meta' => [
     *          'totalPages' => 'int|required',
     *          'pageNum' => 'int|required'
     *      ]
     *  ]
     * ```
     */
    public function fetch(array $params = [], int $page = 1): array;
}
