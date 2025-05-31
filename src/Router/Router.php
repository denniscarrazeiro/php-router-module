<?php

/*
* This file is part of the denniscarrazeiro/php-router-module package.
*
* (c) Dennis Santana Carrazeiro <dennis.carrazeiro@gmail.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace DennisCarrazeiro\Php\Router\Module\Router;

use \Exception;

/**
 * Class Router
 *
 * This class provides a simple routing mechanism for PHP applications.
 * It allows defining routes for different HTTP methods and dispatches
 * requests to the appropriate callback functions. It now includes
 * support for middleware execution before the main route callback.
 *
 * This class is implemented as a Singleton, ensuring that only one instance
 * of the router exists throughout the application lifecycle.
 */
class Router
{
    /**
     * @var Router|null The single instance of the Router class.
     */
    private static $instance = null;

    /**
     * @var string Message for an invalid request method.
     */
    private const INVALID_REQUEST_METHOD = "Request method value is invalid.";

    /**
     * @var string Message for an invalid self instance.
     * Used when static methods are called before the router instance is properly set.
     */
    private const INVALID_INSTANCE = "It is not possible use 'self' instance before create a new one and use dispatch method.";

    /**
     * @var string Message for an invalid route name.
     */
    private const INVALID_ROUTE_NAME = "Route name '<name>' not found.";

    /**
     * @var string Default key to retrieve the request URI from the $_GET superglobal.
     */
    private const DEFAULT_KEY_ROUTER = "__ROUTER__";

    /**
     * @var string Key to retrieve the request method from the $_SERVER superglobal.
     */
    private const DEFAULT_KEY_SERVER_REQUEST_METHOD = "REQUEST_METHOD";

    /**
     * @var string Key to retrieve the redirect_qeury_string from the $_SERVER superglobal.
     */
    private const DEFAULT_KEY_SERVER_REDIRECT_QUERY = 'REDIRECT_QUERY_STRING';

    /**
     * @var string Key to retrieve the request_uri from the $_SERVER superglobal.
     */
    private const DEFAULT_KEY_SERVER_REQUEST_URI = 'REQUEST_URI';

    /**
     * @var array Stores all registered routes, categorized by HTTP method.
     * Each route entry now includes the route pattern, its callback, and an optional middleware.
     */
    private $routes = [];

    /**
     * @var string The current request URI.
     */
    private $requestUri;

    /**
     * @var string The current request method.
     */
    private $requestMethod;

    /**
     * @var array The extracted parameters from the matched route, e.g., from /{id}.
     */
    private $params;

    /**
     * @var callable|null The callback function to execute when no route is found (404).
     */
    private $notFoundCallback;

    /**
     * @var callable|null The callback function to execute when the method is not allowed (405).
     */
    private $notAllowedCallback;

    /**
     * Router constructor.
     *
     * Initializes the router, setting the request URI and method based on superglobals.
     * The constructor is private to enforce the Singleton pattern.
     *
     * @param string|null $keyRouter An optional key to retrieve the request URI from $_GET.
     */
    public function __construct($keyRouter = null)
    {
        // Use the provided keyRouter or the default one.
        $keyRouter = $keyRouter ?? self::DEFAULT_KEY_ROUTER;

        if (! isset($_SERVER[self::DEFAULT_KEY_SERVER_REDIRECT_QUERY])) { // probably the request comes from the built-in web server
            $_GET[$keyRouter] = $_SERVER[self::DEFAULT_KEY_SERVER_REQUEST_URI];
        }

        // Set the request URI retriving from $_GET variables.
        $this->setRequestUri($_GET[$keyRouter] ?? '/');

        // Set the request method from $_SERVER.
        $this->setRequestMethod($_SERVER[self::DEFAULT_KEY_SERVER_REQUEST_METHOD] ?? null);
    }

    /**
     * Statically retrieves the parameters from the currently matched route.
     * This method can be called directly on the class (e.g., `Router::params()`)
     * after a route has been successfully dispatched.
     *
     * @return array The parameters array from the matched route.
     * @throws Exception If the router instance has not been initialized (i.e., `dispatch()` hasn't run).
     */
    public static function params()
    {
        self::validateInstance();
        return self::$instance->getParams();
    }

    /**
     * Statically retrieves the current request URI.
     * This method can be called directly on the class (e.g., `Router::current()`)
     * after a route has been successfully dispatched.
     *
     * @return string The current request URI.
     * @throws Exception If the router instance has not been initialized.
     */
    public static function current()
    {
        self::validateInstance();
        return self::$instance->getRequestUri();
    }

