<?php

declare(strict_types=1);

namespace Peso\Services\Tests\Helpers;

use GuzzleHttp\Psr7\Response;
use Http\Message\RequestMatcher\RequestMatcher;
use Http\Mock\Client;
use Psr\Http\Message\RequestInterface;

final readonly class MockClient
{
    public static function get(): Client
    {
        $client = new Client();

        $client->on(
            new RequestMatcher('/v1/latest', 'api.freecurrencyapi.com', ['GET'], ['https']),
            static function (RequestInterface $request) {
                $query = $request->getUri()->getQuery();
                switch ($query) {
                    case 'apikey=xxxfreexxx&base_currency=EUR':
                        return new Response(body: fopen(__DIR__ . '/../data/latest-EUR.json', 'r'));

                    case 'apikey=xxxfreexxx&base_currency=USD':
                        return new Response(body: fopen(__DIR__ . '/../data/latest-USD.json', 'r'));

                    case 'apikey=xxxfreexxx&base_currency=RUB':
                        return new Response(body: fopen(__DIR__ . '/../data/latest-RUB.json', 'r'));

                    case 'apikey=xxxfreexxx&base_currency=EUR&currencies=EUR%2CUSD':
                        return new Response(body: fopen(__DIR__ . '/../data/latest-EUR-EUR,USD.json', 'r'));

                    case 'apikey=xxxfreexxx&base_currency=USD&currencies=EUR%2CUSD':
                        return new Response(body: fopen(__DIR__ . '/../data/latest-USD-EUR,USD.json', 'r'));

                    case 'apikey=xxxfreexxx&base_currency=RUB&currencies=EUR%2CUSD':
                        return new Response(body: fopen(__DIR__ . '/../data/latest-RUB-EUR,USD.json', 'r'));

                    case 'apikey=rate_exceeded&base_currency=TRY':
                        return new Response(429);

                    case 'apikey=invalid&base_currency=TRY':
                        return new Response(401, body: fopen(__DIR__ . '/../data/invalid-key.json', 'r'));

                    case 'apikey=xxxfreexxx&base_currency=XBT':
                        return new Response(422, body: fopen(__DIR__ . '/../data/invalid-base.json', 'r'));

                    default:
                        throw new \LogicException('Non-mocked query: ' . $query);
                }
            },
        );
        $client->on(
            new RequestMatcher('/v1/historical', 'api.freecurrencyapi.com', ['GET'], ['https']),
            static function (RequestInterface $request) {
                $query = $request->getUri()->getQuery();
                switch ($query) {
                    case 'apikey=xxxfreexxx&date=2025-06-13&base_currency=EUR':
                        return new Response(body: fopen(__DIR__ . '/../data/2025-06-13-EUR.json', 'r'));

                    case 'apikey=xxxfreexxx&date=2025-06-13&base_currency=USD':
                        return new Response(body: fopen(__DIR__ . '/../data/2025-06-13-USD.json', 'r'));

                    case 'apikey=xxxfreexxx&date=2025-06-13&base_currency=RUB':
                        return new Response(body: fopen(__DIR__ . '/../data/2025-06-13-RUB.json', 'r'));

                    case 'apikey=xxxfreexxx&date=2025-06-13&base_currency=EUR&currencies=EUR%2CUSD':
                        return new Response(body: fopen(__DIR__ . '/../data/2025-06-13-EUR-EUR,USD.json', 'r'));

                    case 'apikey=xxxfreexxx&date=2025-06-13&base_currency=USD&currencies=EUR%2CUSD':
                        return new Response(body: fopen(__DIR__ . '/../data/2025-06-13-USD-EUR,USD.json', 'r'));

                    case 'apikey=xxxfreexxx&date=2025-06-13&base_currency=RUB&currencies=EUR%2CUSD':
                        return new Response(body: fopen(__DIR__ . '/../data/2025-06-13-RUB-EUR,USD.json', 'r'));

                    case 'apikey=xxxfreexxx&date=2025-06-13&base_currency=XBT':
                        return new Response(422, body: fopen(__DIR__ . '/../data/invalid-base.json', 'r'));

                    default:
                        throw new \LogicException('Non-mocked query: ' . $query);
                }
            },
        );

        return $client;
    }
}
