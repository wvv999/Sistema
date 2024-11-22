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
        /* Estilos gerais */
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 20px;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
        }

        /* Cabeçalho */
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #000;
            padding-bottom: 20px;
        }

        .company-name {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .company-info {
            font-size: 14px;
            margin-bottom: 5px;
        }

        /* Informações da OS */
        .order-number {
            font-size: 20px;
            font-weight: bold;
            margin-bottom: 20px;
            text-align: center;
        }

        .section {
            margin-bottom: 20px;
        }

        .section-title {
            font-weight: bold;
            margin-bottom: 5px;
        }

        .grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 20px;
        }

        .field {
            margin-bottom: 10px;
        }

        .field-label {
            font-weight: bold;
            font-size: 14px;
        }

        .field-value {
            border: 1px solid #ccc;
            padding: 5px;
            min-height: 20px;
            margin-top: 3px;
        }

        /* Seção de assinaturas */
        .signatures {
            margin-top: 50px;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 40px;
        }

        .signature-line {
            border-top: 1px solid #000;
            padding-top: 5px;
            text-align: center;
            font-size: 14px;
        }

        /* Estilos específicos para impressão */
        @media print {
            body {
                padding: 0;
            }

            .no-print {
                display: none;
            }

            /* Força quebra de página e evita divisão de elementos */
            .page-break {
                page-break-after: always;
            }

            .avoid-break {
                page-break-inside: avoid;
            }
        }
        .brush-script {
            font-family: "Brush Script MT", "Brush Script Std", cursive;
            font-size: 60px;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Botão de impressão - só aparece na tela -->
        <div class="no-print" style="text-align: right; margin-bottom: 20px;">
            <button onclick="window.print()">Imprimir</button>
            <button onclick="window.history.back()">Voltar</button>
        </div>

        <!-- Cabeçalho com dados da empresa -->
        <div class="header avoid-break">
            
    
            <div class="font-example">
                <h1 class="logo brush-script underline">Tele Dil</h1>
                
                <div class="company-info">Assistência Técnica</div>
            </div>
            <div class="company-info">Endereço da Empresa, Número - Bairro</div>
            <div class="company-info">Cidade - Estado - CEP</div>
            <div class="company-info">Telefone: (XX) XXXX-XXXX</div>
        </div>

        <!-- Número da OS -->
        <div class="order-number avoid-break">
            ORDEM DE SERVIÇO Nº <?php echo str_pad($order['id'], 6, '0', STR_PAD_LEFT); ?>
        </div>

        <!-- Informações do Cliente -->
        <div class="section avoid-break">
            <div class="grid">
                <div class="field">
                    <div class="field-label">Cliente:</div>
                    <div class="field-value"><?php echo htmlspecialchars($order['client_name']); ?></div>
                </div>
                <div class="field">
                    <div class="field-label">Data de Entrega Prevista:</div>
                    <div class="field-value"><?php echo $delivery_date; ?></div>
                </div>
            </div>
            <div class="grid">
                <div class="field">
                    <div class="field-label">Telefone 1:</div>
                    <div class="field-value"><?php echo htmlspecialchars($order['phone1']); ?></div>
                </div>
                <div class="field">
                    <div class="field-label">Telefone 2:</div>
                    <div class="field-value"><?php echo htmlspecialchars($order['phone2']); ?></div>
                </div>
            </div>
        </div>

        <!-- Informações do Aparelho -->
        <div class="section avoid-break">
            <div class="field">
                <div class="field-label">Modelo do Aparelho:</div>
                <div class="field-value"><?php echo nl2br(htmlspecialchars($order['device_model'])); ?></div>
            </div>
            <div class="field">
                <div class="field-label">Defeito Reclamado:</div>
                <div class="field-value"><?php echo nl2br(htmlspecialchars($order['reported_issue'])); ?></div>
            </div>
            <div class="field">
                <div class="field-label">Acessórios:</div>
                <div class="field-value"><?php echo nl2br(htmlspecialchars($order['accessories'])); ?></div>
            </div>
        </div>

        <!-- Senhas -->
        <div class="section avoid-break">
            <div class="grid">
                <div class="field">
                    <div class="field-label">Senha do Aparelho:</div>
                    <div class="field-value"><?php echo htmlspecialchars($order['device_password']); ?></div>
                </div>
                <div class="field">
                    <div class="field-label">Padrão:</div>
                    <div class="field-value"><?php echo htmlspecialchars($order['pattern_password']); ?></div>
                </div>
            </div>
        </div>

        <!-- Termos e Condições -->
        <!-- <div class="section avoid-break" style="font-size: 12px; margin-top: 30px;">
            <p><strong>Termos e Condições:</strong></p>
            <ol>
                <li>O prazo de garantia dos serviços é de 90 dias a partir da data de entrega.</li>
                <li>Aparelhos não retirados em 90 dias serão considerados abandonados.</li>
                <li>A empresa não se responsabiliza por dados armazenados no dispositivo.</li>
                <li>O cliente declara estar ciente que pode haver perda de dados durante o reparo.</li>
                <li>Ao deixar o aparelho para manutenção, o cliente concorda com todos os termos acima.</li>
            </ol>
        </div> -->

        <!-- Assinaturas -->
        <div class="signatures avoid-break">
            <div class="signature-line">
                Assinatura do Cliente<br>
                
            </div>
            <div class="signature-line">
                Assinatura do Técnico<br>
                
            </div>
            <div class="signature-line">
            Data: ____/____/______<br>
                
            </div>
        </div>
    </div>
</body>
</html>