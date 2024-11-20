<?php 
session_start(); 
require_once 'config.php';  

if(!isset($_SESSION['user_id'])) {     
    header("Location: index.php");     
    exit; 
}  

if(!isset($_GET['id'])) {     
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
            $textareaContent .= "\n---------------- " . $note['formatted_date'] . " ----------------\n\n";             
            $currentDate = $note['note_date'];         
        }         
        $textareaContent .= "{$note['username']}: {$note['note']}\n";     
    }      

} catch(Exception $e) {         
    header("Location: dashboard.php");         
    exit;     
} 
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
        :root {
            --primary-color: #4a6fff;
            --secondary-color: #f8f9fa;
            --accent-color: #e7e9f6;
            --border-radius: 8px;
            --shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
        }

        body { 
            background-color: #f5f6fa;
            padding: 20px;
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            margin: 0;
            min-height: 100vh;
        }

        .order-container {
            background-color: #fff;
            border-radius: var(--border-radius);
            padding: 24px;
            box-shadow: var(--shadow);
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            flex-direction: column;
            min-height: calc(100vh - 40px);
        }

        .order-info {
            background: linear-gradient(145deg, var(--accent-color), #f8f9ff);
            padding: 20px;
            border-radius: var(--border-radius);
            margin-bottom: 24px;
            border: 1px solid rgba(0,0,0,0.05);
        }

        .client-details {
            border-left: 4px solid #0d6efd;
            padding-left: 15px;
            margin-top: 10px;
        }

        .main-content {
            display: flex;
            gap: 24px;
            flex: 1;
            margin-bottom: 24px;
        }

        .content-left {
            flex: 1;
        }

        .content-right {
            width: 250px;
        }

        .info-label {
            font-weight: bold;
            color: #6c757d;
            margin-bottom: 5px;
            font-size: 0.9rem;
        }

        .info-value {
            color: #333;
            margin-bottom: 15px;
            font-size: 1rem;
        }

        .device-password {
            background-color: #f8f9fa;
            padding: 16px;
            border-radius: var(--border-radius);
            border: 1px solid rgba(0,0,0,0.05);
            border-left: 4px solid #0d6efd;
            margin-bottom: 20px;
        }

        .reported-issue {
            background-color: #f8f9fa;
            padding: 16px;
            margin-bottom: 20px;
            border-radius: var(--border-radius);
            min-height: 100px;
            border: 1px solid rgba(0,0,0,0.05);
            border-left: 4px solid #0d6efd;
        }

        .section-title {
            font-weight: bold;
            color: #6c757d;
            margin-bottom: 12px;
            font-size: 1.1em;
        }

        .side-panel {
            display: flex;
            flex-direction: column;
            gap: 24px;
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
            border-radius: 8px;
            border: 1px solid #dee2e6;
            background: white;
            display: flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
            transition: all 0.2s ease;
            font-weight: 500;
        }

        .action-button:hover {
            transform: translateX(-2px);
            box-shadow: 2px 2px 8px rgba(0,0,0,0.1);
        }

        /* Status buttons */
        .status-button {
            font-weight: 600;
            letter-spacing: 0.5px;
            transition: all 0.3s ease;
            justify-content: center;
        }

        .status-nao-iniciada { background-color: #6c757d; color: white; }
        .status-em-andamento { background-color: #fd7e14; color: white; }
        .status-concluida { background-color: #28a745; color: white; }
        .status-pronto-e-avisado { background-color: #0dcaf0; color: white; }
        .status-entregue { background-color: #20c997; color: white; }

        /* Auth button styles */
        .auth-button {
            font-weight: 600;
            letter-spacing: 0.5px;
            justify-content: center;
        }

        .auth-autorizacao { background-color: #6c757d; color: white; }
        .auth-solicitado { background-color: #ffc107; color: black; }
        .auth-autorizado { background-color: #28a745; color: white; }

        /* Menu buttons */
        .menu-button {
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
            font-weight: 500;
        }

        .menu-button:hover {
            transform: translateX(-2px);
            box-shadow: 2px 2px 8px rgba(0,0,0,0.1);
        }

        /* Technical report section */
        .technical-report {
            background-color: #f8f9fa;
            padding: 16px;
            border-radius: var(--border-radius);
            border: 1px solid rgba(0,0,0,0.05);
            border-left: 4px solid #0d6efd;
        }

        .technical-notes textarea {
            border: none;
            background: transparent;
            width: 100%;
            resize: none;
            padding: 0;
            margin-bottom: 10px;
            font-size: 0.9rem;
            font-family: inherit;
        }

        .technical-notes textarea:focus {
            outline: none;
            box-shadow: none;
        }

        .add-note-form {
            /* border-top: 1px solid rgba(0,0,0,0.1); */
            padding-top: 10px;
            margin-top: 10px;
        }

        .add-note-form .input-group {
            display: grid;
            grid-template-columns: 1fr auto;
            gap: 8px;
            align-items: start;
        }

        .add-note-form textarea {
            min-height: 38px;
            padding: 8px 12px;
            border: 1px solid #dee2e6;
            border-radius: 4px;
            background-color: white;
            resize: none;
            line-height: 20px;
        }

        .add-note-form button {
            height: 38px;
            white-space: nowrap;
            padding: 0 16px;
            display: flex;
            align-items: center;
            gap: 4px;
        }

        @media (max-width: 768px) {
            .main-content {
                flex-direction: column;
            }
            
            .content-right {
                width: 100%;
            }
            
            .menu-button {
                min-width: calc(50% - 12px);
            }
        }
</style>
</head>
<body>
    <div class="order-container">
        <!-- Informações do pedido no topo -->
        <div class="order-info">
            <h4 class="mb-3">
                Ordem número: <?php echo str_pad($order['id'], STR_PAD_RIGHT); ?>
            </h4>
            <div class="client-details">
                <div class="row">
                    <div class="col-md-2">
                        <div class="info-label">Nome do Cliente</div>
                        <div class="info-value"><?php echo htmlspecialchars($order['client_name']); ?></div>
                    </div>
                    <div class="col-md-2">
                        <div class="info-label">Modelo</div>
                        <div class="info-value"><?php echo htmlspecialchars($order['device_model']); ?></div>
                    </div>

                    <div class="col-md-2">
                        <div class="info-label">Telefone Principal</div>
                        <div class="info-value"><?php echo htmlspecialchars($order['phone1']); ?></div>
                    </div>
                    <div class="col-md-2">
                        <div class="info-label">Telefone Secundário</div>
                        <div class="info-value"><?php echo htmlspecialchars($order['phone2'] ?? '-'); ?></div>
                    </div>
                    <div class="col-md-2">
                        <div class="info-label">Data de Abertura</div>
                        <div class="info-value"><?php echo date('d/m/Y', strtotime($order['created_at'])); ?></div>
                    </div>
                    <div class="col-md-2">
                        <div class="info-label">Data de Entrega</div>
                        <div class="info-value"><?php echo date('d/m/Y', strtotime($order['delivery_date'])); ?></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Conteúdo principal com duas colunas -->
        <div class="main-content">
            <!-- Coluna da esquerda -->
            <div class="content-left">
                <div>
                    <div class="info-label">Senha do Dispositivo</div>
                    <div class="device-password">
                        <div class="info-value"><?php echo htmlspecialchars($order['device_password'] ?? '-'); ?></div>
                    </div>
                </div>

                <div>
                    <div class="section-title">Defeito Reclamado</div>
                    <div class="reported-issue">
                        <?php echo htmlspecialchars($order['reported_issue']); ?>
                    </div>
                </div>

                <div>
                    <div class="section-title">Laudo Técnico</div>
                    <div class="technical-report">
                        <?php
                        $notesQuery = "SELECT tn.*, u.username, DATE_FORMAT(tn.created_at, '%d/%m/%y') as formatted_date
                                    FROM technical_notes tn 
                                    JOIN users u ON tn.user_id = u.id 
                                    WHERE tn.order_id = :order_id 
                                    ORDER BY tn.created_at ASC";
                        
                        $stmt = $db->prepare($notesQuery);
                        $stmt->execute([':order_id' => $_GET['id']]);
                        $notes = $stmt->fetchAll(PDO::FETCH_ASSOC);
                        
                        $textareaContent = '';
                        foreach ($notes as $note) {
                            $textareaContent .= "{$note['username']}: {$note['note']} ({$note['formatted_date']})\n";
                        }
                        ?>
                        
                        <div class="technical-notes">
                            <textarea id="technicalNotes" rows="6" readonly><?php echo $textareaContent; ?></textarea>
                            
                            <!-- Formulário para adicionar nova nota -->
                            <div class="add-note-form">
                                <div class="input-group">
                                    <textarea id="newNote" 
                                            rows="1"
                                            placeholder="Digite sua nota técnica..."></textarea>
                                    <button onclick="addNote()" class="btn btn-primary">
                                        <i class="bi bi-plus-circle"></i> Adicionar
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Coluna da direita -->
            <div class="content-right">
                <div class="side-panel">
                    <!-- Primeira seção do menu - Status e Ações -->
                    <div class="menu-section">
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

                    <!-- Segunda seção do menu - Ações da OS -->
                    <div class="menu-section">
                        <button class="menu-button">
                            <i class="bi bi-printer"></i>
                            <span>Histórico</span>
                        </button>
                        <button class="menu-button">
                            <i class="bi bi-printer"></i>
                            <span>Imprimir</span>
                        </button>
                        <button class="menu-button" style="background-color:#28a745; color: white">
                            <i class="bi bi-save"></i>
                            <span>Salvar</span>
                        </button>
                        <button class="menu-button" onclick="javascript:history.go(-1)">
                            <i class="bi bi-x-lg"></i>
                            <span>Fechar</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        async function addNote() {
            const noteText = document.getElementById('newNote').value.trim();
            if (!noteText) {
                alert('Por favor, digite uma nota técnica.');
                return;
            }

            try {
                const response = await fetch('save_technical_note.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        orderId: <?php echo $_GET['id']; ?>,
                        note: noteText
                    })
                });

                const data = await response.json();
                
                if (data.success) {
                    const technicalNotes = document.getElementById('technicalNotes');
                    const notes = technicalNotes.value;
                    const today = new Date().toLocaleDateString('pt-BR', { day: '2-digit', month: '2-digit', year: '2-digit' });
                    
                    let newNoteText = '';
                    
                    // Verifica se já existe entrada para hoje
                    if (!notes.includes(today)) {
                        newNoteText = `\n---------------- ${today} ----------------\n\n`;
                    }
                    
                    newNoteText += `${data.username}: ${noteText}\n`;
                    
                    technicalNotes.value += newNoteText;
                    document.getElementById('newNote').value = '';
                    technicalNotes.scrollTop = technicalNotes.scrollHeight;
                } else {
                    alert('Erro ao salvar nota: ' + data.message);
                }
            } catch (error) {
                console.error('Erro:', error);
                alert('Erro ao salvar nota técnica');
            }
        }

        document.getElementById('newNote').addEventListener('keypress', function(event) {
            if (event.key === 'Enter' && !event.shiftKey) {
                event.preventDefault();
                addNote();
            }
        });

        // Status da Ordem
        const statusButton = document.getElementById('statusButton');
        const statusFlow = ['Não iniciada', 'Em andamento', 'Concluída', 'Pronto e avisado', 'Entregue'];
        
        function updateButtonAppearance(button, status, prefix = 'status') {
            button.className = 'action-button ' + prefix + '-button';
            const statusClass = `${prefix}-${status.toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, "").replace(/ /g, '-')}`;
            button.classList.add(statusClass);
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
    <script>
        import React, { useState } from 'react';
        import { Clock, AlertCircle, CheckCircle, Phone, Package, Tool } from 'lucide-react';

        // Timeline de Status
        const StatusTimeline = ({ currentStatus }) => {
        const statuses = [
            { id: 'new', label: 'Não iniciada', icon: Clock },
            { id: 'progress', label: 'Em andamento', icon: Tool },
            { id: 'complete', label: 'Concluída', icon: CheckCircle },
            { id: 'ready', label: 'Pronto e avisado', icon: Phone },
            { id: 'delivered', label: 'Entregue', icon: Package }
        ];

        const getCurrentIndex = () => statuses.findIndex(s => s.label === currentStatus);

        return (
            <div className="flex items-center justify-between w-full mb-6 px-4">
            {statuses.map((status, idx) => {
                const Icon = status.icon;
                const isActive = idx <= getCurrentIndex();
                return (
                <div key={status.id} className="flex flex-col items-center">
                    <div className={`w-10 h-10 rounded-full flex items-center justify-center mb-2 
                    ${isActive ? 'bg-blue-500 text-white' : 'bg-gray-200'}`}>
                    <Icon size={20} />
                    </div>
                    <span className="text-sm text-gray-600">{status.label}</span>
                    {idx < statuses.length - 1 && (
                    <div className={`h-0.5 w-24 mt-5 -ml-12 
                        ${isActive ? 'bg-blue-500' : 'bg-gray-200'}`} />
                    )}
                </div>
                );
            })}
            </div>
        );
        };

        // Notas Técnicas Melhoradas
        const TechnicalNotes = ({ notes }) => {
        const [newNote, setNewNote] = useState('');
        const [searchTerm, setSearchTerm] = useState('');

        return (
            <div className="bg-white rounded-lg shadow p-4">
            <div className="mb-4">
                <div className="relative">
                <input
                    type="text"
                    placeholder="Pesquisar nas notas..."
                    className="w-full px-4 py-2 border rounded-lg"
                    value={searchTerm}
                    onChange={(e) => setSearchTerm(e.target.value)}
                />
                <div className="absolute right-3 top-2.5 text-gray-400">🔍</div>
                </div>
            </div>
            
            <div className="mb-4 max-h-96 overflow-y-auto">
                {notes.map((note, idx) => (
                <div key={idx} className="mb-4 p-3 bg-gray-50 rounded-lg">
                    <div className="flex justify-between items-center mb-2">
                    <span className="font-medium text-blue-600">{note.username}</span>
                    <span className="text-sm text-gray-500">{note.formatted_date}</span>
                    </div>
                    <p className="text-gray-700">{note.note}</p>
                </div>
                ))}
            </div>

            <div className="border-t pt-4">
                <textarea
                className="w-full p-3 border rounded-lg mb-2"
                placeholder="Digite sua nota técnica..."
                rows="3"
                value={newNote}
                onChange={(e) => setNewNote(e.target.value)}
                />
                <div className="flex gap-2">
                <button className="px-4 py-2 bg-blue-500 text-white rounded-lg flex items-center gap-2">
                    <span>Adicionar Nota</span>
                </button>
                <button className="px-4 py-2 border rounded-lg text-gray-600">
                    Usar Template
                </button>
                </div>
            </div>
            </div>
        );
        };
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>