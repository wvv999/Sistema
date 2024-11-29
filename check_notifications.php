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
    
    $notifications = [];

    // Verifica mudanças de status de autorização baseado no setor
    if ($_SESSION['current_sector'] === 'atendimento') {
        $query = "SELECT so.id as order_id, so.auth_status, u.username as from_username
                  FROM service_orders so
                  JOIN users u ON so.last_modified_by = u.id
                  WHERE so.auth_status = 'solicitada'
                  AND so.auth_notification_sent = 0
                  AND so.last_modified > NOW() - INTERVAL 10 SECOND";
        
        $stmt = $db->prepare($query);
        $stmt->execute();
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $notifications[] = [
                'type' => 'auth_status',
                'order_id' => $row['order_id'],
                'from_username' => $row['from_username']
            ];
            
            // Marca como notificado
            $updateStmt = $db->prepare("UPDATE service_orders SET auth_notification_sent = 1 WHERE id = ?");
            $updateStmt->execute([$row['order_id']]);
        }
    }
    else if ($_SESSION['current_sector'] === 'tecnica') {
        $query = "SELECT so.id as order_id, so.auth_status, u.username as from_username
                  FROM service_orders so
                  JOIN users u ON so.last_modified_by = u.id
                  WHERE so.auth_status = 'autorizada'
                  AND so.auth_notification_sent = 0
                  AND so.last_modified > NOW() - INTERVAL 10 SECOND";
        
        $stmt = $db->prepare($query);
        $stmt->execute();
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $notifications[] = [
                'type' => 'auth_approved',
                'order_id' => $row['order_id'],
                'from_username' => $row['from_username']
            ];
            
            // Marca como notificado
            $updateStmt = $db->prepare("UPDATE service_orders SET auth_notification_sent = 1 WHERE id = ?");
            $updateStmt->execute([$row['order_id']]);
        }
    }
    
    echo json_encode([
        'success' => true,
        'hasNotification' => !empty($notifications),
        'notifications' => $notifications
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}