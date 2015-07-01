<?php

namespace Phapi\Middleware\Route;

use Phapi\Contract\Middleware\Middleware;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class Router
 *
 * @category Phapi
 * @package  Phapi\Middleware\Route
 * @author   Peter Ahinko <peter@ahinko.se>
 * @license  MIT (http://opensource.org/licenses/MIT)
 * @link     https://github.com/phapi/middleware-route
 */
class Route implements Middleware
{

    /**
     * The router
     *
     * @var Router
     */
    private $router;

    /**
     * The attribute name that should be used on the request object
     *
     * @var string
     */
    private $requestAttribName;

    /**
     * @param Router $router
     * @param string $requestAttribName The attribute name that should be used on the request object
     */
    public function __construct(Router $router, $requestAttribName = 'routeEndpoint')
    {
        // Set router
        $this->router = $router;
        // Set attribute name
        $this->requestAttribName = $requestAttribName;
    }

    /**
     * Add routes to the route table
     *
     * @param array $routes
     */
    public function addRoutes(array $routes = [])
    {
        // Add routes to the routers table
        $this->router->addRoutes($routes);
    }

    /**
     * Handle the invoke of the middleware
     *
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @param callable $next
     * @return ResponseInterface
     * @throws \Phapi\Exception\NotFound
     */
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, callable $next = null)
    {
        // Match request to route, error handling is done by the router itself
        $this->router->match($request->getUri()->getPath(), $request->getMethod());

        // Get matched endpoint from router and add to request
        $request = $request->withAttribute($this->requestAttribName, $this->router->getMatchedEndpoint());

        // Get all url params from router and add as attribute to request
        foreach ($this->router->getParams() as $key => $value) {
            $request = $request->withAttribute($key, $value);
        }

        // Call next middleware and return any response
        return $next($request, $response, $next);
    }
}
