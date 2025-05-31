# PHP Router Module

[![Maintainer](http://img.shields.io/badge/maintainer-denniscarrazeiro-blue.svg?style=flat-square)](https://www.linkedin.com/in/dennis-carrazeiro)
[![PHP from Packagist](https://img.shields.io/packagist/php-v/denniscarrazeiro/php-router-module.svg?style=flat-square)](https://packagist.org/packages/denniscarrazeiro/php-router-module)
[![Latest Version](https://img.shields.io/github/release/denniscarrazeiro/php-router-module.svg?style=flat-square)](https://github.com/denniscarrazeiro/php-router-module/releases)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE)

This PHP Router class provides a robust and easy-to-use routing solution for your web applications, offering many features to ensure a single, consistent routing instance throughout your project. It enables you to define routes for various HTTP methods (GET, POST, PUT, DELETE, PATCH, OPTIONS, HEAD), incorporating support for middleware to execute logic before your main route callbacks. The router dynamically handles URL parameters, provides static access to current request details (URI, method, and parameters), and includes built-in mechanisms for handling 404 Not Found and 405 Method Not Allowed errors, making it a comprehensive tool for managing your application's request flow.

## Instalation

```bash
bash ./scripts/composer_install.sh
```

Composer is a dependency manager for the PHP programming language. Therefore, after running the command above, Composer will install all the necessary dependencies to ensure the project functions under the best possible conditions.

## Unit Tests

```bash
bash ./scripts/phpunit_tests.sh
```

PHPUnit is a programmer-oriented testing framework for PHP, designed to facilitate the creation and execution of unit tests. Consequently, after setting up your test suite and running the appropriate command, PHPUnit will execute your tests and provide detailed feedback, ensuring your codebase maintains a high level of quality and reliability.

## PHP Built-in Web Server

```bash
bash ./scripts/start_php_server.sh
```

The PHP built-in web server is a command-line tool that provides a quick and convenient way to test PHP applications locally. Consequently, after navigating to your project's root directory and running the appropriate command, the PHP built-in web server will serve your files over HTTP, allowing you to access and test your application directly in a web browser without the need for a full-fledged web server like Apache or Nginx.

## Bruno

>You can import Bruno collection located in **'http'** folder to test the requests.

Bruno is an open-source, Git-friendly, and offline-first API client designed for exploring and testing APIs. Consequently, after setting up your API collections as plain text files within your project's version control system, Bruno will allow you to send requests, analyze responses, and manage your API environments, ensuring seamless collaboration and a streamlined workflow for API development and testing.

## Server configuration before use
>Remember: You can use the built-in web server run the command start_php_server.sh to execute the example/index.php

### Apache

The .htaccess f*ile is a powerful, directory-level configuration file used on Apache web servers. For your PHP Router class, it plays a crucial role by enabling URL rewriting, which is essential for a clean and efficient routing system.
**Enable mod_rewrite in your apache server and add the following .htaccess in the root of your php project**:

````apacheconfig
<IfModule mod_rewrite.c>
RewriteEngine on
RewriteCond %{SCRIPT_FILENAME} !-f
RewriteCond %{SCRIPT_FILENAME} !-d
RewriteRule ^(.*)$ index.php?__ROUTER__=/$1 [L,QSA]
</IfModule>
````

### Nginx

Nginx uses its own configuration syntax for URL rewriting. You typically configure this in your server's nginx.conf file or a site-specific configuration file within sites-available (and symlinked to sites-enabled).
For your Router class, an Nginx configuration would look something like this within your server block:

````nginxconfig
server {
    listen 80;
    server_name yourdomain.com;
    root /path/to/your/project/public; # Assuming index.php is in a 'public' directory

    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/var/run/php/php8.x-fpm.sock; # Adjust for your PHP-FPM version
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        fastcgi_param QUERY_STRING __ROUTER__=$uri&$query_string; # Pass __ROUTER__ as query param
    }
}
````

## Examples

### Usage Example 1

```php

require_once __DIR__ . "/vendor/autoload.php";

use DennisCarrazeiro\Php\Router\Module\Router\Router;
use DennisCarrazeiro\Php\Router\Module\Router\RouterDTO;

$router = new Router();
$router->get(
    RouterDTO::route('/home')
        ->callback(function(){
            echo "<h1>Hello World!</h1>";
        })
        ->middleware(function (){
            echo "<h2>Hello I am middleware!</h2>"
        })
        ->name('home')
);
$router->dispatch();

```

### Usage Example 2

```php

require_once __DIR__ . "/vendor/autoload.php";

use DennisCarrazeiro\Php\Router\Module\Router\Router;
use DennisCarrazeiro\Php\Router\Module\Router\RouterDTO;

$router = new Router();
$router->post(
    RouterDTO::route('/user/{id}')
        ->callback(function($id){
            echo "<h1>User profile updated!</h1>";
        })
        ->middleware(function(){
            if(!isset($_POST['csrf']) || $_POST['csrf'] !== '123456789'){
                throw new \Exception("Csrf is required.");
            }
        })
        ->name('user.profile')
);
$router->dispatch();

```

## Full example of use

For more examples see the [Examples](https://github.com/denniscarrazeiro/php-router-module/blob/master/example) folder.

## License

The MIT license. Please see [License file](https://github.com/denniscarrazeiro/php-router-module/blob/master/LICENSE) for more information.
