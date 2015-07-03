<?php

namespace Phapi\Tests\Middleware\Route;

require_once __DIR__ .'/TestAssets/Page.php';

use Phapi\Http\Request;
use Phapi\Http\Response;
use Phapi\Middleware\Route\Dispatcher;
use PHPUnit_Framework_TestCase as TestCase;

/**
 * @coversDefaultClass \Phapi\Middleware\Route\Dispatcher
 */
class DispatcherTest extends TestCase
{

    public function testConstruct()
    {
        // Mock container
        $container = \Mockery::mock('Phapi\Contract\Di\Container');

        $dispatcher = new Dispatcher();
        $dispatcher->setContainer($container);

        $request = new Request();
        $request = $request->withAttribute('routeEndpoint', '\\Phapi\\Tests\\Page');
        $request = $request->withMethod('GET');
        $response = new Response();

        $response = $dispatcher(
            $request,
            $response,
            function ($request, $response) {
                return $response;
            }
        );

        $this->assertEquals(['id' => 123456], $response->getUnserializedBody());
    }

    /*
    public function testEndpointDoesNotExists()
    {
        // Mock container
        $container = \Mockery::mock('Phapi\Contract\Di\Container');

        $dispatcher = new Dispatcher();
        $dispatcher->setContainer($container);

        $request = new Request();
        $response = new Response();

        $this->setExpectedException('\Phapi\Exception\NotFound');
        $response = $dispatcher($request, $response, null);
    }

    public function testMethodNotExists()
    {
        // Mock container
        $container = \Mockery::mock('Phapi\Contract\Di\Container');

        $dispatcher = new Dispatcher();
        $dispatcher->setContainer($container);

        $request = new Request();
        $request = $request->withAttribute('routeEndpoint', '\\Phapi\\Tests\\Page');
        $request = $request->withMethod('POST');
        $response = new Response();

        $this->setExpectedException('\Phapi\Exception\MethodNotAllowed');
        $response = $dispatcher($request, $response, function ($request, $response) { return $response; });
    }

    public function testResponseNotCompatible()
    {
        $container = \Mockery::mock('Phapi\Contract\Di\Container');

        $dispatcher = new Dispatcher();
        $dispatcher->setContainer($container);

        $request = new Request();
        $response = \Mockery::mock('Psr\Http\Message\ResponseInterface');

        $this->setExpectedException('\RuntimeException', 'The dispatcher middleware requires a response object that can handle unserialized body.');
        $response = $dispatcher($request, $response, null);
    }
    */
}