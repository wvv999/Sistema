<?php
// get_stats.php
header('Content-Type: application/json');

require_once 'config.php';

try {
    $gestao = new GestaoStats();
    $stats = $gestao->getOrderStats();
    
    echo json_encode([
        'success' => true,
        'stats' => $stats
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}