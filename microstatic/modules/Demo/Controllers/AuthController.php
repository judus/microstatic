<?php namespace App\Demo\Controllers;

use MicroStatic\Response;

/**
 * Class AuthController
 *
 * @package App\Demo\Controllers
 */
class AuthController
{
    /**
     * AuthController constructor.
     */
    public function __construct()
    {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
    }

    /**
     * @return string
     */
    public function loginForm()
    {
        if ($this->isLoggedIn()) {
            $html = '<p>You are logged in as '
                . $_SESSION['currentUser'] .'</p>';

            $html.= '<a href="/auth/logout">Logout</a>';
            return $html;
        }

        $html = '<p>Imagine a login form and press the button:</p>';
        $html.= '<form action="/auth/login" method="post">';
        $html.= '<input type="submit" name="login" value="login">';
        $html.= '</form >';

        return $html;
    }

    /**
     *
     */
    public function login()
    {
        $_SESSION['currentUser'] = 'jondoe';
        Response::redirect();
    }

    /**
     *
     */
    public function logout()
    {
        unset($_SESSION['currentUser']);
        Response::redirect('/auth/login');
    }

    /**
     * @return bool
     */
    private function isLoggedIn()
    {
        if (isset($_SESSION['currentUser'])) {
            return true;
        }

        return false;
    }

    /**
     *
     */
    private function redirect()
    {
        if (isset($_SESSION['redirectUrl'])) {
            $redirectUrl = $_SESSION['redirectUrl'];
            unset($_SESSION['redirectUrl']);
            Response::redirect($redirectUrl);
        } else {
            Response::redirect('/auth/login');
        }
    }
}