<?php

namespace Pervozdanniy\TranslationStorage\Tests\Dynamic;

use Translate\ApiClient;
use Translate\Storage\ArrayStorage;

class Api implements \Pervozdanniy\TranslationStorage\Contracts\Api
{
    private $http;

    public function __construct()
    {
        $this->http = new ApiClient([
            'login' => 'vladislav.dneprov1995@gmail.com',
            'password' => 'f84rdc9w',
            'api' => 'https://api.translate.center/api/v1/'
        ], new ArrayStorage);
        $this->http->setAlias('projectUuid', 'f85fa51e-edc2-4044-8ad5-0e63271230cd');
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