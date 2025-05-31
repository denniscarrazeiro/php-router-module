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
 * Class RouterDispatcher
 *
 * This class is responsible for resolving and dispatching various types of PHP callbacks.
 * It supports anonymous functions, static method calls, and instance method calls from class arrays.
 */
class RouterDispatcher
{
    /**
     * @var string Error message for when an invalid callback type is provided.
     */
    private const INVALID_CALLBACK_PROVIDED = "Invalid route callback provided.";

    /**
     * Dispatches a given callable with an array of parameters.
     *
     * This method executes the provided `$callable`, handling different types:
     * anonymous functions, simple function name strings, or array-based class method calls
     * (e.g., `[MyClass::class, 'myMethod']`).
     *
     * @param callable|array|string $callable The callback to dispatch.
     * @param array $params An associative array of parameters to pass to the callable.
     * @return mixed The return value of the dispatched callable.
     * @throws Exception If the provided $callable is not a valid or resolvable type.
     */
    public static function dispatch($callable, $params = [])
    {
        // If the variable is a standard PHP callable (anonymous function, function name string), call it directly.
        if (is_callable($callable)) {
            return call_user_func_array($callable, $params);
        }

        // If the callable is an array like [ClassName::class, 'methodName'], instantiate the class and call the method.
        if (is_array($callable) && count($callable) === 2 && is_string($callable[0]) && is_string($callable[1])) {
            $className  = $callable[0];
            $methodName = $callable[1];
            return call_user_func_array([new $className(), $methodName], $params);
        }

        // If none of the supported callable types match, throw an exception.
        throw new Exception(self::INVALID_CALLBACK_PROVIDED);
    }
}
