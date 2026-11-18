<?php
session_start();
if (!isset($_SESSION["usuario_id"])) { header("Location: ../login.php"); exit(); }
if (!isset($_SESSION["simulacao_id"])) { header("Location: simulacao.php"); exit(); }
require_once(__DIR__ . "/../../src/config/banco.php");
require_once(__DIR__ . "/../../src/config/seguranca.php");
require_once(__DIR__ . "/../../src/Controllers/ConsumosController.php");
$id = (int)$_SESSION["simulacao_id"];
if (!simulacaoPertenceAoUsuario($conexao, $id, (int)$_SESSION["usuario_id"])) { unset($_SESSION["simulacao_id"]); http_response_code(403); exit("Simulação indisponível."); }
$controller = new ConsumosController(new ConsumosRepository($conexao));
$mensagem = "";
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (!validarCsrf()) { http_response_code(403); $mensagem = "Formulário expirado. Recarregue a página."; }
    else {
        $categoria = $_POST["categoria"] ?? "";
        $quantidade = $_POST["quantidade"] ?? "";
        if (!is_numeric($quantidade) || ($novoId = $controller->adicionar($id, $categoria, (float)$quantidade)) === false) $mensagem = "Informe uma categoria e uma quantidade válida maior que zero.";
        else $mensagem = "Alimentação registrada. Emissão estimada: " . number_format((float)$quantidade * $controller->categorias()[$categoria], 2, ",", ".") . " kg CO₂e.";
    }
}
$stmtAlimentacao = mysqli_prepare($conexao, "SELECT categoria, quantidade, emissao_co2 FROM consumos WHERE id_simulacao = ? AND categoria NOT LIKE 'transporte:%' ORDER BY id_consumo ASC");
mysqli_stmt_bind_param($stmtAlimentacao, "i", $id);
mysqli_stmt_execute($stmtAlimentacao);
$itensAlimentacao = mysqli_fetch_all(mysqli_stmt_get_result($stmtAlimentacao), MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Alimentação - Simulador de Carbono</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .dashboard{width:calc(100% - 2rem);max-width:760px;margin:1rem auto;padding:clamp(1.25rem,4vw,2.5rem);align-self:flex-start;color:#183528}
        .dashboard header{gap:1rem;flex-wrap:wrap;margin-bottom:1.5rem}.dashboard header h1{font-size:clamp(1.35rem,4vw,2rem)}
        .dashboard main{gap:1rem}.dashboard main>p{margin:0;line-height:1.6;color:#46564d}
        .etapas{display:flex;gap:.5rem;list-style:none;padding:0;margin:0 0 .25rem}.etapas li{flex:1;padding:.65rem .5rem;text-align:center;border-radius:999px;background:#edf2ee;color:#526158;font-size:.9rem}.etapas li[aria-current=step]{background:#1d6845;color:#fff;font-weight:700}
        .form-etapa{display:grid;gap:.65rem;max-width:520px}.form-etapa label{font-weight:700}.form-etapa input,.form-etapa select{width:100%;min-height:46px;padding:.7rem;border:1px solid #9aa99f;border-radius:8px;background:#fff;color:#183528;font:inherit}.form-etapa input:focus,.form-etapa select:focus,.dashboard a:focus-visible,.dashboard button:focus-visible{outline:3px solid #e0a927;outline-offset:3px}
        .campo-unidade{display:grid;grid-template-columns:minmax(0,1fr) auto;align-items:center;gap:.65rem}.unidade{font-weight:700;color:#46564d}
        .dashboard button{min-height:44px;padding:.7rem 1rem;border:0;border-radius:8px;background:#1d6845;color:#fff;font:inherit;font-weight:700;cursor:pointer}.navegacao{display:flex;flex-wrap:wrap;gap:.75rem}.navegacao button,.navegacao a{background:#e7eee9;color:#183528}.navegacao a{display:inline-flex;align-items:center;min-height:44px;padding:.7rem 1rem;border-radius:8px;font-weight:700}.mensagem{padding:.85rem 1rem;border-radius:8px;background:#edf2ee;border-left:4px solid #1d6845;line-height:1.5}.mensagem-erro{background:#fef0f0;border-left-color:#b42318;color:#7a1712}
        .secao-alimentacao{display:grid;gap:.8rem;padding:1rem;border-radius:12px;background:#f6f8f6;border:1px solid #dce5de}.secao-alimentacao h3{margin:0;color:#183528}.lista-alimentacao{display:grid;gap:.65rem;list-style:none;padding:0;margin:0}.item-alimentacao{display:grid;grid-template-columns:minmax(0,1fr) auto;gap:.25rem .75rem;padding:.85rem;background:#fff;border:1px solid #e0e7e2;border-radius:8px}.item-alimentacao strong{color:#183528}.item-alimentacao span{color:#46564d}.item-alimentacao .emissao{grid-column:1/-1;font-size:.92rem}.vazio{margin:0;color:#526158}
        @media(max-width:520px){.dashboard{width:calc(100% - 1rem);padding:1.1rem}.dashboard header{align-items:flex-start}.etapas li{font-size:.75rem;padding:.55rem .2rem}}
    </style>
</head>
<body>
<div class="dashboard">
    <header>
        <h1>🌱 Simulador de Crédito de Carbono</h1>
        <a href="energias.php">Voltar para energia</a>
    </header>
    <main>
        <ol class="etapas" aria-label="Etapas da simulação">
            <li>1. Transporte</li><li>2. Energia</li><li aria-current="step">3. Alimentação</li>
        </ol>
        <h2>Alimentação</h2>
        <p>Informe o alimento e a quantidade consumida, em kg, no período da simulação. Os fatores são médias globais de referência de Poore e Nemecek (2018), processadas por Our World in Data, com ano-base 2010. Incluem etapas da produção e da cadeia até o varejo; não representam medições específicas do Brasil. <a href="https://ourworldindata.org/grapher/ghg-per-kg-poore" target="_blank" rel="noopener noreferrer">Ver fonte e metodologia</a>.</p>
        <?php if ($mensagem !== ""): ?><p class="mensagem <?= str_starts_with($mensagem, 'Alimentação registrada') ? 'mensagem-sucesso' : 'mensagem-erro' ?>" role="status" aria-live="polite"><?= htmlspecialchars($mensagem, ENT_QUOTES, 'UTF-8') ?></p><?php endif; ?>
        <form method="post" class="form-etapa">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(tokenCsrf(), ENT_QUOTES, 'UTF-8') ?>">
            <label for="categoria">Alimento</label>
            <select name="categoria" id="categoria" required>
                <?php foreach ($controller->categorias() as $categoria => $fator): ?>
                    <option value="<?= htmlspecialchars($categoria, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars(ucwords(str_replace('_', ' ', $categoria)), ENT_QUOTES, 'UTF-8') ?> (<?= number_format($fator, 2, ",", ".") ?> kg CO₂e/kg)</option>
                <?php endforeach; ?>
            </select>
            <label for="quantidade">Quantidade consumida</label>
            <div class="campo-unidade">
                <input type="number" name="quantidade" id="quantidade" min="0.01" step="0.01" required aria-describedby="unidade-quantidade">
                <span class="unidade" id="unidade-quantidade">kg</span>
            </div>
            <button type="submit">Adicionar alimento</button>
        </form>
        <section class="secao-alimentacao" aria-labelledby="titulo-alimentacao-adicionada">
            <h3 id="titulo-alimentacao-adicionada">Alimentos adicionados</h3>
            <?php if (empty($itensAlimentacao)): ?>
                <p class="vazio">Ainda não há alimentos registrados nesta simulação.</p>
            <?php else: ?>
                <ul class="lista-alimentacao">
                    <?php foreach ($itensAlimentacao as $item): ?>
                        <li class="item-alimentacao">
                            <strong><?= htmlspecialchars(ucwords(str_replace('_', ' ', $item["categoria"])), ENT_QUOTES, 'UTF-8') ?></strong>
                            <span><?= number_format((float)$item["quantidade"], 2, ",", ".") ?> kg</span>
                            <span class="emissao">Emissão estimada: <?= number_format((float)$item["emissao_co2"], 3, ",", ".") ?> kg CO₂e</span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </section>
        <nav class="navegacao" aria-label="Navegação da simulação"><a href="simulacao.php">Concluir e ver total</a></nav>
    </main>
</div>
</body>
</html>
