<?php
session_start();

function isAuthenticated() {
    return isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
}

function requireAuth() {
    if (!isAuthenticated()) {
        header('Location: /backend/admin/login.php');
        exit();
    }
}

function login($username, $password) {
    $adminUsername = getenv('ADMIN_USERNAME') ?: 'admin';
    $adminPassword = getenv('ADMIN_PASSWORD') ?: 'admin123';
    
    if ($username === $adminUsername && $password === $adminPassword) {
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_username'] = $username;
        return true;
    }
    return false;
}

function logout() {
    session_destroy();
}
