<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ordem de Serviço <?php echo $order['id']; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    <style>
        :root {
            --primary-color: #0d6efd;
            --secondary-bg: #f8f9fa;
            --border-radius: 0.5rem;
        }

        body {
            background-color: #f0f2f5;
            min-height: 100vh;
        }

        .main-container {
            max-width: 1400px;
            margin: 2rem auto;
            padding: 0 1rem;
        }

        .card {
            border: none;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
            border-radius: var(--border-radius);
        }

        .header-card {
            background: linear-gradient(145deg, #ffffff, #f8f9fa);
            border-left: 4px solid var(--primary-color);
        }

        .info-label {
            color: #6c757d;
            font-size: 0.875rem;
            font-weight: 600;
            margin-bottom: 0.25rem;
        }

        .info-value {
            background-color: white;
            padding: 0.5rem;
            border-radius: 0.25rem;
            margin-bottom: 0.5rem;
        }

        .section-card {
            height: 100%;
            transition: transform 0.2s;
        }

        .section-card:hover {
            transform: translateY(-2px);
        }

        .status-badge {
            padding: 0.5rem 1rem;
            border-radius: 2rem;
            font-weight: 500;
            text-align: center;
            transition: all 0.3s;
        }

        .notes-container {
            max-height: 300px;
            overflow-y: auto;
            background-color: var(--secondary-bg);
            border-radius: 0.375rem;
            padding: 1rem;
            margin-bottom: 1rem;
        }

        .notes-container::-webkit-scrollbar {
            width: 6px;
        }

        .notes-container::-webkit-scrollbar-thumb {
            background-color: #cbd5e0;
            border-radius: 3px;
        }

        .action-btn {
            transition: all 0.2s;
            position: relative;
            overflow: hidden;
        }

        .action-btn:hover {
            transform: translateY(-2px);
        }

        /* Status colors */
        .status-nao-iniciada { background-color: #dc3545; color: white; }
        .status-em-andamento { background-color: #ffc107; color: black; }
        .status-concluida { background-color: #198754; color: white; }
        .status-pronto-e-avisado { background-color: #0dcaf0; color: white; }
        .status-entregue { background-color: #6c757d; color: white; }

        /* Auth status colors */
        .auth-autorizacao { background-color: #6c757d; color: white; }
        .auth-solicitado { background-color: #ffc107; color: black; }
        .auth-autorizado { background-color: #198754; color: white; }

        /* Toast notifications */
        .toast-container {
            position: fixed;
            bottom: 20px;
            right: 20px;
            z-index: 1000;
        }

        .toast {
            padding: 1rem;
            background: white;
            border-radius: var(--border-radius);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            animation: slideIn 0.3s ease;
        }

        @keyframes slideIn {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        /* History modal styles */
        .history-item {
            padding: 1rem;
            border-radius: var(--border-radius);
            background: var(--secondary-bg);
            margin-bottom: 0.5rem;
            transition: background-color 0.2s;
        }

        .history-item:hover {
            background: #e9ecef;
        }

        .history-date {
            font-size: 0.875rem;
            color: #6c757d;
            margin-bottom: 0.25rem;
        }

        .history-user {
            font-weight: 600;
            margin-bottom: 0.25rem;
        }

        .modal-content {
            border-radius: var(--border-radius);
            border: none;
        }
    </style>
</head>
<body>
    <div class="main-container">
        <!-- Back Button -->
        <a href="javascript:history.go(-1)" class="btn btn-outline-primary mb-4">
            <i class="bi bi-arrow-left"></i> Voltar
        </a>

        <!-- Main Content -->
        <div class="row g-4">
            <!-- Order Info Header -->
            <div class="col-12">
                <div class="card header-card p-4">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h4 class="mb-0">Ordem de Serviço #<?php echo str_pad($order['id'], 5, '0', STR_PAD_LEFT); ?></h4>
                        <span class="status-badge <?php echo 'status-' . strtolower(str_replace(' ', '-', $order['status'])); ?>">
                            <?php echo $order['status']; ?>
                        </span>
                    </div>
                    
                    <div class="row g-3">
                        <div class="col-md-3">
                            <div class="info-label">
                                <i class="bi bi-person-circle"></i> Cliente
                            </div>
                            <div class="info-value"><?php echo htmlspecialchars($order['client_name']); ?></div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-label">
                                <i class="bi bi-laptop"></i> Equipamento
                            </div>
                            <div class="info-value"><?php echo htmlspecialchars($order['device_model']); ?></div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-label">
                                <i class="bi bi-calendar-event"></i> Data de Abertura
                            </div>
                            <div class="info-value"><?php echo date('d/m/Y', strtotime($order['created_at'])); ?></div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-label">
                                <i class="bi bi-calendar-check"></i> Previsão de Entrega
                            </div>
                            <div class="info-value"><?php echo date('d/m/Y', strtotime($order['delivery_date'])); ?></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Content Area -->
            <div class="col-lg-8">
                <div class="row g-4">
                    <!-- Password Section -->
                    <div class="col-md-6">
                        <div class="card section-card p-4">
                            <h5 class="card-title mb-3">
                                <i class="bi bi-key text-primary"></i> Senha do Dispositivo
                            </h5>
                            <div class="info-value">
                                <?php echo htmlspecialchars($order['device_password'] ?? '-'); ?>
                            </div>
                        </div>
                    </div>

                    <!-- Contact Section -->
                    <div class="col-md-6">
                        <div class="card section-card p-4">
                            <h5 class="card-title mb-3">
                                <i class="bi bi-telephone text-primary"></i> Contatos
                            </h5>
                            <div class="info-label">Principal</div>
                            <div class="info-value"><?php echo htmlspecialchars($order['phone1']); ?></div>
                            <div class="info-label">Secundário</div>
                            <div class="info-value"><?php echo htmlspecialchars($order['phone2'] ?? '-'); ?></div>
                        </div>
                    </div>

                    <!-- Reported Issue Section -->
                    <div class="col-12">
                        <div class="card section-card p-4">
                            <h5 class="card-title mb-3">
                                <i class="bi bi-exclamation-triangle text-warning"></i> Defeito Reclamado
                            </h5>
                            <div class="info-value">
                                <?php echo htmlspecialchars($order['reported_issue']); ?>
                            </div>
                        </div>
                    </div>

                    <!-- Technical Notes Section -->
                    <div class="col-12">
                        <div class="card section-card p-4">
                            <h5 class="card-title mb-3">
                                <i class="bi bi-clipboard-data text-primary"></i> Laudo Técnico
                            </h5>
                            
                            <div class="notes-container mb-3">
                                <textarea readonly class="form-control border-0 bg-transparent" 
                                    style="resize: none; min-height: 200px;"><?php echo $textareaContent; ?></textarea>
                            </div>

                            <div class="input-group">
                                <textarea class="form-control" id="newNote" rows="1" 
                                    placeholder="Digite sua nota técnica..." data-autoresize></textarea>
                                <button class="btn btn-primary" onclick="addNote()">
                                    <i class="bi bi-plus-lg"></i> Adicionar
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Side Panel -->
            <div class="col-lg-4">
                <div class="card section-card p-4">
                    <div class="d-flex flex-column gap-3">
                        <!-- Status Button -->
                        <button id="statusButton" 
                            class="btn btn-lg action-btn w-100 status-badge <?php echo 'status-' . strtolower(str_replace(' ', '-', $order['status'])); ?>"
                            data-status="<?php echo $order['status']; ?>"
                            data-order-id="<?php echo $order['id']; ?>">
                            <i class="bi bi-arrow-repeat"></i> <?php echo $order['status']; ?>
                        </button>

                        <!-- Auth Button -->
                        <button id="authButton" 
                            class="btn btn-lg action-btn w-100"
                            data-auth-status="Autorização"
                            data-order-id="<?php echo $order['id']; ?>">
                            <i class="bi bi-check-circle"></i> Autorização
                        </button>

                        <!-- Parts Button -->
                        <button class="btn btn-lg btn-outline-secondary action-btn w-100">
                            <i class="bi bi-cart"></i> Gerenciar Peças
                        </button>

                        <!-- History Button -->
                        <button class="btn btn-lg btn-outline-info action-btn w-100" 
                                data-bs-toggle="modal" data-bs-target="#historyModal">
                            <i class="bi bi-clock-history"></i> Histórico
                        </button>

                        <!-- Print Button -->
                        <button class="btn btn-lg btn-outline-dark action-btn w-100" 
                                onclick="window.open('print_service_order.php?id=<?php echo $order['id']; ?>', '_blank')">
                            <i class="bi bi-printer"></i> Imprimir
                        </button>

                        <!-- Save and Exit -->
                        <button class="btn btn-lg btn-success action-btn w-100" onclick="javascript:history.go(-1)">
                            <i class="bi bi-check-lg"></i> Salvar e Sair
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- History Modal -->
    <div class="modal fade" id="historyModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Histórico da Ordem de Serviço</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <ul class="nav nav-tabs mb-3" id="historyTabs" role="tablist">
                        <li class="nav-item">
                            <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#statusHistory">
                                <i class="bi bi-gear"></i> Status
                            </button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#notesHistory">
                                <i class="bi bi-clipboard"></i> Notas Técnicas
                            </button>
                        </li>
                    </ul>
                    <div class="tab-content">
                        <div class="tab-pane fade show active" id="statusHistory">
                            <!-- Status history content -->
                        </div>
                        <div class="tab-pane fade" id="notesHistory">
                            <!-- Notes history content -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Toast Container -->
    <div class="toast-container"></div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Toast notification system
        function showToast(message, type = 'success') {
            const toast = document.createElement('div');
            toast.className = 'toast';
            toast.innerHTML = `
                <i class="bi bi-${type === 'success' ? 'check-circle-fill text-success' : 'exclamation-circle-fill text-danger'}"></i>
                <span>${message}</span>
            `;
            document.querySelector('.toast-container').appendChild(toast);
            
            setTimeout(() => {
                toast.remove();
            }, 3000);
        }

        // Auto-resize textarea
        document.querySelectorAll('[data-autoresize]').forEach(element => {
            element.style.height = 'auto';
            element.style.height = (element.scrollHeight) + 'px';
            
            element.addEventListener('input', e => {
                e.target.style.height = 'auto';
                e.target.style.height = (e.target.scrollHeight) + 'px';
            });
        });

        // Technical notes functionality
        async function addNote() {
            const noteInput = document.getElementById('newNote');
            const noteText = noteInput.value.trim();
            
            if (!noteText) {
                showToast('Por favor, digite uma nota técnica.', 'error');
                return;
            }

            try {
                const response = await fetch('save_technical_note.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        orderId: <?php echo $_GET['id']; ?>,
                        note: noteText
                    })
                });

                const data = await response.json();
                
                if (data.success) {
                    const technicalNotes = document.querySelector('#technicalNotes');
                    const today = new Date().toLocaleDateString('pt-BR', { 
                        day: '2-digit', 
                        month: '2-digit', 
                        year: '2-digit' 
                    });
                    
                    const newNote = `\n---------------- ${today} ----------------\n\n${data.username}: ${noteText}\n`;
                    technicalNotes.value += newNote;
                    noteInput.value = '';
                    
                    technicalNotes.scrollTop = technicalNotes.scrollHeight;
                    showToast('Nota técnica adicionada com sucesso!');
                } else {
                    showToast(data.message || 'Erro ao salvar nota', 'error');
                }
            } catch (error) {
                console.error('Erro:', error);
                showToast('Erro ao salvar nota técnica', 'error');
            }
        }

        // Status management
        async function updateStatus(button, newStatus) {
            try {
                const response = await fetch('update_status.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        orderId: button.dataset.orderId,
                        status: newStatus
                    })
                });

                const data = await response.json();
                
                if (data.success) {
                    button.dataset.status = newStatus;
                    updateButtonAppearance(button, newStatus, 'status');
                    showToast(`Status atualizado para: ${newStatus}`);
                } else {
                    showToast(data.message || 'Erro ao atualizar status', 'error');
                }
            } catch (error) {
                console.error('Erro:', error);
                showToast('Erro ao atualizar status', 'error');
            }
        }

        // Authorization management
        async function updateAuthStatus(button, newStatus) {
            try {
                const response = await fetch('update_auth_status.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        orderId: button.dataset.orderId,
                        authStatus: newStatus
                    })
                });

                const data = await response.json();
                
                if (data.success) {
                    button.dataset.authStatus = newStatus;
                    updateButtonAppearance(button, newStatus, 'auth');
                    showToast(`Autorização atualizada para: ${newStatus}`);
                } else {
                    showToast(data.message || 'Erro ao atualizar autorização', 'error');
                }
            } catch (error) {
                console.error('Erro:', error);
                showToast('Erro ao atualizar autorização', 'error');
            }
        }

        // Update button appearances
        function updateButtonAppearance(button, status, type = 'status') {
            const classes = [...button.classList];
            classes.forEach(className => {
                if (className.startsWith(type + '-')) {
                    button.classList.remove(className);
                }
            });

            const normalizedStatus = status.toLowerCase()
                .normalize('NFD')
                .replace(/[\u0300-\u036f]/g, '')
                .replace(/ /g, '-');
            
            button.classList.add(`${type}-${normalizedStatus}`);
            button.querySelector('span').textContent = status;
        }

        // Event listeners
        statusButton.addEventListener('click', function() {
            const currentStatus = this.dataset.status;
            const currentIndex = statusFlow.indexOf(currentStatus);
            const nextStatus = statusFlow[(currentIndex + 1) % statusFlow.length];
            updateStatus(this, nextStatus);
        });

        authButton.addEventListener('click', function() {
            const currentStatus = this.dataset.authStatus;
            const currentIndex = authFlow.indexOf(currentStatus);
            const nextStatus = authFlow[(currentIndex + 1) % authFlow.length];
            updateAuthStatus(this, nextStatus);
        });

        // History functionality
        async function loadOrderHistory() {
            try {
                const response = await fetch('get_order_history.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        orderId: <?php echo $_GET['id']; ?>
                    })
                });

                const data = await response.json();
                
                if (data.success) {
                    // Update status history
                    const statusContainer = document.querySelector('#statusHistory');
                    if (data.statusHistory?.length) {
                        statusContainer.innerHTML = data.statusHistory.map(item => `
                            <div class="history-item">
                                <div class="history-date">${item.formatted_date}</div>
                                <div class="history-user">${item.username}</div>
                                <div class="history-content">
                                    <i class="bi bi-arrow-right-circle"></i> 
                                    Alterou status para: ${JSON.parse(item.details).new_status}
                                </div>
                            </div>
                        `).join('');
                    } else {
                        statusContainer.innerHTML = '<div class="p-3 text-muted">Nenhuma alteração de status encontrada.</div>';
                    }

                    // Update notes history
                    const notesContainer = document.querySelector('#notesHistory');
                    if (data.notesHistory?.length) {
                        notesContainer.innerHTML = data.notesHistory.map(item => `
                            <div class="history-item">
                                <div class="history-date">${item.formatted_date}</div>
                                <div class="history-user">${item.username}</div>
                                <div class="history-content">${item.note}</div>
                            </div>
                        `).join('');
                    } else {
                        notesContainer.innerHTML = '<div class="p-3 text-muted">Nenhuma nota técnica encontrada.</div>';
                    }
                } else {
                    showToast('Erro ao carregar histórico: ' + (data.message || 'Erro desconhecido'), 'error');
                }
            } catch (error) {
                console.error('Erro:', error);
                showToast('Erro ao carregar histórico', 'error');
            }
        }

        // Initialize tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        });

        // Check for notifications periodically
        setInterval(async () => {
            try {
                const response = await fetch('check_notifications.php');
                const data = await response.json();
                
                if (data.success && data.hasNotification) {
                    const notification = data.notification;
                    if (notification.type === 'auth_status') {
                        showToast(`Autorização solicitada para OS #${notification.order_id}`);
                    } else if (notification.type === 'auth_approved') {
                        showToast(`Autorização aprovada para OS #${notification.order_id}`);
                        authButton.dataset.authStatus = 'Autorizado';
                        updateButtonAppearance(authButton, 'Autorizado', 'auth');
                    }
                }
            } catch (error) {
                console.error('Erro ao verificar notificações:', error);
            }
        }, 5000);
    </script>
</body>
</html>