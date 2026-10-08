<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

session_start();

if (!isset($_SESSION["usuario_id"])) {

    header("Location: ../login.php");
    exit();

}

if (!isset($_SESSION["simulacao_id"])) {

    header("Location: simulacao.php");
    exit();

}

require_once(__DIR__ . "/../../src/config/banco.php");
require_once(__DIR__ . "/../../src/repositories/EnergiasRepository.php");
require_once(__DIR__ . "/../../src/Controllers/EnergiasController.php");

$repository = new EnergiasRepository($conexao);

$controller = new EnergiasController($repository);

$id_simulacao = $_SESSION["simulacao_id"];

$mensagem = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $consumo_kwh = $_POST["consumo_kwh"];

    if ($consumo_kwh < 0) {

        $mensagem = "O consumo não pode ser negativo.";

    } else {

        $resultado = $controller->adicionarEnergia(
            $id_simulacao,
            $consumo_kwh
        );

        if ($resultado !== false) {

            $emissao = $controller->calcularEmissao($consumo_kwh);

            $mensagem =
                "Consumo adicionado com sucesso! " .
                "Emissão estimada: " .
                number_format($emissao, 2, ",", ".") .
                " kg CO₂.";

        } else {

            $mensagem = "Erro ao adicionar o consumo de energia.";

        }

    }

}

?>

<!DOCTYPE html>
<html lang="pt-br">

<head>

    <meta charset="UTF-8">

    <title>Energia - Simulador de Carbono</title>

    <link rel="stylesheet" href="../assets/css/style.css">

</head>

<body>

    <div class="dashboard">

        <header>

            <h1>
                🌱 Simulador de Crédito de Carbono
            </h1>

            <a href="simulacao.php">
                Voltar para simulação
            </a>

        </header>

        <main>

            <h2>
                ⚡ Consumo de Energia
            </h2>

            <p>
                Informe o consumo de eletricidade utilizado
                durante o período da sua simulação.
            </p>

            <form method="POST">

                <label for="consumo_kwh">
                    Consumo de eletricidade:
                </label>

                <br><br>

                <input
                    type="number"
                    name="consumo_kwh"
                    id="consumo_kwh"
                    step="0.01"
                    min="0"
                    required
                >

                <span>kWh</span>

                <br><br>

                <button type="submit">
                    Adicionar consumo
                </button>

            </form>

            <?php if ($mensagem != ""): ?>

                <p>
                    <?php echo $mensagem; ?>
                </p>

            <?php endif; ?>

        </main>

    </div>

</body>

</html>