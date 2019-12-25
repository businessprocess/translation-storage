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
     * @var ProgressTracker|null
     */
    protected $tracker;

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

    public function setTracker(ProgressTracker $tracker): Manager
    {
        $this->tracker = $tracker;

        return $this;
    }

    /**
     * @param string[] $langs
     * @throws Exception
     */
    public function update(array $langs): void
    {
        $this->storage->clear($langs);
        $this->process(['langs' => implode(',', $langs)]);
    }

    /**
     * @param string $group
     * @param string[] $langs
     * @throws Exception
     */
    public function updateGroup(string $group, array $langs): void
    {
        $this->storage->clearGroup($group, $langs);
        $params = [
            'langs' => implode(',', $langs),
            'group' => $group
        ];
        $this->process($params);
    }

    /**
     * @param array $params
     * @throws Exception
     */
    protected function process(array $params): void
    {
        $this->tracker !== null && $this->tracker->beforeStart();
        foreach ($this->getItemsBatch($params) as $batch) {
            if ($this->storage instanceof BulkActions) {
                $this->storage->bulkInsert($batch);
                continue;
            }
            foreach ($batch as $item) {
                if (!isset($item['value']) && $item['value'] === null) {
                    continue;
                }
                $this->storage->insert($item['key'], $item['value'], $item['lang'], $item['group'] ?? null);
            }
        }
        $this->tracker !== null && $this->tracker->afterFinish();
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
            ++$page;
            $this->tracker !== null && $this->tracker->beforeBatch($page);
            try {
                $resp = $this->api->fetch($params, $page);
            } catch (\Throwable $exception) {
                throw new Exception('Failed to fetch ' . $page . ' page due to: ' .
                    $exception->getMessage(), $exception->getCode(), $exception);
            }
            yield $this->parser->parseBody($resp);
            $this->tracker !== null && $this->tracker->afterBatch($resp);
        } while ($this->parser->hasMore($resp));
    }
}
