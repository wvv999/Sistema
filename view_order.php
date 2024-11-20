<?php
/* ... código PHP inicial permanece igual ... */
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ordem de Serviço <?php echo $order['id']; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    <style>
        /* ... estilos anteriores permanecem ... */

        /* Novos estilos para os botões de status */
        .status-button {
            font-weight: 600;
            letter-spacing: 0.5px;
            transition: all 0.3s ease;
            width: 100%;
            text-align: center;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            min-height: 45px;
        }

        /* Status da Ordem */
        .status-nao-iniciada {
            background-color: #6c757d !important;
            border-color: #6c757d !important;
            color: white !important;
        }

        .status-em-andamento {
            background-color: #fd7e14 !important;
            border-color: #fd7e14 !important;
            color: white !important;
        }

        .status-concluida {
            background-color: #28a745 !important;
            border-color: #28a745 !important;
            color: white !important;
        }

        .status-pronto-e-avisado {
            background-color: #0dcaf0 !important;
            border-color: #0dcaf0 !important;
            color: white !important;
        }

        .status-entregue {
            background-color: #20c997 !important;
            border-color: #20c997 !important;
            color: white !important;
        }

        /* Status de Autorização */
        .auth-button {
            font-weight: 600;
            letter-spacing: 0.5px;
            transition: all 0.3s ease;
            width: 100%;
            text-align: center;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            min-height: 45px;
        }

        .auth-autorizacao {
            background-color: #6c757d !important;
            border-color: #6c757d !important;
            color: white !important;
        }

        .auth-solicitado {
            background-color: #ffc107 !important;
            border-color: #ffc107 !important;
            color: #000 !important;
        }

        .auth-autorizado {
            background-color: #28a745 !important;
            border-color: #28a745 !important;
            color: white !important;
        }

        .technical-info-container {
            display: flex;
            gap: 24px;
            margin-bottom: 24px;
        }

        .technical-notes {
            flex: 1;
            display: flex;
            flex-direction: column;
            gap: 16px;
        }

        .side-panel {
            width: 250px;
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .action-button {
            width: 100%;
            padding: 12px;
            border-radius: 8px;
            border: 1px solid #dee2e6;
            background: white;
            display: flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .action-button:hover {
            transform: translateX(-2px);
            box-shadow: 2px 2px 8px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>
    <div class="order-container">
        <!-- ... header-info permanece igual ... -->

        <div class="technical-info-container">
            <div class="technical-notes">
                <div>
                    <div class="section-title">Defeito Reclamado</div>
                    <div class="reported-issue"> 
                        <?php echo htmlspecialchars($order['reported_issue']); ?>
                    </div>
                </div>

                <div>
                    <div class="section-title">Laudo Técnico</div>
                    <textarea class="form-control" rows="8" placeholder="Descreva o diagnóstico técnico e os procedimentos realizados..."></textarea>
                </div>

                <div>
                    <div class="section-title">Peças Necessárias</div>
                    <textarea class="form-control" rows="4" placeholder="Liste as peças necessárias para o reparo..."></textarea>
                </div>
            </div>

            <div class="side-panel">
                <div id="statusButton" 
                     class="action-button status-button"
                     data-status="<?php echo $order['status']; ?>"
                     data-order-id="<?php echo $order['id']; ?>">
                    <i class="bi bi-gear"></i>
                    <span><?php echo $order['status']; ?></span>
                </div>

                <div id="authButton" 
                     class="action-button auth-button auth-autorizacao"
                     data-auth-status="Autorização"
                     data-order-id="<?php echo $order['id']; ?>">
                    <i class="bi bi-check-circle"></i>
                    <span>Autorização</span>
                </div>

                <div class="action-button">
                    <i class="bi bi-currency-dollar"></i>
                    <span>Negociação</span>
                </div>

                <div class="action-button">
                    <i class="bi bi-cart"></i>
                    <span>Compra de Peças</span>
                </div>
            </div>
        </div>

        <div class="bottom-buttons">
            <button class="bottom-button">
                <i class="bi bi-printer"></i> Histórico
            </button>
            <button class="bottom-button">
                <i class="bi bi-printer"></i> Imprimir
            </button>
            <button style="background-color:#28a745" class="bottom-button">
                <i class="bi bi-save"></i> Salvar
            </button>
            <button class="bottom-button" onclick="javascript:history.go(-1)">
                <i class="bi bi-x-lg"></i> Fechar
            </button>
        </div>
    </div>

    <script>
        // Status da Ordem
        const statusButton = document.getElementById('statusButton');
        const statusFlow = ['Não iniciada', 'Em andamento', 'Concluída', 'Pronto e avisado', 'Entregue'];
        
        function updateButtonAppearance(button, status, prefix = 'status') {
            // Remover todas as classes de status existentes
            button.className = 'action-button ' + prefix + '-button';
            
            // Adicionar a classe apropriada baseada no status atual
            const statusClass = `${prefix}-${status.toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, "").replace(/ /g, '-')}`;
            button.classList.add(statusClass);
            
            // Atualizar o conteúdo do botão
            button.innerHTML = `<i class="bi bi-gear"></i> <span>${status}</span>`;
        }

        statusButton.addEventListener('click', async function() {
            const currentStatus = this.dataset.status;
            const currentIndex = statusFlow.indexOf(currentStatus);
            const nextStatus = statusFlow[(currentIndex + 1) % statusFlow.length];
            
            try {
                const response = await fetch('update_status.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        orderId: this.dataset.orderId,
                        status: nextStatus
                    })
                });

                const data = await response.json();
                
                if (data.success) {
                    this.dataset.status = nextStatus;
                    updateButtonAppearance(this, nextStatus);
                } else {
                    alert('Erro ao atualizar status: ' + data.message);
                }
            } catch (error) {
                console.error('Erro:', error);
                alert('Erro ao atualizar status');
            }
        });

        // Autorização
        const authButton = document.getElementById('authButton');
        const authFlow = ['Autorização', 'Solicitado', 'Autorizado'];

        authButton.addEventListener('click', async function() {
            const currentStatus = this.dataset.authStatus;
            const currentIndex = authFlow.indexOf(currentStatus);
            const nextStatus = authFlow[(currentIndex + 1) % authFlow.length];
            
            try {
                const response = await fetch('update_auth_status.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        orderId: this.dataset.orderId,
                        authStatus: nextStatus
                    })
                });

                const data = await response.json();
                
                if (data.success) {
                    this.dataset.authStatus = nextStatus;
                    updateButtonAppearance(this, nextStatus, 'auth');
                } else {
                    alert('Erro ao atualizar autorização: ' + data.message);
                }
            } catch (error) {
                console.error('Erro:', error);
                alert('Erro ao atualizar autorização');
            }
        });

        // Definir estados iniciais
        updateButtonAppearance(statusButton, statusButton.dataset.status);
        updateButtonAppearance(authButton, authButton.dataset.authStatus, 'auth');
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>