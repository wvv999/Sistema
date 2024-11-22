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
            line-height: 1.3;
            margin: 0;
            padding: 0;
        }

        .container {
            width: 21cm;
            margin: 0 auto;
            height: 14.85cm;
            max-height: 14.85cm;
            overflow: hidden;
            transform: scale(1);
            transform-origin: top center;
            position: relative;
            box-sizing: border-box;
            padding: 5px 10px;
        }

        .header {
            display: grid;
            grid-template-columns: 1fr auto;
            align-items: center;
            margin-bottom: 8px;
            border-bottom: 1px solid #000;
            padding-bottom: 5px;
        }

        .header-left {
            text-align: left;
        }

        .header-right {
            text-align: right;
            font-size: 14px;
            font-weight: bold;
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            gap: 3px;
        }

        .delivery-date {
            font-size: 11px;
            font-weight: normal;
        }

        .company-info {
            font-size: 11px;
            margin: 0;
            line-height: 1.1;
        }

        .section {
            margin-bottom: 8px;
        }

        .grid {
            display: grid;
            gap: 5px;
            margin-bottom: 5px;
        }

        .field {
            margin-bottom: 5px;
        }

        .field-label {
            font-weight: bold;
            font-size: 11px;
            margin-bottom: 1px;
        }

        .field-value {
            border: 1px solid #ccc;
            padding: 2px 4px;
            min-height: 14px;
            margin-top: 1px;
            font-size: 11px;
            position: relative;
        }

        .signatures {
            margin-top: 25px;
            text-align: center;
        }

        .signature-line {
            border-top: 1px solid #000;
            padding-top: 2px;
            text-align: center;
            font-size: 11px;
            width: 50%;
            margin: 0 auto;
            margin-top: 5px;
        }

        .brush-script {
            font-family: "Brush Script MT", "Brush Script Std", cursive;
            font-size: 28px;
            margin: 0;
            line-height: 1;
            display: inline-block;
            border-bottom: 2px solid #000;
            padding-bottom: 0;
        }

        .title-container {
            margin-bottom: 3px;
        }

        .info-row {
            display: grid;
            grid-template-columns: 2fr 2fr 3fr;
            gap: 5px;
            margin-bottom: 5px;
        }

        .reported-issue {
            height: 86px;
            position: relative;
            /* padding-right: 90px; */
        }

        .pattern-box {
            border: 1px solid #ccc;
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 3px;
            width: 80px;
            aspect-ratio: 1;
            background-color: #f9f9f9;
            position: absolute;
            right: 3px;
            top: 3px;
        }

        .pattern-circle {
            width: 100%;
            aspect-ratio: 1;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .pattern-dot {
            width: 4px;
            height: 4px;
            border: 2px solid #666;
            border-radius: 50%;
        }

        .disclaimer {
            font-size: 9px;
            margin: 10px 0;
            text-align: justify;
            line-height: 1.2;
            margin-bottom: 15px;
        }

        .cut-line {
            width: 100%;
            height: 0;
            border-bottom: 1px dashed #999;
            position: absolute;
            bottom: 0;
            left: 0;
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
                padding: 5px 10px;
                page-break-after: always;
                height: 148.5mm;
            }

            @page {
                size: A4;
                margin: 0;
            }
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
                <div>OS Nº <?php echo str_pad($order['id'], 6, '0', STR_PAD_LEFT); ?></div>
                <div class="delivery-date">Previsão de Entrega: <?php echo $delivery_date; ?></div>
            </div>
        </div>

        <div class="section avoid-break">
            <div class="grid" style="grid-template-columns: 50% 25% 25%;">
                <div class="field">
                    <div class="field-label">Cliente:</div>
                    <div class="field-value"><?php echo htmlspecialchars($order['client_name']); ?></div>
                </div>
                <div class="field">
                    <div class="field-label">Celular:</div>
                    <div class="field-value"><?php echo htmlspecialchars($order['phone1']); ?></div>
                </div>
                <div class="field">
                    <div class="field-label">Data de Emissão:</div>
                    <div class="field-value"><?php echo date('d/m/Y'); ?></div>
                </div>
            </div>
            
            <div class="info-row">
                <div class="field">
                    <div class="field-label">Modelo do Aparelho:</div>
                    <div class="field-value"><?php echo nl2br(htmlspecialchars($order['device_model'])); ?></div>
                </div>
                <div class="field">
                    <div class="field-label">Acessórios:</div>
                    <div class="field-value"><?php echo nl2br(htmlspecialchars($order['accessories'])); ?></div>
                </div>
                <div class="field">
                    <div class="field-label">Senha:</div>
                    <div class="field-value"><?php echo htmlspecialchars($order['device_password']); ?></div>
                </div>
            </div>
            
            <div class="field">
                <div class="field-label">Defeito Reclamado:</div>
                <div class="field-value reported-issue">
                    <?php echo nl2br(htmlspecialchars($order['reported_issue'])); ?>
                    <div class="pattern-box">
                        <div class="pattern-circle">
                            <div class="pattern-dot"></div>
                        </div>
                        <div class="pattern-circle">
                            <div class="pattern-dot"></div>
                        </div>
                        <div class="pattern-circle">
                            <div class="pattern-dot"></div>
                        </div>
                        <div class="pattern-circle">
                            <div class="pattern-dot"></div>
                        </div>
                        <div class="pattern-circle">
                            <div class="pattern-dot"></div>
                        </div>
                        <div class="pattern-circle">
                            <div class="pattern-dot"></div>
                        </div>
                        <div class="pattern-circle">
                            <div class="pattern-dot"></div>
                        </div>
                        <div class="pattern-circle">
                            <div class="pattern-dot"></div>
                        </div>
                        <div class="pattern-circle">
                            <div class="pattern-dot"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="disclaimer">
            A empresa da garantia de 90 dias para mão de obra e peça usada no conserto, a garantia só é valida para defeito na peça trocada, sendo que por mau uso o cliente perde a garantia do mesmo. Aparelhos molhados não terão garantia.
            Não nos responsabilizamos por dados contidos nos cartões de memória, chip e no aparelho. a constatação de rompimento do lacre invalidará a garantia. A permanencia do aparelho por mais de 30 dias após a aprovação, poderá sofrer reajuste do preço sem aviso prévio e a partir de 90 dias sem a procura do proprietário será considerada abandono do mesmo, não cabendo reclamação ou indenização. A procedência do aparelho é de responsabilidade do declarante. O aparelho só será entregue mediante esta ordem de serviço.
        </div>

        <div class="signatures avoid-break">
            <div class="signature-line">
                Assinatura do Cliente
            </div>
        </div>

        <div class="cut-line"></div>
    </div>
</body>
</html>