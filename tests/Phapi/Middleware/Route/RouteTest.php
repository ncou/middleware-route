<?php

namespace Phapi\Tests\Middleware\Route;

use Phapi\Http\Request;
use Phapi\Http\Response;
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

        $route = new Route($router);
        $route->addRoutes($routes);

        $response = $route(
            new Request(),
            new Response(),
            function ($request, $response) {
                $expected = [
                    'routeEndpoint' => 'Phapi\Endpoint\Home',
                    'username' => 'phapi'
                ];
                $this->assertEquals($expected, $request->getAttributes());

                return $response;
            }
        );
    }

}