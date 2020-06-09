<?php

namespace Translate\StorageManager\Response;

abstract class Parser implements \Translate\StorageManager\Contracts\Parser
{
    /**
     * @inheritDoc
     */
    public function hasMore(array $response): bool
    {
        return $response['meta']['totalPages'] > $response['meta']['pageNum'];
    }
}
