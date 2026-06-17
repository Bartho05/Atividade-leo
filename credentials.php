<?php

function getUserCredentials()
{
    return [
        'admin' => [
            'password' => '1234',
            'username' => 'Tatu',
            'type' => 'admin',
        ],
        'moderator' => [
            'password' => '1234',
            'username' => 'Tatu3',
            'type' => 'moderator',
        ],
        'user' => [
            'password' => '1234',
            'username' => 'Tatu2',
            'type' => 'user',
        ],
    ];
}

function authenticate($username, $password)
{
    $credentials = getUserCredentials();
    if (isset($credentials[$username]) && $credentials[$username]['password'] === $password) {
        return [
            'username' => $username,
            'name' => $credentials[$username]['username'],
            'type' => $credentials[$username]['type'],
        ];
    }
    return false;
}

?>