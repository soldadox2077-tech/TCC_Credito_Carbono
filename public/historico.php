<?php
session_start();
if (!isset($_SESSION["usuario_id"])) { header("Location: login.php"); exit(); }
require_once(__DIR__ . "/../src/config/banco.php");
$stmt = mysqli_prepare($conexao, "SELECT id_simulacao, emissao_total, data_simulacao FROM simulacoes WHERE id_usuario = ? ORDER BY data_simulacao DESC, id_simulacao DESC");
$idUsuario = (int)$_SESSION["usuario_id"];
mysqli_stmt_bind_param($stmt, "i", $idUsuario);
mysqli_stmt_execute($stmt);
$simulacoes = mysqli_fetch_all(mysqli_stmt_get_result($stmt), MYSQLI_ASSOC);
?>
<!DOCTYPE html><html lang="pt-br"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Histórico de simulações</title><link rel="stylesheet" href="assets/css/dashboard.css"><link rel="stylesheet" href="assets/css/style.css"></head><body><div class="dashboard"><header><h1>Histórico</h1><a href="dashboard.php">Voltar</a></header><main><?php if (!$simulacoes): ?><p>Você ainda não tem simulações.</p><?php else: ?><table><thead><tr><th>Simulação</th><th>Data</th><th>Emissão total (kg CO₂e)</th></tr></thead><tbody><?php foreach ($simulacoes as $simulacao): ?><tr><td><?= (int)$simulacao["id_simulacao"] ?></td><td><?= htmlspecialchars($simulacao["data_simulacao"], ENT_QUOTES, 'UTF-8') ?></td><td><?= htmlspecialchars($simulacao["emissao_total"], ENT_QUOTES, 'UTF-8') ?></td></tr><?php endforeach; ?></tbody></table><?php endif; ?></main></div></body></html>
