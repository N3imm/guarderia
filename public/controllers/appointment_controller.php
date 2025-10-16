<?php
// public/controllers/appointment_controller.php
require_once __DIR__ . '/../../viewmodels/AppointmentViewModel.php';
require_once __DIR__ . '/../../includes/functions.php';

header('Content-Type: application/json');

// Ensure session is started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$action = $_POST['action'] ?? $_GET['action'] ?? '';

if ($action === 'get_available_slots') {
    $date = $_POST['date'] ?? $_GET['date'] ?? '';

    if (empty($date)) {
        echo json_encode(['success' => false, 'message' => 'Date is required.']);
        exit;
    }

    $viewModel = new AppointmentViewModel();
    $availableSlots = $viewModel->getAvailableTimeSlots($date);
    
    $formattedSlots = [];
    foreach ($availableSlots as $slot) {
        $formattedSlots[] = [
            'time' => $slot,
            'display' => date('g:i A', strtotime($slot))
        ];
    }

    echo json_encode(['success' => true, 'data' => $formattedSlots]);
    exit;
}

echo json_encode(['success' => false, 'message' => 'Invalid action.']);
?>