<?php

namespace App\Repositories;

use App\Services\Search\ElasticSearchService;

class RouteRepository
{
    public function __construct(
        protected ElasticSearchService $elasticService
    )
    {
    }

    public function find(string $fromCity, string $toCity)
    {
        $query = [
            'bool' => [
                'must' => [
                    [ 'query_string' => ['default_field' => 'fromCityName', 'query' => '*' . $fromCity . '*' ] ],
                    [ 'query_string' => [ 'default_field' => 'toCityName', 'query' => '*' . $toCity . '*' ] ],
                ]
            ]
        ];

        $routes = $this->elasticService->search($query, 'routes');
        $routes = $this->setRoutesInvert($routes, 0);

        $queryInvert = [
            'bool' => [
                'must' => [
                    [ 'query_string' => ['default_field' => 'toCityName', 'query' => '*' . $fromCity . '*' ] ],
                    [ 'query_string' => [ 'default_field' => 'fromCityName', 'query' => '*' . $toCity . '*' ] ],
                ]
            ]
        ];

        $routesInvert = $this->elasticService->search($queryInvert, 'routes');
        $routesInvert = $this->setRoutesInvert($routesInvert, 1);

        return array_merge($routes, $routesInvert);
    }

    private function setRoutesInvert(array $routes, int $invert)
    {
        foreach ($routes as &$route) {
            $route['_source']['invert'] = $invert;
        }

        return $routes;
    }
}
