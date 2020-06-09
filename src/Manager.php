<?php

namespace Translate\StorageManager;

use Generator;
use Throwable;
use Translate\StorageManager\Contracts\Api;
use Translate\StorageManager\Contracts\Bulk;
use Translate\StorageManager\Contracts\Parser;
use Translate\StorageManager\Contracts\ProgressTracker;
use Translate\StorageManager\Contracts\Storage;
use Translate\StorageManager\Response\Exception;
use function implode;

class Manager
{
    /**
     * @var Api
     */
    protected $api;

    /**
     * @var Storage
     */
    protected $storage;

    /**
     * @return Storage
     */
    public function getStorage(): Storage
    {
        return $this->storage;
    }

    /**
     * @param Storage $storage
     * @return Manager
     */
    public function setStorage(Storage $storage): Manager
    {
        $this->storage = $storage;

        return $this;
    }

    /**
     * @var Parser
     */
    protected $parser;

    /**
     * @var ProgressTracker|null
     */
    protected $tracker;

    public function getTracker(): ProgressTracker
    {
        return $this->tracker;
    }

    public function setTracker(ProgressTracker $tracker): Manager
    {
        $this->tracker = $tracker;

        return $this;
    }

    /**
     * @param Api $api
     * @param Storage $storage
     * @param Parser $parser
     */
    public function __construct(Api $api, Storage $storage, Parser $parser)
    {
        $this->api = $api;
        $this->storage = $storage;
        $this->parser = $parser;
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
        $this->storage->clear($langs, $group);
        $params = [
            'langs' => implode(',', $langs),
            'tags' => $group
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
            if ($this->storage instanceof Bulk) {
                $this->storage->bulkInsert($batch);
                continue;
            }
            foreach ($batch as $index => $item) {
                $this->storage->insert($index, $item);
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
            } catch (Throwable $exception) {
                throw new Exception('Failed to fetch ' . $page . ' page due to: ' .
                    $exception->getMessage(), $exception->getCode(), $exception);
            }
            yield $this->parser->parseBody($resp);
            $this->tracker !== null && $this->tracker->afterBatch($resp);
        } while ($this->parser->hasMore($resp));
    }
}
