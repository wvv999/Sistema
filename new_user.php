<?php
session_start();
require_once 'config.php';

// Verifica se está logado (proteção da página)
if(!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$database = new Database();
$db = $database->getConnection();

// Processamento do formulário de cadastro
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];
    
    try {
        // Verifica se usuário já existe
        $check = $db->prepare("SELECT id FROM users WHERE username = ?");
        $check->execute([$username]);
        
        if ($check->rowCount() > 0) {
            $error = "Este usuário já existe!";
        } else {
            // Cria o novo usuário
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $db->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
            
            if ($stmt->execute([$username, $hashed_password])) {
                $success = "Usuário criado com sucesso!";
            } else {
                $error = "Erro ao criar usuário.";
            }
        }
    } catch(PDOException $e) {
        $error = "Erro no banco de dados: " . $e->getMessage();
    }
}

// Busca todos os usuários
try {
    $query = "SELECT id, username, created_at FROM users ORDER BY created_at DESC";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $users = $stmt->fetchAll();
} catch(PDOException $e) {
    $error = "Erro ao buscar usuários: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciamento de Usuários</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    <style>
        .container {
            padding-top: 2rem;
            padding-bottom: 2rem;
        }
        
        .content-container {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            padding: 20px;
            margin-top: 20px;
        }

        .register-form {
            max-width: 400px;
            margin: 0 auto 2rem auto;
        }

        .header-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .user-count {
            background-color: #f8f9fa;
            padding: 10px 20px;
            border-radius: 5px;
            font-weight: bold;
        }

        .table > tbody > tr > td {
            vertical-align: middle;
        }

        .logout-btn {
            position: absolute;
            top: 20px;
            right: 20px;
        }

        .divider {
            margin: 2rem 0;
            border-top: 2px solid #eee;
        }
    </style>
</head>
<body class="bg-light">
    <a href="dashboard.php" class="btn btn-outline-primary" style="position: absolute; top: 20px; left: 20px;">
        <i class="bi bi-arrow-left"></i> Voltar
    </a>

    <div class="container">
        <div class="content-container">
            <!-- Formulário de Cadastro -->
            <div class="register-form">
                <h2 class="text-center mb-4">Cadastrar Novo Usuário</h2>
                
                <?php if (isset($error)): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <?php if (isset($success)): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>

                <form method="POST" action="">
                    <div class="mb-3">
                        <label for="username" class="form-label">Usuário</label>
                        <input type="text" class="form-control" id="username" name="username" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="password" class="form-label">Senha</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                    
                    <button type="submit" class="btn btn-primary w-100">Cadastrar</button>
                </form>
            </div>

            <div class="divider"></div>

            <!-- Lista de Usuários -->
            <div class="header-container">
                <h2>Usuários Cadastrados</h2>
                <div class="user-count">
                    Total de usuários: <?php echo count($users); ?>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th scope="col">#</th>
                            <th scope="col">Usuário</th>
                            <th scope="col">Data de Cadastro</th>
                            <th scope="col">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                            <tr>
                                <td><?php echo $user['id']; ?></td>
                                <td>
                                    <i class="bi bi-person-circle me-2"></i>
                                    <?php echo htmlspecialchars($user['username']); ?>
                                </td>
                                <td>
                                    <i class="bi bi-calendar-event me-2"></i>
                                    <?php echo date('d/m/Y', strtotime($user['created_at'])); ?>
                                </td>
                                <td>
                                    <?php if ($user['id'] == $_SESSION['user_id']): ?>
                                        <span class="badge bg-success">Online</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Offline</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>