<?php
session_start();
require_once 'config.php';


if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

if (!isset($_GET['id'])) {
    header("Location: dashboard.php");
    exit;
}

$database = new Database();
$db = $database->getConnection();

try {
    // Primeiro, buscar os dados da ordem de serviço
    $query = "SELECT 
            so.*,
            c.name as client_name,
            c.phone1,
            c.phone2,
            so.device_password,
            COALESCE(so.status, 'Não iniciada') as status
          FROM service_orders so 
          INNER JOIN clients c ON so.client_id = c.id 
          WHERE so.id = :id";

    $stmt = $db->prepare($query);
    $stmt->execute([':id' => $_GET['id']]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$order) {
        header("Location: dashboard.php");
        exit;
    }

    // Depois, buscar as notas técnicas
    $notesQuery = "SELECT tn.*, u.username, 
                   DATE_FORMAT(tn.created_at, '%d/%m/%y') as formatted_date,
                   DATE_FORMAT(tn.created_at, '%Y-%m-%d') as note_date
                   FROM technical_notes tn 
                   JOIN users u ON tn.user_id = u.id 
                   WHERE tn.order_id = :order_id 
                   ORDER BY tn.created_at ASC";

    $stmt = $db->prepare($notesQuery);
    $stmt->execute([':order_id' => $_GET['id']]);
    $notes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $textareaContent = '';
    $currentDate = '';

    foreach ($notes as $note) {
        if ($currentDate != $note['note_date']) {
            $textareaContent .= "\n " . $note['formatted_date'] . " \n\n";
            $currentDate = $note['note_date'];
        }
        $textareaContent .= "{$note['username']}: {$note['note']}\n";
    }
} catch (Exception $e) {
    header("Location: dashboard.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ordem de Serviço <?php echo $order['id'] ?> </title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <style>
        body {
            background-color: #f5f6fa;
            padding: 20px;
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            margin: 0;
            min-height: 100vh;

        }

        body::-webkit-scrollbar {
            display: none;
        }
        .container{
            max-width: 100vw;
        }
        .row {
            gap: 20px;
        }

        .col,
        .col-6 {
            height: 90vh;
            background-color: #fff;
            border-radius: 15px;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
        }
        .info-label {
            width: fit-content;
            font-weight: 600;
            color: #6c757d;
            margin-bottom: 5px;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .info-value {
            color: #333;
            margin-bottom: 15px;
            font-size: 1rem;
            padding: 8px;
            background: rgba(255, 255, 255, 0.5);
            border-radius: var(--border-radius);
        }
    </style>
</head>






<body>


    <div class="container text-center">
        <div class="row">
            <div class="col">
                <ul>
                    <h4 class="mb-3">
                        Ordem número: <?php echo str_pad($order['id'], STR_PAD_RIGHT); ?>
                    </h4>

                    <div class="info-label">
                        <i class="bi bi-person"></i> Nome do Cliente
                    </div>
                    <div class="info-value">asdasdasdasdasdasda<?php echo htmlspecialchars($order['client_name']); ?></div>



                    <li>Modelo <?php echo htmlspecialchars($order['device_model']); ?></li>
                    <li>Senha <?php echo htmlspecialchars($order['device_password'] ?? '-'); ?></li>
                    <li>Defeito <?php echo htmlspecialchars($order['reported_issue']); ?></li>
                    <li>Contatos <?php echo htmlspecialchars($order['phone1']); ?><br><?php echo htmlspecialchars($order['phone2'] ?? '-'); ?></li>
                    <li>Data de Abertura <?php echo date('d/m/Y', strtotime($order['created_at'])); ?></li>
                    <li>Data de Entrega <?php echo date('d/m/Y', strtotime($order['delivery_date'])); ?></li>
                </ul>
            </div>
            <div class="col-6">
                <ul>
                    <li>Laudo Técnico</li>
                </ul>
            </div>
            <div class="col">
                <ul>
                    <li>Não-iniciada/Entregue/Concluída</li>
                    <li>Autorizado/NA</li>
                    <br><br>
                    <li>Histórico</li>
                    <li>Imprimir</li>
                    <li>Salvar e sair</li>
                </ul>
            </div>
        </div>
    </div>


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>

</html>