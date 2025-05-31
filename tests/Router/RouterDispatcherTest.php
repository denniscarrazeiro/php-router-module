<?php
namespace DennisCarrazeiro\Php\Router\Module\Router\Tests;

use DennisCarrazeiro\Php\Router\Module\Router\RouterDispatcher;
use Exception;
use PHPUnit\Framework\TestCase;

// --- Helper classes for testing instance and static method calls ---
class TestCallableClass
{
    public function instanceMethod($arg1, $arg2)
    {
        return "Instance: " . $arg1 . "-" . $arg2;
    }

    public static function staticMethod($arg1, $arg2)
    {
        return "Static: " . $arg1 . "+" . $arg2;
    }
}

// A simple global function for testing
function globalTestFunction($arg)
{
    return "Global: " . $arg;
}

/**
 * @coversDefaultClass \DennisCarrazeiro\Php\Router\Module\Router\RouterDispatcher
 */
class RouterDispatcherTest extends TestCase
{
    /**
     * @covers ::dispatch
     * Test dispatching an anonymous function (closure).
     */
    public function testDispatchAnonymousFunction()
    {
        $callable = function ($name) {
            return "Hello, " . $name;
        };
        $params = ['World'];

        $result = RouterDispatcher::dispatch($callable, $params);
        $this->assertEquals("Hello, World", $result);
    }

    /**
     * @covers ::dispatch
     * Test dispatching an anonymous function with no parameters.
     */
    public function testDispatchAnonymousFunctionNoParams()
    {
        $callable = function () {
            return "No params here";
        };

        $result = RouterDispatcher::dispatch($callable);
        $this->assertEquals("No params here", $result);
    }

    /**
     * @covers ::dispatch
     * Test dispatching an instance method using an array callable.
     */
    public function testDispatchInstanceMethod()
    {
        $callable = [TestCallableClass::class, 'instanceMethod'];
        $params   = ['foo', 'bar'];

        $result = RouterDispatcher::dispatch($callable, $params);
        $this->assertEquals("Instance: foo-bar", $result);
    }

    /**
     * @covers ::dispatch
     * Test dispatching a static method using an array callable.
     */
    public function testDispatchStaticMethod()
    {
        $callable = [TestCallableClass::class, 'staticMethod'];
        $params   = ['one', 'two'];

        $result = RouterDispatcher::dispatch($callable, $params);
        $this->assertEquals("Static: one+two", $result);
    }

    /**
     * @covers ::dispatch
     * Test dispatching a static method without explicit class instantiation (PHP's built-in behavior).
     * While not directly handled by `new $className()`, `is_callable` handles this.
     */
    public function testDispatchStaticMethodDirectlyCallable()
    {
        // This is a valid callable form in PHP, which is handled by is_callable
        $callable = 'DennisCarrazeiro\Php\Router\Module\Router\Tests\TestCallableClass::staticMethod';
        $params   = ['hello', 'world'];

        $result = RouterDispatcher::dispatch($callable, $params);
        $this->assertEquals("Static: hello+world", $result);
    }

    /**
     * @covers ::dispatch
     * Test dispatching a global function.
     */
    public function testDispatchGlobalFunction()
    {
        $callable = 'DennisCarrazeiro\Php\Router\Module\Router\Tests\globalTestFunction';
        $params   = ['test'];

        $result = RouterDispatcher::dispatch($callable, $params);
        $this->assertEquals("Global: test", $result);
    }

    /**
     * @covers ::dispatch
     * Test dispatching an invalid callable type.
     */
    public function testDispatchInvalidCallableThrowsException()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage("Invalid route callback provided.");

        // An integer is not a callable type
        RouterDispatcher::dispatch(123);
    }

    /**
     * @covers ::dispatch
     * Test dispatching an array that is not a valid class method callable.
     */
    public function testDispatchInvalidArrayCallableThrowsException()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage("Invalid route callback provided.");

        // Array with wrong count
        RouterDispatcher::dispatch(['just_one_element']);

        // Array with non-string elements
        RouterDispatcher::dispatch([123, 'method']);
    }

    /**
     * @covers ::dispatch
     * Test dispatching a class method where the class does not exist.
     */
    public function testDispatchNonExistentClassMethodThrowsError()
    {
        // PHPUnit 9.0 (PHP 7.4+) expects a TypeError if a non-existent class is attempted to be instantiated
        // or a Warning for older PHP versions if strict_types is not enabled
        // In a real application, you might catch a more specific exception for class loading errors.
        // For unit testing, we ensure it doesn't return silently.

                                                   // This will typically result in a PHP Error (Fatal Error or Warning depending on context)
                                                   // or a TypeError from `new $className()` if the class doesn't exist.
                                                   // PHPUnit's `expectException` can catch `Throwable` which covers `Error` and `Exception`.
        $this->expectException(\Throwable::class); // Catches both Error and Exception

        // The specific error message might vary based on PHP version and configuration
        // but it will likely indicate a class not found or instantiation issue.
        // We are asserting that *some* error/exception is thrown when the class is invalid.

        // Use a class name that definitely does not exist
        $callable = ['NonExistentClass123XYZ::class', 'someMethod'];

        // We set the callable to a string array representation that the code tries to instantiate
        // This will trigger a `TypeError` (or equivalent `Error`) in recent PHP versions
        // because `new NonExistentClass123XYZ::class` is invalid.
        // The `RouterDispatcher` explicitly instantiates: `new $className()`.

        // To accurately test `new $className()` failing:
        // We need to provide a string that PHP would *attempt* to resolve as a class name
        // that legitimately doesn't exist.
        $callable = ['NonExistentClass123XYZ', 'someMethod'];

        RouterDispatcher::dispatch($callable, []);
    }

}
