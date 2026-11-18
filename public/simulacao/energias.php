<?php

session_start();

if (!isset($_SESSION["usuario_id"])) {

    header("Location: ../login.php");
    exit();

}

if (!isset($_SESSION["simulacao_id"])) {

    header("Location: simulacao.php");
    exit();

}

// Recupera o ID da simulação já criada
$id_simulacao = (int) $_SESSION["simulacao_id"];

require_once(__DIR__ . "/../../src/config/banco.php");
require_once(__DIR__ . "/../../src/config/seguranca.php");
$id_usuario = (int) $_SESSION["usuario_id"];
if (!simulacaoPertenceAoUsuario($conexao, $id_simulacao, $id_usuario)) { unset($_SESSION["simulacao_id"]); http_response_code(403); exit("Simulação indisponível."); }
require_once(__DIR__ . "/../../src/repositories/EnergiasRepository.php");
require_once(__DIR__ . "/../../src/Controllers/EnergiasController.php");

$repository = new EnergiasRepository($conexao);

$controller = new EnergiasController($repository);

$mensagem = "";

/*
 * Processamento do formulário
 */

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    if (!validarCsrf()) { http_response_code(403); $mensagem = "Formulário expirado. Recarregue a página."; }

    // Verifica se o campo foi enviado e contém um número válido
    if ($mensagem !== "") {
        // Requisição recusada: token CSRF inválido.
    } elseif (
        !isset($_POST["consumo_kwh"]) ||
        !is_numeric($_POST["consumo_kwh"]) || (float)$_POST["consumo_kwh"] > 100000000
    ) {

        $mensagem = "Informe um consumo válido.";

    } else {

        $consumo_kwh = (float) $_POST["consumo_kwh"];

        if ($consumo_kwh <= 0) {

            $mensagem = "O consumo não pode ser negativo.";

        } else {

            // Insere o consumo na simulação já existente
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

}

?>

<!DOCTYPE html>
<html lang="pt-br">

<head>

    <meta charset="UTF-8">

    <title>Energia - Simulador de Carbono</title>

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
        .orientacao-periodo{padding:.85rem 1rem;border-radius:8px;background:#f2f6f3;border:1px solid #dce5de;color:#46564d}.cartao-consumo{padding:1.1rem;border-radius:12px;background:#f6f8f6;border:1px solid #dce5de}.form-etapa{width:100%}.form-etapa button{justify-self:start;min-width:190px}.mensagem-sucesso{padding:1rem 1.1rem;background:#eaf5ed;border-left:5px solid #24834c;color:#174c2e;font-size:1.05rem;font-weight:700}.mensagem-erro{padding:1rem 1.1rem;font-weight:600}.navegacao{justify-content:space-between;align-items:center}.navegacao .voltar button{background:#e7eee9;color:#183528}.navegacao .proxima button{background:#1d6845;color:#fff}
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
                <li>1. Transporte</li><li aria-current="step">2. Energia</li><li>3. Alimentação</li>
            </ol>

            <h2>
                ⚡ Consumo de Energia
            </h2>

            <p>
                Informe o consumo total de eletricidade correspondente ao período considerado na sua simulação.
            </p>

            <p class="orientacao-periodo" id="orientacao-periodo">
                Use o mesmo intervalo de tempo nas etapas. Por exemplo, se estiver calculando um mês, informe o consumo mensal indicado na conta de luz.
            </p>

            <section class="cartao-consumo" aria-label="Informar consumo de energia">
            <form method="POST" class="form-etapa">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(tokenCsrf(), ENT_QUOTES, 'UTF-8') ?>">

                <label for="consumo_kwh">
                    Consumo de eletricidade:
                </label>

                <div class="campo-unidade">
                    <input type="number" name="consumo_kwh" id="consumo_kwh" step="0.01" min="0" required aria-describedby="unidade-consumo orientacao-periodo">
                    <span class="unidade" id="unidade-consumo">kWh</span>
                </div>

                <button type="submit">
                    Adicionar consumo
                </button>

            </form>
            </section>

            <?php if ($mensagem != ""): ?>

                <p class="mensagem <?= str_starts_with($mensagem, 'Consumo adicionado') ? 'mensagem-sucesso' : 'mensagem-erro' ?>" role="status" aria-live="polite">
                    <?php echo htmlspecialchars($mensagem, ENT_QUOTES, 'UTF-8'); ?>
                </p>

            <?php endif; ?>

            <nav class="navegacao" aria-label="Navegação da simulação">
            <form action="transportes.php" method="GET" class="voltar">

                <button type="submit">
                    Voltar para transportes
                </button>

            </form>

            <form action="consumos.php" method="GET" class="proxima">

                <button type="submit">
                    Prosseguir para alimentação
                </button>

            </form>
            </nav>
            
        </main>

    </div>

</body>

</html>
