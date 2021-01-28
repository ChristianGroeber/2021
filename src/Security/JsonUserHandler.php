<?php

namespace Nacho\Security;


class JsonUserHandler implements UserHandlerInterface
{
    public function __construct()
    {
         if (!isset($_SESSION['user'])) {
             $_SESSION['user'] = ['username' => 'Guest', 'password' => null, 'role' => 'Guest'];
         }
    }

    public function getCurrentUser()
    {
        return $this->findUser($_SESSION['user']['username']);
    }

    public function getUsers()
    {
        return json_decode(file_get_contents(FILE_PATH), true);
    }

    public function findUser($username)
    {
        foreach ($this->getUsers() as $user) {
            if ($username === $user['username']) {
                return $user;
            }
        }

        return false;
    }

    public function logout()
    {
        session_destroy();
    }

    public function getRoles()
    {
        return ['Super Admin', 'Editor', 'Reader', 'Guest'];
    }

    public function isGranted(string $minRight = 'Guest', array $user = null)
    {
        if (!$user) {
            $user = $this->getCurrentUser();
        }

        return array_search($user['role'], $this->getRoles()) <= array_search($minRight, $this->getRoles());
    }
}
