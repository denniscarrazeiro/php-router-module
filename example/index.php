<?php

// This line includes the Composer autoloader, which makes it possible to
// load classes defined in your project's `src` and `tests` directories
// without manually requiring each file. It's essential for dependency management.
require_once __DIR__ . "/../vendor/autoload.php";

// Imports the main Router class, which is responsible for defining and dispatching routes.
use DennisCarrazeiro\Php\Router\Module\Router\Router;
// Imports the RouterDTO (Data Transfer Object) class, used to fluently define route properties.
use DennisCarrazeiro\Php\Router\Module\Router\RouterDTO;

/**
 * Class AuthMiddleware
 *
 * This class demonstrates various middleware methods used for authentication or validation.
 * Middlewares are executed *before* the main route callback.
 * Each method checks for a specific 'csrf' token based on the HTTP method.
 */
class AuthMiddleware
{
    /**
     * Middleware for POST requests.
     * Checks for a 'csrf' token in the $_POST superglobal.
     * @throws \Exception If the CSRF token is invalid.
     */
    public function post()
    {
        // Checks if the 'csrf' token is set in the POST data and matches 'post'.
        if (! isset($_POST['csrf']) || $_POST['csrf'] !== 'post') {
            // Throws an exception if the CSRF check fails, halting request processing.
            throw new \Exception('error on csrf');
            return; // Explicit return, though throw already stops execution.
        }
        return; // Allows the request to proceed to the main route callback.
    }

    /**
     * Middleware for PUT requests.
     * Reads raw input, decodes JSON, and checks for a 'csrf' token.
     * @throws \Exception If the CSRF token is invalid.
     */
    public function put()
    {
        // Reads the raw request body, which is typical for PUT/PATCH/DELETE requests.
        $contents = file_get_contents('php://input') ?? '{}';
        // Decodes the JSON content into an associative array, typically for PUT data.
        $_PUT = json_decode($contents, true);
        // Checks if the 'csrf' token is set in the decoded PUT data and matches a specific value.
        if (! isset($_PUT['csrf']) || $_PUT['csrf'] !== '123456789') {
            throw new \Exception('error on csrf');
            return;
        }
        return;
    }

    /**
     * Middleware for DELETE requests.
     * Reads raw input, decodes JSON, and checks for a 'csrf' token.
     * @throws \Exception If the CSRF token is invalid.
     */
    public function delete()
    {
        // Reads the raw request body.
        $contents = file_get_contents('php://input') ?? '{}';
        // Decodes the JSON content for DELETE data.
        $_DELETE = json_decode($contents, true);
        // Checks for a 'csrf' token in the decoded DELETE data.
        if (! isset($_DELETE['csrf']) || $_DELETE['csrf'] !== '987654321') {
            throw new \Exception('error on csrf');
            return;
        }
        return;
    }
}

/**
 * Class UserController
 *
 * This class serves as a controller, containing various methods that act as route callbacks.
 * Each method demonstrates different functionalities, such as returning JSON,
 * handling route parameters, accessing router's static properties, and generating links.
 */
class UserController
{
    /**
     * A simple callback that returns a JSON response.
     */
    public function json()
    {
        // Sets the Content-Type header to indicate a JSON response.
        header('Content-Type: application/json');
        // Encodes an associative array into a JSON string and outputs it.
        echo json_encode(['message' => 'json']);
        return;
    }

    /**
     * Callback demonstrating how to access route parameters.
     * Parameters can be accessed both directly as function arguments and via `Router::params()`.
     * @param string $id The 'id' parameter extracted from the route.
     * @param string $name The 'name' parameter extracted from the route.
     * @param string $email The 'email' parameter extracted from the route.
     */
    public function params($id, $name, $email)
    {
        header('Content-Type: application/json');
        // Retrieves all matched route parameters using the static `Router::params()` method.
        $params = Router::params();
        echo json_encode([
            'params_from_static_router' => $params, // Parameters as returned by the router's static method.
            'params_from_function'      => [        // Parameters as passed directly to the callback function.
                'id'    => $id,
                'name'  => $name,
                'email' => $email,
            ],
        ]);
        return;
    }

