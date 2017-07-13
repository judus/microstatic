<?php namespace App\Demo\Controllers;

/**
 * Class UserController
 *
 * @package App\Demo\Controllers
 */
class UserController
{
    /**
     * @return array
     */
    public function list()
    {
        return [
            [
                'firstname' => 'Jon',
                'lastname' => 'Doe',
                'username' => 'jondoe'
            ],
            [
                'firstname' => 'Jane',
                'lastname' => 'Doe',
                'username' => 'janedoe'
            ],
        ];
    }

    /**
     * @return string
     */
    public function createForm()
    {
        return 'Imagine a user form';
    }

    /**
     * @param $id
     *
     * @return string
     */
    public function editForm($id)
    {
        return 'Imagine a user form for user with $id = '.$id;
    }
}