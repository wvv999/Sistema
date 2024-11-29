<?php
session_start();
require_once 'config.php';
header('Content-Type: application/json');

error_log("Requisição recebida em send_notification.php");

if (!isset($_SESSION['user_id'])) {
    error_log("Usuário não autenticado");
    echo json_encode(['success' => false, 'message' => 'Não autorizado']);
    exit;
}

try {
    $data = json_decode(file_get_contents('php://input'), true);
    error_log("Dados recebidos: " . print_r($data, true));

    if (!isset($data['sector'])) {
        throw new Exception('Setor não especificado');
    }

    $database = new Database();
    $db = $database->getConnection();
    
    // Insere a notificação
    $query = "INSERT INTO notifications (type, from_user_id, created_at) 
              VALUES (?, ?, NOW())";
    $stmt = $db->prepare($query);
    $result = $stmt->execute([$data['sector'], $_SESSION['user_id']]);
    
    if ($result) {
        error_log("Notificação criada com sucesso");
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