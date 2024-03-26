<?php

namespace App\Repositories;

use App\Services\Search\ElasticSearchService;

class CityRepository
{
    public function __construct(
        protected ElasticSearchService $elasticService
    )
    {
    }

    public function find(string $city)
    {
        $query = [
            'bool' => [
                'must' => [
                    [ 'query_string' => ['default_field' => 'city', 'query' => '*' . $city . '*' ] ],
                ]
            ]
        ];

        return $this->elasticService->search($query, 'cities');
    }
}
