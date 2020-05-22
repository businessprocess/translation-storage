<?php

namespace Translate\StorageManager\Storage;

use Elasticsearch\Client;
use Translate\StorageManager\Contracts\BulkActions;
use Translate\StorageManager\Contracts\Searchable;
use Translate\StorageManager\Contracts\TranslationStorage;

class ElasticStorage implements TranslationStorage, BulkActions, Searchable
{
    protected const DEFAULT_BATCH_SIZE = 500;

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

    /**
     * @param array $options
     */
    protected function processOptions(array $options): void
    {
        $options['indexName'] = $options['indexName'] ?? 'translation';
        $options['batchSize'] = $options['batchSize'] ?? static::DEFAULT_BATCH_SIZE;
        $options['refresh'] = $options['refresh'] ?? false;
        $this->options = $options;
    }

    /**
     * @inheritDoc
     */
    public function insert(string $key, string $value, string $lang, string $group = null): bool
    {
        $update = $this->client->updateByQuery([
            'index' => $this->options['indexName'],
            // 'refresh' => $this->options['refresh'] !== false,
            'body' => [
                'query' => [
                    'bool' => [
                        'filter' => [
                            ['term' => ['lang' => $lang]],
                            ['term' => ['key' => $key]],
                            ['term' => ['group' => $group]]
                        ],
                    ],

                ],
                'script' => [
                    'source' => 'ctx._source.value=params.value',
                    'params' => [
                        'value' => $value
                    ],
                    'lang' => 'painless'
                ]
            ],
        ]);
        if ($update['updated'] === 1) {
            return true;
        }
        $resp = $this->client->index([
            'index' => $this->options['indexName'],
            'refresh' => $this->options['refresh'],
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
    public function find(string $key, string $lang, string $group): ?string
    {
        $resp = $this->client->search(['index' => $this->options['indexName'], 'body' => [
            'query' => [
                'bool' => [
                    'filter' => [
                        ['term' => ['lang' => $lang]],
                        ['term' => ['key' => $key]],
                        ['term' => ['group' => $group]]
                    ],
                ],

            ]
        ]]);

        return $resp['hits']['hits'][0]['_source']['value'] ?? null;
    }

    /**
     * @inheritDoc
     */
    public function findByGroup(string $group, string $lang): array
    {
        $from = 0;
        $result = [];
        do {
            $resp = $this->client->search([
                'index' => $this->options['indexName'],
                'size' => $this->options['batchSize'],
                'from' => $from,
                'body' => [
                    'query' => [
                        'bool' => [
                            'filter' => [
                                ['term' => ['lang' => $lang]],
                                ['term' => ['group' => $group]]
                            ],
                        ],

                    ]
                ]
            ]);
            foreach ($this->parseResults($resp['hits']['hits']) as $key => $value) {
                $result[$key] = $value;
            }
        } while ($resp['hits']['total']['value'] > $from += $this->options['batchSize']);

        return $result;
    }

    /**
     * @param array $hits
     * @return array
     */
    protected function parseResults(array $hits): array
    {
        $result = [];
        foreach ($hits as $hit) {
            $result[$hit['_source']['key']] = $hit['_source']['value'];
        }

        return $result;
    }

    /**
     * @inheritDoc
     */
    public function clearGroup(string $group, array $langs = null): bool
    {
        $query = [
            'bool' => [
                'must' => [
                    'term' => ['group' => $group],
                ]
            ],
        ];
        if ($langs !== null) {
            foreach ($langs as $lang) {
                $query['bool']['should'] = [
                    'term' => ['lang' => $lang]
                ];
            }
        }
        $resp = $this->client->deleteByQuery(['index' => $this->options['indexName'], 'body' => [
            'query' => $query
        ]]);

        return empty($resp['failures']);
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
            $body[] = ['index' => ['_index' => $this->options['indexName']]];
            $body[] = $item;
        }
        $resp = $this->client->bulk([
            'refresh' => $this->options['refresh'],
            'body' => $body
        ]);

        return $resp['errors'] === false;
    }

    /**
     * @inheritDoc
     */
    public function clear(array $langs = null): bool
    {
        if ($langs === null || empty($langs)) {
            $query = ['match_all' => ['boost' => 1.0]];
        } else {
            foreach ($langs as $lang) {
                $query['bool']['should'][] = [
                    'term' => ['lang' => $lang]
                ];
            }
        }

        $this->client->deleteByQuery([
            'index' => $this->options['indexName'],
            'body' => ['query' => $query]
        ]);

        return true;
    }

    /**
     * @inheritDoc
     */
    public function search(string $query, string $lang, string $group = null): array
    {
        $from = 0;
        $result = [];
        $body = [
            'query' => [
                'bool' => [
                    'must' => $this->prepareMustClause($query),
                    'filter' => $this->prepareFilterClause($lang, $group)
                ]
            ],
        ];
        do {
            $resp = $this->client->search([
                'index' => $this->options['indexName'],
                'size' => $this->options['batchSize'],
                'from' => $from,
                'body' => $body
            ]);
            foreach ($this->parseResults($resp['hits']['hits']) as $key => $value) {
                $result[$key] = $value;
            }
        } while ($resp['hits']['total']['value'] > $from += $this->options['batchSize']);

        return $result;
    }


    /**
     * @param string $query
     * @return array
     */
    private function prepareMustClause(string $query): array
    {
        $must = [];
        foreach (array_unique(array_filter(explode(' ', $this->escape($query)))) as $term) {
            $must[] = [
                'wildcard' => [
                    'value' => [
                        'value' => "*$term*"
                    ]
                ]
            ];
        }

        return $must;
    }

    /**
     * @param string $lang
     * @param string|null $group
     * @return array
     */
    private function prepareFilterClause(string $lang, string $group = null): array
    {
        $filter = [
            ['term' => ['lang' => $lang]]
        ];
        if ($group !== null) {
            $filter[] = ['term' => ['group' => $group]];
        }

        return $filter;
    }

    /**
     * @param string $string
     * @return string
     */
    private function escape(string $string): string
    {
        return mb_ereg_replace('[^\w\p{Cyrillic},]', ' ', mb_strtolower($string));
    }
}
