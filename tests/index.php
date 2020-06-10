#!/usr/bin/php
<?php

use Elasticsearch\ClientBuilder;
use Pervozdanniy\TranslationStorage\Manager\DynamicManager;
use Pervozdanniy\TranslationStorage\Storage\Elastic\SpreadIndexStorage;
use Pervozdanniy\TranslationStorage\Tests\Dynamic\Api as DynamicApi;
use Pervozdanniy\TranslationStorage\Tests\Dynamic\Parser as DynamicParser;
use Pervozdanniy\TranslationStorage\Tests\Stat\Api as StaticApi;
use Pervozdanniy\TranslationStorage\Tests\Stat\Parser as StaticParser;

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

$dynamicStorage = new SpreadIndexStorage($client, [
    'prefix' => 'pidor_',
    'batchSize' => 2000,
    'indices' => [
        'db_spine_infractions' => [
            'mappings' => [
                'dynamic' => false,
                'properties' => [
                    'id' => ['type' => 'keyword'],
                    'lang' => ['type' => 'keyword'],
                    'title' => ['type' => 'keyword']
                ]
            ]
        ],
        'db_amino_acids' => [
            'mappings' => [
                'dynamic' => false,
                'properties' => [
                    'id' => ['type' => 'keyword'],
                    'lang' => ['type' => 'keyword'],
                    'title' => ['type' => 'keyword'],
                    'description' => ['type' => 'text']
                ]
            ]
        ]
    ]
]);

$staticStatic = new \Pervozdanniy\TranslationStorage\Storage\Elastic\SimpleStorage($client);
$staticManager = new \Pervozdanniy\TranslationStorage\Manager\StaticManager(new StaticApi(), $staticStatic, new StaticParser());
$staticManager->updateGroup('dynamic', ['ru', 'en', 'de']);

//$storage->reset();
//exit;
//var_dump(iterator_to_array($storage->fetch())); exit;

$manager = new DynamicManager(new DynamicApi, $dynamicStorage, new DynamicParser);
$langs = array_values([
    'ru' => 'ru',
    'de' => 'de', 'en' => 'en', 'es' => 'es', 'el' => 'el', 'he' => 'he', 'it' => 'it', 'lt' => 'lt', 'mn' => 'mn',
    'pl' => 'pl', 'ar' => 'ar', 'sr' => 'sr', 'uk' => 'uk', 'zh' => 'zh', 'tr' => 'tr'
]);
$manager->updateGroup('db_amino_acids', $langs);
$manager->updateGroup('db_spine_infractions', $langs);