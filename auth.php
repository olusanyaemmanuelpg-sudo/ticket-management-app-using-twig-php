<?php
session_start();

function getCurrentUser() {
    return $_SESSION['user'] ?? null;
}

function login($email, $password) {
    if ($email === 'admin@ticket.com' && $password === 'password123') {
        $userData = [
            'id' => 1,
            'name' => 'Admin User',
            'email' => $email
        ];
        $_SESSION['user'] = $userData;
        return ['success' => true];
    }

    return ['success' => false, 'error' => 'Invalid credentials'];
}

function signup($name, $email, $password) {
    $userData = [
        'id' => time(),
        'name' => $name,
        'email' => $email,
        'password' => $password
    ];

    $_SESSION['user'] = $userData;
    return ['success' => true];
}

function logout() {
    session_destroy();
}