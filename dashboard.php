<?php
session_start();

// Verifica se está logado
if(!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    <style>
        .container {
            padding-top: 2rem;
            padding-bottom: 2rem;
        }
        
        .dashboard-container {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            padding: 20px;
            margin-top: 20px;
        }

        .welcome-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
        }

        .nav-button {
            margin-bottom: 15px;
            padding: 15px;
            text-align: left;
            font-size: 1.1em;
        }

        .nav-button i {
            margin-right: 10px;
        }

        .logout-btn {
            position: absolute;
            top: 20px;
            right: 20px;
        }

        .user-info {
            background-color: #f8f9fa;
            padding: 10px 20px;
            border-radius: 5px;
            display: inline-block;
        }
    </style>
</head>
<body class="bg-light">
    <a href="logout.php" class="btn btn-outline-danger logout-btn">
        <i class="bi bi-box-arrow-right"></i> Sair
    </a>

    <div class="container">
        <div class="dashboard-container">
            <div class="welcome-header">
                <h2><i class="bi bi-grid-1x2"></i> Sistema Interno Tele Dil</h2>
                <div class="user-info">
                    <i class="bi bi-person-circle"></i>
                    Bem-vindo, <?php echo htmlspecialchars($_SESSION['username']); ?>
                </div>
            </div>

            <div class="row">
                <!-- <div class="col-md-6">
                    <a href="index.php" class="btn btn-outline-primary w-100 nav-button">
                        <i class="bi bi-box-arrow-in-right"></i>
                        Página de Login
                    </a>
                </div> -->
                <form class="row row-cols-lg-auto g-3 align-items-center">
      <div class="col-2">
        <div class="input-group">  
          <input type="text" class="form-control" placeholder="Número da ordem ou nome do cliente">
        </div>
      </div>
        <button type="submit" class="btn btn-primary">Procurar</button>
    </form>
                <div class="col-md-6">
                    <a href="service_order.php" class="btn btn-outline-success w-100 nav-button">
                    <i class="bi bi-file-earmark-text"></i>
                    Nova Ordem de Serviço
                    </a>
                </div>

                <div class="col-md-6">
                    <a href="new_user.php" class="btn btn-outline-info w-100 nav-button">
                        <i class="bi bi-person-plus"></i>
                        Cadastrar Novo Usuário
                    </a>
                </div>
                
                <div class="col-md-6">
                    <a href="clientes.php" class="btn btn-outline-success w-100 nav-button">
                        <i class="bi bi-person-lines-fill"></i>
                        Cadastrar Clientes
                    </a>
                </div>

                <div class="col-md-6">
                    <a href="users.php" class="btn btn-outline-info w-100 nav-button">
                        <i class="bi bi-people"></i>
                        Lista de Usuários
                    </a>
                </div>

                <div class="col-md-6">
                    <a href="consulta_ordens.php" class="btn btn-outline-info w-100 nav-button">
                        <i class="bi bi-people"></i>
                        Lista de Ordens
                    </a>
                </div>

                <!-- <div class="col-md-6">
                    <a href="config.php" class="btn btn-outline-secondary w-100 nav-button">
                        <i class="bi bi-gear"></i>
                        Configurações do Banco
                    </a>
                </div> -->

                <!-- <div class="col-md-6">
                    <a href="add_user.php" class="btn btn-outline-warning w-100 nav-button">
                        <i class="bi bi-person-plus-fill"></i>
                        Adicionar Usuário (Script)
                    </a>
                </div> -->

                <!-- <div class="col-md-6">
                    <a href="logout.php" class="btn btn-outline-danger w-100 nav-button">
                        <i class="bi bi-box-arrow-right"></i>
                        Logout
                    </a>
                </div> -->
            </div>

            <div class="mt-4 p-3 bg-light rounded">
                <h5><i class="bi bi-info-circle"></i> Abertas recentemente:</h5>
                <ul class="list-group mt-2">
                    <li class="list-group-item"><code>15000</code> - G8 Power Lite</li>
                    <li class="list-group-item"><code>13500</code> - Iphone 11</li>
                    <li class="list-group-item"><code>14200</code> - LG K51s</li>
                    <!-- <li class="list-group-item"><code>config.php</code> - Configurações do Banco</li>
                    <li class="list-group-item"><code>add_user.php</code> - Script para Adicionar Usuário</li>
                    <li class="list-group-item"><code>dashboard.php</code> - Este Painel de Controle</li>
                    <li class="list-group-item"><code>logout.php</code> - Script de Logout</li> -->
                </ul>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>