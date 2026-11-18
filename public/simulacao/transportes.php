<?php

session_start();

if (!isset($_SESSION["usuario_id"])) {

    header("Location: ../login.php");
    exit();

}

require_once(__DIR__ . "/../../src/config/banco.php");
require_once(__DIR__ . "/../../src/config/seguranca.php");

require_once(__DIR__ . "/../../src/repositories/TransportesRepository.php");
require_once(__DIR__ . "/../../src/Controllers/TransportesController.php");


$repository = new TransportesRepository($conexao);

$controller = new TransportesController(
    $repository
);


/*
 * Recupera a simulação que já foi criada
 * em simulacao.php.
 */

if (!isset($_SESSION["simulacao_id"])) {

    header("Location: simulacao.php");
    exit();

}

$id_simulacao = (int) $_SESSION["simulacao_id"];
$id_usuario = (int) $_SESSION["usuario_id"];
if (!simulacaoPertenceAoUsuario($conexao, $id_simulacao, $id_usuario)) { unset($_SESSION["simulacao_id"]); http_response_code(403); exit("Simulação indisponível."); }

$mensagem = "";


/*
 * Processamento do formulário
 */

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    if (!validarCsrf()) { http_response_code(403); $mensagem = "Formulário expirado. Recarregue a página."; }

    $tipo_transporte = $_POST["tipo_transporte"] ?? "";
    $distancia_km = $_POST["distancia_km"] ?? null;


    if ($mensagem !== "") {
        // Requisição recusada: token CSRF inválido.
    } elseif (!is_numeric($distancia_km) || (float)$distancia_km <= 0 || (float)$distancia_km > 100000 || !in_array($tipo_transporte, ["carro", "motocicleta", "onibus", "metro", "bicicleta", "a_pe"], true)) {

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
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        .dashboard{width:calc(100% - 2rem);max-width:760px;margin:1rem auto;padding:clamp(1.25rem,4vw,2.5rem);align-self:flex-start;color:#183528}
        .dashboard header{gap:1rem;flex-wrap:wrap;margin-bottom:1.5rem}.dashboard header h1{font-size:clamp(1.35rem,4vw,2rem)}
        .dashboard main{gap:1rem}.dashboard main>p{margin:0;line-height:1.6;color:#46564d}
        .etapas{display:flex;gap:.5rem;list-style:none;padding:0;margin:0 0 .25rem}.etapas li{flex:1;padding:.65rem .5rem;text-align:center;border-radius:999px;background:#edf2ee;color:#526158;font-size:.9rem}.etapas li[aria-current=step]{background:#1d6845;color:#fff;font-weight:700}
        .form-etapa{display:grid;gap:.65rem;max-width:520px}.form-etapa label{font-weight:700}.form-etapa input,.form-etapa select{width:100%;min-height:46px;padding:.7rem;border:1px solid #9aa99f;border-radius:8px;background:#fff;color:#183528;font:inherit}.form-etapa input:focus,.form-etapa select:focus,.dashboard a:focus-visible,.dashboard button:focus-visible{outline:3px solid #e0a927;outline-offset:3px}
        .campo-unidade{display:grid;grid-template-columns:minmax(0,1fr) auto;align-items:center;gap:.65rem}.unidade{font-weight:700;color:#46564d}
        .dashboard button{min-height:44px;padding:.7rem 1rem;border:0;border-radius:8px;background:#1d6845;color:#fff;font:inherit;font-weight:700;cursor:pointer}.navegacao{display:flex;flex-wrap:wrap;gap:.75rem}.navegacao button,.navegacao a{background:#e7eee9;color:#183528}.navegacao a{display:inline-flex;align-items:center;min-height:44px;padding:.7rem 1rem;border-radius:8px;font-weight:700}.mensagem{padding:.85rem 1rem;border-radius:8px;background:#edf2ee;border-left:4px solid #1d6845;line-height:1.5}.mensagem-erro{background:#fef0f0;border-left-color:#b42318;color:#7a1712}
        .secao-transportes{display:grid;gap:.8rem;padding:1rem;border-radius:12px;background:#f6f8f6;border:1px solid #dce5de}.secao-transportes h3{margin:0;color:#183528}.lista-transportes{display:grid;gap:.65rem;list-style:none;padding:0;margin:0}.item-transporte{display:grid;grid-template-columns:minmax(0,1fr) auto;gap:.25rem .75rem;padding:.85rem;background:#fff;border:1px solid #e0e7e2;border-radius:8px}.item-transporte strong{color:#183528}.item-transporte span{color:#46564d}.item-transporte .emissao{grid-column:1/-1;font-size:.92rem}.vazio{margin:0;color:#526158}
        @media(max-width:520px){.dashboard{width:calc(100% - 1rem);padding:1.1rem}.dashboard header{align-items:flex-start}.etapas li{font-size:.75rem;padding:.55rem .2rem}}
    </style>

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

            <ol class="etapas" aria-label="Etapas da simulação">
                <li aria-current="step">1. Transporte</li><li>2. Energia</li><li>3. Alimentação</li>
            </ol>

            <h2>
                Transporte
            </h2>

            <p>
                Informe os meios de transporte utilizados e a distância percorrida.
            </p>

            <form method="POST" class="form-etapa">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(tokenCsrf(), ENT_QUOTES, 'UTF-8') ?>">

                <label for="tipo_transporte">
                    Tipo de transporte:
                </label>

                <select name="tipo_transporte" id="tipo_transporte" aria-describedby="ajuda-transporte" required>

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
                <small id="ajuda-transporte">Selecione cada meio usado durante o período da simulação. Você pode adicionar mais de um.</small>

                <label for="distancia_km">
                    Distância percorrida:
                </label>

                <div class="campo-unidade">
                    <input type="number" name="distancia_km" id="distancia_km" step="0.01" min="0" required aria-describedby="unidade-distancia">
                    <span class="unidade" id="unidade-distancia">km</span>
                </div>

                <button type="submit">
                    Adicionar transporte
                </button>

            </form>

            <section class="secao-transportes" aria-labelledby="titulo-transportes-adicionados">
                <h3 id="titulo-transportes-adicionados">Transportes adicionados</h3>
                <?php if (empty($transportes)): ?>
                    <p class="vazio">Ainda não há meios de transporte nesta simulação.</p>
                <?php else: ?>
                    <ul class="lista-transportes">
                        <?php
                        $nomesTransportes = [
                            "carro" => "Carro",
                            "motocicleta" => "Motocicleta",
                            "onibus" => "Ônibus",
                            "metro" => "Metrô",
                            "bicicleta" => "Bicicleta",
                            "a_pe" => "A pé"
                        ];
                        foreach ($transportes as $transporte):
                            $tipo = $transporte["tipo_transporte"];
                        ?>
                            <li class="item-transporte">
                                <strong><?= htmlspecialchars($nomesTransportes[$tipo] ?? $tipo, ENT_QUOTES, 'UTF-8') ?></strong>
                                <span><?= number_format((float)$transporte["distancia_km"], 2, ",", ".") ?> km</span>
                                <span class="emissao">Emissão estimada: <?= number_format((float)$transporte["emissao_co2"], 3, ",", ".") ?> kg CO₂e</span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </section>

            <?php if ($mensagem !== ""): ?>
                <p class="mensagem <?= str_starts_with($mensagem, 'Transporte adicionado') ? 'mensagem-sucesso' : 'mensagem-erro' ?>" role="status" aria-live="polite"><?= htmlspecialchars($mensagem, ENT_QUOTES, 'UTF-8') ?></p>
            <?php endif; ?>

            <form action="energias.php" method="GET" class="navegacao">

                <button type="submit">
                    Continuar para consumo de energia
                </button>
            </form>

        </main>

    </div>

</body>

</html>
