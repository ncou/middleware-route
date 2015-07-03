<?php

namespace Phapi\Tests\Middleware\Route;

require_once __DIR__ .'/TestAssets/Page.php';

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

        $request = \Mockery::mock('Psr\Http\Message\ServerRequestInterface');
        $request->shouldReceive('getAttribute')->with('routeEndpoint', '')->andReturn('\\Phapi\\Tests\\Page');
        $request->shouldReceive('getMethod')->andReturn('GET');
        $request->shouldReceive('getAttribute')->with('routeParams', [])->andReturn([]);

        $response = \Mockery::mock('Psr\Http\Message\ResponseInterface');
        $response->shouldReceive('withUnserializedBody')->with([ 'id' => 123456 ])->andReturnSelf();

        $response = $dispatcher(
            $request,
            $response,
            function ($request, $response) {
                return $response;
            }
        );
    }

    public function testEndpointDoesNotExists()
    {
        // Mock container
        $container = \Mockery::mock('Phapi\Contract\Di\Container');

        $dispatcher = new Dispatcher();
        $dispatcher->setContainer($container);

        $request = \Mockery::mock('Psr\Http\Message\ServerRequestInterface');
        $request->shouldReceive('getAttribute')->with('routeEndpoint', '')->andReturn('');
        $request->shouldReceive('getMethod')->andReturn('POST');

        $response = \Mockery::mock('Psr\Http\Message\ResponseInterface');

        $this->setExpectedException('\Phapi\Exception\NotFound');
        $response = $dispatcher($request, $response, null);
    }

    public function testMethodNotExists()
    {
        // Mock container
        $container = \Mockery::mock('Phapi\Contract\Di\Container');

        $dispatcher = new Dispatcher();
        $dispatcher->setContainer($container);

        $request = \Mockery::mock('Psr\Http\Message\ServerRequestInterface');
        $request->shouldReceive('getAttribute')->with('routeEndpoint', '')->andReturn('\\Phapi\\Tests\\Page');
        $request->shouldReceive('getMethod')->andReturn('POST');

        $response = \Mockery::mock('Psr\Http\Message\ResponseInterface');

        $this->setExpectedException('\Phapi\Exception\MethodNotAllowed');
        $response = $dispatcher($request, $response, function ($request, $response) { return $response; });
    }

    public function testResponseNotCompatible()
    {
        $container = \Mockery::mock('Phapi\Contract\Di\Container');

        $dispatcher = new Dispatcher();
        $dispatcher->setContainer($container);

        $request = \Mockery::mock('Psr\Http\Message\ServerRequestInterface');
        $request->shouldReceive('getAttribute')->with('routeEndpoint', '')->andReturn('\\Phapi\\Tests\\Page');
        $request->shouldReceive('getMethod')->andReturn('GET');
        $request->shouldReceive('getAttribute')->with('routeParams', [])->andReturn([]);

        $response = \Mockery::mock('Psr\Http\Message\ResponseInterface');

        $this->setExpectedException('\RuntimeException', 'The dispatcher middleware requires a response object that can handle unserialized body.');
        $response = $dispatcher($request, $response, null);
    }
}