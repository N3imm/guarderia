<?php
// public/admin/clients.php
require_once '../../includes/functions.php';
require_once '../../viewmodels/AuthViewModel.php';
require_once '../../viewmodels/UserViewModel.php';
require_once '../../config/config.php';

$authViewModel = new AuthViewModel();
$userViewModel = new UserViewModel();

// Verificar que sea administrador
$sessionResult = $authViewModel->validateAdminSession();
if (!$sessionResult['success']) {
    redirect($sessionResult['redirect']);
}

// Obtener todos los clientes
$clientsResult = $userViewModel->getAllClients();
$clients = $clientsResult['success'] ? $clientsResult['data'] : [];

$page_title = 'Gestión de Clientes';
$css_path = '../../';

include '../../views/layouts/header.php';
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">
        <i class="fas fa-users me-2 text-primary"></i>Gestión de Clientes
    </h1>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Teléfono</th>
                        <th>Miembro desde</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($clients)): ?>
                        <?php foreach ($clients as $client): ?>
                            <tr>
                                <td><?php echo $client['id']; ?></td>
                                <td><?php echo htmlspecialchars($client['first_name'] . ' ' . $client['last_name']); ?></td>
                                <td><?php echo htmlspecialchars($client['username']); ?></td>
                                <td><?php echo htmlspecialchars($client['email']); ?></td>
                                <td><?php echo htmlspecialchars($client['phone']); ?></td>
                                <td><?php echo date('d/m/Y', strtotime($client['created_at'])); ?></td>
                                <td>
                                    <a href="pets.php?user_id=<?php echo $client['id']; ?>" class="btn btn-sm btn-outline-primary">Ver Mascotas</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center">No hay clientes registrados.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include '../../views/layouts/footer.php'; ?>
