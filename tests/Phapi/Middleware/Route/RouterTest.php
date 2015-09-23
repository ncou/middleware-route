<?php

namespace Phapi\Tests\Middleware\Route;

require_once __DIR__ .'/TestAssets/Page.php';
require_once __DIR__ .'/TestAssets/Article.php';
require_once __DIR__ .'/TestAssets/Users.php';

use Phapi\Middleware\Route\RouteParser;
use Phapi\Middleware\Route\Router;
use PHPUnit_Framework_TestCase as TestCase;

/**
 * @coversDefaultClass \Phapi\Middleware\Route\Router
 */
class RouterTest extends TestCase
{

    public $routes;

    public function setUp()
    {
        $this->routes = [
            '/' => '\\Phapi\\Tests\\Home',
            '/users' => '\\Phapi\\Tests\\Users',
            '/users/{name:a}' => '\\Phapi\\Tests\\User',
            '/articles/{id:[0-9]+}' => '\\Phapi\\Tests\\Article',
            '/color/{id:h}' => '\\Phapi\\Tests\\Color',
            '/products/{name}' => '\\Phapi\\Tests\\Product',
            '/products' => '\\Phapi\\Tests\\Products',
            '/blog/{date:c}?/{title:c}?' => '\\Phapi\\Tests\\Blog\\Post',
            '/page/{slug}/{id:[0-9]+}?' => '\\Phapi\\Tests\\Page',
        ];
    }

    public function testConstructor()
    {
        $cache = \Mockery::mock('Phapi\Contract\Cache\Cache');
        $cache->shouldReceive('get')->with('routeMiddlewareRoutes')->andReturn([ '/somepage/somewhere/' => []]);

        $cache->shouldReceive('set');

        $router = new Router(new RouteParser(), $cache);
        $this->assertInstanceOf('Phapi\Middleware\Route\Router', $router);

        return $router;
    }

    /**
     * @depends testConstructor
     */
    public function testMatch(Router $router)
    {
        $router->addRoutes($this->routes);

        $router->match('/page/someslug/37288', 'GET');
        return $router;
    }

    public function testCacheMatchSuccess()
    {
        $cached = [
            '/users/phapi' => [
                'matchedRoute' => '/users/{name:a}',
                'matchedEndpoint' => '\\Phapi\\Tests\\Users'
            ]
        ];

        $cache = \Mockery::mock('Phapi\Contract\Cache\Cache');
        $cache->shouldReceive('get')->andReturn($cached);

        $router = new Router(new RouteParser(), $cache);
        $router->addRoutes($this->routes);

        $this->assertInstanceOf('Phapi\Middleware\Route\Router', $router);

        $this->assertTrue($router->match('/users/phapi/', 'GET'));
    }

    public function testCacheMatchFail()
    {
        $cached = [
            '/color/54' => [
                'matchedRoute' => '/color/{id:h}',
                'matchedEndpoint' => '\\Phapi\\Tests\\Color'
            ]
        ];

        $cache = \Mockery::mock('Phapi\Contract\Cache\Cache');
        $cache->shouldReceive('get')->andReturn($cached);

        $router = new Router(new RouteParser(), $cache);
        $router->addRoutes($this->routes);

        $this->assertInstanceOf('Phapi\Middleware\Route\Router', $router);

        $this->setExpectedException('\Phapi\Exception\NotFound');
        $router->match('/color/54', 'GET');
    }

    public function testCacheMatchFail2()
    {
        $cached = [
            '/users/phapi' => [
                'matchedRoute' => '/users/{name:a}',
                'matchedEndpoint' => '\\Phapi\\Tests\\Users'
            ]
        ];

        $cache = \Mockery::mock('Phapi\Contract\Cache\Cache');
        $cache->shouldReceive('get')->andReturn($cached);

        $router = new Router(new RouteParser(), $cache);
        $router->addRoutes($this->routes);

        $this->assertInstanceOf('Phapi\Middleware\Route\Router', $router);

        $this->setExpectedException('\Phapi\Exception\MethodNotAllowed');

        $router->match('/users/phapi', 'PUT');
    }

