<?php

namespace Phapi\Tests\Middleware\Route;

use Phapi\Middleware\Route\RouteParser;
use PHPUnit_Framework_TestCase as TestCase;

/**
 * @coversDefaultClass \Phapi\Middleware\Route\RouteParser
 */
class RouteParserTest extends TestCase
{

    protected $routes = [
        '/'                          => ["#^/(/)?$#", []],
        '/users'                     => ["#^/users(/)?$#", []],
        '/users/{name:a}'            => ["#^/users/([0-9A-Za-z]+)(/)?$#", ['name']],
        '/articles/{id:[0-9]+}'      => ["#^/articles/([0-9]+)(/)?$#", ['id']],
        '/color/{id:h}'              => ["#^/color/([0-9A-Fa-f]+)(/)?$#", ['id']],
        '/product/{name}'            => ["#^/product/([^/]+)(/)?$#", ['name']],
        '/blog/{date:c}?/{title:c}?' => ["#^/blog(/)?([a-zA-Z0-9+_\-.]+)?(/)?([a-zA-Z0-9+_\-.]+)?(/)?$#", ['date', 'title']],
        '/page/{slug}/{id:[0-9]+}?'  => ["#^/page/([^/]+)(/)?([0-9]+)?(/)?$#", ['slug', 'id']]
    ];

    public function testParse()
    {
        $parser = new RouteParser();

        foreach ($this->routes as $route => $regex) {
            $this->assertEquals($parser->parse($route), $regex);
        }
    }
}