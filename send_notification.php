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
    
    // Insere a notificação
    $query = "INSERT INTO notifications (type, from_user_id, created_at, viewed) 
              VALUES (?, ?, NOW(), 0)";
    $stmt = $db->prepare($query);
    $result = $stmt->execute([$data['sector'], $_SESSION['user_id']]);
    
    if ($result) {
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
?>