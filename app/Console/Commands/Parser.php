<?php

namespace App\Console\Commands;

use App\Models\Route;
use App\Models\Place;
use App\Models\RouteCar;
use Illuminate\Console\Command;
use App\Parser\MyDayTripParser;
use Illuminate\Console\Input\InputOption;
use Illuminate\Console\Input\InputArgument;

class Parser extends Command
{
    public const SOURCE_CARS = [
        1 => 0,
        2 => 1,
        4 => 2,
        10 => 3,
    ];
    
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'parser:run {--route_id=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Парсинг сайтов';

    /**
     * Execute the console command.
     *
     */
    public function handle()
    {
        $parser = new MyDayTripParser();
        if (!empty($this->option('route_id'))) {
            $routes = Route::select('id', 'title', 'price')->where([['status', 'open'], ['id', $this->option('route_id')]])->get();
        } else {
            $routes = Route::select('id', 'title', 'price')->where('status', 'open')->get();
        }

        $routesCount = $routes->count();
        $i = 0;
        $routeNotFoundCount = 0;
        $routeParsedSuccessCount = 0;

        foreach ($routes as $route) {
            $i++;
            $cities = str_replace(' ', '-', strtolower($route->title));
            $cars   = RouteCar::where('route_id', $route->id)->get();
            $parser->setCities($cities);
            $parseData = $parser->getData(); // Получаем новую цену и цены мест

            if (!$parseData) {
                var_dump("Route $route->title not found!");
                $routeNotFoundCount++;
                continue;
            }

            $newPrice = ceil($parseData['price'] * 0.96);

            if (!empty($newPrice) && $newPrice != $route->price) {
                var_dump("$i/$routesCount $route->title $route->price|$newPrice");
                $route->update([
                    'price' => $newPrice
                ]);
            }
            
            if (count($parseData['places'])) {
                var_dump("Обновляем цены мест(Places)...");
                $placesUpdates = [];
                foreach ($parseData['places'] as $place) {
                    $placeExist = Place::where('title_en', $place['location'])->first();
                    $placeExist = $placeExist ?: Place::where('title_en', $place['location'] . ' City')->first();
                    if ($placeExist) {
                        $placesUpdates[] = $placeExist->title_en;
                        $placeExist->update([
                            'price' => ceil($place['price'] * 0.9)
                        ]);
                    }
                }
                var_dump("Места которые обновоились: " . implode(', ', $placesUpdates));
            }
            
            if ($cars->count() && count($parseData['cars'])) {
                foreach ($cars as $car) {
                    if (!array_key_exists($car->car_id, self::SOURCE_CARS)) {
                        continue;
                    }
                    $searchCarId = self::SOURCE_CARS[$car->car_id];
                    foreach ($parseData['cars'] as $parsedDataCar) {
                        if ($parsedDataCar['type'] === $searchCarId) {
                            $car->update([
                                'price' => ceil($parsedDataCar['price'] * 0.96)
                            ]);
                            break;
                        }
                    }
                }
            }
            
            $routeParsedSuccessCount++;
        }
        
        var_dump("Парсинг цен завершен успешно, спарсилось $routeParsedSuccessCount, не спарсилось $routeNotFoundCount");

        return 1;
    }
}
