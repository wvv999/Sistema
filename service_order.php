<?php
// ... (resto do código permanece igual até a parte do processamento do formulário)

// Processa o formulário quando enviado
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        $client_id = $_POST['client_id'];
        $phone1 = $_POST['phone1'];
        $phone2 = $_POST['phone2'];
        $delivery_date = $_POST['delivery_date'];
        $reported_issue = $_POST['reported_issue'];
        $accessories = $_POST['accessories'];
        $device_password = $_POST['device_password'];
        $pattern_password = $_POST['pattern_password'];

        // Primeiro, encontra o menor ID disponível
        $stmt = $db->query("
            SELECT t1.id + 1 AS next_id
            FROM service_orders t1
            LEFT JOIN service_orders t2 ON t1.id + 1 = t2.id
            WHERE t2.id IS NULL
            UNION
            SELECT 1
            ORDER BY next_id
            LIMIT 1
        ");
        
        $next_id = $stmt->fetch(PDO::FETCH_ASSOC)['next_id'];

        // Insere a nova ordem usando o ID encontrado
        $stmt = $db->prepare("
            INSERT INTO service_orders (id, client_id, phone1, phone2, delivery_date, 
                                      reported_issue, accessories, device_password, pattern_password) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        if ($stmt->execute([$next_id, $client_id, $phone1, $phone2, $delivery_date, 
                           $reported_issue, $accessories, $device_password, $pattern_password])) {
            $success = "Ordem de serviço #" . $next_id . " criada com sucesso!";
        } else {
            throw new Exception("Erro ao criar ordem de serviço.");
        }
    } catch(Exception $e) {
        $error = $e->getMessage();
    }
}