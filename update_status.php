<?php
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
    
    // Atualizar o status da ordem
    $query = "UPDATE service_orders SET status = :status WHERE id = :id";
    $stmt = $db->prepare($query);
    
    $result = $stmt->execute([
        ':status' => $data['status'],
        ':id' => $data['orderId']
    ]);
    
    if ($result) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Erro ao atualizar status']);
    }
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>