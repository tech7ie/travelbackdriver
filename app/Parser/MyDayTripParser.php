<?php

namespace App\Parser;

use App\Services\Parser\Parser;
use App\Services\Parser\HtmlParser;
use App\Services\Parser\StringParser;

class MyDayTripParser extends Parser
{
    private $contentType = 'json';
    private $routeIdUrl = 'https://mydaytrip.com/graphql?query=query%20RouteAndDirectionByMachineName($machineName:%20String!)%20%7B%20routeByMachineName(machineName:%20$machineName)%20%7B%20_id%20isOtherDirection%20poolRoute%20%7B%20_id%20minimumPricePerSeat%20__typename%20%7D%20originLocation%20%7B%20_id%20name%20image%20machineName%20timezone%20country%20%7B%20englishName%20__typename%20%7D%20__typename%20%7D%20destinationLocation%20%7B%20_id%20name%20image%20machineName%20timezone%20country%20%7B%20englishName%20__typename%20%7D%20__typename%20%7D%20availableFrom%20routeGeographyFlag%20vehicleTypesPricesFees%20%7B%20vehicleType%20__typename%20%7D%20locations%20%7B%20_id%20name%20image%20title%20perex%20description%20isSuggested%20defaultDuration%20order%20timezone%20country%20%7B%20englishName%20__typename%20%7D%20__typename%20%7D%20travelData%20%7B%20distance%20duration%20__typename%20%7D%20pricingCurrency%20isBidirectional%20__typename%20%7D%20%7D%20&operationName=RouteAndDirectionByMachineName&variables=%7B%22machineName%22:%22{{cities}}%22%7D';
    //private $routePriceUrl = 'https://mydaytrip.com/configurator?adults=2&children=0&currency=0&departureAt=1735614000000&isOtherDirection=true&luggage=2&passengers=2&routeId={{routeId}}&vehicles=0';
    private $routePriceUrl = 'https://mydaytrip.com/graphql';
    private $routeLocationsUrl = 'https://mydaytrip.com/graphql?query=fragment LocationForBooking on Location { _id name image machineName timezone country { _id englishName isoCode __typename } position { latitude longitude __typename } radiusKm countryIso __typename } query RouteForBookingById($_id: String!, $originId: String, $isOtherDirection: Boolean) { route(_id: $_id, originId: $originId, isOtherDirection: $isOtherDirection) { _id poolRoute { _id minimumPricePerSeat __typename } availableFrom isOtherDirection originLocation { ...LocationForBooking __typename } destinationLocation { ...LocationForBooking __typename } routeGeographyFlag vehicleTypesPricesFees { vehicleType __typename } locations { _id name image title perex description isSuggested defaultDuration order timezone country { englishName __typename } __typename } travelData { distance duration __typename } pricingCurrency isBidirectional __typename } } &operationName=RouteForBookingById&variables={"_id":"{{routeId}}","isOtherDirection":false}';

    protected $cities = '';

    protected $httpOptions = [
        'method' => 'GET',
        'body'   => [],
    ];

    public function getData(): ?array
    {
        $routeId = $this->getRouteId();
        
        if (empty($routeId)) {
            return null;
        }

        return $this->getPrice($routeId);
    }

    private function getRouteId(): string
    {
        $routeIdUrl = str_replace('{{cities}}', $this->cities, $this->routeIdUrl);

        $response = $this->doRequest($routeIdUrl, [], $this->httpOptions);

        return json_decode($response['response'])->data->routeByMachineName->_id ?? '';
    }

    private function getPrice(string $routeId): array
    {
        $locations = $this->getLocations($routeId);
        $body['body']['query'] = 'mutation RequestManagedOffer($offerRequest: ManagedOfferRequestInput!) {  requestManagedOffer(offerRequest: $offerRequest) {    ...OfferForBooking    __typename  }}fragment OfferForBooking on Offer {  _id  finalPrice {    total    fee    __typename  }  totalPrice {    total    fee    __typename  }  vehicles {    vehicleType    totalPrice {      total      fee      __typename    }    finalPrice {      total      fee      __typename    }    productConfig {      englishSpeakingDriver      stopsEnabled      __typename    }    __typename  }  stops {    locationId    totalPrice {      total      fee      __typename    }    finalPrice {      total      fee      __typename    }    waitingPrice {      total      fee      __typename    }    location {      ...LocationStopForBooking      __typename    }    __typename  }  pricingCurrency  createdAt  __typename}fragment LocationStopForBooking on Location {  _id  name  image  title  perex  description  isSuggested  defaultDuration  order  timezone  country {    englishName    __typename  }  __typename}';
        $body['body']['variables'] = '{"offerRequest":{"departureAt":"2024-12-31T17:45:00.000Z","routeId": "' . $routeId . '","originLocationId": "' . $locations['originLocationId'] .'","destinationLocationId":"' . $locations['destinationLocationId'] . '","stopsParameters":' . json_encode($locations['locations']) . ',"selectedStops":[],"selectedVehicles":[0]}}';
        $body['body']['operationName'] = "RequestManagedOffer";
        $body['method'] = 'POST';

        $response = $this->doRequest($this->routePriceUrl, ['Referer'=>'https://mydaytrip.com/configurator?adults=2&children=0&currency=0&departureAt=1735639200000&isOtherDirection=false&luggage=2&passengers=2&routeId="' . $routeId . '"&vehicles=0'], $body);
        $priceData = json_decode($response['response'])->data->requestManagedOffer;

        $places = $this->serializePlaces($priceData->stops);
        $cars = $this->serializeCars($priceData->vehicles);

        return [
            'price' => $priceData->totalPrice->total,
            'places' => $places,
            'cars'   => $cars,
        ];
    }

    private function getLocations(string $routeId)
    {
        $routeLocationsUrl = str_replace('{{routeId}}', $routeId, $this->routeLocationsUrl);
        $response = $this->doRequest($routeLocationsUrl, [], $this->httpOptions);

        if (!$response['status']) {
            return null;
        }

        $locationsData = json_decode($response['response'])->data->route;
        $locations = [];
        $locations['originLocationId'] = $locationsData->originLocation->_id;
        $locations['destinationLocationId'] = $locationsData->destinationLocation->_id;
        $locations['departureAt'] = $locationsData->availableFrom;

        foreach($locationsData->locations as $location) {
            $locations['locations'][] = [
                'locationId' => $location->_id,
                'duration' => $location->defaultDuration
            ];
        }

        return $locations;
    }

    private function serializePlaces(array $places): array
    {
        $placesResult = [];

        foreach ($places as $place) {
            $placesResult[] = [
                'location' => $place->location->name,
                'price' => $place->finalPrice->total,
            ];
        }

        return $placesResult;
    }

    private function serializeCars(array $cars): array
    {
        $carsResult = [];

        foreach ($cars as $car) {
            $carsResult[] = [
                'type' => $car->vehicleType,
                'price' => $car->finalPrice->total,
            ];
        }

        return $carsResult;
    }

    public function setCities(string $cities): void
    {
        $this->cities = $cities;
    }
}
