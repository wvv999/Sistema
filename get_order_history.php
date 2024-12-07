<?php
session_start();
require_once 'config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Usuário não autenticado']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['orderId'])) {
    echo json_encode(['success' => false, 'message' => 'ID da ordem não fornecido']);
    exit;
}

try {
    $database = new Database();
    $db = $database->getConnection();
    
    // Buscar histórico de status
    $statusQuery = "SELECT sh.*, u.username,
                    DATE_FORMAT(sh.created_at, '%d/%m/%y %H:%i') as formatted_date
                    FROM status_history sh
                    JOIN users u ON sh.user_id = u.id
                    WHERE sh.order_id = :order_id
                    ORDER BY sh.created_at DESC";
                    
    $stmt = $db->prepare($statusQuery);
    $stmt->execute([':order_id' => $data['orderId']]);
    $statusHistory = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Buscar histórico de notas
    $notesQuery = "SELECT tn.*, u.username,
                   DATE_FORMAT(tn.created_at, '%d/%m/%y %H:%i') as formatted_date
                   FROM technical_notes tn
                   JOIN users u ON tn.user_id = u.id
                   WHERE tn.order_id = :order_id
                   ORDER BY tn.created_at DESC";
                   
    $stmt = $db->prepare($notesQuery);
    $stmt->execute([':order_id' => $data['orderId']]);
    $notesHistory = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'statusHistory' => $statusHistory,
        'notesHistory' => $notesHistory
    ]);
    
} catch(Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Erro ao carregar histórico: ' . $e->getMessage()]);
}