<?php

namespace Tests;

class Parser extends \Translate\StorageManager\Response\Parser
{
    /**
     * @inheritDoc
     */
    public function parseBody(array $response): array
    {
        $body = [];
        foreach ($response['items'] as $item) {
            if (preg_match('/(?<group>[^.]*)\.\S*/', $item['key'], $matches) && in_array($matches['group'], $item['tags'], true)) {
                $group = $matches['group'];
                $item['key'] = str_replace($group . '.', '', $item['key']);
            } else {
                $group = reset($item['tags']);
            }

            if (is_array($item['value'])) {
                foreach ($item['value'] as $lang => $value) {
                    $body[] = [
                        'id' => $item['key'],
                        'index' => $group,
                        'value' => $value,
                        'lang' => $lang,
                        'tags' => $item['tags']
                    ];
                }
            } else {
                $body[] = [
                    'id' => $item['key'],
                    'index' => $group,
                    'value' => $item['value'],
                    'lang' => reset($response['meta']['langs']),
                    'tags' => $item['tags']
                ];
            }
        }

        return $body;
    }
}