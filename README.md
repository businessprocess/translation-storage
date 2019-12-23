Translate Storage Manager
=============================

[![Latest Stable Version](https://poser.pugx.org/pervozdanniy/translation-client/v/stable)](https://packagist.org/packages/pervozdanniy/translation-client)
![Total Downloads](https://poser.pugx.org/pervozdanniy/translation-client/downloads)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE)

Translate API is a PSR-compatible PHP HTTP client for working with translate API.

[API Documentation](http://dev-api.translate.center/api-docs/)


## Installation
The recommended way to install Translate API client is through
[Composer](http://getcomposer.org).

```bash
# Install Composer
curl -sS https://getcomposer.org/installer | php
```

Next, run the Composer command to install the latest stable version of Guzzle:

```bash
composer require pervozdanniy/translation-client
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

```php
$options = [
    'login' => '<YOUR_LOGIN>',
    'password' => '<YOUR_PASSWORD>',
];
// you can pass any storage you want that implements \Psr\SimpleCache\CacheInterface
$client = new \Translate\ApiClient($options, new \Translate\Storage\ArrayStorage);
$response = $client->request('GET', 'users');

echo $response->getStatusCode(); # 200
echo $response->getHeaderLine('content-type'); # 'application/json; charset=utf8'
echo $response->getBody(); # '{"items": [{"uuid": ...}'
```

#### Aliases
Client also resolves aliases, received from login request:
```php
$response = $client->request('GET', 'users/{userUuid}/projects');
```
For authenticated user 2 aliases are available by default:
`userUuid` and `authToken`


You can add your own aliases using:
```php
$client->setAlias('projectUuid', '<PROJECT_UUID>');
// use user-defined alias
$response = $client->request('GET', 'projects/{projectUuid}/languages');
```

#### Available Options

| Option      | Description                                                      | Default value                           | 
|-------------|------------------------------------------------------------------|-----------------------------------------|
| login       | Your API login (required)                                        | null                                    |
| password    | Your API password (required)                                     | null                                    |
| api         | API base uri                                                     | http://dev-api.translate.center/api/v1/ |
| maxAttempts | Number of attempts to reauthenticate to API on 401 response code | 3                                       |