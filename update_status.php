<?php
session_start();
require_once 'config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    die(json_encode(['success' => false, 'message' => 'Não autorizado']));
}

// Pega o JSON do corpo da requisição
$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['orderId']) || !isset($data['status'])) {
    http_response_code(400);
    die(json_encode(['success' => false, 'message' => 'Dados inválidos']));
}

$allowedStatuses = ['Não iniciada', 'Em andamento', 'Concluída'];
if (!in_array($data['status'], $allowedStatuses)) {
    http_response_code(400);
    die(json_encode(['success' => false, 'message' => 'Status inválido']));
}

try {
    $database = new Database();
    $db = $database->getConnection();

    $query = "UPDATE service_orders SET status = :status WHERE id = :id";
    $stmt = $db->prepare($query);
    
    $success = $stmt->execute([
        ':status' => $data['status'],
        ':id' => $data['orderId']
    ]);

    if ($success) {
        echo json_encode(['success' => true]);
    } else {
        throw new Exception('Erro ao atualizar status');
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao atualizar status: ' . $e->getMessage()
    ]);
}