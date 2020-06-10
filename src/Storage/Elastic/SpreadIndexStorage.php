<?php

namespace Pervozdanniy\TranslationStorage\Storage\Elastic;

use Elasticsearch\Client;
use Elasticsearch\Common\Exceptions\Missing404Exception;
use InvalidArgumentException;
use Pervozdanniy\TranslationStorage\Contracts\Bulk;
use Pervozdanniy\TranslationStorage\Contracts\Storage\DynamicStorage;
use function array_key_exists;
use function count;
use function is_array;

class SpreadIndexStorage implements DynamicStorage, Bulk
{
    /**
     * @var Client
     */
    protected $client;

    /**
     * @var array
     */
    protected $options;

    /**
     * @var array|null
     */
    private $indices;

    public function __construct(Client $client, array $options = [])
    {
        $this->client = $client;
        $this->processOptions($options);
        foreach ($this->options['indices'] as $name => $config) {
            $this->ensureIndex($name, $config);
        }
    }

    /**
     * @param array $options
     */
    protected function processOptions(array $options): void
    {
        if (!isset($options['indices']) || !is_array($options['indices'])) {
            throw new InvalidArgumentException('Option \'indices is required and should be an array\'');
        }
        $options['prefix'] = $options['prefix'] ?? '';
        $options['batchSize'] = $options['batchSize'] ?? 500;
        $options['batchTimeout'] = $options['batchTimeout'] ?? '10s';
        $options['refresh'] = $options['refresh'] ?? false;
        $this->options = $options;
    }

    /**
     * @param string $name
     * @param array $config
     */
    protected function ensureIndex(string $name, array $config): void
    {
        if ($this->indices === null) {
            $this->indices = $this->client->cat()->indices(['index' => $this->options['prefix'] . '*', 'format' => 'json']);
        }
        $indexName = $this->options['prefix'] . $name;
        foreach ($this->indices as $info) {
            if ($info['index'] === $indexName) {
                return;
            }
        }
        $this->client->indices()->create([
            'index' => $indexName,
            'body' => $config
        ]);
    }

    /**
     * @inheritDoc
     */
    public function insert(string $index, array $fields): bool
    {
        if (!array_key_exists('id', $fields) || !array_key_exists('lang', $fields)) {
            throw new InvalidArgumentException('$fields MUST contain both \'id\' and \'lang\' keys');
        }

        $update = $this->client->update([
            'index' => $this->options['prefix'] . $index,
            'id' => $fields['lang'] . '.' . $fields['id'],
            'body' => [
                'doc' => $fields,
                'doc_as_upsert' => true,
            ],
        ]);

        return $update['result'] === 'updated' || $update['result'] === 'noop';
    }

    /**
     * @inheritDoc
     */
    public function find(string $index, string $id, string $lang): ?array
    {
        try {
            $resp = $this->client->get([
                'index' => $this->options['prefix'] . $index,
                'id' => $lang . '.' . $id,
            ]);
        } catch (Missing404Exception $exception) {
            return null;
        }

        return $resp['_source'];
    }

    /**
     * @inheritDoc
     * @return \Generator
     */
    public function fetch(array $langs = null, string $index = null): iterable
    {
        $body = ['query' => $this->prepareLangsQuery($langs)];
        $resp = $this->client->search([
            'index' => $this->options['prefix'] . ($index ?? '*'),
            'size' => $this->options['batchSize'],
            'scroll' => $this->options['batchTimeout'],
            'body' => $body,
        ]);
        $fetched = count($resp['hits']['hits']);
        foreach ($resp['hits']['hits'] as $hit) {
            yield $hit['_source'];
        }
        if ($fetched >= $resp['hits']['total']['value']) {
            return $fetched;
        }
        do {
            $resp = $this->client->scroll([
                'scroll_id' => $resp['_scroll_id'],
                'scroll' => $this->options['batchTimeout'],
            ]);
            foreach ($resp['hits']['hits'] as $hit) {
                yield $hit['_source'];
            }
        } while ($resp['hits']['total']['value'] > $fetched += count($resp['hits']['hits']));
        $this->client->clearScroll(['body' => [
            'scroll_id' => $resp['_scroll_id']
        ]]);

        return $fetched;
    }

    /**
     * @param array|null $langs
     * @return array
     */
    protected function prepareLangsQuery(array $langs = null): array
    {
        if ($langs === null || empty($langs)) {
            return ['match_all' => ['boost' => 1.0]];
        }
        $query = [];
        foreach ($langs as $lang) {
            $query['bool']['should'][] = [
                'term' => ['lang' => $lang]
            ];
        }

        return $query;
    }

    /**
     * @inheritDoc
     */
    public function clear(array $langs = null, string $index = null): void
    {
        $this->client->deleteByQuery([
            'index' => $this->options['prefix'] . ($index ?? '*'),
            'slices' => 'auto',
            'body' => ['query' => $this->prepareLangsQuery($langs)],
        ]);
    }

    /**
     * @inheritDoc
     */
    public function bulkInsert(array $data): bool
    {
        if (empty($data)) {
            return true;
        }
        $body = [];
        foreach ($data as $item) {
            $body[] = ['update' => [
                '_index' => $this->options['prefix'] . $item['index'],
                '_id' => $item['lang'] . '.' . $item['id']
            ]];
            unset($item['index']);
            $body[] = ['doc' => $item, 'doc_as_upsert' => true];
        }
        $resp = $this->client->bulk([
            'refresh' => $this->options['refresh'],
            'body' => $body
        ]);

        return $resp['errors'] === false;
    }

    public function reset(): void
    {
        $this->client->indices()->delete([
            'index' => $this->options['prefix'] . '*'
        ]);
    }
}
