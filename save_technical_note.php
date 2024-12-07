<?php
session_start();
require_once 'config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
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
    
    $query = "INSERT INTO technical_notes (order_id, user_id, note) VALUES (:order_id, :user_id, :note)";
    $stmt = $db->prepare($query);
    
    $success = $stmt->execute([
        ':order_id' => $data['orderId'],
        ':user_id' => $_SESSION['user_id'],
        ':note' => $data['note']
    ]);

    if ($success) {
        // Buscar nome do usuário
        $userQuery = "SELECT username FROM users WHERE id = :user_id";
        $stmt = $db->prepare($userQuery);
        $stmt->execute([':user_id' => $_SESSION['user_id']]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        echo json_encode([
            'success' => true, 
            'message' => 'Nota salva com sucesso',
            'username' => $user['username']
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Erro ao salvar nota']);
    }
} catch(Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Erro ao salvar nota: ' . $e->getMessage()]);
}