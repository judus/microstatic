<?php namespace MicroStatic;

require __DIR__ . '/microstatic/helpers/loader.php';

Micro::run([
    'path' => __DIR__,
    'config' => 'microstatic/config/env.php'
], function () {

    Router::get('/', function () {
        return '<h1>Hello from MicroStatic!</h1>';
    });

    Router::get('hello/(:any)/(:any)', function ($firstname, $lastname) {
        return 'Hello ' . ucfirst($firstname) . ' ' . ucfirst($lastname);
    });

    Router::get('array', function () {
        return Config::getItems();
    });

    Router::get('object', function () {
        return new Collection();
    });

    Router::get('database', function () {
        if (PDO::connection(Config::item('database'))) {
            return 'Successfuly connected to database';
        }
    });

    Router::get('include', function () {
        include 'microstatic/modules/Demo/demo.php';
    });

    /**
     * Using controllers
     */
    Router::get('welcome/(:any)/(:any)', 'App\\Demo\\Controllers\\YourController@yourMethod');

    /**
     * Route groups
     */
    Router::group([

        // Prefixes all uris in the group with 'auth/'
        'uriPrefix' => 'auth',

        // Define the class namespace for all routes in this group
        // Will be prefixed to the controllers
        'namespace' => 'App\\Demo\\Controllers\\'

    ], function () {

        // GET request: 'auth/login'
        // Controller 'App\\Demo\\Controllers\AuthController
        Router::get('login', [
            'controller' => 'AuthController',
            'action' => 'loginForm' // Show the login form
        ]);

        // POST request: 'auth/login'
        // Controller 'App\\Demo\\Controllers\AuthController
        Router::post('login', [
            'controller' => 'AuthController',
            'action' => 'login' // Login the user
        ]);

        // GET request: 'auth/logout'
        // Controller 'App\\Demo\\Controllers\AuthController
        Router::get('logout', [
            'controller' => 'AuthController',
            'action' => 'logout' // Logout the user
        ]);

        /**
         * Subgroup with middlewares
         */
        Router::group([
            // Middlewares apply to all route in this (sub)group
            'middlewares' => [
                // Check if the client is authorised to access these routes
                'App\\Demo\\Middlewares\\CheckPermission',
                // Send a email to the administrator
                'App\\Demo\\Middlewares\\ReportAccess',
            ]
        ], function () {

            // No access to these routes if middleware CheckPermission fails
            // Middleware ReportAccess reports all access to these routes

            // GET request: 'auth/users'
            // Controller 'App\\Demo\\Controllers\UserController
            Router::get('users', [
                'controller' => 'UserController',
                'action' => 'list' // Show a list of users
            ]);

            // GET request: 'auth/users/create'
            // Controller 'App\\Demo\\Controllers\UserController
            Router::get('users/create', [
                'controller' => 'UserController',
                'action' => 'createForm' // Show a empty user form
            ]);

            // GET request: 'auth/users/edit'
            // Controller 'App\\Demo\\Controllers\UserController
            Router::get('users/edit/(:num)', [
                'controller' => 'UserController',
                'action' => 'editForm' // Show a edit form for user (:num)
            ]);

            // etc...
        });
    });

    // Example: file download
    Router::get('download/pdf', function () {
        Response::header('Content-Type: application/pdf');
        Response::header('Content-Disposition: attachment; filename="downloaded.pdf"');
        readfile('sample.pdf');
    });

    // Example: caching
    Router::get('huge/data/table', [
        'middlewares' => ['App\\Demo\\Middlewares\\Cache' => [10]],
        // Cache for 10sec
        'controller' => 'App\\Demo\\Controllers\\YourController',
        'action' => 'timeConsumingAction'
    ]);

    Router::group([
        'middlewares' => ['App\\Demo\\Middlewares\\Cache' => [(10)]]
    ], function () {

        Router::get('test/(:num)', function ($a) {
            return $a;
        });

        Router::get('pages/(:any)/(:any)', [
            'controller' => 'Acme\\Pages\\Controllers\\PagesController',
            'action' => 'getStaticPage',
        ]);

        Router::get('pages/info',
            'Acme\\Pages\\Controllers\\PagesController@info');
    });

});
