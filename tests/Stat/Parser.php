<?php

namespace Pervozdanniy\TranslationStorage\Tests\Stat;

class Parser extends \Pervozdanniy\TranslationStorage\Response\Parser
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
                        'key' => $item['key'],
                        'value' => $value,
                        'lang' => $lang,
                        'group' => $group
                    ];
                }
            } else {
                $body[] = [
                    'key' => $item['key'],
                    'value' => $item['value'],
                    'lang' => reset($response['meta']['langs']),
                    'group' => $group
                ];
            }
        }

        return $body;
    }
}