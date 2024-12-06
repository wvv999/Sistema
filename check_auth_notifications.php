<?php
session_start();
header('Content-Type: application/json');
require_once 'config.php';

try {
    $database = new Database();
    $db = $database->getConnection();
    
    // Busca atividades de mudança de autorização recentes
    $query = "SELECT a.*, so.id as order_number, u.username 
              FROM activities a
              JOIN service_orders so ON a.order_id = so.id
              JOIN users u ON a.user_id = u.id
              WHERE a.action_type = 'auth_status_change'
              AND a.user_id != :current_user
              AND a.created_at >= NOW() - INTERVAL 1 MINUTE
              ORDER BY a.created_at DESC";
              
    $stmt = $db->prepare($query);
    $stmt->execute([':current_user' => $_SESSION['user_id']]);
    
    $notifications = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $details = json_decode($row['details'], true);
        $notifications[] = [
            'id' => $row['id'],
            'order_id' => $row['order_number'],
            'type' => 'auth_status_change',
            'message' => "Status alterado para '{$details['new_status']}' por {$row['username']}"
        ];
    }

    echo json_encode([
        'success' => true,
        'hasNotification' => !empty($notifications),
        'notifications' => $notifications
    ]);

} catch (Exception $e) {
    error_log('Erro ao verificar notificações de autorização: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao verificar notificações'
    ]);
}
?>