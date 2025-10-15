<?php
// views/layouts/header.php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . ' - ' : ''; ?>Guardería Happy Pets</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome Icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <!-- SweetAlert2 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>

    <!-- Custom CSS -->
    <link href="<?php echo ASSETS_URL; ?>css/style.css?v=1.1" rel="stylesheet">
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="<?php echo ASSETS_URL; ?>images/favicon.ico">
</head>
<body class="<?php echo isset($body_class) ? $body_class : ''; ?>">
    <script>
        // Aplicar modo oscuro inmediatamente para prevenir flash
        if (localStorage.getItem('darkMode') === 'enabled') {
            document.body.classList.add('dark-mode');
        }
    </script>

<?php 
// Incluir helpers de menú
$menuHelpersPath = __DIR__ . '/../../includes/menu_helpers.php';
if (file_exists($menuHelpersPath)) {
    require_once $menuHelpersPath;
} else {
    die('Error: No se pudo encontrar el archivo menu_helpers.php');
}

if (isset($_SESSION['user_id'])): 
    $userRole = $_SESSION['role'];
    $menuItems = getMenuItems($userRole);
    $userMenu = getUserProfileMenu(
        $_SESSION['first_name'],
        $_SESSION['last_name'] ?? '',
        $userRole
    );
?>
<!--Navbar para usuarios logueados -->
<nav class="navbar navbar-expand-lg navbar-dark bg-primary fixed-top">
    <div class="container">
        <a class="navbar-brand fw-bold" href="<?php echo $userRole === 'admin' ? ADMIN_URL : CLIENT_URL; ?>dashboard.php">
            <i class="fas fa-paw me-2"></i>Happy Pets
        </a>
        
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMain">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        <div class="collapse navbar-collapse" id="navbarMain">
            <!-- Menú principal -->
            <ul class="navbar-nav me-auto">
                <?php foreach ($menuItems as $item): ?>
                    <?php echo renderMenuItem($item); ?>
                <?php endforeach; ?>
            </ul>
            
            <!-- Menú de usuario -->
            <ul class="navbar-nav">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-user-circle me-1"></i>
                        <?php echo htmlspecialchars($userMenu['header']['name']); ?>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end shadow">
                        <?php if ($userMenu['header']['role']): ?>
                            <li><h6 class="dropdown-header">Rol: <?php echo ucfirst($userMenu['header']['role']); ?></h6></li>
                            <li><hr class="dropdown-divider"></li>
                        <?php endif; ?>
                        
                        <?php foreach ($userMenu['items'] as $item): ?>
                            <li>
                                <a class="dropdown-item <?php echo $item['class'] ?? ''; ?>" 
                                   href="<?php echo htmlspecialchars($item['url']); ?>"
                                   <?php echo ($item['title'] === 'Cerrar Sesión') ? 'onclick="return confirm(\'¿Estás seguro de que deseas cerrar sesión?\')"' : ''; ?>> 
                                    <i class="fas fa-<?php echo htmlspecialchars($item['icon']); ?> me-2"></i>
                                    <?php echo htmlspecialchars($item['title']); ?>
                                </a>
                            </li>
                            <?php if ($item['title'] === 'Configuración'): ?>
                                <li><hr class="dropdown-divider"></li>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>

<!-- Espaciado para navbar fija -->
<div class="navbar-spacer"></div>
<?php endif; ?>

<!-- Contenedor principal -->
<div class="<?php echo isset($_SESSION['user_id']) ? 'container-fluid' : 'container'; ?> main-content">
    <?php if (isset($_SESSION['user_id'])): ?>
        <div class="row">
            <?php if ($_SESSION['role'] === 'admin'): ?>
                <!-- Sidebar para administrador -->
                <nav class="col-md-2 d-none d-md-block sidebar">
                    <div class="position-sticky pt-3">
                        <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-muted">
                            <span>Panel de Control</span>
                        </h6>
                        <ul class="nav flex-column">
                            <li class="nav-item">
                                <a class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'dashboard.php') ? 'active' : ''; ?>" href="<?php echo ADMIN_URL; ?>dashboard.php">
                                    <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                                </a>
                            </li>
                        </ul>
                        
                        <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-muted">
                            <span>Gestión</span>
                        </h6>
                        <ul class="nav flex-column">
                            <li class="nav-item">
                                <a class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'pets.php') ? 'active' : ''; ?>" href="<?php echo ADMIN_URL; ?>pets.php">
                                    <i class="fas fa-dog me-2"></i>Mascotas
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'appointments.php') ? 'active' : ''; ?>" href="<?php echo ADMIN_URL; ?>appointments.php">
                                    <i class="fas fa-calendar-alt me-2"></i>Citas
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'visits.php') ? 'active' : ''; ?>" href="<?php echo ADMIN_URL; ?>visits.php">
                                    <i class="fas fa-history me-2"></i>Visitas
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'clients.php') ? 'active' : ''; ?>" href="<?php echo ADMIN_URL; ?>clients.php">
                                    <i class="fas fa-users me-2"></i>Clientes
                                </a>
                            </li>
                        </ul>
                    </div>
                </nav>
                <main class="col-md-10 ms-sm-auto px-md-4">
            <?php else: ?>
                <main class="col-12">
            <?php endif; ?>
    <?php else: ?>
        <main>
    <?php endif; ?>

    <!-- Mensajes de alerta -->
    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="alert alert-success alert-dismissible fade show mt-3" role="alert">
            <i class="fas fa-check-circle me-2"></i>
            <?php 
            echo $_SESSION['success_message']; 
            unset($_SESSION['success_message']);
            ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['error_message'])): ?>
        <div class="alert alert-danger alert-dismissible fade show mt-3" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>
            <?php 
            echo $_SESSION['error_message']; 
            unset($_SESSION['error_message']);
            ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>