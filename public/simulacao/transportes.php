<?php

session_start();

/* 1. Impede acesso sem autenticação. */
if (!isset($_SESSION["usuario_id"])) {
    header("Location: ../login.php");
    exit();
}

/* 2. Carrega a conexão e as classes do módulo. */
require_once(__DIR__ . "/../../src/config/banco.php");
require_once(__DIR__ . "/../../src/repositories/TransportesRepository.php");
require_once(__DIR__ . "/../../src/Controllers/TransportesController.php");

/* 3. Recupera a simulação criada anteriormente. */
if (!isset($_SESSION["simulacao_id"])) {
    header("Location: simulacao.php");
    exit();
}

$id_simulacao = (int) $_SESSION["simulacao_id"];

$repository = new TransportesRepository($conexao);
$controller = new TransportesController($repository);

$mensagem = "";
$tipo_mensagem = "";

/* 4. Processa o envio do formulário. */
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
    } elseif (
        !is_finite((float) $distancia_km) ||
        (float) $distancia_km <= 0
    ) {
        $mensagem = "A distância deve ser um número maior que zero.";
        $tipo_mensagem = "erro";
    } else {
        /*
         * O controlador busca o fator no banco.
         * O navegador envia somente o ID do tipo e a distância.
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
            $mensagem = "Não foi possível registrar o transporte. Confira o tipo selecionado, a distância e a estrutura do banco.";
            $tipo_mensagem = "erro";
        }
    }
}

/* 5. Carrega opções e registros. Falha SQL não é confundida com lista vazia. */
$tipos_transportes = $controller->listarTiposTransportes();
$transportes = $controller->listarPorSimulacao($id_simulacao);

$erro_catalogo = ($tipos_transportes === false);
$erro_listagem = ($transportes === false);

if ($erro_catalogo) {
    $tipos_transportes = [];
}

if ($erro_listagem) {
    $transportes = [];
}

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
                <p class="mensagem <?php echo htmlspecialchars($tipo_mensagem, ENT_QUOTES, 'UTF-8'); ?>">
                    <?php echo htmlspecialchars($mensagem, ENT_QUOTES, 'UTF-8'); ?>
                </p>
            <?php endif; ?>

            <?php if ($erro_catalogo): ?>

                <p role="alert">
                    Não foi possível consultar o catálogo de transportes.
                    Verifique se a tabela tipos_transportes existe e se a conexão com o banco está funcionando.
                </p>

            <?php elseif (count($tipos_transportes) > 0): ?>

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
                        max="99999999.9999"
                        required
                    >

                    <span>km</span>

                    <br><br>

                    <button type="submit">Adicionar transporte</button>

                </form>

            <?php else: ?>

                <p>
                    Não há tipos de transporte ativos no catálogo.
                    Os fatores pendentes de validação permanecem indisponíveis.
                </p>

            <?php endif; ?>

            <section>
                <h3>Transportes registrados nesta simulação</h3>

                <?php if ($erro_listagem): ?>

                    <p role="alert">
                        Não foi possível consultar os transportes registrados.
                        Verifique a estrutura das tabelas e a conexão com o banco.
                    </p>

                <?php elseif (count($transportes) > 0): ?>

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
                                    <?php echo htmlspecialchars($transporte["unidade_referencia"], ENT_QUOTES, 'UTF-8'); ?>.
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
