<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Elastic\Elasticsearch\ClientInterface;
use App\Models\Route;
use App\Models\City;
use App\Models\Country;

class ReindexRoutesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'search:routes:reindex';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reindex all routes';

    public function __construct(protected ClientInterface $elasticsearch)
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Indexing all routes...');
//        if ($this->elasticsearch->indices()->exists(['index' => 'routes'])) {
//            $this->elasticsearch->indices()->delete(['index' => 'routes']);
//        }
        $this->elasticsearch->indices()->create(
            [
                'index' => 'routes',
                'body' => [
                    'settings' => [
                        "analysis" => [
                            "analyzer" => ["my_analyzer" => ["tokenizer" => "my_tokenizer"]],
                            "tokenizer" => [
                                "my_tokenizer" => [
                                    "type" => "ngram",
                                    "min_gram" => 2,
                                    "max_gram" => 15,
                                ],
                            ],
                        ],
                        "max_ngram_diff" => 15
                    ],
                    'mappings' => [
                        '_source' => [
                            'enabled' => true
                        ],
                        'properties' => [
                            'id' => [
                                'type' => 'integer'
                            ],
                            'fromCityName' => [
                                'type' => 'text',
                                'analyzer' => 'my_analyzer'
                            ],
                            'toCityName' => [
                                'type' => 'text',
                                'analyzer' => 'my_analyzer'
                            ],
                        ]
                    ]
                ]
            ],
        );
        $routes = Route::where('status', 'open')->get(); //status
        $count  = $routes->count();
        $i      = 0;
        $params = ['body' => []];
        $cities = [];

        foreach ($routes as $route) {
            $i++;
            $this->output->write($i . '/'  . $count . ': ' . $route->title . PHP_EOL);

            try {
                $fromCity         = City::find($route->route_from_city_id);
                $toCity           = City::find($route->route_to_city_id);
                $fromCountry      = Country::find($route->route_from_country_id);
                $toCountry        = Country::find($route->route_to_country_id);

                if ($i == 1) {
                    $cities[] = [
                        'cityName' => $fromCity->name,
                        'countryName' => $fromCountry->name,
                    ];
                } else {
                    if (!in_array($fromCity->name, array_column($cities, 'cityName'))) {
                        $cities[] = [
                            'cityName' => $fromCity->name,
                            'countryName' => $fromCountry->name,
                        ];
                    }
                }

                if ($i == 1) {
                    $cities[] = [
                        'cityName' => $toCity->name,
                        'countryName' => $toCountry->name,
                    ];
                } else {
                    if (!in_array($toCity->name, array_column($cities, 'cityName'))) {
                        $cities[] = [
                            'cityName' => $toCity->name,
                            'countryName' => $toCountry->name,
                        ];
                    }
                }

                $params['body'][] = [
                    'index' => [
                        '_index' => 'routes',
                    ]
                ];

                $params['body'][] = [
                    'id'               => $route->id,
                    'fromCityName'     => $fromCity->name,
                    'toCityName'       => $toCity->name,
                    'fromCountryName'  => $fromCountry->name,
                    'toCountryName'    => $toCountry->name,

                ];
            } catch (\Exception $exception) {
                $this->output->write("Error: route $route->title");
                continue;
            }
        }

        $this->elasticsearch->bulk($params);
        $this->indexCities($cities);

        return Command::SUCCESS;
    }

    private function indexCities(array $cities)
    {
//        if ($this->elasticsearch->indices()->exists(['index' => 'cities'])) {
//            $this->elasticsearch->indices()->delete(['index' => 'cities']);
//        }
        $this->elasticsearch->indices()->create(
            [
                'index' => 'cities',
                'body' => [
                    'settings' => [
                        "analysis" => [
                            "analyzer" => ["my_analyzer" => ["tokenizer" => "my_tokenizer"]],
                            "tokenizer" => [
                                "my_tokenizer" => [
                                    "type" => "ngram",
                                    "min_gram" => 2,
                                    "max_gram" => 10,
                                ],
                            ],
                        ],
                        "max_ngram_diff" => 10
                    ],
                    'mappings' => [
                        '_source' => [
                            'enabled' => true
                        ],
                        'properties' => [
                            'city' => [
                                'type' => 'text',
                                'analyzer' => 'my_analyzer'
                            ],
                            'direction' => [
                                'type' => 'text',
                                'analyzer' => 'my_analyzer'
                            ],
                        ]
                    ]
                ]
            ],
        );
        
        $params = ['body' => []];

        foreach ($cities as $city) {
            $params['body'][] = [
                'index' => [
                    '_index' => 'cities',
                ]
            ];

            $params['body'][] = [
                'city'      => $city['cityName'],
                'country'   => $city['countryName'],
            ];
        }

        $this->elasticsearch->bulk($params);
    }
}
