<?php
session_start();
require_once 'config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Usuário não autenticado']);
    exit;
}

try {
    $database = new Database();
    $db = $database->getConnection();
    
    // Verificar notificações não lidas para o usuário
    $query = "SELECT n.*, u.username as from_username 
              FROM notifications n 
              JOIN users u ON n.from_user_id = u.id
              WHERE n.to_user_id = :user_id 
              AND n.read = 0
              ORDER BY n.created_at DESC
              LIMIT 1";
              
    $stmt = $db->prepare($query);
    $stmt->execute([':user_id' => $_SESSION['user_id']]);
    $notification = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($notification) {
        // Marcar notificação como lida
        $updateQuery = "UPDATE notifications SET read = 1 WHERE id = :id";
        $stmt = $db->prepare($updateQuery);
        $stmt->execute([':id' => $notification['id']]);
        
        echo json_encode([
            'success' => true,
            'hasNotification' => true,
            'notification' => $notification
        ]);
    } else {
        echo json_encode([
            'success' => true,
            'hasNotification' => false
        ]);
    }
    
} catch(Exception $e) {
    echo json_encode([
        'success' => false, 
        'message' => 'Erro ao verificar notificações: ' . $e->getMessage()
    ]);
}