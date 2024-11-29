<?php
session_start();
header('Content-Type: application/json');
require_once 'config.php';

// Verificar se é uma requisição POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método não permitido']);
    exit;
}

// Obter os dados enviados
$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['orderId']) || !isset($data['status'])) {
    echo json_encode(['success' => false, 'message' => 'Dados incompletos']);
    exit;
}

try {
    $database = new Database();
    $db = $database->getConnection();
    
    // Primeiro, buscar o status atual
    $currentStatusQuery = "SELECT status FROM service_orders WHERE id = :id";
    $stmt = $db->prepare($currentStatusQuery);
    $stmt->execute([':id' => $data['orderId']]);
    $currentStatus = $stmt->fetchColumn();
    
    // Iniciar transação
    $db->beginTransaction();
    
    // Atualizar o status da ordem
    $query = "UPDATE service_orders 
              SET status = :status,
                  last_modified_by = :user_id,
                  notification_sent = 0 
              WHERE id = :id";
    $stmt = $db->prepare($query);
    
    $result = $stmt->execute([
        ':status' => $data['status'],
        ':user_id' => $_SESSION['user_id'],
        ':id' => $data['orderId']
    ]);
    
    if ($result) {
        // Registrar a mudança na tabela activities
        $activityQuery = "INSERT INTO activities (order_id, user_id, action_type, details) 
                         VALUES (:order_id, :user_id, :action_type, :details)";
        $stmt = $db->prepare($activityQuery);
        
        $activityResult = $stmt->execute([
            ':order_id' => $data['orderId'],
            ':user_id' => $_SESSION['user_id'],
            ':action_type' => 'status_change',
            ':details' => json_encode([
                'old_status' => $currentStatus,
                'new_status' => $data['status']
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
        echo json_encode(['success' => false, 'message' => 'Erro ao atualizar status']);
    }
    
} catch (Exception $e) {
    if (isset($db)) {
        $db->rollBack();
    }
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}