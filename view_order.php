<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ordem <?php echo $order['id']; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    <style>
        body {
            background-color: #f8f9fa;
        }
        
        .page-header {
            padding: 20px;
            background: white;
            border-bottom: 1px solid #e9ecef;
        }

        .page-title {
            font-size: 20px;
            font-weight: 500;
            color: #2c3e50;
            margin-bottom: 5px;
        }

        .page-subtitle {
            font-size: 14px;
            color: #6c757d;
        }

        .back-button {
            color: #007bff;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            font-size: 14px;
        }

        .main-container {
            padding: 20px;
            display: grid;
            grid-template-columns: 300px 1fr 300px;
            gap: 20px;
            height: calc(100vh - 100px);
        }

        .left-panel {
            background: white;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }

        .middle-panel {
            background: white;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            display: flex;
            flex-direction: column;
        }

        .right-panel {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .info-section {
            padding: 20px;
            border-bottom: 1px solid #e9ecef;
        }

        .info-section:last-child {
            border-bottom: none;
        }

        .info-title {
            font-size: 16px;
            font-weight: 500;
            color: #2c3e50;
            margin-bottom: 15px;
        }

        .info-group {
            margin-bottom: 15px;
        }

        .info-label {
            font-size: 12px;
            color: #6c757d;
            margin-bottom: 5px;
        }

        .info-value {
            font-size: 14px;
            color: #2c3e50;
        }

        .tabs {
            display: flex;
            padding: 10px 20px 0;
            border-bottom: 1px solid #e9ecef;
        }

        .tab {
            padding: 10px 20px;
            color: #6c757d;
            text-decoration: none;
            border-bottom: 2px solid transparent;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .tab.active {
            color: #007bff;
            border-bottom-color: #007bff;
        }

        .notes-container {
            flex: 1;
            padding: 20px;
            display: flex;
            flex-direction: column;
        }

        .notes-content {
            flex: 1;
            overflow-y: auto;
            margin-bottom: 20px;
        }

        .note-input {
            display: flex;
            gap: 10px;
        }

        .note-input input {
            flex: 1;
            padding: 8px 12px;
            border: 1px solid #dee2e6;
            border-radius: 4px;
            font-size: 14px;
        }

        .action-button {
            background: white;
            border: 1px solid #dee2e6;
            border-radius: 4px;
            padding: 12px;
            text-align: center;
            color: #2c3e50;
            text-decoration: none;
            font-size: 14px;
            transition: all 0.2s;
        }

        .action-button:hover {
            background: #f8f9fa;
        }

        .status-button {
            background: #dc3545;
            color: white;
            border: none;
        }

        .status-button:hover {
            background: #c82333;
        }

        .auth-button {
            background: #ffc107;
            color: #2c3e50;
            border: none;
        }

        .auth-button:hover {
            background: #e0a800;
        }
    </style>
</head>
<body>
    <div class="page-header">
        <a href="javascript:history.back()" class="back-button">
            <i class="bi bi-arrow-left"></i> Voltar
        </a>
        <div class="page-title">Ordem <?php echo str_pad($order['id'], 6, "0", STR_PAD_LEFT); ?></div>
        <div class="page-subtitle">Aberta em <?php echo date('d/m/Y', strtotime($order['created_at'])); ?></div>
    </div>

    <div class="main-container">
        <!-- Left Panel -->
        <div class="left-panel">
            <div class="info-section">
                <div class="info-title">Informações do Cliente</div>
                <div class="info-group">
                    <div class="info-label">
                        <i class="bi bi-person"></i> Nome do Cliente
                    </div>
                    <div class="info-value"><?php echo htmlspecialchars($order['client_name']); ?></div>
                </div>
                <div class="info-group">
                    <div class="info-label">
                        <i class="bi bi-telephone"></i> Telefones
                    </div>
                    <div class="info-value"><?php echo htmlspecialchars($order['phone1']); ?></div>
                    <div class="info-value"><?php echo htmlspecialchars($order['phone2'] ?? ''); ?></div>
                </div>
                <div class="info-group">
                    <div class="info-label">
                        <i class="bi bi-laptop"></i> Equipamento
                    </div>
                    <div class="info-value"><?php echo htmlspecialchars($order['device_model']); ?></div>
                </div>
                <div class="info-group">
                    <div class="info-label">
                        <i class="bi bi-key"></i> Senha
                    </div>
                    <div class="info-value"><?php echo htmlspecialchars($order['device_password'] ?? '-'); ?></div>
                </div>
            </div>

            <div class="info-section">
                <div class="info-title">Defeito Relatado</div>
                <div class="info-value"><?php echo nl2br(htmlspecialchars($order['reported_issue'])); ?></div>
            </div>
        </div>

        <!-- Middle Panel -->
        <div class="middle-panel">
            <div class="tabs">
                <a href="#" class="tab active">
                    <i class="bi bi-card-text"></i> Notas Técnicas
                </a>
                <a href="#" class="tab">
                    <i class="bi bi-camera"></i> Fotos
                </a>
                <a href="#" class="tab">
                    <i class="bi bi-tools"></i> Peças
                </a>
            </div>

            <div class="notes-container">
                <div class="notes-content">
                    <?php echo nl2br(htmlspecialchars($textareaContent)); ?>
                </div>
                <div class="note-input">
                    <input type="text" placeholder="Digite sua nota técnica..." id="newNote">
                    <button class="action-button" onclick="addNote()">
                        <i class="bi bi-plus"></i> Adicionar
                    </button>
                </div>
            </div>
        </div>

        <!-- Right Panel -->
        <div class="right-panel">
            <button class="action-button status-button">
                <i class="bi bi-gear-fill"></i> Não iniciada
            </button>
            
            <button class="action-button auth-button">
                <i class="bi bi-check-circle"></i> Solicitado
            </button>
            
            <button class="action-button">
                <i class="bi bi-cart"></i> Compra de Peças
            </button>
            
            <button class="action-button">
                <i class="bi bi-clock-history"></i> Histórico
            </button>
            
            <button class="action-button">
                <i class="bi bi-printer"></i> Imprimir
            </button>
        </div>
    </div>
</body>
</html>