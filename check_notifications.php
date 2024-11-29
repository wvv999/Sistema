<?php
session_start();
require_once 'config.php';
header('Content-Type: application/json');

try {
    if (!isset($_SESSION['user_id'])) {
        throw new Exception('Usuário não autenticado');
    }

    $database = new Database();
    $db = $database->getConnection();
    
    // Log para debug
    error_log("Verificando notificações para usuário: " . $_SESSION['user_id']);
    
    // Busca notificações não lidas
    $query = "SELECT n.*, u.username as from_username 
              FROM notifications n
              JOIN users u ON n.from_user_id = u.id 
              WHERE n.created_at > NOW() - INTERVAL 10 SECOND
              AND n.from_user_id != ?
              ORDER BY n.created_at DESC
              LIMIT 1";
              
    $stmt = $db->prepare($query);
    $stmt->execute([$_SESSION['user_id']]);
    $notification = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Log para debug
    error_log("Notificação encontrada: " . print_r($notification, true));
    
    echo json_encode([
        'success' => true,
        'hasNotification' => !empty($notification),
        'notification' => $notification,
        'current_user' => $_SESSION['user_id']
    ]);

} catch (Exception $e) {
    error_log("Erro em check_notifications.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}