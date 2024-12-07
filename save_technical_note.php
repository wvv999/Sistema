<?php
session_start();
header('Content-Type: application/json');
require_once 'config.php';

if(!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Usuário não autenticado']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['orderId']) || !isset($data['note'])) {
    echo json_encode(['success' => false, 'message' => 'Dados incompletos']);
    exit;
}

try {
    $database = new Database();
    $db = $database->getConnection();
    
    $query = "INSERT INTO technical_notes (order_id, user_id, note) 
              VALUES (:order_id, :user_id, :note)";
              
    $stmt = $db->prepare($query);
    $result = $stmt->execute([
        ':order_id' => $data['orderId'],
        ':user_id' => $_SESSION['user_id'],
        ':note' => $data['note']
    ]);
    
    if ($result) {
        // Buscar o username do usuário
        $userQuery = "SELECT username FROM users WHERE id = ?";
        $userStmt = $db->prepare($userQuery);
        $userStmt->execute([$_SESSION['user_id']]);
        $username = $userStmt->fetchColumn();
        
        echo json_encode([
            'success' => true,
            'username' => $username,
            'created_at' => date('d/m/Y H:i')
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Erro ao salvar nota']);
    }
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>