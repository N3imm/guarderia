<?php
// viewmodels/UserViewModel.php
require_once __DIR__ . '/../models/User.php';

class UserViewModel {
    private $user;

    public function __construct() {
        $this->user = new User();
    }

    public function getAllClients() {
        $clients = $this->user->getAllClients();
        return ['success' => true, 'data' => $clients];
    }
}
?>