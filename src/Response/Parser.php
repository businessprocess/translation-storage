<?php

namespace Translate\StorageManager\Response;

class Parser implements \Translate\StorageManager\Contracts\Parser
{

    /**
     * @inheritDoc
     */
    public function parseBody(array $response): array
    {
        $body = [];
        foreach ($response['items'] as $item) {
            if (is_array($item['value'])) {
                foreach ($item['value'] as $lang => $value) {
                    $body[] = [
                        'key' => $item['key'],
                        'value' => $value,
                        'lang' => $lang,
                        'group' => reset($item['tags'])
                    ];
                }
            } else {
                $body[] = [
                    'key' => $item['key'],
                    'value' => $item['value'],
                    'lang' => reset($response['meta']['langs']),
                    'group' => reset($item['tags'])
                ];
            }
        }

        return $body;
    }

    /**
     * @inheritDoc
     */
    public function hasMore(array $response): bool
    {
        return $response['meta']['totalPages'] > $response['meta']['pageNum'];
    }
}