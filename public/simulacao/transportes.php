<?php
session_start();
if (!isset($_SESSION["usuario_id"])) { header("Location: ../login.php"); exit(); }
if (!isset($_SESSION["simulacao_id"])) { header("Location: simulacao.php"); exit(); }
require_once(__DIR__ . "/../../src/config/banco.php");
require_once(__DIR__ . "/../../src/config/seguranca.php");
require_once(__DIR__ . "/../../src/repositories/TransportesRepository.php");
require_once(__DIR__ . "/../../src/Controllers/TransportesController.php");
$id_simulacao = (int)$_SESSION["simulacao_id"];
if (!simulacaoPertenceAoUsuario($conexao, $id_simulacao, (int)$_SESSION["usuario_id"])) { unset($_SESSION["simulacao_id"]); http_response_code(403); exit("Simulação indisponível."); }
$controller = new TransportesController(new TransportesRepository($conexao));
$mensagem = "";
$tipo_mensagem = "erro";
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $id_tipo_transporte = filter_var($_POST["id_tipo_transporte"] ?? null, FILTER_VALIDATE_INT);
    $distancia_km = $_POST["distancia_km"] ?? null;
    if (!validarCsrf()) {
        http_response_code(403);
        $mensagem = "Formulário expirado. Recarregue a página.";
    } elseif (!$id_tipo_transporte || !is_numeric($distancia_km) || !is_finite((float)$distancia_km) || (float)$distancia_km <= 0) {
        $mensagem = "Selecione um meio de transporte ativo e informe uma distância válida.";
    } else {
        $resultado = $controller->adicionarTransporte($id_simulacao, $id_tipo_transporte, $distancia_km);
        if ($resultado !== false) {
            $mensagem = "Transporte adicionado com sucesso!";
            $tipo_mensagem = "sucesso";
        } else {
            $mensagem = "Não foi possível registrar o transporte. Confira o tipo selecionado e a distância.";
        }
    }
}
$tipos_transportes = $controller->listarTiposTransportes();
$transportes = $controller->listarPorSimulacao($id_simulacao);
$erro_catalogo = ($tipos_transportes === false);
$erro_listagem = ($transportes === false);
if ($erro_catalogo) $tipos_transportes = [];
if ($erro_listagem) $transportes = [];
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Transporte - Simulador de Carbono</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .dashboard{width:calc(100% - 2rem);max-width:760px;margin:1rem auto;padding:clamp(1.25rem,4vw,2.5rem);align-self:flex-start;color:#183528}
        .dashboard header{gap:1rem;flex-wrap:wrap;margin-bottom:1.5rem}.dashboard header h1{font-size:clamp(1.35rem,4vw,2rem)}.dashboard main{gap:1rem}.dashboard main>p{margin:0;line-height:1.6;color:#46564d}
        .etapas{display:flex;gap:.5rem;list-style:none;padding:0;margin:0 0 .25rem}.etapas li{flex:1;padding:.65rem .5rem;text-align:center;border-radius:999px;background:#edf2ee;color:#526158;font-size:.9rem}.etapas li[aria-current=step]{background:#1d6845;color:#fff;font-weight:700}
        .form-etapa{display:grid;gap:.65rem;max-width:520px;padding:1.1rem;border-radius:12px;background:#f6f8f6;border:1px solid #dce5de}.form-etapa label{font-weight:700}.form-etapa input,.form-etapa select{width:100%;min-height:46px;padding:.7rem;border:1px solid #9aa99f;border-radius:8px;background:#fff;color:#183528;font:inherit}.form-etapa input:focus,.form-etapa select:focus,.dashboard a:focus-visible,.dashboard button:focus-visible{outline:3px solid #e0a927;outline-offset:3px}.form-etapa small{line-height:1.5;color:#526158}
        .campo-unidade{display:grid;grid-template-columns:minmax(0,1fr) auto;align-items:center;gap:.65rem}.unidade{font-weight:700;color:#46564d}.dashboard button{min-height:44px;padding:.7rem 1rem;border:0;border-radius:8px;background:#1d6845;color:#fff;font:inherit;font-weight:700;cursor:pointer}.navegacao{display:flex;flex-wrap:wrap;gap:.75rem}.navegacao button{background:#e7eee9;color:#183528}.mensagem{padding:.85rem 1rem;border-radius:8px;background:#eaf5ed;border-left:4px solid #24834c;color:#174c2e;line-height:1.5}.mensagem.erro{background:#fef0f0;border-left-color:#b42318;color:#7a1712}
        .secao-transportes{display:grid;gap:.8rem;padding:1rem;border-radius:12px;background:#f6f8f6;border:1px solid #dce5de}.secao-transportes h3{margin:0;color:#183528}.lista-transportes{display:grid;gap:.65rem;list-style:none;padding:0;margin:0}.item-transporte{display:grid;gap:.3rem;padding:.85rem;background:#fff;border:1px solid #e0e7e2;border-radius:8px}.item-transporte strong{color:#183528}.item-transporte span,.item-transporte small{color:#46564d;line-height:1.5}.vazio{margin:0;color:#526158}
        @media(max-width:520px){.dashboard{width:calc(100% - 1rem);padding:1.1rem}.dashboard header{align-items:flex-start}.etapas li{font-size:.75rem;padding:.55rem .2rem}}
    </style>
</head>
<body>
<div class="dashboard">
    <header><h1>🌱 Simulador de Crédito de Carbono</h1><a href="simulacao.php">Voltar para simulação</a></header>
    <main>
        <ol class="etapas" aria-label="Etapas da simulação"><li aria-current="step">1. Transporte</li><li>2. Energia</li><li>3. Alimentação</li></ol>
        <h2>Transporte</h2>
        <p>Informe cada meio utilizado e a distância percorrida no período da simulação.</p>
        <p><strong>Unidade:</strong> os fatores ativos são expressos em kg CO₂e por passageiro-quilômetro. Os dados de referência e o escopo aparecem junto a cada registro.</p>
        <?php if ($mensagem !== ""): ?><p class="mensagem <?= htmlspecialchars($tipo_mensagem, ENT_QUOTES, 'UTF-8') ?>" role="status" aria-live="polite"><?= htmlspecialchars($mensagem, ENT_QUOTES, 'UTF-8') ?></p><?php endif; ?>
        <?php if ($erro_catalogo): ?>
            <p class="mensagem erro" role="alert">Não foi possível consultar o catálogo de transportes.</p>
        <?php elseif ($tipos_transportes): ?>
            <form method="POST" class="form-etapa">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(tokenCsrf(), ENT_QUOTES, 'UTF-8') ?>">
                <label for="id_tipo_transporte">Tipo de transporte</label>
                <select name="id_tipo_transporte" id="id_tipo_transporte" aria-describedby="ajuda-transporte" required>
                    <option value="">Selecione</option>
                    <?php foreach ($tipos_transportes as $tipo): ?>
                        <option value="<?= (int)$tipo["id_tipo_transporte"] ?>"><?= htmlspecialchars($tipo["nome"], ENT_QUOTES, 'UTF-8') ?></option>
                    <?php endforeach; ?>
                </select>
                <small id="ajuda-transporte">Você pode adicionar mais de um meio. A lista inclui apenas fatores ativos no catálogo.</small>
                <label for="distancia_km">Distância percorrida</label>
                <div class="campo-unidade"><input type="number" name="distancia_km" id="distancia_km" step="0.01" min="0.01" max="99999999.9999" required aria-describedby="unidade-distancia"><span id="unidade-distancia" class="unidade">km</span></div>
                <button type="submit">Adicionar transporte</button>
            </form>
        <?php else: ?>
            <p>Não há tipos de transporte ativos no catálogo. Os fatores pendentes de validação permanecem indisponíveis.</p>
        <?php endif; ?>
        <section class="secao-transportes" aria-labelledby="titulo-transportes">
            <h3 id="titulo-transportes">Transportes adicionados</h3>
            <?php if ($erro_listagem): ?>
                <p class="mensagem erro" role="alert">Não foi possível consultar os transportes registrados.</p>
            <?php elseif ($transportes): ?>
                <ul class="lista-transportes">
                    <?php foreach ($transportes as $transporte): ?>
                        <li class="item-transporte">
                            <strong><?= htmlspecialchars($transporte["tipo_transporte"], ENT_QUOTES, 'UTF-8') ?> — <?= number_format((float)$transporte["distancia_km"], 2, ",", ".") ?> km</strong>
                            <span>Emissão estimada: <?= number_format((float)$transporte["emissao_co2e"], 4, ",", ".") ?> kg CO₂e</span>
                            <small>Fator: <?= number_format((float)$transporte["fator_emissao_aplicado"], 8, ",", ".") ?> <?= htmlspecialchars($transporte["unidade_referencia"], ENT_QUOTES, 'UTF-8') ?>. Fonte: <?= htmlspecialchars($transporte["fonte"], ENT_QUOTES, 'UTF-8') ?> (<?= (int)$transporte["ano_referencia"] ?>). Escopo: <?= htmlspecialchars($transporte["escopo_geografico"], ENT_QUOTES, 'UTF-8') ?>.</small>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <p class="vazio">Ainda não há meios de transporte nesta simulação.</p>
            <?php endif; ?>
        </section>
        <form action="energias.php" method="GET" class="navegacao"><button type="submit">Continuar para consumo de energia</button></form>
    </main>
</div>
</body>
</html>
