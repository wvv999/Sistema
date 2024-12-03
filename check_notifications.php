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
    
    // Atualiza última atividade do usuário
    $updateActivity = "UPDATE users SET last_activity = NOW() WHERE id = ?";
    $stmt = $db->prepare($updateActivity);
    $stmt->execute([$_SESSION['user_id']]);
    
    $notifications = [];
    
    // Busca notificações manuais entre setores que não foram visualizadas
    $query = "SELECT n.*, u.username as from_username
             FROM notifications n
             JOIN users u ON n.from_user_id = u.id
             WHERE n.created_at > NOW() - INTERVAL 10 SECOND
             AND n.from_user_id != ?
             AND n.type != ?
             AND n.viewed = 0
             ORDER BY n.created_at DESC
             LIMIT 1";
             
    $stmt = $db->prepare($query);
    $stmt->execute([$_SESSION['user_id'], $_SESSION['current_sector']]);
    $notification = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($notification) {
        // Marca notificação como visualizada
        $updateQuery = "UPDATE notifications SET viewed = 1 WHERE id = ?";
        $updateStmt = $db->prepare($updateQuery);
        $updateStmt->execute([$notification['id']]);
        
        $notifications[] = $notification;
    }
    
    echo json_encode([
        'success' => true,
        'hasNotification' => !empty($notifications),
        'notification' => !empty($notifications) ? $notifications[0] : null
    ]);

} catch (Exception $e) {
    error_log('Erro em check_notifications.php: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}