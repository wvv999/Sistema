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
    
    // Verifica mudanças de status baseado no setor do usuário
    $notifications = [];

    // Primeiro verifica notificações de status/autorização
    $query = "SELECT so.id as order_id, so.status, so.auth_status, 
                     u.username as from_username, so.last_modified
              FROM service_orders so
              JOIN users u ON so.last_modified_by = u.id
              WHERE (so.status = 'solicitada' OR so.auth_status = 'autorizada')
              AND so.notification_sent = 0
              AND so.last_modified > NOW() - INTERVAL 10 SECOND";
              
    $stmt = $db->prepare($query);
    $stmt->execute();
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        if ($row['status'] === 'solicitada') {
            $notifications[] = [
                'type' => 'auth_status',
                'order_id' => $row['order_id'],
                'from_username' => $row['from_username']
            ];
        } else if ($row['auth_status'] === 'autorizada') {
            $notifications[] = [
                'type' => 'auth_approved',
                'order_id' => $row['order_id'],
                'from_username' => $row['from_username']
            ];
        }
        
        // Marca como notificado
        $updateStmt = $db->prepare("UPDATE service_orders SET notification_sent = 1 WHERE id = ?");
        $updateStmt->execute([$row['order_id']]);
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