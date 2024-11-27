<!DOCTYPE html>
<html lang="pt-BR">
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
            padding: 1cm;  /* Added 1cm padding */
        }

        .page {
            width: 21cm;
            height: 29.7cm;
            margin: 0 auto;
            position: relative;
            transform: scale(1.05);  /* Increased scale by 5% */
            transform-origin: top center;
        }

        .container {
            width: 21cm;
            height: 14.85cm;
            max-height: 14.85cm;
            overflow: hidden;
            position: relative;
            box-sizing: border-box;
            padding: 5px 25px;
        }

        /* First copy at the top */
        .container:first-child {
            border-bottom: 1px dashed #999;
        }

        .header {
            display: grid;
            grid-template-columns: 1fr auto;
            align-items: center;
            margin-bottom: 6px;
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
            margin-bottom: 6px;
        }

        .grid {
            display: grid;
            gap: 18%;
            margin-bottom: 4px;
        }

        .field {
            margin-bottom: 4px;
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

        .reported-issue-container {
            display: flex;
            gap: 8px;
            margin-top: 4px;
        }

        .reported-issue {
            flex: 1;
            min-height: 80px;
            max-height: 80px;
            border: 1px solid #ccc;
            font-size: 11px;
            padding: 3px;
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
            gap: 4px;
            margin-bottom: 4px;
        }

        .disclaimer {
            font-size: 11px;
            margin: 8px 0;
            text-align: justify;
            line-height: 1.2;
            margin-bottom: 12px;
        }

        .signatures {
            margin-top: 40px;
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

        @media print {
            body {
                padding: 1cm;  /* Maintain padding when printing */
            }

            .no-print {
                display: none;
            }

            .page {
                page-break-after: always;
            }

            @page {
                size: A4;
                margin: 0;
            }
        }
    </style>
</head>
<!-- Rest of the HTML remains the same -->
<body>
    <div class="page">
        <!-- First copy -->
        <div class="container">
            <div class="header">
                <div class="header-left">
                    <div class="title-container">
                        <h1 class="brush-script">Tele Dil</h1>
                    </div>
                    <div class="company-info">Assistência Técnica</div>
                    <div class="company-info">Rua José de Quadros, 161</div>
                    <div class="company-info">Telefone: (4444) 3561-5145</div>
                </div>
                <div class="header-right">
                    <div>OS Nº <?php echo str_pad($order['id'], 5, "0", STR_PAD_LEFT); ?></div>
                    <div class="delivery-date">Previsão de Entrega: <?php echo $delivery_date; ?></div>
                </div>
            </div>

            <div class="section">
                <div class="grid" style="grid-template-columns: 48% 24% 24%;">
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
                    <div class="reported-issue-container">
                        <div class="reported-issue">
                            <?php echo nl2br(htmlspecialchars($order['reported_issue'])); ?>
                        </div>
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 80 80" style="width: 86px; height: 86px;">
                            <rect width="80" height="80" fill="#f9f9f9" stroke="#ccc" stroke-width="1"/>
                            <circle cx="15" cy="15" r="3" fill="#666"/>
                            <circle cx="40" cy="15" r="3" fill="#666"/>
                            <circle cx="65" cy="15" r="3" fill="#666"/>
                            <circle cx="15" cy="40" r="3" fill="#666"/>
                            <circle cx="40" cy="40" r="3" fill="#666"/>
                            <circle cx="65" cy="40" r="3" fill="#666"/>
                            <circle cx="15" cy="65" r="3" fill="#666"/>
                            <circle cx="40" cy="65" r="3" fill="#666"/>
                            <circle cx="65" cy="65" r="3" fill="#666"/>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="disclaimer">
                A empresa da garantia de 90 dias para mão de obra e peça usada no conserto, a garantia só é valida para defeito na peça trocada, sendo que por mau uso o cliente perde a garantia do mesmo. Aparelhos molhados não terão garantia.
                Não nos responsabilizamos por dados contidos nos cartões de memória, chip e no aparelho. a constatação de rompimento do lacre invalidará a garantia. A permanencia do aparelho por mais de 30 dias após a aprovação, poderá sofrer reajuste do preço sem aviso prévio e a partir de 90 dias sem a procura do proprietário será considerada abandono do mesmo, não cabendo reclamação ou indenização. A procedência do aparelho é de responsabilidade do declarante. O aparelho só será entregue mediante esta ordem de serviço.
            </div>

            <div class="signatures">
                <div class="signature-line">
                    Assinatura do Cliente
                </div>
            </div>
        </div>

        <!-- Second copy (identical to the first) -->
        <div class="container">
            <div class="header">
                <div class="header-left">
                    <div class="title-container">
                        <h1 class="brush-script">Tele Dil</h1>
                    </div>
                    <div class="company-info">Assistência Técnica</div>
                    <div class="company-info">Rua José de Quadros, 161</div>
                    <div class="company-info">Telefone: (4444) 3561-5145</div>
                </div>
                <div class="header-right">
                    <div>OS Nº <?php echo str_pad($order['id'], 5, "0", STR_PAD_LEFT); ?></div>
                    <div class="delivery-date">Previsão de Entrega: <?php echo $delivery_date; ?></div>
                </div>
            </div>

            <div class="section">
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
                    <div class="reported-issue-container">
                        <div class="reported-issue">
                            <?php echo nl2br(htmlspecialchars($order['reported_issue'])); ?>
                        </div>
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 80 80" style="width: 86px; height: 86px;">
                            <rect width="80" height="80" fill="#f9f9f9" stroke="#ccc" stroke-width="1"/>
                            <circle cx="15" cy="15" r="3" fill="#666"/>
                            <circle cx="40" cy="15" r="3" fill="#666"/>
                            <circle cx="65" cy="15" r="3" fill="#666"/>
                            <circle cx="15" cy="40" r="3" fill="#666"/>
                            <circle cx="40" cy="40" r="3" fill="#666"/>
                            <circle cx="65" cy="40" r="3" fill="#666"/>
                            <circle cx="15" cy="65" r="3" fill="#666"/>
                            <circle cx="40" cy="65" r="3" fill="#666"/>
                            <circle cx="65" cy="65" r="3" fill="#666"/>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="disclaimer">
                A empresa da garantia de 90 dias para mão de obra e peça usada no conserto, a garantia só é valida para defeito na peça trocada, sendo que por mau uso o cliente perde a garantia do mesmo. Aparelhos molhados não terão garantia.
                Não nos responsabilizamos por dados contidos nos cartões de memória, chip e no aparelho. a constatação de rompimento do lacre invalidará a garantia. A permanencia do aparelho por mais de 30 dias após a aprovação, poderá sofrer reajuste do preço sem aviso prévio e a partir de 90 dias sem a procura do proprietário será considerada abandono do mesmo, não cabendo reclamação ou indenização. A procedência do aparelho é de responsabilidade do declarante. O aparelho só será entregue mediante esta ordem de serviço.
            </div>

            <div class="signatures">
                <div class="signature-line">
                    Assinatura do Cliente
                </div>
            </div>
        </div>
    </div>
</body>
</html>