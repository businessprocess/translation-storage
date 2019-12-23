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
            yield $resp['items'];
        } while ($resp['meta']['totalPages'] > $resp['meta']['pageNum']);
    }
}
