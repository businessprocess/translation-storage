<?php

namespace Translate\StorageManager;

use Generator;
use Translate\StorageManager\Contracts\Api;
use Translate\StorageManager\Contracts\BulkActions;
use Translate\StorageManager\Contracts\Parser;
use Translate\StorageManager\Contracts\ProgressTracker;
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
     * @param ProgressTracker|null $tracker
     * @throws Exception
     */
    public function update(string $langs = null, ProgressTracker $tracker = null): void
    {
        $this->process(['langs' => $langs], $tracker);
    }

    /**
     * @param string $group
     * @param string|null $langs
     * @param ProgressTracker|null $tracker
     * @throws Exception
     */
    public function updateGroup(string $group, string $langs = null, ProgressTracker $tracker = null): void
    {
        $this->storage->deleteGroup($group);
        $params = [
            'langs' => $langs,
            'group' => $group
        ];
        $this->process($params, $tracker);
    }

    /**
     * @param array $params
     * @param ProgressTracker|null $tracker
     * @throws Exception
     */
    protected function process(array $params, ProgressTracker $tracker = null): void
    {
        $track = $tracker !== null;
        foreach ($this->getItemsBatch($params) as $batch) {
            if ($this->storage instanceof BulkActions) {
                $track && $tracker->beforeBatch($batch);
                $this->storage->bulkSet($batch);
                $track && $tracker->afterBatch($batch);
                continue;
            }
            foreach ($batch as $item) {
                $track && $tracker->beforeItem($item);
                if (!isset($item['value']) && $item['value'] === null) {
                    continue;
                }
                $this->storage->set($item['key'], $item['value'], $item['lang'], $item['group'] ?? null);
                $track && $tracker->afterBatch($item);
            }
        }
    }

    /**
     * @param array $params
     * @return Generator
     * @throws Exception
     */
    protected function getItemsBatch(array $params = []): Generator
    {
        $page = 0;
        do {
            try {
                $resp = $this->api->fetch($params, ++$page);
            } catch (\Throwable $exception) {
                throw new Exception('Failed to fetch ' . $page . ' page due to: ' .
                    $exception->getMessage(), $exception->getCode(), $exception);
            }
            yield $this->parser->parseBody($resp);
        } while ($this->parser->hasMore($resp));
    }
}
