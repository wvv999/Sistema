// search_order.php
<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    die(json_encode(['error' => 'Unauthorized']));
}

$database = new Database();
$db = $database->getConnection();

$search = $_GET['search'] ?? '';

try {
    // Query que busca tanto por ID quanto por nome do cliente
    $query = "SELECT so.*, c.name as client_name, c.phone1, c.phone2 
              FROM service_orders so 
              INNER JOIN clients c ON so.client_id = c.id 
              WHERE so.id = :search 
              OR c.name LIKE :name_search 
              ORDER BY so.id DESC 
              LIMIT 10";

    $stmt = $db->prepare($query);
    $stmt->execute([
        ':search' => $search,
        ':name_search' => "%$search%"
    ]);

    $results = $stmt->fetchAll();
    
    if (count($results) > 0) {
        echo json_encode(['success' => true, 'data' => $results]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Nenhuma ordem encontrada']);
    }

} catch(Exception $e) {
    echo json_encode(['error' => 'Erro ao buscar ordem: ' . $e->getMessage()]);
}