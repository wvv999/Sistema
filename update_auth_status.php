<?php
session_start();
header('Content-Type: application/json');
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método não permitido']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['orderId']) || !isset($data['authStatus'])) {
    echo json_encode(['success' => false, 'message' => 'Dados incompletos']);
    exit;
}

try {
    $database = new Database();
    $db = $database->getConnection();
    
    $db->beginTransaction();
    
    $query = "UPDATE service_orders 
              SET auth_status = :auth_status,
                  last_modified_by = :user_id,
                  notification_sent = 0 
              WHERE id = :id";
    $stmt = $db->prepare($query);
    
    $result = $stmt->execute([
        ':auth_status' => $data['authStatus'],
        ':user_id' => $_SESSION['user_id'],
        ':id' => $data['orderId']
    ]);
    
    if ($result) {
        // Registra a alteração na tabela activities
        $activityQuery = "INSERT INTO activities (order_id, user_id, action_type, details)
                         VALUES (:order_id, :user_id, :action_type, :details)";
        $stmt = $db->prepare($activityQuery);
        
        $activityResult = $stmt->execute([
            ':order_id' => $data['orderId'],
            ':user_id' => $_SESSION['user_id'],
            ':action_type' => 'auth_status_change',
            ':details' => json_encode([
                'new_status' => $data['authStatus']
            ])
        ]);
        
        if ($activityResult) {
            $db->commit();
            echo json_encode(['success' => true]);
        } else {
            $db->rollBack();
            echo json_encode(['success' => false, 'message' => 'Erro ao registrar atividade']);
        }
    } else {
        $db->rollBack();
        echo json_encode(['success' => false, 'message' => 'Erro ao atualizar status de autorização']);
    }
    
} catch (Exception $e) {
    if (isset($db)) {
        $db->rollBack();
    }
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}