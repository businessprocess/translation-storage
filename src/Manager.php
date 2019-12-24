<?php

namespace Translate\StorageManager;

use Generator;
use Translate\StorageManager\Contracts\Api;
use Translate\StorageManager\Contracts\BulkActions;
use Translate\StorageManager\Contracts\Parser;
use Translate\StorageManager\Contracts\TranslationStorage;
use Translate\StorageManager\Response\Exception;

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
     * @var Parser
     */
    protected $parser;

    /**
     * @param Api $api
     * @param TranslationStorage $storage
     * @param Parser|null $parser
     */
    public function __construct(Api $api, TranslationStorage $storage, Parser $parser = null)
    {
        $this->api = $api;
        $this->storage = $storage;
        $this->parser = $parser ?? new Response\Parser();
    }

    /**
     * @param string|null $langs
     * @throws Exception
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
     * @throws Exception
     */
    protected function getItemsBatch(string $langs = null): Generator
    {
        $page = 0;
        do {
            try {
                $resp = $this->api->fetch($langs, ++$page);
            } catch (\Throwable $exception) {
                throw new Exception('Failed to fetch ' . $page . ' page for ' . $langs . ' languages due to: ' .
                    $exception->getMessage(), $exception->getCode(), $exception);
            }
            yield $this->parser->parseBody($resp);
        } while ($this->parser->hasMore($resp));
    }
}
