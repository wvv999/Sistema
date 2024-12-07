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
    
    // Busca o setor atual do usuário
    $sectorQuery = "SELECT current_sector FROM users WHERE id = ?";
    $stmt = $db->prepare($sectorQuery);
    $stmt->execute([$_SESSION['user_id']]);
    $currentSector = $stmt->fetchColumn();
    
    // Busca notificações não visualizadas
    $query = "SELECT n.*, u.username as from_username
             FROM notifications n
             JOIN users u ON n.from_user_id = u.id
             WHERE n.created_at > NOW() - INTERVAL 10 SECOND
             AND n.from_user_id != ?
             AND n.type = ?
             AND n.viewed = 0
             ORDER BY n.created_at DESC
             LIMIT 1";
             
    $stmt = $db->prepare($query);
    $stmt->execute([$_SESSION['user_id'], $currentSector]);
    $notification = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($notification) {
        // Marca como visualizada
        $updateQuery = "UPDATE notifications SET viewed = 1 WHERE id = ?";
        $updateStmt = $db->prepare($updateQuery);
        $updateStmt->execute([$notification['id']]);
        
        echo json_encode([
            'success' => true,
            'hasNotification' => true,
            'notification' => $notification
        ]);
    } else {
        echo json_encode([
            'success' => true,
            'hasNotification' => false,
            'notification' => null
        ]);
    }

} catch (Exception $e) {
    error_log('Erro em check_notifications.php: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>