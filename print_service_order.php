<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <!-- [Headers anteriores permanecem os mesmos] -->
    <style>
        /* [Estilos anteriores permanecem os mesmos] */

        /* Novo estilo para o container de senha */
        .password-container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            margin-bottom: 8px;
        }

        /* Estilo para o padrão de senha */
        .pattern-box {
            border: 1px solid #ccc;
            padding: 5px;
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 5px;
            aspect-ratio: 1;
            width: 100%;
            max-width: 100px;
            margin-left: auto;
        }

        .pattern-dot {
            aspect-ratio: 1;
            border: 2px solid #666;
            border-radius: 50%;
            margin: auto;
            width: 80%;
        }

        /* Ajuste para o campo de senha numérica */
        .password-field {
            flex: 1;
        }

        .half-width {
            width: calc(100% - 10px);
        }
    </style>
</head>
<body>
    <!-- [Conteúdo anterior permanece o mesmo até o campo de senha] -->

    <!-- Substitua o campo de senha original por este: -->
    <div class="field">
        <div class="field-label">Senhas:</div>
        <div class="password-container">
            <div class="field-value half-width">
                <?php echo htmlspecialchars($order['device_password']); ?>
            </div>
            <div class="pattern-box">
                <?php for($i = 0; $i < 9; $i++): ?>
                    <div class="pattern-dot"></div>
                <?php endfor; ?>
            </div>
        </div>
    </div>

    <!-- [Resto do conteúdo permanece o mesmo] -->
</body>
</html>