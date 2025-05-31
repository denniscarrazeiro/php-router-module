<?php
namespace DennisCarrazeiro\Php\Router\Module\Router;

// No specific 'use \Closure;' is strictly needed here as callable is more general for doc blocks

/**
 * Class RouterDTO
 *
 * This Data Transfer Object (DTO) class is used to encapsulate all parameters
 * required to define a single route in the router. It provides a fluent interface
 * (method chaining) for improved readability and ease of use when registering routes.
 *
 * It allows defining the route pattern, its corresponding callback, an optional name,
 * and optional middleware(s) to be executed before the main callback.
 */
class RouterDTO
{
    /**
     * @var string The URL pattern for the route, e.g., 'users/{id}'.
     */
    private $route;

    /**
     * @var callable|array The callback function to be executed when this route is matched.
     * Can be an anonymous function, a string (function name), or an array ([ClassName::class, 'methodName']).
     */
    private $callback;

    /**
     * @var string|null An optional name for the route, useful for generating URLs or referencing routes programmatically.
     */
    private $name = null;

    /**
     * @var callable|array|null An optional single middleware or an array of middlewares
     * to be executed before the main route callback.
     */
    private $middleware = null;

    /**
     * Private constructor to enforce the use of the static factory method `route()`.
     * This ensures that route definition always starts with the route pattern.
     *
     * @param string $route The URL pattern for the route, e.g., 'user/{id}'.
     */
    private function __construct(string $route)
    {
        $this->route = $route;
    }

    /**
     * Static factory method to begin the fluent definition of a new route.
     * This is the starting point for chaining route configuration methods.
     *
     * @param string $route The URL pattern of the route (e.g., 'user/{id}', '/dashboard').
     * @return self A new instance of RouterDTO.
     */
    public static function route(string $route): self
    {
        return new self($route);
    }

    /**
     * Sets the callback function to be executed when this route is matched.
     *
     * @param callable|array $callback The callback. Can be an anonymous function,
     * a string (function name), or an array ([ClassName::class, 'methodName']).
     * @return self Returns the current RouterDTO instance for method chaining.
     */
    public function callback($callback): self
    {
        // Optional: Add validation here to ensure $callback is actually callable,
        // e.g., if (!is_callable($callback)) { throw new \InvalidArgumentException("Callback must be callable."); }
        $this->callback = $callback;
        return $this;
    }

    /**
     * Sets an optional name for the route.
     * Route names can be used for URL generation or for easier programmatic lookup of routes.
     *
     * @param string $name The unique name for the route (e.g., 'user.profile', 'admin.dashboard').
     * @return self Returns the current RouterDTO instance for method chaining.
     */
    public function name(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    /**
     * Sets the middleware(s) that should be executed before the main route callback.
     * This can be a single callable (anonymous function, string function name, or class array)
     * or an array of such callables if multiple middlewares are required.
     *
     * @param callable|array|string $middleware A single middleware or an array of middlewares.
     * @return self Returns the current RouterDTO instance for method chaining.
     */
    public function middleware($middleware): self
    {
        // Optional: Add validation here to ensure $middleware is callable or an array of callables,
        // e.g., if (!is_callable($middleware) && !is_array($middleware)) { throw new \InvalidArgumentException("Middleware must be callable or an array of callables."); }
        $this->middleware = $middleware;
        return $this;
    }

    /**
     * Retrieves the route URL pattern.
     *
     * @return string The route pattern.
     */
    public function getRoute(): string
    {
        return $this->route;
    }

    /**
     * Retrieves the route callback.
     *
     * @return callable|array The callback function.
     */
    public function getCallback()
    {
        return $this->callback;
    }

    /**
     * Retrieves the optional route name.
     *
     * @return string|null The route name, or null if not set.
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * Retrieves the optional middleware(s).
     *
     * @return callable|array|string|null The middleware(s), or null if not set.
     */
    public function getMiddleware()
    {
        return $this->middleware;
    }
}
