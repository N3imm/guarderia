<?php
// includes/session.php

// Iniciar sesión si no está iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Función para verificar si el usuario está logueado
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Función para verificar si el usuario es administrador
function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

// Función para verificar si el usuario es cliente
function isClient() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'client';
}

// Función para obtener el ID del usuario actual
function getCurrentUserId() {
    return $_SESSION['user_id'] ?? null;
}

// Función para obtener el rol del usuario actual
function getCurrentUserRole() {
    return $_SESSION['role'] ?? null;
}

// Función para destruir la sesión
function logout() {
    $_SESSION = array();
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    session_destroy();
}
