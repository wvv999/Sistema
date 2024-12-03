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
    
    // Atualiza o setor do usuário no banco de dados
    $query = "UPDATE users SET current_sector = ? WHERE id = ?";
    $stmt = $db->prepare($query);
    $result = $stmt->execute([$data['sector'], $_SESSION['user_id']]);
    
    if ($result) {
    $_SESSION['current_sector'] = $data['sector'];
    echo json_encode([
        'success' => true,
        'current_sector' => $data['sector']
    ]);
} else {
    throw new Exception('Erro ao atualizar setor');
}
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false, 
        'message' => $e->getMessage()
    ]);
}