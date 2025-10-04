<?php
// includes/functions.php

/**
 * Redirige a otra página.
 *
 * @param string $url La URL a la que redirigir.
 */
function redirect($url) {
    // Si es una URL absoluta o comienza con http, usarla como está
    if (filter_var($url, FILTER_VALIDATE_URL) || strpos($url, 'http') === 0) {
        $redirect_url = $url;
    }
    // Si comienza con una de las URLs base definidas, usarla como está
    else if (strpos($url, BASE_URL) === 0 || 
             strpos($url, CLIENT_URL) === 0 || 
             strpos($url, ADMIN_URL) === 0 || 
             strpos($url, ASSETS_URL) === 0) {
        $redirect_url = $url;
    }
    // En otro caso, asumir que es relativa a BASE_URL
    else {
        $redirect_url = BASE_URL . ltrim($url, '/');
    }

    header('Location: ' . $redirect_url);
    exit();
}

/**
 * Limpia y sanitiza una entrada de usuario
 *
 * @param string $data El dato a sanitizar
 * @return string El dato sanitizado
 */
function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}
?>