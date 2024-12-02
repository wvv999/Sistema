<?php
session_start();
header('Content-Type: application/json');
require_once 'config.php';

// Log inicial
error_log('Iniciando update_status.php');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método não permitido']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

// Log dos dados recebidos
error_log('Dados recebidos: ' . print_r($data, true));

if (!isset($data['orderId']) || !isset($data['status'])) {
    echo json_encode(['success' => false, 'message' => 'Dados incompletos']);
    exit;
}

try {
    $database = new Database();
    $db = $database->getConnection();
    
    // Inicia transação
    $db->beginTransaction();
    
    // Busca status atual para log
    $currentQuery = "SELECT status FROM service_orders WHERE id = :id";
    $stmt = $db->prepare($currentQuery);
    $stmt->execute([':id' => $data['orderId']]);
    $currentStatus = $stmt->fetchColumn();
    
    // Log do status atual
    error_log('Status atual: ' . $currentStatus);
    
    // Atualiza o status
    $query = "UPDATE service_orders
               SET status = :status,
                  last_modified_by = :user_id,
                  notification_sent = 0,
                  last_modified = NOW()
              WHERE id = :id";
               
    $stmt = $db->prepare($query);
    
    $params = [
        ':status' => $data['status'],
        ':user_id' => $_SESSION['user_id'],
        ':id' => $data['orderId']
    ];
    
    // Log da query e parâmetros
    error_log('Query: ' . $query);
    error_log('Parâmetros: ' . print_r($params, true));
    
    $result = $stmt->execute($params);
    
    if ($result) {
        // Registra a atividade
        $activityQuery = "INSERT INTO activities (order_id, user_id, action_type, details, created_at)
                         VALUES (:order_id, :user_id, :action_type, :details, NOW())";
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
            error_log('Transação completada com sucesso');
            echo json_encode(['success' => true]);
        } else {
            $db->rollBack();
            error_log('Erro ao registrar atividade');
            echo json_encode(['success' => false, 'message' => 'Erro ao registrar atividade']);
        }
    } else {
        $db->rollBack();
        error_log('Erro ao atualizar status');
        echo json_encode(['success' => false, 'message' => 'Erro ao atualizar status']);
    }
    
} catch (Exception $e) {
    if (isset($db)) {
        $db->rollBack();
    }
    error_log('Erro em update_status.php: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}