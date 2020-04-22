Translate Storage Manager
=============================

[![Latest Stable Version](https://poser.pugx.org/pervozdanniy/translation-storage/v/stable)](https://packagist.org/packages/pervozdanniy/translation-storage)
![Total Downloads](https://poser.pugx.org/pervozdanniy/translation-storage/downloads)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE)

[API Documentation](http://dev-api.translate.center/api-docs/)


## Installation
The recommended way to install is through
[Composer](http://getcomposer.org).

```bash
# Install Composer
curl -sS https://getcomposer.org/installer | php
```

Next, run the Composer command to install the latest stable version of Guzzle:

```bash
composer require pervozdanniy/translation-storage
```

After installing, you need to require Composer's autoloader:

```php
require 'vendor/autoload.php';
```

You can then later update Guzzle using composer:

 ```bash
composer update
 ```


## Usage

1. Create an api adapter
```php
class ApiAdapter implements \Translate\StorageManager\Contracts\Api
{
    // MUST return data compatible with storage's data structure
    public function fetch(array $params = [], int $page = 1) : array
    {
        // TODO: Implement fetch() method.
        return [];
    }
}
```

2. Initialize Storage Manager
```php
/** @var \Translate\StorageManager\Contracts\Api $api */
$api = new ApiAdapter();

$builder = \Elasticsearch\ClientBuilder::create();
// set all options for elastic client you need
$elastic = $builder->build();
$storage = new \Translate\StorageManager\Storage\ElasticStorage($elastic);
// you can pass any storage you want that implements \Translate\StorageManager\Contracts\TranslationStorage interface
$manager = new \Translate\StorageManager\Manager($api, $storage);
$manager->update(['en', 'es', 'ru']);
```

3. Update your translations whenever you need
```php
/** @var \Translate\StorageManager\Manager $manager*/
//update all translation groups for specified languages
$manager->update(['en', 'es', 'ru']);

//update specified group
$manager->updateGroup('app', ['en', 'es', 'ru']);

```


### Configuration Options

| Option      | Description                                                                                                                                               | Default value                           | 
|-------------|-----------------------------------------------------------------------------------------------------------------------------------------------------------|-----------------------------------------|
| indexName   | Name of index created in Elasticsearch                                                                                                                    | `translation`                           |
| batchSize   | Number fo results storage fetching from Elasticsearch on  `findByGroup` and `search` methods                                                              | `500`                                   |
| refresh     | [refresh option](https://www.elastic.co/guide/en/elasticsearch/reference/current/docs-refresh.html) for `insert` and `bulkInsert` methods   | `false`                                 |