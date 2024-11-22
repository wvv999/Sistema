<?php
session_start();
require_once 'config.php';

if(!isset($_SESSION['user_id']) || !isset($_GET['id'])) {
    header("Location: index.php");
    exit;
}

$database = new Database();
$db = $database->getConnection();

try {
    // Busca os dados da ordem de serviço
    $query = "SELECT so.*, c.name as client_name, c.phone1, c.phone2 
              FROM service_orders so 
              JOIN clients c ON so.client_id = c.id 
              WHERE so.id = ?";
    $stmt = $db->prepare($query);
    $stmt->execute([$_GET['id']]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$order) {
        throw new Exception("Ordem de serviço não encontrada");
    }
} catch(Exception $e) {
    die("Erro: " . $e->getMessage());
}

// Formata a data para o padrão brasileiro
$delivery_date = date("d/m/Y", strtotime($order['delivery_date']));
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Ordem de Serviço #<?php echo $order['id']; ?></title>
    <link href="https://fonts.cdnjs.com/css2?family=Style+Script" rel="stylesheet">
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.4;
            margin: 0;
            padding: 10px;
        }

        .container {
            max-width: 21cm;
            margin: 0 auto;
            height: 14.85cm;
        }

        .header {
            display: grid;
            grid-template-columns: 1fr auto;
            align-items: center;
            margin-bottom: 15px;
            border-bottom: 1px solid #000;
            padding-bottom: 10px;
        }

        .header-left {
            text-align: left;
        }

        .header-right {
            text-align: right;
            font-size: 16px;
            font-weight: bold;
        }

        .company-info {
            font-size: 12px;
            margin: 0;
            line-height: 1.2;
        }

        .section {
            margin-bottom: 15px;
        }

        .grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            margin-bottom: 10px;
        }

        .field {
            margin-bottom: 8px;
        }

        .field-label {
            font-weight: bold;
            font-size: 12px;
        }

        .field-value {
            border: 1px solid #ccc;
            padding: 3px 5px;
            min-height: 16px;
            margin-top: 2px;
            font-size: 12px;
        }

        .signatures {
            margin-top: 20px;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .signature-line {
            border-top: 1px solid #000;
            padding-top: 3px;
            text-align: center;
            font-size: 12px;
        }

        .date-line {
            text-align: center;
            margin-top: 15px;
            grid-column: 1 / -1;
            font-size: 12px;
        }

        @media print {
            body {
                padding: 0;
                margin: 0;
            }

            .no-print {
                display: none;
            }

            .container {
                padding: 10px;
                box-sizing: border-box;
            }

            @page {
                size: A4;
                margin: 0;
            }
        }

        .brush-script {
            font-family: "Brush Script MT", "Brush Script Std", cursive;
            font-size: 32px;
            margin: 0;
            line-height: 1;
            display: inline-block;
            border-bottom: 2px solid #000;
            padding-bottom: 0;
        }

        .contacts-value {
            border: 1px solid #ccc;
            padding: 3px 5px;
            min-height: 16px;
            font-size: 12px;
            margin-top: 2px;
        }

        .title-container {
            margin-bottom: 5px;
        }

        /* Estilos para o campo de senha e padrão */
        .password-container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            margin-bottom: 8px;
        }

        .pattern-box {
            border: 1px solid #ccc;
            padding: 5px;
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 5px;
            aspect-ratio: 1;
            width: 100%;
            max-width: 100px;
            margin-left: auto;
            background-color: #f9f9f9;
        }

        .pattern-dot {
            aspect-ratio: 1;
            border: 2px solid #666;
            border-radius: 50%;
            margin: auto;
            width: 80%;
            background-color: white;
        }

        .half-width {
            width: calc(100% - 10px);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="no-print" style="text-align: right; margin-bottom: 10px;">
            <button onclick="window.print()">Imprimir</button>
            <button onclick="window.history.back()">Voltar</button>
        </div>

        <div class="header">
            <div class="header-left">
                <div class="title-container">
                    <h1 class="brush-script">Tele Dil</h1>
                </div>
                <div class="company-info">Assistência Técnica</div>
                <div class="company-info">Rua José de Quadros, 161</div>
                <div class="company-info">Telefone: (44) 3561-5145</div>
            </div>
            <div class="header-right">
                OS Nº <?php echo str_pad($order['id'], 6, '0', STR_PAD_LEFT); ?>
            </div>
        </div>

        <div class="section avoid-break">
            <div class="grid">
                <div class="field">
                    <div class="field-label">Cliente:</div>
                    <div class="field-value"><?php echo htmlspecialchars($order['client_name']); ?></div>
                </div>
                <div class="field">
                    <div class="field-label">Data de Entrega:</div>
                    <div class="field-value"><?php echo $delivery_date; ?></div>
                </div>
            </div>
            
            <div class="field">
                <div class="field-label">Contatos:</div>
                <div class="contacts-value">
                    <?php 
                    $contacts = array_filter([
                        htmlspecialchars($order['phone1']),
                        htmlspecialchars($order['phone2'])
                    ]);
                    echo implode(' / ', $contacts);
                    ?>
                </div>
            </div>

            <div class="grid">
                <div class="field">
                    <div class="field-label">Modelo do Aparelho:</div>
                    <div class="field-value"><?php echo nl2br(htmlspecialchars($order['device_model'])); ?></div>
                </div>
                <div class="field">
                    <div class="field-label">Acessórios:</div>
                    <div class="field-value"><?php echo nl2br(htmlspecialchars($order['accessories'])); ?></div>
                </div>
            </div>
            
            <div class="field">
                <div class="field-label">Defeito Reclamado:</div>
                <div class="field-value"><?php echo nl2br(htmlspecialchars($order['reported_issue'])); ?></div>
            </div>

            <div class="field">
                <div class="field-label">Senhas:</div>
                <div class="password-container">
                    <div class="field-value half-width">
                        <?php echo htmlspecialchars($order['device_password']); ?>
                    </div>
                    <div class="pattern-box">
                        <?php for($i = 0; $i < 9; $i++): ?>
                            <div class="pattern-dot"></div>
                        <?php endfor; ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="signatures avoid-break">
            <div class="signature-line">
                Assinatura do Cliente
            </div>
            <div class="signature-line">
                Assinatura do Funcionário
            </div>
            <div class="date-line">
                Data: ____/____/______
            </div>
        </div>
    </div>
</body>
</html>