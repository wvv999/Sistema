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
    
    // Buscar o setor atual do usuário
    $sectorQuery = "SELECT current_sector FROM users WHERE id = ?";
    $stmt = $db->prepare($sectorQuery);
    $stmt->execute([$_SESSION['user_id']]);
    $currentSector = $stmt->fetchColumn();
    
    // Buscar o status atual de autorização
    $currentStatusQuery = "SELECT auth_status FROM service_orders WHERE id = :id";
    $stmt = $db->prepare($currentStatusQuery);
    $stmt->execute([':id' => $data['orderId']]);
    $currentStatus = $stmt->fetchColumn();
    
    // Iniciar transação
    $db->beginTransaction();
    
    // Atualizar o status de autorização
    $query = "UPDATE service_orders SET auth_status = :auth_status WHERE id = :id";
    $stmt = $db->prepare($query);
    
    $result = $stmt->execute([
        ':auth_status' => $data['authStatus'],
        ':id' => $data['orderId']
    ]);
    
    if ($result) {
        // Registrar a mudança na tabela activities
        $activityQuery = "INSERT INTO activities (order_id, user_id, action_type, details, created_at) 
                         VALUES (:order_id, :user_id, 'auth_status_change', :details, NOW())";
        $stmt = $db->prepare($activityQuery);
        
        $activityResult = $stmt->execute([
            ':order_id' => $data['orderId'],
            ':user_id' => $_SESSION['user_id'],
            ':details' => json_encode([
                'old_status' => $currentStatus,
                'new_status' => $data['authStatus']
            ])
        ]);

        // Criar notificação baseada no novo status
        if ($data['authStatus'] === 'Solicitado' || $data['authStatus'] === 'Autorizado') {
            // Define o tipo de notificação baseado no status
            $notificationType = $data['authStatus'] === 'Solicitado' ? 'auth_status' : 'auth_approved';
            
            // Insere a notificação
            $notificationQuery = "INSERT INTO notifications (type, order_id, from_user_id, created_at, viewed) 
                                VALUES (:type, :order_id, :user_id, NOW(), 0)";
            $stmt = $db->prepare($notificationQuery);
            $notificationResult = $stmt->execute([
                ':type' => $notificationType,
                ':order_id' => $data['orderId'],
                ':user_id' => $_SESSION['user_id']
            ]);
            
            if (!$notificationResult) {
                throw new Exception('Erro ao criar notificação');
            }
        }
        
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
    error_log('Erro ao atualizar status de autorização: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}