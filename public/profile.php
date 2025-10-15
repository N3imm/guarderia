<?php
// public/profile.php
require_once '../includes/functions.php';
require_once '../includes/session.php';
require_once '../viewmodels/UserViewModel.php';
require_once '../config/config.php';

// Verificar que el usuario esté logueado
if (!isLoggedIn()) {
    redirect(BASE_URL . 'login.php');
}

$userViewModel = new UserViewModel();
$user_id = getCurrentUserId();

$profile_error = '';
$password_error = '';

// Procesar formularios
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $form_action = $_POST['action'] ?? '';

    if ($form_action === 'update_profile') {
        $_POST['id'] = $user_id;
        $result = $userViewModel->updateUserProfile($_POST);
        if ($result['success']) {
            $_SESSION['success_message'] = $result['message'];
            redirect('profile.php');
        } else {
            $profile_error = implode('<br>', $result['errors']);
        }
    } elseif ($form_action === 'update_password') {
        $_POST['id'] = $user_id;
        $result = $userViewModel->updatePassword($_POST);
        if ($result['success']) {
            $_SESSION['success_message'] = $result['message'];
            redirect('profile.php');
        } else {
            $password_error = implode('<br>', $result['errors']);
        }
    }
}

// Obtener datos del usuario
$userResult = $userViewModel->getUserById($user_id);
if (!$userResult['success']) {
    // Si no se encuentra el usuario, redirigir o mostrar error
    $_SESSION['error_message'] = 'No se pudo cargar tu información de perfil.';
    redirect(CLIENT_URL . 'dashboard.php');
}
$user = $userResult['data'];

$page_title = 'Mi Perfil';
include '../views/layouts/header.php';
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="fas fa-user-cog me-2"></i>Mi Perfil</h1>
</div>

<div class="row">
    <!-- Columna de Información de Cuenta -->
    <div class="col-lg-6 mb-4">
        <div class="card shadow-sm">
            <div class="card-header">
                <h5 class="card-title mb-0"><i class="fas fa-user-edit me-2"></i>Información de la Cuenta</h5>
            </div>
            <div class="card-body">
                <?php if ($profile_error): ?>
                    <div class="alert alert-danger">
                        <?php echo $profile_error; ?>
                    </div>
                <?php endif; ?>
                <form action="profile.php" method="POST">
                    <input type="hidden" name="action" value="update_profile">
                    
                    <div class="mb-3">
                        <label for="first_name" class="form-label">Nombre *</label>
                        <input type="text" class="form-control" id="first_name" name="first_name" value="<?php echo htmlspecialchars($user['first_name']); ?>" required>
                    </div>

                    <div class="mb-3">
                        <label for="last_name" class="form-label">Apellido</label>
                        <input type="text" class="form-control" id="last_name" name="last_name" value="<?php echo htmlspecialchars($user['last_name'] ?? ''); ?>">
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label">Correo Electrónico *</label>
                        <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                    </div>

                    <div class="mb-3">
                        <label for="phone" class="form-label">Teléfono</label>
                        <input type="tel" class="form-control" id="phone" name="phone" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Nombre de usuario</label>
                        <input type="text" class="form-control" value="<?php echo htmlspecialchars($user['username']); ?>" disabled>
                        <div class="form-text">El nombre de usuario no se puede cambiar.</div>
                    </div>

                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary"><i class="fas fa-save me-2"></i>Guardar Cambios</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Columna de Cambio de Contraseña -->
    <div class="col-lg-6 mb-4">
        <div class="card shadow-sm">
            <div class="card-header">
                <h5 class="card-title mb-0"><i class="fas fa-key me-2"></i>Cambiar Contraseña</h5>
            </div>
            <div class="card-body">
                <?php if ($password_error): ?>
                    <div class="alert alert-danger">
                        <?php echo $password_error; ?>
                    </div>
                <?php endif; ?>
                <form action="profile.php" method="POST">
                    <input type="hidden" name="action" value="update_password">

                    <div class="mb-3">
                        <label for="current_password" class="form-label">Contraseña Actual *</label>
                        <input type="password" class="form-control" id="current_password" name="current_password" required>
                    </div>

                    <div class="mb-3">
                        <label for="new_password" class="form-label">Nueva Contraseña *</label>
                        <input type="password" class="form-control" id="new_password" name="new_password" required>
                    </div>

                    <div class="mb-3">
                        <label for="confirm_password" class="form-label">Confirmar Nueva Contraseña *</label>
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                    </div>

                    <div class="d-grid">
                        <button type="submit" class="btn btn-secondary"><i class="fas fa-sync-alt me-2"></i>Actualizar Contraseña</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include '../views/layouts/footer.php'; ?>
