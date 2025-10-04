<?php
// public/logout.php
require_once '../includes/functions.php';
require_once '../viewmodels/AuthViewModel.php';
require_once '../config/config.php';

session_start();

$authViewModel = new AuthViewModel();

// Cerrar sesión
$result = $authViewModel->logout();

// Destruir la cookie de sesión si existe
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time()-42000, '/');
}

// Mensaje de despedida
$_SESSION['success_message'] = 'Has cerrado sesión correctamente. ¡Hasta pronto!';

// Redirigir al login
redirect(BASE_URL . 'login.php');
?>