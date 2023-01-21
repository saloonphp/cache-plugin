<?php

declare(strict_types=1);

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Sammyjo20\Saloon\Http\MockResponse;
use Sammyjo20\Saloon\Clients\MockClient;
use Saloon\CachePlugin\Tests\Fixtures\Stores\ArrayCache;
use Saloon\CachePlugin\Tests\Fixtures\Requests\SimpleCachedUserRequest;

test('the response body is rewound after caching for other middleware', function () {
    $mockClient = new MockClient([
        MockResponse::make(['name' => 'Sam']),
    ]);

    $arrayCache = new ArrayCache();

    $requestA = new SimpleCachedUserRequest($arrayCache);

    // Register another handler...

    $requestA->addHandler('rewindTest', function (callable $handler) {
        return function (RequestInterface $request, array $options) use ($handler) {
            return $handler($request, $options)->then(function (ResponseInterface $response) {
                $data = $response->getBody()->getContents();

                expect($data)->toEqual('{"name":"Sam"}');

                return $response;
            });
        };
    });

    $responseA = $requestA->send($mockClient);

    expect($responseA->isCached())->toBeFalse();
    expect($responseA->json())->toEqual(['name' => 'Sam']);
    expect($arrayCache->all())->toHaveCount(1);
});
