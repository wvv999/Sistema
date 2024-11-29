// send_notification.php
<?php
session_start();
require_once 'config.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Não autorizado']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

try {
    $database = new Database();
    $db = $database->getConnection();
    
    $notification = new NotificationSystem($db);
    $result = $notification->createNotification(
        $data['sector'],
        $_SESSION['user_id']
    );
    
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}