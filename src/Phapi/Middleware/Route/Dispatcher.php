<?php

namespace Phapi\Middleware\Route;

use Phapi\Contract\Di\Container;
use Phapi\Contract\Middleware\Middleware;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Phapi\Exception\MethodNotAllowed;
use Phapi\Exception\NotFound;

/**
 * Dispatcher Middleware
 *
 * Middleware handling the dispatch to an endpoint. Uses the information
 * the router provided by looking at the request attributes.
 *
 * @category Phapi
 * @package  Phapi\Middleware\Route
 * @author   Peter Ahinko <peter@ahinko.se>
 * @license  MIT (http://opensource.org/licenses/MIT)
 * @link     https://github.com/phapi/middleware-dispatcher
 */
class Dispatcher implements Middleware
{

    /**
     * Dependency Injection Container
     *
     * @var Container
     */
    private $container;

    /**
     * The attribute name that should be used on the request object
     *
     * @var string
     */
    private $requestAttribName;

    /**
     * The dispatcher will look for the value provided in the $requetAttribName
     * and use it to look in the request attributes to find the endpoint the router
     * found based on the request uri.
     *
     * @param string $requestAttribName
     */
    public function __construct($requestAttribName = 'routeEndpoint')
    {
        $this->requestAttribName = $requestAttribName;
    }

    /**
     * Set Dependency injection container
     *
     * @param Container $container
     */
    public function setContainer(Container $container)
    {
        $this->container = $container;
    }

    /**
     * Invoke dispatcher
     *
     * Uses information from the router and makes sure the endpoint and method exists.
     * Calls the method and takes what the endpoint returns and saves it to the response
     * object as an unserialized body.
     *
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @param callable $next
     * @return ResponseInterface
     * @throws MethodNotAllowed
     * @throws NotFound
     */
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, callable $next = null)
    {
        // Get the endpoint class name set by the router, use the provided attribute name
        $endpointName = $request->getAttribute($this->requestAttribName, '');
        // Get request method
        $method = $request->getMethod();

        // Check if endpoint exists
        if (!class_exists($endpointName)) {
            throw new NotFound();
        }

        // Check if method exists
        if (!method_exists($endpointName, $method)) {
            throw new MethodNotAllowed();
        }

        // Create endpoint
        $endpoint = new $endpointName($request, $response, $this->container);

        // Call endpoint and method and retrieve the return body
        $unserializedBody = call_user_func_array([$endpoint, $method], $request->getAttribute('routeParams', []));

        // Get updated response
        $response = $endpoint->getResponse();

        // Make sure we have an array with content, if empty then client won't get a body.
        // It's impossible to do any error check here since we have no idea to know if there
        // should be any content or not to be returned. Error check should be done in the
        // called endpoint instead.
        if (is_array($unserializedBody) && !empty($unserializedBody)) {
            // Set the unserialized body to the response object
            try {
                $response = $response->withUnserializedBody($unserializedBody);
            } catch (\Exception $e) {
                // We don't have access to a response class that can handle unserialized bodies
                throw new \RuntimeException(
                    'The dispatcher middleware requires a response object that can handle unserialized body.'
                );
            }
        }

        // Call next middleware and return the response
        return $next($request, $response, $next);
    }
}
