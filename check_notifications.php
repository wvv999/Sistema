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
    
    $notifications = [];
    $currentSector = $_SESSION['current_sector'] ?? null;

    // Verifica mudanças de status baseado no setor do usuário
    if ($currentSector === 'atendimento') {
        // Busca ordens que foram marcadas como 'solicitada' pelo setor técnico
        $query = "SELECT so.id as order_id, u.username as from_username 
                FROM service_orders so
                JOIN users u ON so.last_modified_by = u.id
                WHERE so.auth_status = 'solicitada'
                AND so.notification_sent = 0
                AND u.current_sector = 'tecnica'";
                
        $stmt = $db->prepare($query);
        $stmt->execute();
        
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $notifications[] = [
                'type' => 'auth_status',
                'order_id' => $row['order_id'],
                'from_username' => $row['from_username']
            ];
            
            // Marca como notificado
            $updateStmt = $db->prepare("UPDATE service_orders SET notification_sent = 1 WHERE id = ?");
            $updateStmt->execute([$row['order_id']]);
        }
    } 
    else if ($currentSector === 'tecnica') {
        // Busca ordens que foram autorizadas pelo atendimento
        $query = "SELECT so.id as order_id, u.username as from_username 
                FROM service_orders so
                JOIN users u ON so.last_modified_by = u.id
                WHERE so.auth_status = 'autorizada'
                AND so.notification_sent = 0
                AND u.current_sector = 'atendimento'";
                
        $stmt = $db->prepare($query);
        $stmt->execute();
        
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $notifications[] = [
                'type' => 'auth_approved',
                'order_id' => $row['order_id'],
                'from_username' => $row['from_username']
            ];
            
            // Marca como notificado
            $updateStmt = $db->prepare("UPDATE service_orders SET notification_sent = 1 WHERE id = ?");
            $updateStmt->execute([$row['order_id']]);
        }
    }

    // Também verifica notificações manuais entre setores
    $query = "SELECT n.*, u.username as from_username 
              FROM notifications n
              JOIN users u ON n.from_user_id = u.id 
              WHERE n.created_at > NOW() - INTERVAL 10 SECOND
              AND n.from_user_id != ?
              AND n.type = ?
              ORDER BY n.created_at DESC
              LIMIT 1";
              
    $stmt = $db->prepare($query);
    $stmt->execute([$_SESSION['user_id'], $currentSector]);
    $notification = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($notification) {
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