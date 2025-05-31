<?php
namespace DennisCarrazeiro\Php\Router\Module\Router\Tests;

use DennisCarrazeiro\Php\Router\Module\Router\Router;
use DennisCarrazeiro\Php\Router\Module\Router\RouterDTO;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \DennisCarrazeiro\Php\Router\Module\Router\Router
 *
 * This test suite verifies the functionality of the `Router` class, ensuring that
 * routing, parameter handling, link creation, and error handling work as expected.
 */
class RouterTest extends TestCase
{
    /**
     * Sets up the test environment before each test execution.
     * Clears the `$_GET` and `$_SERVER` superglobal variables to ensure
     * each test starts with a clean request state.
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $_GET    = [];
        $_SERVER = [];
    }

    /**
     * Helper to simulate an HTTP request.
     * Sets the values in the `$_GET` and `$_SERVER` superglobal variables
     * to emulate different request scenarios for the router.
     *
     * @param string $uri The URI to set in `$_GET` for the router to process.
     * @param string $method The HTTP method (e.g., 'GET', 'POST') to set in `$_SERVER['REQUEST_METHOD']`.
     * @param string $routerKey The key in `$_GET` for the URI, defaults to '__ROUTER__'.
     * @return void
     */
    private function simulateRequest(string $uri, string $method, string $routerKey = '__ROUTER__'): void
    {
        $_GET[$routerKey]                 = $uri;
        $_SERVER['REDIRECT_QUERY_STRING'] = true;
        $_SERVER['REQUEST_METHOD']        = $method;
    }

    /**
     * Test static method 'construct' of Router class on exception.
     * Ensures that when REDIRECT_QUERY_STRING is not setted,
     * Router instance has been created and the dispatch method will work fine.
     *
     * @return void
     */
    public function testConstructMethodException()
    {
        $_SERVER['REQUEST_URI']           = '/';
        $_SERVER['REQUEST_METHOD']        = 'GET';
        $_SERVER['REDIRECT_QUERY_STRING'] = null;
        $router                           = new Router();
        $this->assertNull($router->dispatch());
    }

    /**
     * Test static method 'validateInstance' of Router class on exception.
     * Ensures that an exception is thrown if `validateInstance` is called before a
     * Router instance has been created and the dispatch method has been used.
     *
     * @return void
     */
    public function testValidateInstanceMethodException(): void
    {
        $this->expectException(\Exception::class);
        call_user_func([Router::class, 'validateInstance']);
        $this->expectExceptionMessage("It is not possible use 'self' instance before create a new one and use dispatch method.");
    }

    /**
     * Test instantiation of Router class.
     * Verifies that the `Router` class can be successfully instantiated and that
     * adding a route via the `get` method returns the `Router` instance itself,
     * allowing for method chaining.
     *
     * @return void
     */
    public function testRouterMethodInitializesAndReturnsSelf(): void
    {
        $this->simulateRequest('/home', 'GET');
        $router = new Router();
        $router->get(RouterDTO::route('/')->callback(function () {})->name('test'));
        $this->assertInstanceOf(Router::class, $router);
    }

    /**
     * Test static method 'params' of Router class.
     * Ensures that the `params()` method correctly retrieves route parameters
     * after a successful dispatch.
     *
     * @return void
     */
    public function testParamsMethod(): void
    {
        $this->simulateRequest('/user/123', 'GET');
        $router = new Router();
        $router->get(RouterDTO::route('/user/{id}')->callback(function ($id) {})->name('test'));
        $router->dispatch();
        $this->assertArrayHasKey('id', Router::params());
    }

    /**
     * Test static method 'current' of Router class.
     * Verifies that the `current()` method returns the currently matched URI
     * after dispatching.
     *
     * @return void
     */
    public function testCurrentMethod(): void
    {
        $this->simulateRequest('/user/123', 'GET');
        $router = new Router();
        $router->get(RouterDTO::route('/user/{id}')->callback(function ($id) {})->name('test'));
        $router->dispatch();
        $this->assertEquals('/user/123', Router::current());
    }

    /**
     * Test static method 'method' of Router class.
     * Ensures that the `method()` method returns the HTTP method of the current request
     * after dispatching.
     *
     * @return void
     */
    public function testMethodMethod(): void
    {
        $this->simulateRequest('/user/123', 'GET');
        $router = new Router();
        $router->get(RouterDTO::route('/user/{id}')->callback(function ($id) {})->name('test'));
        $router->dispatch();
        $this->assertEquals('GET', Router::method());
    }

