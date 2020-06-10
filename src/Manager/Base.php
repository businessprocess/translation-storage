<?php

namespace Pervozdanniy\TranslationStorage\Manager;

use Generator;
use Pervozdanniy\TranslationStorage\Contracts\Api;
use Pervozdanniy\TranslationStorage\Contracts\Parser;
use Pervozdanniy\TranslationStorage\Contracts\ProgressTracker;
use Pervozdanniy\TranslationStorage\Response\Exception;

abstract class Base
{
    /**
     * @var Api
     */
    protected $api;

    /**
     * @var ProgressTracker|null
     */
    protected $tracker;

    /**
     * @var Parser
     */
    protected $parser;

    /**
     * @return ProgressTracker
     */
    public function getTracker(): ProgressTracker
    {
        return $this->tracker;
    }

    /**
     * @param ProgressTracker $tracker
     * @return static
     */
    public function setTracker(ProgressTracker $tracker): self
    {
        $this->tracker = $tracker;

        return $this;
    }

    /**
     * @param Api $api
     * @param Parser|null $parser
     */
    public function __construct(Api $api, Parser $parser)
    {
        $this->api = $api;
        $this->parser = $parser;
    }

    /**
     * @param array $params
     * @throws Exception
     */
    protected function process(array $params): void
    {
        $this->tracker !== null && $this->tracker->beforeStart();
        foreach ($this->getItemsBatch($params) as $batch) {
            $this->insertBatch($batch);
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

    /**
     * @param array $batch
     */
    abstract protected function insertBatch(array $batch): void;
}
