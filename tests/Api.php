<?php

namespace Tests;

use Translate\ApiClient;
use Translate\Storage\ArrayStorage;

class Api implements \Translate\StorageManager\Contracts\Api
{
    private $http;

    public function __construct()
    {
        $this->http = new ApiClient([
            'login' => 'pervozadiy@gmail.com',
            'password' => 'pzgjc407',
            'api' => 'https://api.translate.center/api/v1/'
        ], new ArrayStorage);
        $this->http->setAlias('projectUuid', '9295be96-9cfd-4466-9ca9-46a19ce9b3b1');
    }

    public function list(array $queryParams = ['pageNum' => 1, 'pageSize' => 200]): array
    {
        $response = $this->http->request('GET', 'projects/{projectUuid}/resources', [
            'query' => $queryParams
        ]);

        return json_decode($response->getBody(), true);
    }

    /**
     * @inheritDoc
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Psr\SimpleCache\InvalidArgumentException
     * @throws \JsonException
     */
    public function fetch(array $params = [], int $page = 1): array
    {
        return $this->list(array_merge($params, ['pageNum' => $page, 'pageSize' => 500]));
    }
}