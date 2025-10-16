<?php
// public/visits.php

// Cargar dependencias
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../viewmodels/AuthViewModel.php';

// Instanciar el ViewModel de autenticación
$authViewModel = new AuthViewModel();

// Obtener los parámetros de la URL original
$queryString = $_SERVER['QUERY_STRING'] ?? '';

// Definir el destino por defecto
$destination = 'login.php';

// Verificar si el usuario ha iniciado sesión
if ($authViewModel->isLoggedIn()) {
    // Si ha iniciado sesión, verificar si es administrador
    if ($authViewModel->isAdmin()) {
        $destination = 'admin/visits.php';
    } else {
        $destination = 'client/visits.php';
    }
}

// Si había parámetros en la URL, añadirlos a la nueva URL de destino
if (!empty($queryString)) {
    $destination .= '?' . $queryString;
}

// Redirigir al destino apropiado
redirect($destination);

?>