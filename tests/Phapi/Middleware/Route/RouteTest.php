<?php

namespace Phapi\Tests\Middleware\Route;

use Phapi\Middleware\Route\Route;
use PHPUnit_Framework_TestCase as TestCase;

/**
 * @coversDefaultClass \Phapi\Middleware\Route\Route
 */
class RouteTest extends TestCase
{

    public function testConstruct()
    {
        $routes = [
            '/' => '\\Phapi\\Tests\\Home',
            '/users/' => '\\Phapi\\Tests\\Users',
            '/users/{name:a}/' => '\\Phapi\\Tests\\User',
            '/articles/{id:[0-9]+}/' => '\\Phapi\\Tests\\Article',
            '/color/{id:h}/' => '\\Phapi\\Tests\\Color',
            '/products/{name}/' => '\\Phapi\\Tests\\Product',
            '/products/' => '\\Phapi\\Tests\\Products',
            '/blog/{date:c}?/{title:c}?/' => '\\Phapi\\Tests\\Blog\\Post',
            '/page/{slug}/{id:[0-9]+}?/' => '\\Phapi\\Tests\\Page',
        ];

        $router = \Mockery::mock('Phapi\Middleware\Route\Router');
        $router->shouldReceive('match')->andReturn(true);
        $router->shouldReceive('getMatchedEndpoint')->andReturn('Phapi\Endpoint\Home');
        $router->shouldReceive('getParams')->andReturn([ 'username' => 'phapi' ]);
        $router->shouldReceive('addRoutes')->withArgs([$routes]);

        $response = \Mockery::mock('Psr\Http\Message\ResponseInterface');

        $request = \Mockery::mock('Psr\Http\Message\ServerRequestInterface');
        $request->shouldReceive('getUri')->andReturnSelf();
        $request->shouldReceive('getPath')->andReturn('/');
        $request->shouldReceive('getMethod')->andReturn('GET');
        $request->shouldReceive('withAttribute')->withArgs([ 'routeEndpoint', 'Phapi\Endpoint\Home' ])->andReturnSelf();
        $request->shouldReceive('withAttribute')->withArgs([ 'routeParams', ['username' => 'phapi']])->andReturnSelf();

        $route = new Route($router);
        $route->addRoutes($routes);

        $response = $route(
            $request,
            $response,
            function ($request, $response) {

                return $response;
            }
        );
    }
}
