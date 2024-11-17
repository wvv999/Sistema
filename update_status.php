<?php
header('Content-Type: application/json');
require_once 'config.php';

$data = json_decode(file_get_contents('php://input'), true);
$orderId = $data['orderId'];
$newStatus = $data['status'];

$database = new Database();
$db = $database->getConnection();

try {
    $query = "UPDATE service_orders SET status = :status WHERE id = :id";
    $stmt = $db->prepare($query);
    $stmt->execute([':status' => $newStatus, ':id' => $orderId]);

    echo json_encode(['success' => true]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
