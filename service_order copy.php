<?php
session_start();
require_once 'config.php';

// Verifica se está logado
if(!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$success = $error = '';
$database = new Database();
$db = $database->getConnection();

// Busca todos os clientes para o select
try {
    $query = "SELECT id, name, phone1, phone2 FROM clients ORDER BY name";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $clients = $stmt->fetchAll();
} catch(Exception $e) {
    $error = "Erro ao buscar clientes: " . $e->getMessage();
}

// Processa o formulário quando enviado
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        $client_id = $_POST['client_id'];
        $phone1 = $_POST['phone1'];
        $phone2 = $_POST['phone2'];
        $delivery_date = $_POST['delivery_date'];
        $reported_issue = $_POST['reported_issue'];
        $accessories = $_POST['accessories'];
        $device_password = $_POST['device_password'];
        $pattern_password = $_POST['pattern_password'];

        $stmt = $db->prepare("INSERT INTO service_orders (client_id, phone1, phone2, delivery_date, 
                             reported_issue, accessories, device_password, pattern_password) 
                             VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        
        if ($stmt->execute([$client_id, $phone1, $phone2, $delivery_date, $reported_issue, 
                           $accessories, $device_password, $pattern_password])) {
            $success = "Ordem de serviço #" . $db->lastInsertId() . " criada com sucesso!";
        } else {
            throw new Exception("Erro ao criar ordem de serviço.");
        }
    } catch(Exception $e) {
        $error = $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nova Ordem de Serviço</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    <style>
        *{
            user-select: none;
        }
        .container { padding-top: 2rem; padding-bottom: 2rem; }
        .content-container {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            padding: 20px;
            margin-top: 20px;
        }
        .pattern-container {
            display: grid;
            grid-template-columns: repeat(3, 60px);
            gap: 10px;
            margin: 20px auto;
            width: 200px;
        }
        .pattern-dot {
            width: 60px;
            height: 60px;
            background-color: #f8f9fa;
            border: 2px solid #dee2e6;
            border-radius: 50%;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        .pattern-dot.active {
            background-color: #0d6efd;
            border-color: #0d6efd;
        }
        .pattern-line {
            position: absolute;
            background-color: #0d6efd;
            height: 2px;
            pointer-events: none;
            transform-origin: left center;
        }
    </style>
</head>
<body class="bg-light">
    <a href="dashboard.php" class="btn btn-outline-primary" style="position: absolute; top: 20px; left: 20px;">
        <i class="bi bi-arrow-left"></i> Voltar
    </a>
    
    <a href="logout.php" class="btn btn-outline-danger" style="position: absolute; top: 20px; right: 20px;">
        <i class="bi bi-box-arrow-right"></i> Sair
    </a>

    <div class="container">
        <div class="content-container">
            <h2 class="mb-4"><i class="bi bi-file-earmark-text"></i> Nova Ordem de Serviço</h2>

            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>

            <form method="POST" id="serviceOrderForm">
                <div class="row">
                    <!-- Select do Cliente com busca -->
                    <div class="col-md-6 mb-3">
                        <label for="client_id" class="form-label">Cliente</label>
                        <select class="form-select" id="client_id" name="client_id" required>
                            <option value="">Selecione um cliente</option>
                            <?php foreach ($clients as $client): ?>
                                <option value="<?php echo $client['id']; ?>" 
                                        data-phone1="<?php echo $client['phone1']; ?>"
                                        data-phone2="<?php echo $client['phone2']; ?>">
                                    <?php echo htmlspecialchars($client['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Telefones -->
                    <div class="col-md-3 mb-3">
                        <label for="phone1" class="form-label">Telefone 1</label>
                        <input type="text" class="form-control" id="phone1" name="phone1" readonly>
                    </div>

                    <div class="col-md-3 mb-3">
                        <label for="phone2" class="form-label">Telefone 2</label>
                        <input type="text" class="form-control" id="phone2" name="phone2" readonly>
                    </div>

                    <!-- Data de Entrega -->
                    <div class="col-md-6 mb-3">
                        <label for="delivery_date" class="form-label">Data de Entrega Prevista</label>
                        <input type="date" class="form-control" id="delivery_date" name="delivery_date" required>
                    </div>

                    <!-- Defeito Reclamado -->
                    <div class="col-12 mb-3">
                        <label for="reported_issue" class="form-label">Defeito Reclamado</label>
                        <textarea class="form-control" id="reported_issue" name="reported_issue" rows="3" required></textarea>
                    </div>

                    <!-- Acessórios -->
                    <div class="col-12 mb-3">
                        <label for="accessories" class="form-label">Acessórios</label>
                        <textarea class="form-control" id="accessories" name="accessories" rows="2"></textarea>
                    </div>

                    <!-- Senha Numérica -->
                    <div class="col-md-6 mb-3">
                        <label for="device_password" class="form-label">Senha do Aparelho</label>
                        <input type="text" class="form-control" id="device_password" name="device_password">
                    </div>

                    <!-- Padrão de Desenho -->
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Padrão de Desenho</label>
                        <input type="hidden" id="pattern_password" name="pattern_password">
                        <div class="pattern-container" id="patternContainer">
                            <?php for($i = 0; $i < 9; $i++): ?>
                                <div class="pattern-dot" data-index="<?php echo $i; ?>"></div>
                            <?php endfor; ?>
                        </div>
                        <button type="button" class="btn btn-secondary btn-sm d-block mx-auto" onclick="clearPattern()">
                            Limpar Padrão
                        </button>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-save"></i> Criar Ordem de Serviço
                </button>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
    // Atualiza os telefones quando um cliente é selecionado
    document.getElementById('client_id').addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        document.getElementById('phone1').value = selectedOption.dataset.phone1 || '';
        document.getElementById('phone2').value = selectedOption.dataset.phone2 || '';
    });

    // Código para o padrão de desenho
    const patternContainer = document.getElementById('patternContainer');
    const dots = document.querySelectorAll('.pattern-dot');
    const patternPassword = document.getElementById('pattern_password');
    let pattern = [];
    let isDrawing = false;
    let lines = [];

    function clearPattern() {
        pattern = [];
        patternPassword.value = '';
        dots.forEach(dot => dot.classList.remove('active'));
        lines.forEach(line => line.remove());
        lines = [];
    }

    function createLine(start, end) {
        const line = document.createElement('div');
        line.className = 'pattern-line';
        
        const rect = patternContainer.getBoundingClientRect();
        const startRect = start.getBoundingClientRect();
        const endRect = end.getBoundingClientRect();
        
        const x1 = startRect.left + startRect.width/2 - rect.left;
        const y1 = startRect.top + startRect.height/2 - rect.top;
        const x2 = endRect.left + endRect.width/2 - rect.left;
        const y2 = endRect.top + endRect.height/2 - rect.top;
        
        const length = Math.sqrt(Math.pow(x2-x1, 2) + Math.pow(y2-y1, 2));
        const angle = Math.atan2(y2-y1, x2-x1) * 180/Math.PI;
        
        line.style.width = length + 'px';
        line.style.left = x1 + 'px';
        line.style.top = y1 + 'px';
        line.style.transform = `rotate(${angle}deg)`;
        
        patternContainer.appendChild(line);
        lines.push(line);
    }

    dots.forEach(dot => {
        dot.addEventListener('mousedown', (e) => {
            isDrawing = true;
            const index = dot.dataset.index;
            if (!pattern.includes(index)) {
                pattern.push(index);
                dot.classList.add('active');
                patternPassword.value = pattern.join('');
            }
        });

        dot.addEventListener('mouseenter', (e) => {
            if (isDrawing) {
                const index = dot.dataset.index;
                if (!pattern.includes(index)) {
                    pattern.push(index);
                    dot.classList.add('active');
                    patternPassword.value = pattern.join('');
                    
                    if (pattern.length > 1) {
                        const prevDot = document.querySelector(`[data-index="${pattern[pattern.length-2]}"]`);
                        createLine(prevDot, dot);
                    }
                }
            }
        });
    });

    document.addEventListener('mouseup', () => {
        isDrawing = false;
    });

    // Define a data mínima como hoje
    document.getElementById('delivery_date').min = new Date().toISOString().split('T')[0];
    </script>
</body>
</html>