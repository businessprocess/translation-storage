<?php

namespace Pervozdanniy\TranslationStorage\Manager;

use Pervozdanniy\TranslationStorage\Contracts\Api;
use Pervozdanniy\TranslationStorage\Contracts\Storage\Bulkable;
use Pervozdanniy\TranslationStorage\Contracts\Parser;
use Pervozdanniy\TranslationStorage\Contracts\Storage\StaticStorage;
use Pervozdanniy\TranslationStorage\Contracts\Storage\Updateable;
use Pervozdanniy\TranslationStorage\Response\Exception;

class StaticManager extends Base
{
    /**
     * @var StaticStorage
     */
    protected $storage;

    /**
     * @return StaticStorage
     */
    public function getStorage(): StaticStorage
    {
        return $this->storage;
    }

    /**
     * @param StaticStorage $storage
     * @return StaticManager
     */
    public function setStorage(StaticStorage $storage): StaticManager
    {
        $this->storage = $storage;

        return $this;
    }

    /**
     * @param Api $api
     * @param StaticStorage $storage
     * @param Parser|null $parser
     */
    public function __construct(Api $api, StaticStorage $storage, Parser $parser)
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
        if (!$this->storage instanceof Updateable) {
            $this->storage->clear($langs);
        }
        $this->process(['langs' => implode(',', $langs)]);
    }

    /**
     * @param string $group
     * @param string[] $langs
     * @throws Exception
     */
    public function updateGroup(string $group, array $langs): void
    {
        if (!$this->storage instanceof Updateable) {
            $this->storage->clearGroup($group, $langs);
        }
        $params = [
            'langs' => implode(',', $langs),
            'tags' => $group
        ];
        $this->process($params);
    }

    /**
     * @inheritDoc
     */
    protected function insertBatch(array $batch): void
    {
        if ($this->storage instanceof Bulkable) {
            $this->storage->bulkInsert($batch);
            return;
        }
        foreach ($batch as $item) {
            if (!isset($item['value']) && $item['value'] === null) {
                continue;
            }
            $this->storage->set($item['key'], $item['value'], $item['lang'], $item['group'] ?? null);
        }
    }
}
