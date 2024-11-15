<?php
require_once 'config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $database = new Database();
    $db = $database->getConnection();
    
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
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastrar Usuário</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        html, body {
            height: 100%;
            margin: 0;
        }
        .container {
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .register-container {
            width: 100%;
            max-width: 400px;
            padding: 20px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            border-radius: 8px;
            background-color: white;
        }
    </style>
</head>
<body>
    
    <div class="container">
        <div class="register-container">
            <h2 class="text-center mb-4">Cadastrar Usuário</h2>
            
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
            <a href="javascript:history.back();" class="btn btn-outline-danger logout-btn">Voltar</a>
            <i class="bi bi-box-arrow-right"></i> Sair
            </a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>