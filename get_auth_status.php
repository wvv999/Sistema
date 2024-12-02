<?php
session_start();
header('Content-Type: application/json');
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Não autorizado']);
    exit;
}

try {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['orderId'])) {
        throw new Exception('ID da ordem não fornecido');
    }

    $database = new Database();
    $db = $database->getConnection();
    
    // Busca o status de autorização atual
    $query = "SELECT auth_status FROM service_orders WHERE id = ?";
    $stmt = $db->prepare($query);
    $stmt->execute([$data['orderId']]);
    
    $authStatus = $stmt->fetchColumn();
    
    // Se não houver status definido, retorna o status padrão "Autorização"
    if (!$authStatus) {
        $authStatus = 'Autorização';
    }
    
    echo json_encode([
        'success' => true,
        'authStatus' => $authStatus
    ]);
    
} catch (Exception $e) {
    error_log('Erro em get_auth_status.php: ' . $e->getMessage());
    echo json_encode([
        'success' => false, 
        'message' => $e->getMessage()
    ]);
}