    /**
     * Statically retrieves the current request method.
     * This method can be called directly on the class (e.g., `Router::method()`)
     * after a route has been successfully dispatched.
     *
     * @return string The current request method (e.g., 'GET', 'POST').
     * @throws Exception If the router instance has not been initialized.
     */
    public static function method()
    {
        self::validateInstance();
        return self::$instance->getRequestMethod();
    }

    public static function createLink($routerName, $params = [])
    {
        self::validateInstance();
        foreach (self::$instance->routes as $routeConfigurations) {
            foreach ($routeConfigurations as $routeConfiguration) {
                extract($routeConfiguration);
                if ($name && $name === $routerName) {
                    $keysPaternsUri = ['(?P', '[^/]+)', '<', '>'];
                    if (empty($params)) {
                        $route = str_replace($keysPaternsUri, ['', '', '{', '}'], $route);
                        return $route;
                    }
                    $route = str_replace($keysPaternsUri, '', $route);
                    foreach ($params as $key => &$param) {
                        $route = str_replace($key, $param, $route);
                    }
                    return $route;
                }
            }
        }
        http_response_code(500);
        throw new Exception(str_replace('<name>', $routerName, self::INVALID_ROUTE_NAME));
    }

    /**
     * Sets the singleton instance of the Router.
     * This method is used internally, primarily by `dispatch()` after a route is matched,
     * to make the router's state accessible via static methods.
     *
     * @param Router $instance The Router instance to set as the singleton.
     */
    private static function setInstance(Router $instance)
    {
        self::$instance = $instance;
    }

    /**
     * Validates if the Router singleton instance has been properly set.
     * This ensures that static getter methods (like `params()`, `requestUri()`)
     * are not called before the router has processed a request.
     *
     * @throws Exception If the router instance is not set, indicating an invalid usage.
     * @return bool True if the instance is valid.
     */
    public static function validateInstance()
    {;
        if (self::$instance === null) {
            throw new Exception(self::INVALID_INSTANCE);
        }
        return true;}

    /**
     * Sets the request URI.
     *
     * @param string $uri The request URI to set.
     * @throws Exception If the provided URI is empty.
     */
    private function setRequestUri($uri)
    {
        $this->requestUri = $uri;
    }

    /**
     * Sets the request method.
     *
     * @param string $requestMethod The request method to set.
     * @throws Exception If the provided request method is empty.
     */
    private function setRequestMethod($requestMethod)
    {
        if (! $requestMethod) {
            throw new Exception(self::INVALID_REQUEST_METHOD);
        }
        $this->requestMethod = $requestMethod;
    }

    /**
     * Sets the extracted parameters for the current request.
     * These parameters are typically extracted from the URI by matching routes.
     *
     * @param array $params An associative array of parameters.
     */
    private function setParams($params)
    {
        $this->params = $params;
    }

    /**
     * Registers a new route with its corresponding method, callback, and optional middleware.
     *
     * Converts route parameters like '{param}' into named regex capture groups.
     * This is the internal method used by all public HTTP method registration functions.
     *
     * @param string $method The HTTP method (e.g., 'GET', 'POST', 'OPTIONS').
     * @param string $route The route pattern (e.g., '/users/{id}').
     * @param callable $callback The callback function to execute for this route.
     * @param callable|null $middleware An optional middleware function to execute before the callback.
     * @param string| $name The route pattern (e.g., '/users/{id}').
     */
    private function registerRoute($method, $route, $callback, $middleware = null, $name = null)
    {
        // Convert method to uppercase for consistency.
        $method = strtoupper($method);

        // Convert route parameters (e.g., {id}) into named regex capture groups for parsing.
        $route = preg_replace('/\{(\w+)\}/', '(?P<$1>[^/]+)', $route);

        // Store the route configuration, including the pattern, callback, and middleware.
        $this->routes[$method][] = [
            'route'      => $route,
            'callback'   => $callback,
            'middleware' => $middleware,
            'name'       => $name,
        ];
    }

    /**
     * Registers an GET route using a RouterDTO object.
     *
     * @param RouterDTO $routeDTO The DTO containing route details.
     */
    public function get(RouterDTO $dto)
    {
        $this->registerRoute('GET', $dto->getRoute(), $dto->getCallback(), $dto->getMiddleware(), $dto->getName());
    }

    /**
     * Registers an POST route using a RouterDTO object.
     *
     * @param RouterDTO $routeDTO The DTO containing route details.
     */
    public function post(RouterDTO $dto)
    {
        $this->registerRoute('POST', $dto->getRoute(), $dto->getCallback(), $dto->getMiddleware(), $dto->getName());
    }

    /**
     * Registers an DELETE route using a RouterDTO object.
     *
     * @param RouterDTO $routeDTO The DTO containing route details.
     */
    public function delete(RouterDTO $dto)
    {
        $this->registerRoute('DELETE', $dto->getRoute(), $dto->getCallback(), $dto->getMiddleware(), $dto->getName());
    }

