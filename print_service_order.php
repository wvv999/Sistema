<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Ordem de Serviço #<?php echo $order['id']; ?></title>
    <link href="https://fonts.cdnjs.com/css2?family=Style+Script" rel="stylesheet">
    <style>
        /* Estilos gerais com tamanhos reduzidos */
        body {
            font-family: Arial, sans-serif;
            line-height: 1.4;
            margin: 0;
            padding: 10px;
        }

        .container {
            max-width: 21cm; /* Largura A4 */
            margin: 0 auto;
            height: 14.85cm; /* Metade de uma A4 (29.7cm / 2) */
        }

        /* Cabeçalho mais compacto */
        .header {
            text-align: center;
            margin-bottom: 15px;
            border-bottom: 1px solid #000;
            padding-bottom: 10px;
        }

        .company-info {
            font-size: 12px;
            margin-bottom: 2px;
        }

        /* Informações da OS */
        .order-number {
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 15px;
            text-align: center;
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

        /* Seção de assinaturas mais compacta */
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

        /* Estilos para impressão */
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

            /* Força duas ordens por página */
            @page {
                size: A4;
                margin: 0;
            }
        }

        .brush-script {
            font-family: "Brush Script MT", "Brush Script Std", cursive;
            font-size: 40px; /* Reduzido de 60px */
        }

        .font-example {
            text-align: center;
        }

        .logo {
            margin: 0;
            display: inline-block;
        }

        .underline {
            border-bottom: 2px solid #000;
            padding-bottom: 2px;
        }

        /* Novo estilo para contatos combinados */
        .contacts-value {
            border: 1px solid #ccc;
            padding: 3px 5px;
            min-height: 16px;
            font-size: 12px;
            margin-top: 2px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="no-print" style="text-align: right; margin-bottom: 10px;">
            <button onclick="window.print()">Imprimir</button>
            <button onclick="window.history.back()">Voltar</button>
        </div>

        <div class="header avoid-break">
            <div class="font-example">
                <h1 class="logo brush-script underline">Tele Dil</h1>
                <div class="company-info">Assistência Técnica</div>
            </div>
            <div class="company-info">Rua José de Quadros, 161</div>
            <div class="company-info">Telefone: (44) 3561-5145</div>
        </div>

        <div class="order-number avoid-break">
            ORDEM DE SERVIÇO Nº <?php echo str_pad($order['id'], 6, '0', STR_PAD_LEFT); ?>
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
            
            <!-- Contatos combinados -->
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

            <div class="field">
                <div class="field-label">Modelo do Aparelho:</div>
                <div class="field-value"><?php echo nl2br(htmlspecialchars($order['device_model'])); ?></div>
            </div>
            
            <div class="field">
                <div class="field-label">Defeito Reclamado:</div>
                <div class="field-value"><?php echo nl2br(htmlspecialchars($order['reported_issue'])); ?></div>
            </div>

            <div class="grid">
                <div class="field">
                    <div class="field-label">Acessórios:</div>
                    <div class="field-value"><?php echo nl2br(htmlspecialchars($order['accessories'])); ?></div>
                </div>
                <div class="field">
                    <div class="field-label">Senha do Aparelho:</div>
                    <div class="field-value"><?php echo htmlspecialchars($order['device_password']); ?></div>
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