    public function testAddFirstToCache()
    {
        $cache = \Mockery::mock('Phapi\Contract\Cache\Cache');
        $cache->shouldReceive('get')->andReturn(null);
        $cache->shouldReceive('set')->withArgs([
            'routeMiddlewareRoutes',
            [
                '/page/slug/56789' => [
                    'matchedRoute' => '/page/{slug}/{id:[0-9]+}?',
                    'matchedEndpoint' => '\\Phapi\\Tests\\Page',
                    'params' => [
                        'slug' => 'slug',
                        'id' => '56789'
                    ]
                ]
            ]
        ]);

        $router = new Router(new RouteParser(), $cache);
        $router->addRoutes($this->routes);

        $this->assertInstanceOf('Phapi\Middleware\Route\Router', $router);

        $this->assertTrue($router->match('/page/slug/56789', 'GET'));
    }

    /**
     * @depends testConstructor
     */
    public function testSetRoutes(Router $router)
    {
        // add default set of routes
        $router->addRoutes($this->routes);
        $this->assertEquals($router->getRoutes(), $this->routes);

        // add a new dummy route to change route table
        $router->addRoutes(['/help' => '\\Phapi\\Resource\\Help']);
        $this->assertNotEquals($router->getRoutes(), $this->routes);

        // (re)set default routes
        $router->setRoutes($this->routes);
        $this->assertEquals($router->getRoutes(), $this->routes);
    }

    /**
     * @depends testConstructor
     */
    public function testAddRoutes(Router $router)
    {
        $router->addRoutes($this->routes);
        return $router;
    }

    /**
     * @depends testAddRoutes
     */
    public function testGetRoutes(Router $router)
    {
        $this->assertEquals($router->getRoutes(), $this->routes);
    }

    /**
     * @depends testMatch
     */
    public function testGetMatchedMethod(Router $router)
    {
        $this->assertEquals($router->getMatchedMethod(), 'GET');
        $this->assertNotEquals($router->getMatchedMethod(), 'POST');
    }

    /**
     * @depends testMatch
     */
    public function testGetMatchedEndpoint(Router $router)
    {
        $this->assertEquals('\\Phapi\\Tests\\Page', $router->getMatchedEndpoint());
        $this->assertNotEquals('\\Paphi\\Endpoint\\Users', $router->getMatchedEndpoint());
    }

    /**
     * @depends testMatch
     */
    public function testGetMatchedRoute(Router $router)
    {
        $this->assertEquals('/page/{slug}/{id:[0-9]+}?', $router->getMatchedRoute());
        $this->assertNotEquals('/articles/{id:[0-9]+}/', $router->getMatchedRoute());
    }

    /**
     * @depends testMatch
     */
    public function testGetParams(Router $router)
    {
        $this->assertEquals(['slug' => 'someslug', 'id' => '37288'], $router->getParams());
        $this->assertNotEquals(['id' => '37288'], $router->getParams());
    }

    /**
     * @depends testConstructor
     */
    public function testMatchMore(Router $router)
    {
        $router->addRoutes($this->routes);

        $router->match('/users', 'GET');
        $this->assertEquals('\\Phapi\\Tests\\Users', $router->getMatchedEndpoint());

        $router->match('/articles/100', 'GET');
        $this->assertEquals('\\Phapi\\Tests\\Article', $router->getMatchedEndpoint());

        $router->match('/articles/100', 'GET');
        $this->assertEquals('\\Phapi\\Tests\\Article', $router->getMatchedEndpoint());
    }

    /**
     * @depends testConstructor
     */
    public function testMatchRouteNotFound(Router $router)
    {
        $router->addRoutes($this->routes);

        $this->setExpectedException('\Phapi\Exception\NotFound');
        $router->match('/products', 'GET');
    }

    /**
     * @depends testConstructor
     */
    public function testMatchRouteNotFound2(Router $router)
    {
        $router->addRoutes($this->routes);

        $this->setExpectedException('\Phapi\Exception\NotFound');
        $router->match('/nonexisting', 'GET');
    }

    /**
     * @depends testConstructor
     */
    public function testMatchRouteNotFound3(Router $router)
    {
        $router->addRoutes($this->routes);

        $this->setExpectedException('\Phapi\Exception\NotFound');
        $router->match('/blog/2014-03-01/the-title', 'GET');
    }

    /**
     * @depends testConstructor
     */
    public function testMatchMethodNotAllowed(Router $router)
    {
        $router->addRoutes($this->routes);

        $this->setExpectedException('\Phapi\Exception\MethodNotAllowed');
        $router->match('/users', 'PUT');
    }

    /**
     * @depends testConstructor
     */
    public function testMatchMethodNotAllowed2(Router $router)
    {
        $router->addRoutes($this->routes);

        $this->setExpectedException('\Phapi\Exception\MethodNotAllowed');
        $router->match('/articles/189', 'PUT');
    }
}