<?php
session_start();
require_once 'config.php';
header('Content-Type: application/json');

try {
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['current_sector'])) {
        throw new Exception('Usuário não autenticado ou setor não definido');
    }

    $database = new Database();
    $db = $database->getConnection();
    
    // Busca notificações para o setor do usuário
    $query = "SELECT n.*, u.username as from_username 
              FROM notifications n
              JOIN users u ON n.from_user_id = u.id 
              WHERE n.created_at > NOW() - INTERVAL 10 SECOND
              AND n.type = ?
              AND n.from_user_id != ?
              ORDER BY n.created_at DESC
              LIMIT 1";
              
    $stmt = $db->prepare($query);
    $stmt->execute([$_SESSION['current_sector'], $_SESSION['user_id']]);
    $notification = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'hasNotification' => !empty($notification),
        'notification' => $notification
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}