    /**
     * Registers an PATCH route using a RouterDTO object.
     *
     * @param RouterDTO $routeDTO The DTO containing route details.
     */
    public function patch(RouterDTO $dto)
    {
        $this->registerRoute('PATCH', $dto->getRoute(), $dto->getCallback(), $dto->getMiddleware(), $dto->getName());
    }

    /**
     * Registers an PUT route using a RouterDTO object.
     *
     * @param RouterDTO $routeDTO The DTO containing route details.
     */
    public function put(RouterDTO $dto)
    {
        $this->registerRoute('PUT', $dto->getRoute(), $dto->getCallback(), $dto->getMiddleware(), $dto->getName());
    }

    /**
     * Registers an OPTIONS route using a RouterDTO object.
     *
     * @param RouterDTO $routeDTO The DTO containing route details.
     */
    public function options(RouterDTO $dto)
    {
        $this->registerRoute('OPTIONS', $dto->getRoute(), $dto->getCallback(), $dto->getMiddleware(), $dto->getName());
    }

    /**
     * Registers an HEAD route using a RouterDTO object.
     *
     * @param RouterDTO $routeDTO The DTO containing route details.
     */
    public function head(RouterDTO $dto)
    {
        $this->registerRoute('HEAD', $dto->getRoute(), $dto->getCallback(), $dto->getMiddleware(), $dto->getName());
    }

    /**
     * Sets the callback function to be executed when no route is found (404 Not Found).
     *
     * @param callable $callback The 404 callback.
     */
    public function notFound($callback)
    {
        $this->notFoundCallback = $callback;
    }

    /**
     * Sets the callback function to be executed when the request method is not allowed (405 Method Not Allowed).
     *
     * @param callable $callback The 405 callback.
     */
    public function notAllowed($callback)
    {
        $this->notAllowedCallback = $callback;
    }

    /**
     * Dispatches the current request to the appropriate route.
     *
     * Matches the request URI and method against registered routes and executes the associated callback.
     * Handles 404 Not Found, 405 Method Not Allowed, and 500 Internal Server Error scenarios.
     *
     * @return mixed|null The result of the executed callback, or null if no callback is found.
     * @throws Exception If an unhandled error occurs during callback or middleware execution.
     */
    public function dispatch()
    {
        $requestUri    = $this->getRequestUri();
        $requestMethod = $this->getRequestMethod();
        // Check if there are any routes defined for the current request method.
        if (! isset($this->routes[$requestMethod])) {
            // If no routes for this method, send 405 header and call notAllowed callback if set.
            http_response_code(405);
            if (! $this->notAllowedCallback) {
                return null;
            }
            return RouterDispatcher::dispatch($this->notAllowedCallback);
        }

        // Iterate through routes defined for the current request method.
        foreach ($this->routes[$requestMethod] as $routeConfiguration) {
            // Extract route, callback, and middleware from the configuration array.
            extract($routeConfiguration);

            // Create a full regex pattern for matching the route.
            $pattern = "#^" . $route . "$#";

            // Attempt to match the request URI against the route pattern.
            if (preg_match($pattern, $requestUri, $matches)) {
                // Extract named parameters from the regex matches.
                $this->setParams(array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY));

                try {
                    // Set the current instance as the singleton instance for static access.
                    // This is crucial for allowing `Router::params()`, `Router::requestUri()` etc.
                    // to work after a successful route match.
                    self::setInstance($this);

                    // If middleware is defined, execute it first.
                    if ($middleware) {
                        RouterDispatcher::dispatch($middleware);
                    }
                    // Dispatch the main route callback with the extracted parameters.
                    return RouterDispatcher::dispatch($callback, $this->getParams());
                } catch (\Exception $e) {
                    // Log the error for debugging purposes.
                    error_log("Router Error: " . $e->getMessage());
                    // Re-throw the exception to allow for higher-level error handling if needed.
                    throw new Exception($e->getMessage());
                }
            }
        }
        // If no route matches after checking all, send 404 header and call notFound callback.
        http_response_code(404);
        return RouterDispatcher::dispatch($this->notFoundCallback);
    }

    /**
     * Returns the current request URI.
     *
     * @return string The request URI.
     */
    public function getRequestUri()
    {
        return $this->requestUri;
    }

    /**
     * Returns the current request method in uppercase.
     *
     * @return string The request method.
     */
    public function getRequestMethod()
    {
        return strtoupper($this->requestMethod);
    }

    /**
     * Returns the extracted parameters from the currently matched route.
     *
     * @return array The parameters array.
     */
    public function getParams()
    {
        return $this->params;
    }
}
