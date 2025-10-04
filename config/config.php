<?php
// config/config.php

// Función para cargar variables de entorno desde .env
function load_env($path) {
    if (!file_exists($path)) {
        return;
    }
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) {
            continue;
        }
        list($name, $value) = explode('=', $line, 2);
        $name = trim($name);
        $value = trim($value);
        if (!array_key_exists($name, $_SERVER) && !array_key_exists($name, $_ENV)) {
            putenv(sprintf('%s=%s', $name, $value));
            $_ENV[$name] = $value;
            $_SERVER[$name] = $value;
        }
    }
}

// Cargar el archivo .env desde el directorio raíz
load_env(__DIR__ . '/../.env');

// Iniciar sesión si no está iniciada y configurar cookies seguras
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    ini_set('session.cookie_secure', isset($_SERVER['HTTPS']));
    session_start();
}

// Configuración de la aplicación
$db_pass = getenv('DB_PASS') ?: '';

// AHORA ES UNA VARIABLE, NO UNA CONSTANTE
$APP_CONFIG = [
    // Base de datos
    'db' => [
        'host' => getenv('DB_HOST') ?: 'localhost',
        'user' => getenv('DB_USER') ?: 'root',
        'pass' => $db_pass,
        'name' => getenv('DB_NAME') ?: 'guarderia'
    ],
    // URLs
    'urls' => [
        'base' => '/guarderia/public/',
        'client' => '/guarderia/public/client/',
        'admin' => '/guarderia/public/admin/',
        'assets' => '/guarderia/assets/'
    ],
    // Configuración de seguridad
    'security' => [
        'hash_algo' => PASSWORD_ARGON2ID,
        'session_lifetime' => 3600
    ]
];

// Definir constantes para compatibilidad con código existente
foreach ($APP_CONFIG['urls'] as $key => $value) {
    define(strtoupper($key) . '_URL', $value);
}
define('DB_HOST', $APP_CONFIG['db']['host']);
define('DB_USER', $APP_CONFIG['db']['user']);
define('DB_PASS', $APP_CONFIG['db']['pass']);
define('DB_NAME', $APP_CONFIG['db']['name']);

// Configuración de la zona horaria y errores
date_default_timezone_set('America/Bogota');
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Función para obtener configuración
function getConfig(string $path) {
    global $APP_CONFIG; // Se accede a la variable global
    $keys = explode('.', $path);
    $config = $APP_CONFIG;
    
    foreach ($keys as $key) {
        if (!isset($config[$key])) {
            return null;
        }
        $config = $config[$key];
    }
    
    return $config;
}
?>
