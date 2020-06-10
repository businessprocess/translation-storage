<?php

namespace Pervozdanniy\TranslationStorage\Manager;

use Pervozdanniy\TranslationStorage\Contracts\Api;
use Pervozdanniy\TranslationStorage\Contracts\Bulk;
use Pervozdanniy\TranslationStorage\Contracts\Parser;
use Pervozdanniy\TranslationStorage\Contracts\Storage\DynamicStorage;
use Pervozdanniy\TranslationStorage\Response\Exception;
use function implode;

class DynamicManager extends Base
{
    /**
     * @var DynamicStorage
     */
    protected $storage;

    /**
     * @return DynamicStorage
     */
    public function getStorage(): DynamicStorage
    {
        return $this->storage;
    }

    /**
     * @param DynamicStorage $storage
     * @return DynamicManager
     */
    public function setStorage(DynamicStorage $storage): DynamicManager
    {
        $this->storage = $storage;

        return $this;
    }

    /**
     * @param Api $api
     * @param DynamicStorage $storage
     * @param Parser $parser
     */
    public function __construct(Api $api, DynamicStorage $storage, Parser $parser)
    {
        $this->storage = $storage;
        parent::__construct($api, $parser);
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
        $this->process([
            'langs' => implode(',', $langs),
            'tags' => $group
        ]);
    }

    /**
     * @inheritDoc
     */
    protected function insertBatch(array $batch): void
    {
        if ($this->storage instanceof Bulk) {
            $this->storage->bulkInsert($batch);
            return;
        }
        foreach ($batch as $item) {
            $index = $item['index'];
            unset($item['index']);
            $this->storage->insert($index, $item);
        }
    }
}
