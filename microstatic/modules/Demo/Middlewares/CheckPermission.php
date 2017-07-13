<?php namespace App\Demo\Middlewares;

use MicroStatic\Request;
use MicroStatic\Response;

/**
 * Class CheckPermission
 *
 * @package Acme\Middlewares
 */
class CheckPermission
{
    /**
     * CheckPermission constructor.
     */
    public function __construct()
    {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
    }

    /**
     * Redirect to login page if not logged in
     */
    public function before()
    {
        if (!isset($_SESSION['currentUser'])) {
            $_SESSION['redirectUrl'] = '/' . Request::getUriString();
            return Response::redirect('/auth/login');
        }
    }

    /**
     * Do nothing
     */
    public function after()
    {
    }
}