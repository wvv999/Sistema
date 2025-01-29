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
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
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

        .container {
            max-width: 90vw;
            padding: 10px;
            display:flex;
            gap:5px;
            justify-content: space-around;
        }

        

        .left,
        .right {
            padding: 20px;
            height: 90vh;
            width: 25%;
            max-width: 25%;
            min-width: 25%;
            background-color: #fff;
            border-radius: 15px;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
            display: flex;
            flex: 1 1 25%;
            flex-direction: column;
            gap: 20px;
        }

        .mid{
            height: 90vh;
            width: 45%;
            max-width: 45%;
            min-width: 45%;
            padding: 20px;
            flex: 1 1 40%;
            background-color: #fff;
            border-radius: 15px;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .info-label {
            max-width: fit-content;
            font-weight: 600;
            color: #6c757d;
            margin-bottom: 5px;
            font-size: 0.9rem;
            display: flex;
            gap: 6px;
        }

        .info-value {
            max-width: 300px;
            min-height: max-content;
            text-wrap: wrap;
            color: #333;
            margin-bottom: 15px;
            font-size: 1rem;
            border-radius: var(--border-radius);
            text-align: left;
            
        }

        .item {
            width: 100%;
            height: fit-content;
            background-color: #f5f6fa;
            border-radius: 10px;
            padding: 8px;
        }

        /* PAINEL DIREIO */
        /* PAINEL DIREIO */
        /* PAINEL DIREIO */

        .side-panel {
            width: 300px;
            height: 495px;
            padding-right: 20px;
            display: flex;
            flex-direction: column;
            gap: 24px;
            justify-content: space-between;
        }
        .content-right {
            width: 300px;
            height: 495px;
        }
        .menu-section {
            display: flex;
            flex-direction: column;
            gap: 12px;
            padding: 16px;
            border: 1px solid rgba(0,0,0,0.1);
            border-radius: var(--border-radius);
            background-color: #fff; 
        }
        .action-button {
            width: 100%;
            padding: 12px;
            border-radius: var(--border-radius);
            border: 1px solid #dee2e6;
            background: white;
            display: flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
            font-weight: 500;
            position: relative;
            overflow: hidden;
        }
        .action-button::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            height: 100%;
            width: 0;
            background: rgba(0,0,0,0.05);  
        }
        .action-button:hover::before {
            width: 100%;
            transition: var(--transition);
        }

        .action-button:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow);
            transition: var(--transition);
        }
        /* Status button styles */
        .status-button {
            font-weight: 600;
            letter-spacing: 0.5px;
            justify-content: center;
        }

        /* Status button styles */
        .status-nao-iniciada { background-color: #e74c3c; color: white; }
        .status-em-andamento { background-color: #f39c12; color: white; }
        .status-concluida { background-color: #27ae60; color: white; }
        .status-pronto-e-avisado { background-color: #3498db; color: white; }
        .status-entregue { background-color: #2c3e50; color: white; }

        /* Auth button styles */
        .auth-button {
            font-weight: 600;
            letter-spacing: 0.5px;
            justify-content: center;
        }

        .auth-autorizacao { background-color: #6c757d; color: white; }
        .auth-solicitado { background-color: var(--warning-color); color: black; }
        .auth-autorizado { background-color: var(--success-color); color: white; }
        /* PAINEL DIREIO */
        /* PAINEL DIREIO */
        /* PAINEL DIREIO */
    </style>
</head>






<body>


    <div class="container text-center">

        

            <div class="left">

                <h4 class="mb-3">
                    Ordem número: <?php echo str_pad($order['id'], STR_PAD_RIGHT); ?>
                </h4>

                <div class="item">
                    <div class="info-label">
                        <i class="bi bi-person"></i> Nome do Cliente
                    </div>
                    <div class="info-value">
                        <?php echo htmlspecialchars($order['client_name']); ?>
                    </div>
                </div>

                <div class="item">
                    <div class="info-label">
                        <i class="bi bi-laptop"></i> Modelo
                    </div>
                    <div class="info-value">
                        <?php echo htmlspecialchars($order['device_model']); ?>
                    </div>
                </div>

                <div class="item">
                    <div class="info-label">
                        <i class="bi bi-key"></i> Senha do Dispositivo
                    </div>
                    <div class="info-value">
                        <?php echo htmlspecialchars($order['device_password'] ?? '-'); ?>
                    </div>
                </div>

                <div class="item">
                    <div class="info-label">
                        <i class="bi bi-exclamation-triangle"></i> Defeito Reclamado
                    </div>
                    <div class="info-value">
                        <?php echo htmlspecialchars($order['reported_issue']); ?>
                    </div>
                </div>




                <ul>
                    <li>Contatos <?php echo htmlspecialchars($order['phone1']); ?><br><?php echo htmlspecialchars($order['phone2'] ?? '-'); ?></li>
                    <li>Data de Abertura <?php echo date('d/m/Y', strtotime($order['created_at'])); ?></li>
                    <li>Data de Entrega <?php echo date('d/m/Y', strtotime($order['delivery_date'])); ?></li>
                </ul>
            </div>


            <div class="mid">
                <ul>
                    <li>Laudo Técnico</li>
                </ul>
                <!-- <div class="technical-report">
                        <div class="technical-notes">
                            <textarea id="technicalNotes" rows="6" readonly><?php echo $textareaContent; ?></textarea>
                            
                            <div class="add-note-form">
                                <div class="input-group">
                                    <textarea id="newNote" 
                                            rows="1"
                                            placeholder="Digite sua nota técnica..."
                                            data-autoresize></textarea>
                                    <button onclick="addNote()" class="btn btn-primary"><i class="bi bi-plus-circle"></i> Adicionar</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div> -->
            </div>



            <div class="right">

                <div class="item">
                    <div class="info-label">
                            <i class="bi bi-telephone"></i> Contatos
                        </div>
                        <div class="info-value"><?php echo htmlspecialchars($order['phone1  ']);  ?> <?php echo htmlspecialchars($order['  phone1'] ?? '-'); ?></div>
                        
                </div>

                <ul>
                    <li>Não-iniciada/Entregue/Concluída</li>
                    <li>Autorizado/NA</li>
                    <br><br>
                    <li>Histórico</li>
                    <li>Imprimir</li>
                    <li>Salvar e sair</li>
                </ul>
                <div class="side-panel">
                    <!-- Status e Ações -->
                    <div class="menu-section">
                        <div id="statusButton" 
                            class="action-button status-button"
                            data-status="<?php echo $order['status']; ?>"
                            data-order-id="<?php echo $order['id']; ?>"
                            data-bs-toggle="tooltip"
                            title="">
                            <i class="bi bi-gear"></i>
                            <span><?php echo $order['status']; ?></span>
                        </div>

                        <div id="authButton" 
                            class="action-button auth-button auth-autorizacao"
                            data-auth-status="Autorização"
                            data-order-id="<?php echo $order['id']; ?>"
                            data-bs-toggle="tooltip"
                            title="Clique para alterar a autorização">
                            <i class="bi bi-check-circle"></i>
                            <span>Autorização</span>
                        </div>

                    </div>

                    <!-- Ações da OS -->
                    <div class="menu-section">
                        <button class="action-button" data-bs-toggle="tooltip" title="Ver histórico completo">
                            <i class="bi bi-clock-history"></i>
                            <span>Histórico</span>
                        </button>
                        <button class="action-button" data-bs-toggle="tooltip" title="Imprimir ordem de serviço" 
                                onclick="window.open('print_service_order.php?id=<?php echo $order['id']; ?>', '_blank')">
                            <i class="bi bi-printer"></i>
                            <span>Imprimir</span>
                        </button>
                        <button class="action-button" style="background-color:var(--success-color); color: white" onclick="javascript:history.go(-1)">
                            <i class="bi bi-box-arrow-right"></i>
                        
                            <span>Salvar e Sair</span>
                        </button>
                    </div>
                </div>
            </div>
        
    </div>


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>

</html>