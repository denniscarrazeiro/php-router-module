<?php

namespace DennisCarrazeiro\Php\Router\Module\Router\Tests;

use PHPUnit\Framework\TestCase;
use DennisCarrazeiro\Php\Router\Module\Router\RouterDTO;

/**
 * @coversDefaultClass \DennisCarrazeiro\Php\Router\Module\Router\RouterDTO
 */
class RouterDTOTest extends TestCase
{
    /**
     * @covers ::route
     * @covers ::__construct
     * @covers ::getRoute
     * Test that the static `route` method correctly initializes the DTO with the given route.
     */
    public function testRouteMethodInitializesDTOAndReturnsSelf()
    {
        $routePattern = '/test/route';
        $dto = RouterDTO::route($routePattern);

        $this->assertInstanceOf(RouterDTO::class, $dto);
        $this->assertEquals($routePattern, $dto->getRoute());
        $this->assertNull($dto->getCallback());
        $this->assertNull($dto->getName());
        $this->assertNull($dto->getMiddleware());
    }

    /**
     * @covers ::callback
     * @covers ::getCallback
     * Test setting and retrieving a simple anonymous function callback.
     */
    public function testCallbackMethodSetsAndGetsAnonymousFunction()
    {
        $routePattern = '/api/data';
        $callback = function () { return 'data'; };

        $dto = RouterDTO::route($routePattern)->callback($callback);

        $this->assertInstanceOf(RouterDTO::class, $dto); // Ensure fluent interface
        $this->assertEquals($routePattern, $dto->getRoute());
        $this->assertSame($callback, $dto->getCallback()); // Use assertSame for closures/objects
    }

    /**
     * @covers ::callback
     * @covers ::getCallback
     * Test setting and retrieving an array-based callback.
     */
    public function testCallbackMethodSetsAndGetsArrayCallback()
    {
        $routePattern = '/settings';
        $arrayCallback = ['MyClass', 'myMethod']; // Simulate a class method callable

        $dto = RouterDTO::route($routePattern)->callback($arrayCallback);

        $this->assertInstanceOf(RouterDTO::class, $dto);
        $this->assertEquals($routePattern, $dto->getRoute());
        $this->assertEquals($arrayCallback, $dto->getCallback());
    }

    /**
     * @covers ::name
     * @covers ::getName
     * Test setting and retrieving the route name.
     */
    public function testNameMethodSetsAndGetsName()
    {
        $routePattern = '/profile';
        $routeName = 'user.profile';

        $dto = RouterDTO::route($routePattern)->name($routeName);

        $this->assertInstanceOf(RouterDTO::class, $dto); // Ensure fluent interface
        $this->assertEquals($routePattern, $dto->getRoute());
        $this->assertEquals($routeName, $dto->getName());
    }

    /**
     * @covers ::middleware
     * @covers ::getMiddleware
     * Test setting and retrieving a single middleware (anonymous function).
     */
    public function testMiddlewareMethodSetsAndGetsSingleMiddleware()
    {
        $routePattern = '/admin/dashboard';
        $middleware = function () { /* check auth */ };

        $dto = RouterDTO::route($routePattern)->middleware($middleware);

        $this->assertInstanceOf(RouterDTO::class, $dto); // Ensure fluent interface
        $this->assertEquals($routePattern, $dto->getRoute());
        $this->assertSame($middleware, $dto->getMiddleware());
    }

    /**
     * @covers ::middleware
     * @covers ::getMiddleware
     * Test setting and retrieving an array of middlewares.
     */
    public function testMiddlewareMethodSetsAndGetsArrayOfMiddlewares()
    {
        $routePattern = '/api/v1/users';
        $middleware1 = function () { /* auth */ };
        $middleware2 = ['Logger', 'logRequest'];
        $middlewares = [$middleware1, $middleware2];

        $dto = RouterDTO::route($routePattern)->middleware($middlewares);

        $this->assertInstanceOf(RouterDTO::class, $dto);
        $this->assertEquals($routePattern, $dto->getRoute());
        $this->assertEquals($middlewares, $dto->getMiddleware()); // assertEquals is fine for arrays
    }

    /**
     * @covers ::route
     * @covers ::callback
     * @covers ::name
     * @covers ::middleware
     * @covers ::getRoute
     * @covers ::getCallback
     * @covers ::getName
     * @covers ::getMiddleware
     * Test full fluent interface usage with all setters.
     */
    public function testFluentInterfaceFullConfiguration()
    {
        $routePattern = '/full/example/{id}';
        $callback = function($id) { return "Processing " . $id; };
        $routeName = 'full.route.example';
        $middleware = function() { /* complex middleware */ };

        $dto = RouterDTO::route($routePattern)
                        ->callback($callback)
                        ->name($routeName)
                        ->middleware($middleware);

        $this->assertInstanceOf(RouterDTO::class, $dto);
        $this->assertEquals($routePattern, $dto->getRoute());
        $this->assertSame($callback, $dto->getCallback());
        $this->assertEquals($routeName, $dto->getName());
        $this->assertSame($middleware, $dto->getMiddleware());
    }

    /**
     * @covers ::route
     * @covers ::getRoute
     * @covers ::getCallback
     * @covers ::getName
     * @covers ::getMiddleware
     * Test DTO when only the route is set (default null values for others).
     */
    public function testDTOWithOnlyRouteSet()
    {
        $routePattern = '/simple';
        $dto = RouterDTO::route($routePattern);

        $this->assertEquals($routePattern, $dto->getRoute());
        $this->assertNull($dto->getCallback());
        $this->assertNull($dto->getName());
        $this->assertNull($dto->getMiddleware());
    }

    /**
     * @covers ::route
     * @covers ::callback
     * @covers ::name
     * Test specific order of chaining (should not matter).
     */
    public function testFluentInterfaceChainingOrder()
    {
        $routePattern = '/order/test';
        $callback = function () {};
        $routeName = 'order.test';

        // Different order of chaining
        $dto1 = RouterDTO::route($routePattern)->name($routeName)->callback($callback);
        $dto2 = RouterDTO::route($routePattern)->callback($callback)->name($routeName);

        $this->assertEquals($dto1->getRoute(), $dto2->getRoute());
        $this->assertSame($dto1->getCallback(), $dto2->getCallback());
        $this->assertEquals($dto1->getName(), $dto2->getName());
    }
}