    /**
     * Test static method 'createLink' of Router class.
     * Verifies that links are correctly generated based on route names and provided parameters.
     * Also asserts that an exception is thrown for non-existent route names.
     *
     * @return void
     */
    public function testCreateLinkMethod(): void
    {
        $this->simulateRequest('/user/123', 'GET');
        $router = new Router();
        $router->get(RouterDTO::route('/user/{id}')->callback(function ($id) {})->name('test'));
        $router->get(RouterDTO::route('/user/profile/{id}')->callback(function ($id) {})->name('test.profile'));
        $router->get(RouterDTO::route('/home')->callback(function ($id) {})->name('test.home'));
        $router->dispatch();
        $this->assertEquals('/user/profile/123', Router::createLink('test.profile', ['id' => '123']));
        $this->assertEquals('/home', Router::createLink('test.home'));
        $this->expectExceptionMessage("Route name 'test.error' not found.");
        Router::createLink('test.error');
    }

    /**
     * Test static method 'dispatch' of Router class under various scenarios.
     * Includes tests for successful GET route matching, handling of non-allowed methods,
     * execution of `notAllowed` and `notFound` callbacks, and middleware execution.
     * Also verifies behavior for various HTTP methods (POST, DELETE, OPTIONS, PATCH, HEAD, PUT).
     *
     * @return void
     */
    public function testDispatchMethod(): void
    {
        $this->simulateRequest('/user/123', 'GET');
        $router = new Router();
        $router->get(RouterDTO::route('/user/{id}')->callback(function ($id) {})->name('test'));
        $router->get(RouterDTO::route('/user/profile/{id}')->callback(function ($id) {})->name('test.profile'));
        $router->get(RouterDTO::route('/home')->callback(function ($id) {})->name('test.home'));
        $this->assertNull($router->dispatch());
        $this->simulateRequest('/user/123', 'HEAD');
        $router = new Router();
        $this->assertNull($router->dispatch());
        // not allowed
        $this->simulateRequest('/user/123', 'HEAD');
        $router = new Router();
        $router->notAllowed(function () {
            return true;
        });
        $this->assertEquals(true, $router->dispatch());
        // middleware
        $this->simulateRequest('/user/123', 'GET');
        $router = new Router();
        $router->get(RouterDTO::route('/user/{id}')
                ->callback(function ($id) {})
                ->middleware(function () {return true;}));
        $this->assertNull($router->dispatch());
        // not found
        $this->simulateRequest('/not-found', 'GET');
        $router = new Router();
        $router->get(RouterDTO::route('/')->callback(function () {}));
        $router->notFound(function () {
            return true;
        });
        $this->assertEquals(true, $router->dispatch());
        // post, delete, options, patch, head, put
        $methods = ['post', 'delete', 'options', 'patch', 'head', 'put'];
        foreach ($methods as $method) {
            $this->simulateRequest('/' . $method, strtoupper($method));
            $router = new Router();
            $router->{$method}(RouterDTO::route('/' . $method)->callback(function () {return true;}));
            $this->assertEquals(true, $router->dispatch());
        }
    }

    /**
     * Test 'dispatch' method when a route is not found and the `notFound` callback is null.
     * Ensures that `dispatch` returns `null` in this scenario.
     *
     * @return void
     */
    public function testDispatchMethodExceptionNotFound(): void
    {
        $this->simulateRequest('/not-found', 'GET');
        $router = new Router();
        $router->notFound(null);
        $this->assertEquals(null, $router->dispatch());
    }

    /**
     * Test exception handling in the 'dispatch' method for an invalid route callback.
     * Ensures that an exception is thrown if an invalid route callback is provided.
     *
     * @return void
     */
    public function testDispatchMethodExceptionTryCatch(): void
    {
        $this->expectException(\Exception::class);
        $this->simulateRequest('/user', 'GET');
        $router = new Router();
        $router->get(RouterDTO::route('/user')->callback('error'));
        $router->dispatch();
        $this->expectExceptionMessage('Invalid route callback provided.');
    }

    /**
     * Test 'requestMethodMethodException' for the Router class.
     * Ensures that an exception is thrown if the request method value is invalid (e.g., boolean false).
     *
     * @return void
     */
    public function testRequestMethodMethodException(): void
    {
        $this->expectException(\Exception::class);
        $this->simulateRequest('/', false);
        $router = new Router();
        $this->expectExceptionMessage('Request Method value is invalid.');
    }
}
