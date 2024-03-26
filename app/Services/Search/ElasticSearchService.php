<?php

namespace App\Services\Search;

use Elastic\Elasticsearch\ClientInterface;

class ElasticSearchService
{
    public function __construct(
        protected ClientInterface $elasticClient
    )
    {
    }

    public function search(array $query, string $index, int $size = 9999)
    {
        return $this->elasticClient->search([
            'index' => $index,
            'body' => [
                'query' => $query
            ],
            'size' => $size,
            'client' => [
                'curl' => [CURLOPT_HTTPHEADER => ['Content-type: application/json']]
            ],
        ])['hits']['hits'];
    }
}

