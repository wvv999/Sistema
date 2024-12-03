<?php
session_start();
require_once 'config.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Não autorizado']);
    exit;
}

try {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['sector'])) {
        throw new Exception('Setor não especificado');
    }

    $database = new Database();
    $db = $database->getConnection();
    
    // Insere a notificação geral
    $query = "INSERT INTO notifications (type, from_user_id, created_at, viewed) 
              VALUES (?, ?, NOW(), 0)";
    $stmt = $db->prepare($query);
    $result = $stmt->execute([$data['sector'], $_SESSION['user_id']]);

    // Insere a notificação específica de atualização de status ou de autorização
    if ($data['sector'] === 'atendimento') {
        $notificationQuery = "INSERT INTO notifications (type, from_user_id, order_id, created_at, viewed) 
                              VALUES ('auth_status', ?, NULL, NOW(), 0)";
    } else {
        $notificationQuery = "INSERT INTO notifications (type, from_user_id, order_id, created_at, viewed) 
                              VALUES ('auth_approved', ?, NULL, NOW(), 0)";
    }
    $notificationStmt = $db->prepare($notificationQuery);
    $notificationStmt->execute([$_SESSION['user_id']]);
    
    if ($result) {
        echo json_encode(['success' => true]);
    } else {
        throw new Exception('Erro ao criar notificação');
    }
    
} catch (Exception $e) {
    error_log("Erro em send_notification.php: " . $e->getMessage());
    echo json_encode([
        'success' => false, 
        'message' => $e->getMessage()
    ]);
}