<?php
session_start();
require_once 'config.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Não autorizado']);
    exit;
}

try {
    $database = new Database();
    $db = $database->getConnection();
    
    // Atualiza última atividade do usuário
    $notification = new NotificationSystem($db);
    $notification->updateUserActivity($_SESSION['user_id']);
    
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
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}