    /**
     * Callback demonstrating how to get the current request URI.
     */
    public function current()
    {
        header('Content-Type: application/json');
        // Retrieves the current request URI using the static `Router::current()` method.
        $current = Router::current();
        echo json_encode(['current_from_static_router' => $current]);
        return;
    }

    /**
     * Callback demonstrating how to create a link to a named route.
     */
    public function createLink()
    {
        header('Content-Type: application/json');
        // Generates a URL for the route named 'params', providing the necessary parameters.
        $createLink = Router::createLink('params', [
            'id'    => '123',
            'name'  => 'dennis',
            'email' => 'dennis@email.com',
        ]);
        echo json_encode(['createLink' => $createLink]);
        return;
    }

    /**
     * Callback for POST requests, demonstrating access to $_POST data.
     */
    public function post()
    {
        header('Content-Type: application/json');
        // Outputs the contents of the $_POST superglobal.
        echo json_encode(['post_fields' => $_POST]);
        return;
    }

    /**
     * Callback for PUT requests, demonstrating how to read raw input data.
     */
    public function put()
    {
        header('Content-Type: application/json');
        // Reads the raw request body for PUT requests.
        $contents = file_get_contents('php://input') ?? '{}';
        // Decodes the raw JSON input into an associative array for PUT data.
        $_PUT = json_decode($contents, true);
        echo json_encode(['put_fields' => $_PUT]);
        return;
    }

    /**
     * Callback for DELETE requests, demonstrating route parameters and raw input.
     * @param string $id The 'id' parameter extracted from the route.
     */
    public function delete($id)
    {
        header('Content-Type: application/json');
        // Reads raw request body.
        $contents = file_get_contents('php://input') ?? '{}';
        // Decodes raw JSON input for DELETE data.
        $_DELETE = json_decode($contents, true);
        echo json_encode([
            'params_from_request'       => $_DELETE,         // Data from raw input.
            'params_from_static_router' => Router::params(), // Parameters from router's static method.
            'params_from_function'      => ['id' => $id],    // Parameters passed directly to the callback function.
        ]);
        return;
    }

    /**
     * Callback for PATCH requests, demonstrating route parameters and raw input.
     * @param string $id The 'id' parameter extracted from the route.
     */
    public function patch($id)
    {
        // Reads raw request body.
        $contents = file_get_contents('php://input') ?? '{}';
        // Decodes raw JSON input for PATCH data.
        $_PATCH = json_decode($contents, true);
        // Uses print_r to output the array for debugging purposes.
        echo print_r([
            'params_from_request'       => $_PATCH,
            'params_from_static_router' => Router::params(),
            'params_from_function'      => ['id' => $id],
        ], true); // The 'true' argument makes print_r return a string instead of printing directly.
        return;
    }

    /**
     * Callback for 404 Not Found scenarios.
     */
    public function notFound()
    {
        header('Content-Type: application/json');
        echo json_encode(['message' => 'Request not found.']);
        return;
    }
}

// --- Router Initialization and Route Definitions ---

// Creates a new instance of the Router. This is a singleton, so subsequent calls
// to `Router::getInstance()` (if it were public) would return this same instance.
$router = new Router();

// --- GET Routes ---

// Defines a GET route for the root URL '/'.
$router->get(
    // Uses RouterDTO to fluently define the route.
    RouterDTO::route('/')
    // Sets an anonymous function as the callback.
        ->callback(function () {
            echo "<h1>Php Router Module</h1><p>Welcome Home</p>";
        })
    // Assigns a name to the route for easier referencing (e.g., for link generation).
        ->name('home.anonymous.callback')
);

// Defines a GET route for '/json'.
$router->get(
    RouterDTO::route('/json')
    // Sets a method from UserController as the callback.
        ->callback([UserController::class, 'json'])
        ->name('json')
);

