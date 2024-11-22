<?php
session_start();
require_once 'config.php';

if(!isset($_SESSION['user_id']) || !isset($_GET['id'])) {
    header("Location: index.php");
    exit;
}

$database = new Database();
$db = $database->getConnection();

try {
    // Busca os dados da ordem de serviço
    $query = "SELECT so.*, c.name as client_name, c.phone1, c.phone2 
              FROM service_orders so 
              JOIN clients c ON so.client_id = c.id 
              WHERE so.id = ?";
    $stmt = $db->prepare($query);
    $stmt->execute([$_GET['id']]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$order) {
        throw new Exception("Ordem de serviço não encontrada");
    }
} catch(Exception $e) {
    die("Erro: " . $e->getMessage());
}

// Formata a data para o padrão brasileiro
$delivery_date = date("d/m/Y", strtotime($order['delivery_date']));
?>
<?php
session_start();
require_once 'config.php';

if(!isset($_SESSION['user_id']) || !isset($_GET['id'])) {
    header("Location: index.php");
    exit;
}

$database = new Database();
$db = $database->getConnection();

try {
    $query = "SELECT so.*, c.name as client_name, c.phone1, c.phone2 
              FROM service_orders so 
              JOIN clients c ON so.client_id = c.id 
              WHERE so.id = ?";
    $stmt = $db->prepare($query);
    $stmt->execute([$_GET['id']]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$order) {
        throw new Exception("Ordem de serviço não encontrada");
    }
} catch(Exception $e) {
    die("Erro: " . $e->getMessage());
}

$delivery_date = date("d/m/Y", strtotime($order['delivery_date']));
?>
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
            padding: 0;
        }

        .container {
            width: 21cm;
            margin: 0 auto;
            height: 14.85cm;
            max-height: 14.85cm;
            overflow: hidden;
            transform: scale(1);
            transform-origin: top center;
            position: relative;
            box-sizing: border-box;
            padding: 5px 10px;
        }

        /* All other styles remain exactly the same until .disclaimer */

        .disclaimer {
            font-size: 9px;
            margin: 10px 0;
            text-align: justify;
            line-height: 1.2;
            margin-bottom: 15px; /* Added space before cut line */
        }

        .cut-line {
            width: 100%;
            height: 0;
            border-bottom: 1px dashed #999;
            position: absolute;
            bottom: 0;
            left: 0;
        }

        @media print {
            body {
                padding: 0;
                margin: 0;
            }

            .no-print {
                display: none;
            }

            .container {
                padding: 5px 10px;
                page-break-after: always;
                height: 148.5mm; /* Exactly half of A4 */
            }

            @page {
                size: A4;
                margin: 0;
            }
        }
    </style>
</head>
<body>
    <!-- All content remains exactly the same until the final div -->
    <div class="container">
        <!-- All previous content remains exactly the same -->
        
        <div class="cut-line"></div>
    </div>
</body>
</html>