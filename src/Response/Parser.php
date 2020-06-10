<?php

namespace Pervozdanniy\TranslationStorage\Response;

abstract class Parser implements \Pervozdanniy\TranslationStorage\Contracts\Parser
{
    /**
     * @inheritDoc
     */
    public function hasMore(array $response): bool
    {
        return $response['meta']['totalPages'] > $response['meta']['pageNum'];
    }
}
