<?php

namespace Translate\StorageManager;

use Generator;
use Translate\StorageManager\Contracts\Api;
use Translate\StorageManager\Contracts\BulkActions;
use Translate\StorageManager\Contracts\TranslationStorage;

class Manager
{
    /**
     * @var Api
     */
    protected $api;

    /**
     * @var TranslationStorage
     */
    protected $storage;

    /**
     * @param Api $api
     * @param TranslationStorage $storage
     */
    public function __construct(Api $api, TranslationStorage $storage)
    {
        $this->api = $api;
        $this->storage = $storage;
    }

    /**
     * @param string|null $langs
     */
    public function update(string $langs = null): void
    {
        foreach ($this->getItemsBatch($langs) as $batch) {
            if ($this->storage instanceof BulkActions) {
                $this->storage->bulkInsert($batch);
            }
            foreach ($batch as $item) {
                if (!isset($item['value']) && $item['value'] === null) {
                    continue;
                }
                $this->storage->insert($item['key'], $item['value'], $item['lang'], $item['group'] ?? null);
            }
        }
    }

    /**
     * @param string|null $langs
     * @return Generator
     */
    protected function getItemsBatch(string $langs = null): Generator
    {
        $page = 0;
        do {
            $resp = $this->api->fetch($langs, ++$page);
            yield $this->processBatch($resp);
        } while ($resp['meta']['totalPages'] > $resp['meta']['pageNum']);
    }

    /**
     * @param array $data
     * @return array
     */
    protected function processBatch(array $data): array
    {
        $body = [];
        foreach ($data['items'] as $item) {
            if (is_array($item['value'])) {
                foreach ($item['value'] as $lang => $value) {
                    $body[] = [
                        'key' => $item['key'],
                        'value' => $value,
                        'lang' => $lang,
                        'group' => $item['tags']
                    ];
                }
            } else {
                $body[] = [
                    'key' => $item['key'],
                    'value' => $item['value'],
                    'lang' => reset($data['meta']['langs']),
                    'group' => $item['tags']
                ];
            }
        }

        return $body;
    }
}
