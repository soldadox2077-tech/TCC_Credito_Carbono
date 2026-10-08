<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

session_start();

if (!isset($_SESSION["usuario_id"])) {

    header("Location: ../login.php");
    exit();

}

require_once(__DIR__ . "/../../src/config/banco.php");

require_once(__DIR__ . "/../../src/repositories/SimulacoesRepository.php");
require_once(__DIR__ . "/../../src/Controllers/SimulacoesController.php");

require_once(__DIR__ . "/../../src/repositories/TransportesRepository.php");
require_once(__DIR__ . "/../../src/Controllers/TransportesController.php");


$simulacoesRepository = new SimulacoesRepository($conexao);

$simulacoesController = new SimulacoesController(
    $simulacoesRepository
);


$repository = new TransportesRepository($conexao);

$controller = new TransportesController(
    $repository
);


/*
 * Cria uma nova simulação somente
 * quando o usuário começa uma nova simulação.
 */

if (!isset($_SESSION["simulacao_id"])) {

    $id_simulacao = $simulacoesController->criar(
        $_SESSION["usuario_id"]
    );

    $_SESSION["simulacao_id"] = $id_simulacao;
}


$id_simulacao = $_SESSION["simulacao_id"];

$mensagem = "";


/*
 * Processamento do formulário
 */

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $tipo_transporte = $_POST["tipo_transporte"];
    $distancia_km = $_POST["distancia_km"];


    if ($distancia_km <= 0) {

        $mensagem = "A distância deve ser maior que zero.";

    } else {

        $resultado = $controller->adicionarTransporte(
            $id_simulacao,
            $tipo_transporte,
            $distancia_km
        );


        if ($resultado !== false) {

            $mensagem = "Transporte adicionado com sucesso!";

        } else {

            $mensagem = "Erro ao adicionar transporte.";

        }
    }
}


/*
 * Busca todos os transportes
 * dessa simulação.
 */

$transportes = $controller->listarPorSimulacao(
    $id_simulacao
);

?>

<!DOCTYPE html>
<html lang="pt-br">

<head>

    <meta charset="UTF-8">

    <title>Transportes - Simulador de Carbono</title>

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
                Transporte
            </h2>

            <p>
                Informe os meios de transporte utilizados e a distância percorrida.
            </p>

            <form method="POST">

                <label for="tipo_transporte">
                    Tipo de transporte:
                </label>

                <select name="tipo_transporte" id="tipo_transporte" required>

                    <option value="">
                        Selecione
                    </option>

                    <option value="carro">
                        Carro
                    </option>

                    <option value="motocicleta">
                        Motocicleta
                    </option>

                    <option value="onibus">
                        Ônibus
                    </option>

                    <option value="metro">
                        Metrô
                    </option>

                    <option value="bicicleta">
                        Bicicleta
                    </option>

                    <option value="a_pe">
                        A pé
                    </option>

                </select>

                <br><br>

                <label for="distancia_km">
                    Distância percorrida:
                </label>

                <input
                    type="number"
                    name="distancia_km"
                    id="distancia_km"
                    step="0.01"
                    min="0"
                    required
                >

                <span>km</span>

                <br><br>

                <button type="submit">
                    Adicionar transporte
                </button>

            </form>

        </main>

    </div>

</body>

</html>