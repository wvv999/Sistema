<?php
session_start();
require_once 'config.php';

header('Content-Type: application/json');

// Verificar autenticação
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    die(json_encode(['error' => 'Unauthorized']));
}

// Verificar se o parâmetro de busca existe
if (!isset($_GET['search']) || empty(trim($_GET['search']))) {
    http_response_code(400);
    die(json_encode(['error' => 'Parâmetro de busca não fornecido']));
}

$database = new Database();
$db = $database->getConnection();

$search = trim($_GET['search']);

try {
    // Preparar a query com INNER JOIN para pegar dados do cliente
    $query = "SELECT 
                so.id,
                so.delivery_date,
                so.reported_issue,
                so.accessories,
                so.device_password,
                so.pattern_password,
                c.name as client_name,
                c.phone1,
                c.phone2 
              FROM service_orders so 
              INNER JOIN clients c ON so.client_id = c.id 
              WHERE (so.id = :search_id OR c.name LIKE :name_search)
              ORDER BY so.id DESC 
              LIMIT 10";

    $stmt = $db->prepare($query);
    
    // Executar com ambos os parâmetros
    $stmt->execute([
        ':search_id' => is_numeric($search) ? $search : -1, // -1 para garantir que não encontre nada se não for número
        ':name_search' => "%$search%"
    ]);

    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if ($results && count($results) > 0) {
        echo json_encode([
            'success' => true, 
            'data' => $results
        ]);
    } else {
        echo json_encode([
            'success' => false, 
            'message' => 'Nenhuma ordem encontrada'
        ]);
    }

} catch(PDOException $e) {
    http_response_code(500);
    error_log("Database error: " . $e->getMessage());
    echo json_encode([
        'error' => 'Erro ao buscar ordem',
        'message' => 'Ocorreu um erro ao processar sua busca. Tente novamente.'
    ]);
} catch(Exception $e) {
    http_response_code(500);
    error_log("General error: " . $e->getMessage());
    echo json_encode([
        'error' => 'Erro interno',
        'message' => 'Ocorreu um erro ao processar sua busca. Tente novamente.'
    ]);
}