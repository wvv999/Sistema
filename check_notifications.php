<?php
session_start();
require_once 'config.php';
require_once 'notifications.php';
header('Content-Type: application/json');

try {
    if (!isset($_SESSION['user_id'])) {
        throw new Exception('Usuário não autenticado');
    }

    $database = new Database();
    $db = $database->getConnection();
    
    // Busca notificações não lidas
    $query = "SELECT * FROM notifications 
              WHERE created_at > NOW() - INTERVAL 10 SECOND
              AND from_user_id != ?
              ORDER BY created_at DESC
              LIMIT 1";
              
    $stmt = $db->prepare($query);
    $stmt->execute([$_SESSION['user_id']]);
    $notification = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'hasNotification' => !empty($notification),
        'notification' => $notification
    ]);

} catch (Exception $e) {
    error_log("Erro em check_notifications.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}