<?php

namespace Storage;

use Elasticsearch\Client;
use Translate\StorageManager\Contracts\BulkActions;
use Translate\StorageManager\Contracts\TranslationStorage;

class ElasticSearch implements TranslationStorage, BulkActions
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
     * @param Client $client
     * @param array $options
     */
    public function __construct(Client $client, array $options = [])
    {
        $this->client = $client;
        $this->processOptions($options);
        if (!$this->client->indices()->exists(['index' => $this->options['indexName']])) {
            $this->client->indices()->create([
                'index' => $this->options['indexName'],
                'body' => [
                    'mappings' => [
                        'properties' => [
                            'key' => ['type' => 'keyword'],
                            'value' => ['type' => 'text', 'index_options' => 'freqs'],
                            'lang' => ['type' => 'keyword'],
                            'group' => ['type' => 'keyword']
                        ]
                    ]
                ],
            ]);
        }
    }

    protected function processOptions(array $options): void
    {
        $options['indexName'] = $options['indexName'] ?? 'translation';
        $this->options = $options;
    }

    /**
     * @inheritDoc
     */
    public function insert(string $key, string $value, string $lang, string $group = null): bool
    {
        $resp = $this->client->index([
            'index' => $this->options['indexName'],
            'body' => [
                'key' => $key,
                'value' => $value,
                'lang' => $lang,
                'group' => $group
            ]
        ]);

        return isset($resp['result']) && $resp['result'] === 'created';
    }

    /**
     * @inheritDoc
     */
    public function find(string $key, string $lang): ?string
    {
        $resp = $this->client->search(['index' => $this->options['indexName'], 'body' => [
            'query' => [
                'bool' => [
                    'filter' => [
                        ['term' => ['lang' => $lang]],
                        ['term' => ['key' => $key]]
                    ],
                ],

            ]
        ]]);

        return $resp['hits']['hits']['_source']['value'] ?? null;
    }

    /**
     * @inheritDoc
     */
    public function delete(string $key): bool
    {
        $resp = $this->client->deleteByQuery(['index' => $this->options['indexName'], 'body' => [
            'query' => [
                'term' => ['key' => $key]
            ]
        ]]);

        return empty($resp['failures']);
    }

    /**
     * @inheritDoc
     */
    public function group(string $group, string $lang): ?array
    {
        $resp = $this->client->search(['index' => $this->options['indexName'], 'body' => [
            'query' => [
                'bool' => [
                    'filter' => [
                        ['term' => ['lang' => $lang]],
                        ['term' => ['group' => $group]]
                    ],
                ],

            ]
        ]]);

        return $resp['hits']['hits']['_source']['value'] ?? null;
    }

    /**
     * @inheritDoc
     */
    public function deleteGroup(string $group): bool
    {
        $resp = $this->client->deleteByQuery(['index' => $this->options['indexName'], 'body' => [
            'query' => [
                'term' => ['group' => $group]
            ]
        ]]);

        return empty($resp['failures']);
    }

    /**
     * @inheritDoc
     */
    public function bulkInsert(array $data): bool
    {
        $body = [];
        foreach ($data as $item) {
            $body[] = ['index' => []];
            $body[] = $item;
        }
        $resp = $this->client->bulk(['index' => $this->options['indexName'], 'body' => $body]);

        return $resp['errors'] === false;
    }
}
