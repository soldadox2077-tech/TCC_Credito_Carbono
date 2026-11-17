<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

session_start();

/* 1. Impede que usuários não autenticados acessem a simulação. */
if (!isset($_SESSION["usuario_id"])) {
    header("Location: ../login.php");
    exit();
}

/* 2. Carrega conexão, repositório e controlador. */
require_once(__DIR__ . "/../../src/config/banco.php");
require_once(__DIR__ . "/../../src/repositories/TransportesRepository.php");
require_once(__DIR__ . "/../../src/Controllers/TransportesController.php");

/* 3. Recupera a simulação criada anteriormente em simulacao.php. */
if (!isset($_SESSION["simulacao_id"])) {
    header("Location: simulacao.php");
    exit();
}

$id_simulacao = (int) $_SESSION["simulacao_id"];

$repository = new TransportesRepository($conexao);
$controller = new TransportesController($repository);

$mensagem = "";
$tipo_mensagem = "";

/* 4. Processa o formulário quando o usuário adiciona um deslocamento. */
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $id_tipo_transporte = filter_input(
        INPUT_POST,
        "id_tipo_transporte",
        FILTER_VALIDATE_INT
    );

    $distancia_km = $_POST["distancia_km"] ?? null;

    if (!$id_tipo_transporte || !is_numeric($distancia_km)) {
        $mensagem = "Selecione um transporte e informe uma distância válida.";
        $tipo_mensagem = "erro";
    } elseif (!is_finite((float) $distancia_km) || (float) $distancia_km <= 0) {
        $mensagem = "A distância deve ser um número maior que zero.";
        $tipo_mensagem = "erro";
    } else {
        /*
         * O controlador consulta o fator no banco.
         * Não recebemos fator de emissão pelo HTML, evitando adulteração.
         */
        $resultado = $controller->adicionarTransporte(
            $id_simulacao,
            $id_tipo_transporte,
            $distancia_km
        );

        if ($resultado !== false) {
            $mensagem = "Transporte adicionado com sucesso!";
            $tipo_mensagem = "sucesso";
        } else {
            $mensagem = "Não foi possível registrar o transporte. Verifique o catálogo e tente novamente.";
            $tipo_mensagem = "erro";
        }
    }
}

/* 5. Busca as opções do catálogo e os deslocamentos já registrados. */
$tipos_transportes = $controller->listarTiposTransportes();
$transportes = $controller->listarPorSimulacao($id_simulacao);

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
            <h1>🌱 Simulador de Crédito de Carbono</h1>
            <a href="simulacao.php">Voltar para simulação</a>
        </header>

        <main>

            <h2>Transporte</h2>

            <p>
                Informe os meios de transporte utilizados e a distância
                percorrida por você, em quilômetros.
            </p>

            <p>
                <strong>Unidade:</strong> os fatores estão expressos em
                kg CO₂e por passageiro-quilômetro.
            </p>

            <?php if ($mensagem !== ""): ?>
                <p>
                    <?php echo htmlspecialchars($mensagem, ENT_QUOTES, 'UTF-8'); ?>
                </p>
            <?php endif; ?>

            <?php if (count($tipos_transportes) > 0): ?>

                <form method="POST">

                    <label for="id_tipo_transporte">Tipo de transporte:</label>

                    <select name="id_tipo_transporte" id="id_tipo_transporte" required>
                        <option value="">Selecione</option>

                        <?php foreach ($tipos_transportes as $tipo): ?>
                            <option value="<?php echo (int) $tipo["id_tipo_transporte"]; ?>">
                                <?php echo htmlspecialchars($tipo["nome"], ENT_QUOTES, 'UTF-8'); ?>
                            </option>
                        <?php endforeach; ?>

                    </select>

                    <br><br>

                    <label for="distancia_km">Distância percorrida:</label>

                    <input
                        type="number"
                        name="distancia_km"
                        id="distancia_km"
                        step="0.01"
                        min="0.01"
                        required
                    >

                    <span>km</span>

                    <br><br>

                    <button type="submit">Adicionar transporte</button>

                </form>

            <?php else: ?>

                <p>
                    O catálogo de transportes está vazio ou indisponível.
                    Verifique se o script SQL foi executado.
                </p>

            <?php endif; ?>

            <section>
                <h3>Transportes registrados nesta simulação</h3>

                <?php if (count($transportes) > 0): ?>

                    <ul>
                        <?php foreach ($transportes as $transporte): ?>
                            <li>
                                <strong>
                                    <?php echo htmlspecialchars($transporte["tipo_transporte"], ENT_QUOTES, 'UTF-8'); ?>
                                </strong>:
                                <?php echo number_format((float) $transporte["distancia_km"], 2, ",", "."); ?> km —
                                <?php echo number_format((float) $transporte["emissao_co2e"], 4, ",", "."); ?> kg CO₂e
                                <br>
                                <small>
                                    Fator aplicado:
                                    <?php echo number_format((float) $transporte["fator_emissao_aplicado"], 8, ",", "."); ?>
                                    kg CO₂e/passageiro-km.
                                    Referência: <?php echo htmlspecialchars($transporte["fonte"], ENT_QUOTES, 'UTF-8'); ?>,
                                    <?php echo (int) $transporte["ano_referencia"]; ?>.
                                    Escopo geográfico: <?php echo htmlspecialchars($transporte["escopo_geografico"], ENT_QUOTES, 'UTF-8'); ?>.
                                </small>
                            </li>
                        <?php endforeach; ?>
                    </ul>

                <?php else: ?>

                    <p>Nenhum transporte foi registrado nesta simulação.</p>

                <?php endif; ?>
            </section>

            <br><br>

            <form action="energias.php" method="GET">
                <button type="submit">Continuar para consumo de energia</button>
            </form>

        </main>

    </div>

</body>
</html>
