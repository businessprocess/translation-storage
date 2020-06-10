#!/usr/bin/php
<?php

use Elasticsearch\ClientBuilder;
use Tests\Api;
use Tests\Parser;
use Translate\StorageManager\Manager;
use Translate\StorageManager\Storage\ElasticStorage;

require __DIR__ . '/../vendor/autoload.php';

$builder = new ClientBuilder();
$builder->setHosts([[
    'host' => 'localhost',
    'port' => 9600,
    'scheme' => 'http',
    'user' => null,
    'pass' => null,
]]);

$client = $builder->build();

$storage = new ElasticStorage($client, [
    'prefix' => 'pidor_',
    'batchSize' => 2000,
    'indices' => [
        'test' => [
            'mappings' => [
                'dynamic' => false,
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


$storage->reset();
//var_dump(iterator_to_array($storage->fetch())); exit;

$manager = new Manager(new Api, $storage, new Parser);
$manager->update(['ru', 'en', 'de']);