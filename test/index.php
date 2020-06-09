<?php

require __DIR__ . '/../vendor/autoload.php';

$builder = new \Elasticsearch\ClientBuilder();
$builder->setHosts([
    'host' => 'localhost',
    'port' => 9600,
    'scheme' => 'http',
    'user' => null,
    'pass' => null,
]);

$client = $builder->build();

$storage = new \Translate\StorageManager\Storage\ElasticStorage($client, [
    'prefix' => 'pidor_',
    'indices' => [
        'test' => [
            'mappings' => [
                'properties' => [
                    'id' => ['type' => 'keyword'],
                    'value' => ['type' => 'text', 'index_options' => 'freqs'],
                    'lang' => ['type' => 'keyword'],
                    'group' => ['type' => 'keyword']
                ]
            ]
        ]
    ]
]);