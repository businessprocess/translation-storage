<?php

namespace Pervozdanniy\TranslationStorage\Tests\Dynamic;

class Parser extends \Pervozdanniy\TranslationStorage\Response\Parser
{
    /**
     * @inheritDoc
     */
    public function parseBody(array $response): array
    {
        $body = [];
        $group = reset($response['meta']['tags']);
        foreach ($response['items'] as $item) {
            if (is_array($item['value'])) {
                foreach ($item['value'] as $lang => $value) {
                    $body[] = [
                        'id' => $this->getId($item['key']),
                        'index' => $group,
                        'lang' => $lang,
                        $this->getField($item['key'], $group) => $value
                    ];
                }
            } else {
                $body[] = [
                    'id' => $this->getId($item['key']),
                    'index' => $group,
                    'lang' => reset($response['meta']['langs']),
                    $this->getField($item['key'], $group) => $item['value'],
                ];
            }
        }

        return $body;
    }

    protected function getField($key, $group)
    {
        $key = ltrim(str_replace($group, '', $key), '_');
        $parts = explode('_', $key);
        array_pop($parts);
        return implode('_', $parts);
    }

    protected function getId($key)
    {
        return substr(strrchr($key, '_'), 1) ?: null;
    }
}