// Defines a GET route with parameters in the URL: '/params/{id}/{name}/{email}'.
// The curly braces define dynamic segments that will be extracted as parameters.
$router->get(
    RouterDTO::route('/params/{id}/{name}/{email}')
        ->callback([UserController::class, 'params'])
        ->name('params')
);

// Defines a GET route for '/current', demonstrating access to the current URI.
$router->get(
    RouterDTO::route('/current')
        ->callback([UserController::class, 'current'])
        ->name('current')
);

// Defines a GET route for '/create-link', demonstrating the URL generation feature.
$router->get(
    RouterDTO::route('/create-link')
        ->callback([UserController::class, 'createLink'])
        ->name('create.link')
);

// --- POST Route ---

// Defines a POST route for '/post'.
$router->post(
    RouterDTO::route('/post')
        ->callback([UserController::class, 'post'])
    // Assigns a middleware to this route. The middleware will be executed
    // before the `UserController::post` callback.
        ->middleware([AuthMiddleware::class, 'post'])
        ->name('post')
);

// --- PUT Route ---

// Defines a PUT route for '/put'.
$router->put(
    RouterDTO::route('/put')
        ->callback([UserController::class, 'put'])
    // Middleware specific to PUT requests.
        ->middleware([AuthMiddleware::class, 'put'])
        ->name('put')
);

// --- DELETE Route ---

// Defines a DELETE route with a parameter '/delete/{id}'.
$router->delete(
    RouterDTO::route('/delete/{id}')
        ->callback([UserController::class, 'delete'])
    // Middleware specific to DELETE requests.
        ->middleware([AuthMiddleware::class, 'delete'])
        ->name('delete')
);

// --- PATCH Route ---

// Defines a PATCH route with a parameter '/patch/{id}'.
$router->patch(
    RouterDTO::route('/patch/{id}')
        ->callback([UserController::class, 'patch'])
    // Example of an anonymous function used directly as middleware.
        ->middleware(function () {
            // This middleware also accesses parameters from the router's static method.
            echo print_r(['params_from_middleware' => Router::params()], true);
        })
        ->name('patch')
);

// --- OPTIONS Route ---

// Defines an OPTIONS route with a parameter '/options/{id}'.
$router->options(
    RouterDTO::route('/options/{id}')
        ->callback(function ($id) {
            // Handles raw input for OPTIONS method, similar to PUT/DELETE/PATCH.
            $contents = file_get_contents('php://input') ?? '{}';
            $_OPTIONS = json_decode($contents, true);
            echo json_encode([
                'params_from_request'       => $_OPTIONS,
                'params_from_static_router' => Router::params(),
                'params_from_function'      => ['id' => $id],
            ]);
            return;
        })
    // Another example of an anonymous function as middleware, performing a simple GET parameter check.
        ->middleware(function () {
            if (! isset($_GET['auth']) || $_GET['auth'] !== 'true') {
                throw new Exception('Error on middleware of options request.');
                return;
            }
        })
        ->name('options')
);

// --- HEAD Route ---

// Defines a HEAD route for '/head'. HEAD requests typically only return headers, no body.
$router->head(
    RouterDTO::route('/head')
        ->callback(function () {
            return; // No content is returned for a HEAD request's body.
        })
        ->name('head')
);

// --- Error Handlers ---

// Creates an instance of UserController to be used by the notFound callback.
$userController = new UserController();
// Sets the callback for 404 Not Found errors. This will be triggered if no route matches the request URI.
$router->notFound(function () use ($userController) {
    echo $userController->notFound(); // Calls the notFound method from UserController.
    return;
});

// Sets the callback for 405 Method Not Allowed errors. This will be triggered
// if a route matches the URI but not the HTTP method.
$router->notAllowed(function () {
    echo "<h1>405 Method Not Allowed</h1><p>O método de requisição para esta URL não é permitido.</p>";
});

// --- Dispatch the Request ---

// This is the core command that starts the routing process.
// The router will attempt to match the current request URI and method
// against the registered routes and execute the corresponding callback (and middlewares).
$router->dispatch();
