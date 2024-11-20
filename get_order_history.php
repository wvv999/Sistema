<?php
header('Content-Type: application/json');
require_once 'config.php';

try {
    $database = new Database();
    $db = $database->getConnection();
    
    $data = json_decode(file_get_contents('php://input'), true);
    $orderId = $data['orderId'];
    
    // Buscar histórico de status
    $statusQuery = "SELECT 
        a.id,
        a.action_type,
        a.details,
        u.username,
        DATE_FORMAT(a.created_at, '%d/%m/%Y %H:%i') as formatted_date
    FROM activities a
    JOIN users u ON a.user_id = u.id
    WHERE a.order_id = :orderId 
    AND a.action_type = 'status_change'
    ORDER BY a.created_at DESC";
    
    $stmt = $db->prepare($statusQuery);
    $stmt->execute([':orderId' => $orderId]);
    $statusHistory = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Buscar histórico de notas
    $notesQuery = "SELECT 
        tn.id,
        tn.note,
        u.username,
        DATE_FORMAT(tn.created_at, '%d/%m/%Y %H:%i') as formatted_date
    FROM technical_notes tn
    JOIN users u ON tn.user_id = u.id
    WHERE tn.order_id = :orderId
    ORDER BY tn.created_at DESC";
    
    $stmt = $db->prepare($notesQuery);
    $stmt->execute([':orderId' => $orderId]);
    $notesHistory = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'statusHistory' => $statusHistory,
        'notesHistory' => $notesHistory
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}